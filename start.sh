#!/bin/bash

# start.sh

composer install
yarn install
yarn build

docker-compose up -d

container_id=$(docker-compose ps -q php)

docker exec "$container_id" php bin/console doctrine:schema:update --force
docker exec "$container_id" php bin/console doctrine:fixtures:load --no-interaction
