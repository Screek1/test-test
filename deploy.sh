#!/bin/bash
composer install #--no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate -n
yarn install
yarn encore prod
php bin/console cache:clear -e prod