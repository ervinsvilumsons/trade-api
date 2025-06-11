#!/bin/sh

echo "Waiting for PgSQL..."

while ! nc -z $DB_HOST $DB_PORT; do
  echo "PgSQL is down"
  sleep 2
done

echo "PgSQL is up"

php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php bin/console doctrine:database:create --env=test --no-interaction
php bin/console doctrine:migrations:migrate --env=test --no-interaction

exec "$@"
