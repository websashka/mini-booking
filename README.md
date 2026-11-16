# Mini Booking

Бронирование одной переговорной по часовым слотам (09:00–18:00 каждый день).

## Зависимости

- **Backend:** PHP 8.4+, Composer 2, Laravel 13, PostgreSQL 17 (или SQLite).
- **Frontend:** Node.js 22+, npm, Vue 3, Vuetify 4, Vite.
- **Опционально:** Docker и Docker Compose — запуск всего проекта одной командой.

PHP должен быть доступен в `PATH`: сборка фронтенда вызывает `php artisan wayfinder:generate`, чтобы сгенерировать URL маршрутов.

## Настройки окружения

Скопируйте `.env.example` в `.env`. Основные переменные:

| Переменная                                  | По умолчанию                             | Назначение                                             |
| ------------------------------------------- | ---------------------------------------- | ------------------------------------------------------ |
| `APP_KEY`                                   | —                                        | ключ приложения, генерируется командой ниже            |
| `APP_TIMEZONE`                              | `UTC`                                    | часовой пояс расписания                                |
| `DB_CONNECTION`                             | `pgsql`                                  | `pgsql` или `sqlite`                                   |
| `DB_HOST`, `DB_PORT`                        | `127.0.0.1`, `5432`                      | адрес PostgreSQL                                       |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `mini_booking`, `mini_booking`, `secret` | доступ к БД (те же значения использует `compose.yaml`) |
| `APP_PORT`, `FORWARD_DB_PORT`               | `8000`, `5432`                           | внешние порты приложения и БД в Docker                 |

Для SQLite укажите `DB_CONNECTION=sqlite` и создайте файл `database/database.sqlite`.

## Запуск через Docker

```bash
cp .env.example .env
docker compose build
```

Сгенерируйте ключ и вставьте его в `APP_KEY` в `.env`:

```bash
docker compose run --rm --no-deps --entrypoint php app artisan key:generate --show
```

```bash
docker compose up -d
```

Откройте http://localhost:8000. Миграции и заполнение расписания выполняются автоматически при старте контейнера. Вручную:

```bash
docker compose exec app php artisan migrate
```

```bash
docker compose exec app php artisan db:seed
```

## Локальный запуск

Установка:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

PostgreSQL можно поднять из compose, если нет своего:

```bash
docker compose up -d db
```

Миграции и заполнение базы (7 дней расписания по 9 слотов):

```bash
php artisan migrate
```

```bash
php artisan db:seed
```

Backend (http://localhost:8000):

```bash
php artisan serve
```

Frontend (Vite dev-сервер; для продакшен-сборки — `npm run build`):

```bash
npm run dev
```

## Тесты

Тесты используют SQLite в памяти, отдельная БД не нужна.

```bash
php artisan test
```

## Уникальность бронирования

Гарантию даёт БД: частичный уникальный индекс `UNIQUE (slot_id) WHERE status = 'active'` разрешает только одну активную бронь на слот. Отмена мягкая: бронь получает статус `cancelled` и перестаёт занимать слот. При одновременных запросах индекс пропускает ровно одну вставку, а остальные получают ошибку уникальности, которую API превращает в `409`.

## Время

Приложение работает в часовом поясе `APP_TIMEZONE` (`config('app.timezone')`). В нём сидер создаёт слоты 09:00–18:00 и сравнивает их с текущим серверным временем: слот с началом не позже текущего момента считается прошедшим. API отдаёт время в ISO 8601 со смещением, интерфейс показывает даты и время в том же поясе, а не в поясе браузера. Если меняете пояс, пересоздайте данные: `php artisan migrate:fresh --seed`.
