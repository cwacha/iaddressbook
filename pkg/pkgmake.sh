#!/bin/sh

TMP=`pwd`; cd `dirname $0`; BASEDIR=`pwd`; cd $TMP

SVNBASEDIR=..
REV=`svn info -R $BASEDIR/$SVNBASEDIR 2>/dev/null | grep "Last Changed Rev" | awk '{print $4}' | sort -nr | head -1`
SVN_REVISION=${SVN_REVISION:-$REV}

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
	rm -rf BUILD/lib/php/SabreDAV/composer*
	chmod 777 BUILD/conf
	chmod 777 BUILD/var/state
	chmod 777 BUILD/var/images
	chmod 777 BUILD/var/import
	echo "`head -1 BUILD/VERSION` (Rev: $SVN_REVISION)" > BUILD/VERSION
	#touch BUILD/iaddressbook-$VERSION-$SVN_REVISION-manifest
}

pkg() {
	echo "##### packaging"
	VERSION=`head -1 BUILD/VERSION | sed 's/[ 	].*//g'`
	
	PKG=iaddressbook-$VERSION
	mv BUILD $PKG
	tar cfz $PKG+$SVN_REVISION.tar.gz $PKG
	zip -r $PKG+$SVN_REVISION.zip $PKG >/dev/null
}

clean() {
	echo "##### cleaning"
	rm -rf BUILD
	rm -rf iaddressbook-*
}

deploy() {
	echo "##### deploying"
	
	if [ ! -f .deploy-urls ]; then
		echo "Creating empty file .deploy-urls ..."
		echo "# ftp://user:pass@www.domain.com/subdomain/folder" > .deploy-urls  
	fi
	
	IFS="
"
	for line in `grep -v "^#" .deploy-urls`; do
		$BASEDIR/ftp_upload.sh $line iaddressbook-*/*
	done
}

if [ $# -eq 0 ]; then
	echo "Usage: $0 <action>"
	echo
	echo "ACTIONS:"
	declare -F | awk '{print "   "$3}' | grep -v usage
	exit
fi

action="$1"
shift

declare -F "$action" >/dev/null
[ $? -ne 0 ] && echo "no such action: $action" && exit 1

cd $BASEDIR

$action $*
echo "##### done"

