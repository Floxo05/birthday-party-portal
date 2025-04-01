#!/bin/bash

docker compose run --rm app ./vendor/bin/phpstan analyse src
