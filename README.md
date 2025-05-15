### МЕГАЛАБА

Запустить проект

```
docker-compose -f docker-compose.yaml up --build
```

Загрузка валюты

```
php bin/console app:import-exchange-rates
```

Запуск тестов

```
php bin/phpunit
```