#!/bin/sh

FTP_DMODE=${FTP_DMODE:-0777}
FTP_FMODE=${FTP_FMODE:-0666}

parse_url() {
	URL=`echo "$1" | sed -n 's/\(ftp:..\)\{0,1\}\(.*\)/\2/p'`
	URI=`echo "$URL " | sed 's/\// /' | cut -d" " -f2`
	UHOST=`echo "$URL " | sed 's/\// /' | cut -d" " -f1`

	UPASS=`echo "$UHOST@" | cut -d@ -f1`
	HOST=`echo "$UHOST@" | cut -d@ -f2`
	[ -z "$HOST" ] && HOST=$UPASS && UPASS=

	PORT=`echo "$HOST:" | cut -d: -f2`
	HOST=`echo "$HOST:" | cut -d: -f1`
	[ -z "$PORT" ] && PORT=21

	USER=`echo "$UPASS:" | cut -d: -f1`
	PASS=`echo "$UPASS:" | cut -d: -f2`

	FTP_USER=$USER
	FTP_PASS=$PASS
	FTP_HOST=$HOST
	FTP_URI=$URI
	FTP_PORT=$PORT
	
	#echo "user=$FTP_USER pass=$FTP_PASS host=$FTP_HOST port=$FTP_PORT uri=$FTP_URI"
	[ -z "$FTP_HOST" ] && echo "ERROR: no target host specified" && usage && exit 1
}

add_ftpcmd() {
	LF='
'
	FTP_COMMANDS="$FTP_COMMANDS$@$LF"
}

ftp_tryconnect() {
	add_ftpcmd "open $FTP_HOST $FTP_PORT"
	add_ftpcmd "user $FTP_USER $FTP_PASS"
	add_ftpcmd "bye"

	run_ftp
}

ftp_connect() {
	add_ftpcmd "open $FTP_HOST $FTP_PORT"
	add_ftpcmd "user $FTP_USER $FTP_PASS"
	add_ftpcmd "binary"
	#add_ftpcmd "verbose"
	#add_ftpcmd "debug 9"
	[ -n "$FTP_URI" ] && add_ftpcmd "cd $FTP_URI"
}

ftp_commit() {
	add_ftpcmd "bye"

	run_ftp

	ERRORS=$?
	[ $ERRORS -eq 0 ] && echo "FTP transfer successfully completed."
	[ $ERRORS -ne 0 ] && echo "FTP transfer completed with errors."

	return $ERRORS 
}

run_ftp() {
	#echo "$FTP_COMMANDS"
	echo "$FTP_COMMANDS" | ftp -n | tee ftp.log
	ERRORS=`grep -v "Directory not empty" ftp.log | grep -v "File exists" | wc -l`
	
	rm ftp.log
	return $ERRORS
}

ftp_putfile() {
	local_file="$1"
	remote_file="${2:-`basename $local_file`}"
	add_ftpcmd "put \"${local_file}\" \"${remote_file}.part\""
	add_ftpcmd "chmod $FTP_FMODE \"${remote_file}.part\""
	add_ftpcmd "rename \"${remote_file}.part\" \"${remote_file}\""
}

ftp_putdir() {
	echo "Scanning directory: $1"
	local_dir="$1"
	basepath=`dirname $local_dir`/

	IFS='
'
	for dir in `find $local_dir -type d`; do
		remote_dir=${dir#$basepath}
		add_ftpcmd "mkdir \"$remote_dir\""
		add_ftpcmd "chmod $FTP_DMODE \"$remote_dir\""
	done

	for file in `find $local_dir -type f`; do
		remote_file=${file#$basepath}
		ftp_putfile "$file" "$remote_file"
	done
}

usage() {
	echo "usage: $0 [URL] [FILES]"
	echo
	echo "upload files or directories via FTP to URL"
	echo "URL format: ftp://user:pass@example.com/target/dir"
}

[ $# -lt 2 ] && usage && exit 1

parse_url "$1"
shift

echo "Starting upload: user=$FTP_USER pass=***** host=$FTP_HOST port=$FTP_PORT uri=$FTP_URI"
ftp_tryconnect || exit 1

ftp_connect
for opt in "$@"; do
	[ -f "$opt" ] && ftp_putfile "$opt"
	[ -d "$opt" ] && ftp_putdir "$opt"
done
echo "Scanning complete."
ftp_commit

