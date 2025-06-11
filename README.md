# trade-api

### Project setup
```
git clone https://github.com/ervinsvilumsons/trade-api.git
cp .env.dev .env
docker-compose up -d --build
```

### Tests
```
docker exec -it trade-api-workspace /bin/bash
vendor/bin/phpunit --coverage-html coverage
```
