# Локальный запуск проекта Symfony

## 1. Клонирование проекта

```bash
git clone https://github.com/bars90M9lso/SymfonyApp
cd SymfonyApp
```

---

## 2. Установка зависимостей

После скачивания проекта нужно установить все зависимости:

```bash
composer install
```

---

## 3. Настройка файла `.env`

Перед запуском необходимо настроить файл `.env`.

Пример основных параметров:

```env
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=your_secret_key
APP_SHARE_DIR=var/share
###< symfony/framework-bundle ###

###> symfony/routing ###
DEFAULT_URI=http://localhost
###< symfony/routing ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_%kernel.environment%.db"
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=null://null
###< symfony/mailer ###

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
###< nelmio/cors-bundle ###
```

---

## 4. Создание базы данных

После настройки `.env` необходимо создать базу данных:

```bash
php bin/console doctrine:database:create
```

---

## 5. Выполнение миграций

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

---

## 6. Запуск локального сервера

Для запуска локального сервера выполнить команду:

```bash
symfony server:start
```

После запуска проект будет доступен по адресу:

```text
http://127.0.0.1:8000
```

Остановить сервер можно командой:

```bash
symfony server:stop
```

---

# Полезные команды Symfony

## Очистка кеша

После изменения конфигурации, сущностей или контроллеров рекомендуется очищать кеш:

```bash
php bin/console cache:clear
php bin/console cache:warmup
```

---

## Просмотр всех роутов

```bash
php bin/console debug:router
```

---

## Создание контроллера

```bash
php bin/console make:controller NameController
```

Контроллеры сохраняются в папке:

```text
/src/Controller
```

---

# Работа с базой данных

## Установка Doctrine ORM

Если ORM еще не установлен:

```bash
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```

---

## Настройка подключения к БД

Symfony поддерживает несколько типов БД.

## MySQL

```env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0.37"
```

## MariaDB

```env
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=10.5.8-MariaDB"
```

## SQLite

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"
```

## PostgreSQL

```env
DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=16&charset=utf8"
```

---

## Создание сущностей

```bash
php bin/console make:entity
```

Сущности сохраняются в папке:

```text
/src/Entity
```

---

## Создание миграций

После изменения сущностей необходимо создать миграцию:

```bash
php bin/console make:migration
```

Миграции сохраняются в папке:

```text
/ migrations
```

---

## Применение миграций

```bash
php bin/console doctrine:migrations:migrate
```

---

## Создание связей между сущностями

Связи (`OneToMany`, `ManyToOne`, `ManyToMany`) можно создавать через:

```bash
php bin/console make:entity
```

При создании нового поля нужно выбрать тип `relation` и следовать инструкциям консоли.

---

# Работа с API Platform

При использовании API Platform API создаются автоматически на основе сущностей.

Пример:

```php
#[ApiResource]
class Product
{
}
```

Также можно:

* создавать собственные контроллеры;
* настраивать операции `GET`, `POST`, `PATCH`, `DELETE`.

Документация API обычно доступна по адресу:

```text
/api
```

---

# Работа с EasyAdmin

Для создания административной панели используется EasyAdmin.

## Создание Dashboard

```bash
php bin/console make:admin:dashboard
```

После этого создается основной Dashboard, в котором настраиваются:

* меню;
* панели;
* маршруты;
* отображение сущностей.

---

# Создание CRUD для сущности

```bash
php bin/console make:admin:crud
```

---
# Пример готового сайта

Демонстрационная версия проекта доступна по ссылке:

🔗 http://f1266753.xsph.ru/

---

# Тестовые аккаунты

## Администратор

| Параметр | Значение |
|---|---|
| Логин | `admin` |
| Пароль | `fooo` |

---

## Пользователь

| Параметр | Значение |
|---|---|
| Логин | `bars` |
| Пароль | `1234` |

---

