#!/bin/bash

composer selfupdate
composer install --no-interaction --no-scripts --quiet
mkdir -p vendor/ionut && mkdir -p vendor/ionut/frod && mv `ls -A | grep -v vendor` ./vendor/ionut/frod
cp vendor/ionut/frod/travis-composer.json composer.json
composer dumpautoload --quiet