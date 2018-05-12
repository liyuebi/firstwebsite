#!/bin/sh
#backup

this_dir=`dirname $0`
cd $this_dir

timestamp="`date +%y%m%d%H%M`"
path="/root/dump"

if [ ! -d "$path" ] ;then
	exit 1
elif [ ! -x "$path" ] ;then
	exit 2
fi

folderpath=${path}"/"${timestamp}
mkdir $folderpath

#dump database
sqldumpfile=${path}"/"${timestamp}"/"${timestamp}".sql"
mysqldump -u root -p"123456789" mifeng_db > $sqldumpfile

#copy license pic
cp -a ./olLicensePic $folderpath 

#copy qrCode pic
cp -a ./olqrc $folderpath

#copy poster pic
cp -a ./poster $folderpath

#copy product package pic
cp -a ./pPackPic $folderpath

#compress folder
cd $path
tar czf ${timestamp}".tar.gz" $timestamp
rm -rf $timestamp
