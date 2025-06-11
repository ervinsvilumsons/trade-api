# trade-api

### Project setup
```
git clone https://github.com/ervinsvilumsons/trade-api.git
docker-compose up -d --build
docker exec -it trade-api-workspace /bin/bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Tests
```
docker exec -it rick-and-morty-workspace /bin/bash
vendor/bin/phpunit --coverage-html coverage
```
