#!/bin/sh

#FTP_TARGET=${FTP_TARGET:-example.com}
#FTP_USER=${FTP_USER:-guest}
#FTP_PASS=${FTP_PASS:-password}
#FTP_TARGETDIR=${FTP_TARGETDIR:-httpdocs}

FTP_DMODE=${FTP_DMODE:-0777}
FTP_FMODE=${FTP_FMODE:-0666}

parse_url() {
	read FTP_USER FTP_PASS FTP_TARGET FTP_TARGETDIR << EOF
		`echo "$1" | sed -n 's/ftp:\/\/\([^:]*\):\([^@]*\)@\([^\/]*\)\/\(.*\)/\1 \2 \3 \4/p'`
EOF
	[ -z "$FTP_TARGET" ] && echo "ERROR: no target host specified" && usage && exit 1
}

add_ftpcmd() {
	LF='
'
	FTP_COMMANDS="$FTP_COMMANDS$@$LF"
}

ftp_open() {
	add_ftpcmd "open $FTP_TARGET"
	add_ftpcmd "user $FTP_USER $FTP_PASS"
	add_ftpcmd "binary"
	#add_ftpcmd "verbose"
	#add_ftpcmd "debug 9"
	[ -n "$FTP_TARGETDIR" ] && add_ftpcmd "cd $FTP_TARGETDIR"
}

ftp_commit() {
	add_ftpcmd "bye"

	#echo "$FTP_COMMANDS"
	echo "$FTP_COMMANDS" | ftp -n | tee ftp.log
	ERRORS=`grep -v "Directory not empty" ftp.log | wc -l`
	[ $ERRORS -eq 0 ] && echo "FTP transfer successfully completed."
	[ $ERRORS -ne 0 ] && echo "FTP transfer failed."

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

ftp_open
for opt in "$@"; do
	[ -f "$opt" ] && ftp_putfile "$opt"
	[ -d "$opt" ] && ftp_putdir "$opt"
done
ftp_commit

