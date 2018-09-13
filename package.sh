#!/bin/sh

composer install --no-dev

sed -i "s/$this->version = '1.0.0'/$this->version = '${TRAVIS_TAG#"v"}'/g" onepay.php
sed -i "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" config.xml
sed -i "s/\[1.0.0\]/\[${TRAVIS_TAG#"v"}\]/g" config_es.xml

mkdir .onepay
cp -R * .onepay
mv .onepay onepay 

zip -r9 "onepay-$TRAVIS_TAG.zip" onepay -x composer.json composer.lock docker-compose.yml docs/\* package.sh README.md

rm -Rf onepay
