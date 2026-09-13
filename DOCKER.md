# Running the API with Docker

Two containers, one command:

| Service   | What runs                                 | URL (host)            |
| --------- | ----------------------------------------- | --------------------- |
| `backend` | Laravel 10 API (`php artisan serve`)      | http://localhost:8000 |
| `db`      | PostgreSQL 16                             | `localhost:5433`      |

This replaces running `php artisan serve` against a database you installed and
looked after yourself: the PHP version and extensions are fixed by the image, and
the database is a throwaway Postgres container by default. To run against a
hosted Supabase project instead, change a few `LMS_DB_*` values — see
[Using Supabase](#using-supabase-instead-of-the-local-container).

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
| Open a database shell                                    | `docker compose exec db psql -U lms -d lms`             |

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
| `LMS_DB_HOST`              | `db`                        | `db` is the bundled container; a Supabase hostname otherwise. |
| `LMS_DB_PORT`              | `5432`                      | Supabase's pooler uses `6543`.                            |
| `LMS_DB_HOST_PORT`         | `5433`                      | Host port for the bundled Postgres (not 5432, which a local install usually owns). |
| `LMS_DB_DATABASE` / `LMS_DB_USERNAME` / `LMS_DB_PASSWORD` | `lms` | Also used to create the database on first boot. Supabase: `postgres` / `postgres.<project-ref>`. |
| `LMS_DB_SSLMODE`           | `prefer`                    | Supabase: `require`.                                      |
| `LMS_DB_EMULATE_PREPARES`  | `false`                     | Set `true` when the database is behind a transaction pooler. |
| `DATABASE_URL`             | unset                       | Optional single connection URI; overrides every `LMS_DB_*` value when set. |
| `LMS_APP_KEY`              | the project's existing key  | See below.                                                |

Nothing has to be configured for your user account: the image's entrypoint works
out who owns the checkout and runs the API as them, so the files Laravel writes
(`storage/logs`, compiled views, `bootstrap/cache`) stay editable on the host.

### About `APP_KEY`

The frontend decrypts every API response with a key it ships, so the API has to
encrypt with exactly that key or login and every other request fails. Compose
sets it for you; override with `LMS_APP_KEY` if you rotate it — and remember
`GET /api/lms` hands the key out to anyone who asks, which is how the frontend
could fetch it at runtime instead of hard-coding it.

## How the image is built

`Dockerfile` builds `php:8.2-cli` plus `pdo_pgsql`/`mbstring`, with Composer
installed from the official Composer image. `composer install` runs at build
time; the repository is then bind-mounted so PHP picks up your edits instantly,
while `vendor/` stays in a named volume so a host-side `vendor/` never leaks into
the container. No `.env` is baked into the image — all runtime configuration is
passed as environment variables by compose.

## Using Supabase instead of the local container

Supabase is managed PostgreSQL, so the API runs against it unchanged — only the
connection settings differ. Keep them in `.env` (which is gitignored) rather than
in `docker-compose.yml`, and never commit the database password.

### Use the pooler hostname, not `db.<project-ref>.supabase.co`

Supabase offers four Postgres endpoints. **Only the shared pooler works from
inside these containers:**

| Mode | Host:Port | IP version (per Supabase) | Usable here? |
| ---- | --------- | ------------------------- | ------------ |
| Direct | `db.<project-ref>.supabase.co:5432` | IPv6; IPv4 only with the paid add-on | No — no IPv6 route in the container, so `Network is unreachable` |
| Shared pooler, session | `aws-<INDEX>-<REGION>.pooler.supabase.com:5432` | IPv4 on every plan | **Yes — use this** |
| Shared pooler, transaction | `aws-<INDEX>-<REGION>.pooler.supabase.com:6543` | IPv4 on every plan | Yes, but prepared statements are unsupported |
| Dedicated pooler (paid) | `db.<project-ref>.supabase.co:6543` | IPv6 unless the add-on | No, same reason as direct |

Supabase's own advice agrees with the measurement: a *persistent backend on an
IPv4-only network* should use the **shared pooler in session mode**, which is
exactly this situation.

So when the dashboard shows you
`postgresql://postgres:<password>@db.<ref>.supabase.co:5432/postgres`, click
**Connect → Session pooler** and take that string instead. Two things change —
the hostname, and the username, which carries the project ref:

```dotenv
# Direct (as shown by Supabase) — IPv6 only, fine on the host, not in Docker:
#   postgresql://postgres:<password>@db.<project-ref>.supabase.co:5432/postgres
# Pooler (use this) — note postgres.<project-ref>, not postgres:
#   postgresql://postgres.<project-ref>:<password>@aws-<INDEX>-<REGION>.pooler.supabase.com:5432/postgres
```

**Copy that host out of the Connect dialog rather than composing it.** `<INDEX>`
is a pooler cluster index, not part of the region name — a region can hold more
than one — so the host is not derivable from the region, and Supabase says so
explicitly. `Tenant or user not found` is the error when the pooler cannot match
that host + username to a project, i.e. one of the two is wrong.

Percent-encode any of `@ : / ? #` inside the password (`@` → `%40`, `:` → `%3A`,
`/` → `%2F`, `?` → `%3F`, `#` → `%23`).

### The IPv4 add-on, if you would rather use the direct connection

Supabase sells an IPv4 add-on that makes the direct endpoint reachable over
IPv4, which *would* work from a container. Two things to know: it is **not
dual-stack** — it swaps the project's AAAA record for an A record, so the
endpoint becomes IPv4-only rather than supporting both — and it is a paid extra.
Session mode is free and, per Supabase, supports everything the direct connection
does.

### Two ways to write it

Either the discrete variables:

```dotenv
LMS_DB_HOST=aws-<INDEX>-<REGION>.pooler.supabase.com
LMS_DB_PORT=5432
LMS_DB_DATABASE=postgres
LMS_DB_USERNAME=postgres.<project-ref>
LMS_DB_PASSWORD=<database password>
LMS_DB_SSLMODE=require
```

…or one URI in `DATABASE_URL`, which is the same shape Supabase gives you. This
one is not prefixed `LMS_` like the rest because the container *and* a host-run
`php artisan` both want it:

```dotenv
DATABASE_URL=postgresql://postgres.<project-ref>:<password>@aws-<INDEX>-<REGION>.pooler.supabase.com:5432/postgres?sslmode=require
```

Careful: a non-empty `DATABASE_URL` **overrides every `DB_*` and `LMS_DB_*`
value** — Laravel applies the URI last — so comment it out again to go back to
the local container, rather than half-editing both.

Port `5432` is **session** mode and is the better choice here: the connection
keeps one backend for its lifetime, so prepared statements and session state
behave normally. Port `6543` is **transaction** mode — state does not survive
between transactions — so it additionally needs `LMS_DB_EMULATE_PREPARES=true`.
Session mode also supports everything Supabase lists as unavailable in
transaction mode, which makes it a straight substitute for the direct connection
that this container cannot reach.

Then recreate the API container so it picks the values up, and create the schema:

```bash
docker compose up -d --no-deps backend   # --no-deps leaves the local Postgres alone
docker compose exec backend php artisan migrate
```

`--no-deps` is worth the extra word: `backend` normally waits for the local `db`
container to be healthy, so without it compose starts that Postgres too even
though nothing is connecting to it.

Supabase nominates the *direct* connection for migrations and `pg_dump`/restore.
You can use it here, but only from outside the containers: the host reaches that
endpoint, while PHP 8.3 on the host has no `pdo_pgsql` (install `php-pgsql`
first). Session mode is a valid substitute — Supabase states it supports
everything the direct connection does.

One more SSL note: `sslmode=require` encrypts but does not verify the server, so
it does not stop a man-in-the-middle. To verify as well, download the root
certificate from **Database settings** in the dashboard and switch to
`sslmode=verify-full` with `sslrootcert` pointing at it.

Take the values from the project's **Project settings → Database** page:

| Setting | Notes |
| ------- | ----- |
| `LMS_DB_HOST` | The pooler hostname, `aws-0-<region>.pooler.supabase.com`. |
| `LMS_DB_USERNAME` | On the pooler Supabase prefixes the user with the project ref (`postgres.abcdefghijklm`); on the direct connection it is plain `postgres`. |
| `LMS_DB_DATABASE` | `postgres` — the database Laravel calls `lms` locally. |
| `LMS_DB_PORT` | `5432` for the session pooler / direct connection, `6543` for the transaction pooler. Either works for the API. |
| `LMS_DB_SSLMODE` | `require`. Supabase serves a certificate chain libpq has no CA for, so `require` (encrypt, do not verify) is what works; `verify-full` needs the CA downloaded first. |

### About `LMS_DB_EMULATE_PREPARES`

Supabase documents that transaction mode **does not support prepared statements**
— "to avoid errors, turn them off in your connection library" — and their table
lists the setting per driver (`prepare: false` for Postgres.js, `pgbouncer=true`
for Prisma, `statement_cache_size=0` for asyncpg, and so on). For PDO the
equivalent is `PDO::ATTR_EMULATE_PREPARES`, which is what this flag sets: PDO
substitutes the parameters client-side and never asks the server to hold a named
statement. Leave it `false` for a direct or session-mode connection, where
prepared statements work normally and are faster; set it `true` only for `6543`.

If you would rather keep everything in one string, `DATABASE_URL` is honoured too
and is read in preference to the `DB_*` values:

```dotenv
DATABASE_URL=postgresql://postgres.<ref>:<password>@aws-0-<region>.pooler.supabase.com:6543/postgres?sslmode=require
```

## Troubleshooting

- **`composer install` times out while building** — Docker's DNS can return an
  IPv6 address the container has no route to. The Dockerfile already forces IPv4
  preference via `/etc/gai.conf`; check that line is still there if you see
  `Connection timed out`.
- **Port already in use** — something on the host (a local Postgres, another
  stack) owns 8000 or 5433. Change `LMS_BACKEND_PORT` / `LMS_DB_HOST_PORT` in `.env`.
- **The API returns "Connection refused" right after `up`** — it waits for the
  database healthcheck; `docker compose ps` shows `healthy` when it is ready.
- **`Tenant or user not found` against the pooler** — the pooler could not match
  your host + username to a project. Either the `<INDEX>`/`<REGION>` in the host
  is wrong (copy it from Connect → Session pooler; it is not derivable), or the
  username is missing the project ref (`postgres.<ref>`, not `postgres`).
- **`Connection refused` from Supabase** — their docs note this is usually an IP
  ban rather than an outage.
- **`prepared statement ... already exists`** — you are on port `6543`; set
  `LMS_DB_EMULATE_PREPARES=true`, or move to `5432`.
- **Switching from the old MariaDB stack** — the Postgres volume is named
  `pg_data`, so the old MariaDB data directory can never be mounted where
  `initdb` expects an empty one. The old `lmsbackend_db_data` volume is simply
  left behind; remove it with `docker volume rm lmsbackend_db_data` once you are
  sure you do not want the rows.
- **Running the old monorepo compose stack at the same time** — both publish
  8000/5433, so stop one first.
