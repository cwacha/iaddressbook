#!/bin/sh

SFTP_DMODE=${SFTP_DMODE:-0777}
SFTP_FMODE=${SFTP_FMODE:-0666}

parse_url() {
	URL=`echo "$1" | sed 's#^sftp://##'`
	UHOST=`echo "$URL" | cut -d/ -f1`
	URI=`echo "$URL/" | sed "s#^[^/]*/##" | sed 's#/$##'`

	UPASS=`echo "$UHOST@" | cut -d@ -f1`
	HOST=`echo "$UHOST@" | cut -d@ -f2`
	[ -z "$HOST" ] && HOST=$UPASS && UPASS=

	PORT=`echo "$HOST:" | cut -d: -f2`
	HOST=`echo "$HOST:" | cut -d: -f1`
	[ -z "$PORT" ] && PORT=21

	USER=`echo "$UPASS:" | cut -d: -f1`
	PASS=`echo "$UPASS:" | cut -d: -f2`

	SFTP_USER=$USER
	SFTP_PASS=$PASS
	SFTP_HOST=$HOST
	SFTP_URI=$URI
	SFTP_PORT=$PORT

	#echo "user=$SFTP_USER pass=$SFTP_PASS host=$SFTP_HOST port=$SFTP_PORT uri=$SFTP_URI"
	[ -z "$SFTP_HOST" ] && echo "ERROR: no target host specified" && usage && exit 1
}

add_sftpcmd() {
	LF='
'
	SFTP_COMMANDS="$SFTP_COMMANDS$@$LF"
}

run_sftp() {
	if [ -n "$SFTP_PASS" ]; then
		if ! command -v sshpass >/dev/null 2>&1; then
			echo "ERROR: password authentication requires sshpass"
			return 1
		fi
		echo "$SFTP_COMMANDS" | sshpass -p "$SFTP_PASS" sftp -P "$SFTP_PORT" -b - "$SFTP_TARGET" > sftp.log 2>&1
	else
		echo "$SFTP_COMMANDS" | sftp -P "$SFTP_PORT" -b - "$SFTP_TARGET" > sftp.log 2>&1
	fi
	ERRORS=$?

	SFTP_COMMANDS=
	return $ERRORS
}

sftp_tryconnect() {
	add_sftpcmd "pwd"
	add_sftpcmd "bye"

	run_sftp
}

sftp_connect() {
	[ -n "$SFTP_URI" ] && add_sftpcmd "cd \"$SFTP_URI\""
}

sftp_commit() {
	add_sftpcmd "bye"

	run_sftp

	ERRORS=$?
	[ $ERRORS -eq 0 ] && echo "SFTP transfer successfully completed."
	[ $ERRORS -ne 0 ] && {
		echo "SFTP transfer completed with errors."
		cat sftp.log
	}

	return $ERRORS
}

sftp_putfile() {
	local_file="$1"
	remote_file="${2:-`basename "$local_file"`}"
	add_sftpcmd "put \"$local_file\" \"$remote_file.part\""
	add_sftpcmd "chmod $SFTP_FMODE \"$remote_file.part\""
	add_sftpcmd "rename \"$remote_file.part\" \"$remote_file\""
}

sftp_putdir() {
	echo "Scanning directory: $1"
	local_dir="$1"
	basepath=`dirname "$local_dir"`/

	IFS='
'
	for dir in `find "$local_dir" -type d`; do
		remote_dir=${dir#$basepath}
		add_sftpcmd "-mkdir \"$remote_dir\""
		add_sftpcmd "chmod $SFTP_DMODE \"$remote_dir\""
	done

	for file in `find "$local_dir" -type f`; do
		remote_file=${file#$basepath}
		sftp_putfile "$file" "$remote_file"
	done
}

usage() {
	echo "usage: $0 [URL] [FILES]"
	echo
	echo "upload files or directories via SFTP to URL"
	echo "URL format: sftp://user[:password]@example.com[:port]/target/dir"
}

[ $# -lt 2 ] && usage && exit 1

parse_url "$1"
shift

if [ -n "$SFTP_USER" ]; then
	SFTP_TARGET="$SFTP_USER@$SFTP_HOST"
else
	SFTP_TARGET="$SFTP_HOST"
fi

echo "Starting upload: user=$SFTP_USER pass=***** host=$SFTP_HOST port=$SFTP_PORT uri=$SFTP_URI"
sftp_tryconnect || exit 1

sftp_connect
for opt in "$@"; do
	[ -f "$opt" ] && sftp_putfile "$opt"
	[ -d "$opt" ] && sftp_putdir "$opt"
done
echo "Scanning complete."
sftp_commit
