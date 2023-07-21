# start.ps1

docker-compose -f docker-compose.test.yml up --build -d


php bin/console doctrine:schema:update --force --complete
# php bin/console --env=test doctrine:schema:update --force --complete
php bin/console doctrine:fixtures:load --no-interaction
# php bin/console --env=test doctrine:fixtures:load --no-interaction
