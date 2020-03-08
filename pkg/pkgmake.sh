#!/bin/sh

TMP=`pwd`; cd `dirname $0`; BASEDIR=`pwd`; cd $TMP

all() {
	echo "# building: $app_pkgname"
	clean && import && pkg
}

_init() {
	app_pkgid="iaddressbook"
    app_displayname="Visual Studio Code Portable"
    app_version=`head -1 $BASEDIR/../src/VERSION | sed 's/[ 	].*//g'`
    app_revision=`git log --pretty=oneline | wc -l | xargs`
    app_build=`git rev-parse --short HEAD`

    app_pkgname="$app_pkgid-$app_version-$app_revision-$app_build"
}

import() {
	echo "##### importing"
	[ -d BUILD ] && rm -rf BUILD
	mkdir BUILD
	cp -r ../src/* BUILD
	find BUILD -depth -name ".DS_Store" -exec rm {} \;
	find BUILD -depth -name ".svn" -exec rm -rf {} \;
	rm -rf BUILD/conf/*
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
	echo "`head -1 BUILD/VERSION` (Rev: $app_revision Build: $app_build)" > BUILD/VERSION
	#touch BUILD/iaddressbook-$app_version-$app_revision-manifest
}

pkg() {
	echo "##### packaging"
	
	PKG=$app_pkgid-$app_version
	mv BUILD $PKG
	tar cfz $app_pkgname.tar.gz $PKG
	echo "# created $app_pkgname.tar.gz"
	zip -r $app_pkgname.zip $PKG >/dev/null
	echo "# created $app_pkgname.zip"
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
	declare -F | awk '{print $3}' | grep -v ^_ | awk '{print "    "$1}'
	exit
fi

action="$1"
shift

declare -F "$action" >/dev/null
[ $? -ne 0 ] && echo "no such action: $action" && exit 1

cd $BASEDIR
_init

$action $*
echo "##### done"

