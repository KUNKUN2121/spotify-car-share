#!/bin/bash

export APP_DIR=${APP_DIR:-$(pwd)}
export APP_SERVICE=${APP_SERVICE:-"laravel.test"}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}
export SAIL_USER=${SAIL_USER:-"sail"}

run_container () {
    docker-compose run --rm --no-deps -e WWWUSER=${WWWUSER} -e WWWGROUP=${WWWGROUP} --volume="${APP_DIR}:/var/www/html" ${APP_SERVICE} "$@"
}

if [ ! -e .env ]; then
    cp .env.example-sail .env
fi

run_container composer install
run_container php artisan key:generate
