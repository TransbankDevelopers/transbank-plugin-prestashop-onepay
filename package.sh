#!/bin/sh

#Script for create the plugin artifact
echo "Travis tag: $TRAVIS_TAG"

if [ "$TRAVIS_TAG" = "" ]
then
   TRAVIS_TAG='1.0.0'
fi

composer install --no-dev
composer update --no-dev

SRC_DIR="onepay"
FILE1="onepay.php"
FILE2="config.xml"
FILE3="config_es.xml"

mkdir .$SRC_DIR
cp -R * .$SRC_DIR
mv .$SRC_DIR $SRC_DIR

sed -i.bkp "s/$this->version = '1.0.0'/$this->version = '${TRAVIS_TAG#"v"}'/g" "$SRC_DIR/$FILE1"
sed -i.bkp "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" "$SRC_DIR/$FILE2"
sed -i.bkp "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" "$SRC_DIR/$FILE3"

PLUGIN_FILE="plugin-prestashop-onepay-$TRAVIS_TAG.zip"

zip -r9 $PLUGIN_FILE onepay -x $SRC_DIR/composer.json $SRC_DIR/composer.lock $SRC_DIR/docker-compose.yml $SRC_DIR/docs/\* $SRC_DIR/package.sh $SRC_DIR/docker-prestashop1/\* $SRC_DIR/.travis.yml $SRC_DIR/.travis.yml "$SRC_DIR/$FILE1.bkp" "$SRC_DIR/$FILE2.bkp" "$SRC_DIR/$FILE3.bkp" README.md

rm -Rf $SRC_DIR

echo "Plugin version: $TRAVIS_TAG"
echo "Plugin file: $PLUGIN_FILE"
