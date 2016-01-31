#!/bin/sh

# download composer.phar
curl -sS https://getcomposer.org/installer | php

# install libraries
php composer.phar install

# prepare files
cp storage/database.sqlite_default storage/database.sqlite

# database migration
php artisan migrate --seed --force
php artisan vendor:publish --provider="Owl\Providers\TwitterBootstrapServiceProvider"

# run npm build
npm i
npm run build
