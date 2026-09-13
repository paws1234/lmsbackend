# Running the API with Docker

Two containers, one command:

| Service   | What runs                                 | URL (host)        |
| --------- | ----------------------------------------- | ----------------- |
| `backend` | Laravel 10 API (`php artisan serve`)      | http://localhost:8000 |
| `db`      | MariaDB (LTS)                             | `localhost:3307`  |

This replaces running `php artisan serve` against XAMPP: the PHP version and
extensions are fixed by the image and the database is throwaway.

The Vue frontend is a separate repository (`lmsfrontend`) and a separate compose
project. The browser talks to this API directly, so no shared network is needed.

## Quick start

```bash
docker compose up -d --build
docker compose exec backend php artisan migrate     # first run only
curl http://localhost:8000/api/lms
```

## Everyday commands

| Task                                                    | Command                                                |
| ------------------------------------------------------- | ------------------------------------------------------ |
| Start (after the first build)                            | `docker compose up -d`                                 |
| Stop, keep the database                                  | `docker compose down`                                  |
| Stop and wipe the database                               | `docker compose down -v`                               |
| Rebuild after `composer.json` changes                    | `docker compose build`                                 |
| Follow logs                                              | `docker compose logs -f backend`                       |
| Run an artisan command                                   | `docker compose exec backend php artisan <cmd>`        |
| Open a shell                                             | `docker compose exec backend bash`                     |
| Run the test suite                                       | `docker compose exec backend php artisan test`         |
| Add a PHP package                                        | `docker compose exec backend composer require <pkg>`   |
| Reset the database                                       | `docker compose exec backend php artisan migrate:fresh` |
| Open a database shell                                    | `docker compose exec db mariadb -u lms -plms lms`      |

`composer require` only needs a rebuild afterwards so the image matches
`composer.lock`.

## Configuration

Everything has a working default, so the stack starts with no configuration at
all. Overrides go in `.env` — note that **this is the same file Laravel reads**,
which is why every Docker setting is prefixed with `LMS_`; `php artisan` ignores
the prefixed lines and `docker compose` ignores the Laravel ones. A commented
block listing them is at the bottom of `.env.example`.

| Variable                   | Default                     | Notes                                                     |
| -------------------------- | --------------------------- | --------------------------------------------------------- |
| `LMS_BACKEND_PORT`         | `8000`                      | Host port for the API.                                    |
| `LMS_DB_HOST_PORT`         | `3307`                      | Host port for MariaDB (not 3306, so XAMPP's MySQL can still run). |
| `LMS_DB_DATABASE` / `LMS_DB_USERNAME` / `LMS_DB_PASSWORD` | `lms` | Also used to create the database on first boot. |
| `LMS_DB_ROOT_PASSWORD`     | `lms_root`                  | MariaDB root password.                                    |
| `LMS_DOCKER_UID` / `LMS_DOCKER_GID` | `1000`             | The user the API container runs as, so Laravel's log/view/cache files stay yours. Set to `id -u` / `id -g`. |
| `LMS_APP_KEY`              | the project's existing key  | See below.                                                |

### About `APP_KEY`

The frontend decrypts every API response with a key it ships, so the API has to
encrypt with exactly that key or login and every other request fails. Compose
sets it for you; override with `LMS_APP_KEY` if you rotate it — and remember
`GET /api/lms` hands the key out to anyone who asks, which is how the frontend
could fetch it at runtime instead of hard-coding it.

## How the image is built

`Dockerfile` builds `php:8.2-cli` plus `pdo_mysql`/`mbstring`, with Composer
installed from the official Composer image. `composer install` runs at build
time; the repository is then bind-mounted so PHP picks up your edits instantly,
while `vendor/` stays in a named volume so a host-side `vendor/` never leaks into
the container. No `.env` is baked into the image — all runtime configuration is
passed as environment variables by compose.

## Troubleshooting

- **`composer install` times out while building** — Docker's DNS can return an
  IPv6 address the container has no route to. The Dockerfile already forces IPv4
  preference via `/etc/gai.conf`; check that line is still there if you see
  `Connection timed out`.
- **Port already in use** — something on the host (often XAMPP) owns 8000 or
  3306. Change `LMS_BACKEND_PORT` / `LMS_DB_HOST_PORT` in `.env`.
- **The API returns "Connection refused" right after `up`** — it waits for the
  database healthcheck; `docker compose ps` shows `healthy` when it is ready.
- **Running the old monorepo compose stack at the same time** — both publish
  8000/3307, so stop one first.
