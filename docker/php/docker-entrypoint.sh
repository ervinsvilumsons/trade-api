#!/bin/sh

echo "Waiting for PgSQL..."

while ! nc -z $DB_HOST $DB_PORT; do
  echo "PgSQL is down"
  sleep 2
done

echo "PgSQL is up"

php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

exec "$@"
