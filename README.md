# МЕГАЛАБА

Лабораторную работу сделали студенты 3 группы ПМИ:
### Маркова Анастасия 
### Черанёва Анастасия

# Инструкция:

1. Скачать проект:

```
git clone https://github.com/Slash20/Project_PHP_Zachet
```

2. Установить необходимые зависимости:

```
composer install
```

3. Запустить проект в папке, куда был перемещен проект:

```
docker-compose -f docker-compose.yaml up --build
```

# Поддерживаются такие операции как:

Загрузка валюты:

```
php bin/console app:import-exchange-rates
```

Запуск тестов:

```
php bin/phpunit
```