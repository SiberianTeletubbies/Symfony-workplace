# Symfony-workplace
## Описание приложения
Проект для изучения Symfony

## Требования к системе
Для запуска приложения необходимы
* MySQL 5.7
* PHP7
* Composer
* Redis (для Windows взять [отсюда](https://github.com/MicrosoftArchive/redis/releases) и добавить [расширение](https://pecl.php.net/package/redis/3.1.4/windows) в PHP)
* OpenSSL (для Windows взять [отсюда](https://slproweb.com/products/Win32OpenSSL.html))
* Node.js
* Yarn

## Установка приложения
1. скопировать содержимое репозитория в локальную папку
2. открыть папку проекта в командной строке
3. установить все зависимости backend
```
composer install
```
4. создать файл **.env.local**, скопировав в него содержимое **.env**
5. получить SSH-ключи для генерации JWT-токена
```
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
6. указать секретную фразу, использованную при генерации SSH, в файле **.env.local**
7. настроить подключение к базе данных в файле **.env.local**
8. создать базу данных
```
php bin/console doctrine:database:create
```
9. выполнить миграции
```
php bin/console doctrine:migrations:migrate
```
10. установить все зависимости frontend
```
yarn install
```
11. скомпоновать файлы frontend
```
yarn encore dev
```
12. запустить сервер разработки
```
php bin/console server:run
```

## Дополнительно
Можно назначить пользователя администратором командой
```
php bin/console app:set-admin {username}
```
