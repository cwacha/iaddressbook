#!/bin/sh


all() {
	clean && import && pkg
}

import() {
	echo "##### importing"
	[ -d BUILD ] && rm -rf BUILD
	mkdir BUILD
	cp -r ../src/* BUILD
	find BUILD -depth -name ".DS_Store" -exec rm {} \;
	find BUILD -depth -name ".svn" -exec rm -rf {} \;
	rm BUILD/conf/config.php
	rm BUILD/conf/auth.php
	rm BUILD/conf/localhost
	rm BUILD/_images/*
	rm BUILD/_import/*
	chmod 777 BUILD/_images
	chmod 777 BUILD/_import
	chmod 777 BUILD/conf
}

pkg() {
	echo "##### packaging"
	VERSION=`head -1 BUILD/VERSION | sed 's/[ \t]/_/g'`
	PKG=iaddressbook-$VERSION
	mv BUILD $PKG
	tar cfz $PKG.tar.gz $PKG
	zip -r $PKG.zip $PKG >/dev/null
}

clean() {
	echo "##### cleaning"
	rm -rf BUILD
	rm -rf iaddressbook-*
}

usage() {
	echo "Usage: $0 <action>"
	echo
	echo "ACTIONS:"
	declare -F | awk '{print "   "$3}' | grep -v usage
}

if [ $# -eq 0 ]; then
	usage
	exit
fi

action="$1"
shift
# eclipse support
[ "$action" = incremental ] && action=all
[ "$action" = full ] && action=all

declare -F "$action" >/dev/null
[ $? -ne 0 ] && echo "no such action: $action" && exit 1

$action $*
echo "##### done"

