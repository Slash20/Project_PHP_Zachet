# МЕГАЛАБА

Лабораторную работу сделали студенты 3 группы ПМИ:
### Маркова Анастасия 
### Черанёва Анастасия

Скачать проект:

```
git clone https://github.com/Slash20/Project_PHP_Zachet
```

Запустить проект

```
docker-compose -f docker-compose.yaml up --build
```

Загрузка валюты:

```
php bin/console app:import-exchange-rates
```

Запуск тестов:

```
php bin/phpunit
```