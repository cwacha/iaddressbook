#!/bin/sh
SVNBASEDIR=..
REVISION=`svn info -R $SVNBASEDIR 2>/dev/null | grep "Last Changed Rev" | awk '{print $4}' | sort -nr | head -1`

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
	rm -rf BUILD/conf/config.php
	rm -rf BUILD/conf/auth.php
	rm -rf BUILD/var/state/*
	rm -rf BUILD/var/images/*
	rm -rf BUILD/var/import/*

	rm -rf BUILD/lib/php/SabreDAV/docs
	rm -rf BUILD/lib/php/SabreDAV/examples
	rm -rf BUILD/lib/php/SabreDAV/tests
	rm -rf BUILD/lib/php/SabreDAV/bin
	chmod 777 BUILD/conf
	chmod 777 BUILD/var/state
	chmod 777 BUILD/var/images
	chmod 777 BUILD/var/import
	#touch BUILD/iaddressbook-$VERSION-$REVISION-manifest
}

pkg() {
	echo "##### packaging"
	VERSION=`head -1 BUILD/VERSION | sed 's/[ 	]/_/g'`
	
	PKG=iaddressbook-$VERSION
	mv BUILD $PKG
	tar cfz $PKG-$REVISION.tar.gz $PKG
	zip -r $PKG-$REVISION.zip $PKG >/dev/null
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

