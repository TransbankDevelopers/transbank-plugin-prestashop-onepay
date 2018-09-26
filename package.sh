#!/bin/sh

composer install --no-dev

sed -i "s/$this->version = '1.0.0'/$this->version = '${TRAVIS_TAG#"v"}'/g" onepay.php
sed -i "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" config.xml
sed -i "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" config_es.xml

mkdir .onepay
cp -R * .onepay
mv .onepay onepay 

zip -r9 "plugin-prestashop-onepay-$TRAVIS_TAG.zip" onepay -x onepay/composer.json onepay/composer.lock onepay/docker-compose.yml onepay/docs/\* onepay/package.sh README.md

rm -Rf onepay
