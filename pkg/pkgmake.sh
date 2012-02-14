#!/bin/sh


all() {
	import && pkg
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
}

pkg() {
	echo "##### packaging"
	VERSION=`head -1 BUILD/VERSION | sed 's/[ \t]/_/g'`
	PKG=iaddressbook-$VERSION
	mv BUILD $PKG
	tar cfz $PKG.tar.gz $PKG
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
	declare -F | awk '{print $3}' | grep -v usage
}

if [ $# -eq 0 ]; then
	usage
	exit
fi

$*
echo "##### done"

