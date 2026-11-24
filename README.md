# Multi-Tenant Blog

A white-label blogging platform ("Medium for companies") built with **Laravel 11** and
**stancl/tenancy 3.8**. A central admin creates a blog for each company; every company gets its
own branded blog on its own domain, with its own database, writers, posts, files and cache.

- **Central app** (`blogs.test`): admins create tenants (name, domain, logo, brand color) and see
  every tenant with its post count.
- **Tenant blogs** (`acme.blogs.test`, `globex.blogs.test`): a public homepage and post pages
  styled with the tenant's logo and color, plus a writers' dashboard to create, edit and delete
  posts with a cover image and a draft / scheduled / published status.

## How tenancy works here

| Concern | How it's isolated |
| --- | --- |
| Database | One MySQL database per tenant (`tenant_acme`, `tenant_globex`), created and migrated when the tenant is created. Tenants and domains live in the central `blogs` database. |
| Identification | `InitializeTenancyByDomain` matches the request host against the central `domains` table. Central routes only answer on `CENTRAL_DOMAINS`. |
| Files | The filesystem bootstrapper points the `public` disk at `storage/tenant_{id}/app/public`. Covers are served through `tenant_asset()`. |
| Cache | Every cache key is tagged with the tenant id (Redis). The homepage list is cached per tenant and invalidated by a post observer. |
| Queues | Jobs dispatched in tenant context carry the tenant id; the worker re-initializes that tenant before running the job. The jobs table itself is pinned to the central connection. |
| Branding | Logo and color are columns on the central `tenants` table, read once the tenant is identified. |

## Requirements

- PHP 8.4 (Laravel 11 does not support 8.5), Composer, Node 20+
- MySQL 8+ and Redis running locally
- [Laravel Herd](https://herd.laravel.com) for `*.test` domains with subdomains

## Setup

```bash
git clone https://github.com/tayyaba-123/multi-tenant-blog.git
cd multi-tenant-blog

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Create the central database (and the test database, if you will run tests):

```bash
mysql -uroot -e "CREATE DATABASE blogs; CREATE DATABASE blogs_testing;"
```

Check `DB_*` and `REDIS_*` in `.env`, then migrate and seed. Seeding creates the admin and two
demo tenants, each with its own database, writers, posts and images:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Serve it with Herd. Linked sites answer on their subdomains too, so this covers the tenants:

```bash
herd link blogs
herd isolate 8.4 --site=blogs
```

Run the queue worker and scheduler (scheduled posts are published by a queued job every minute):

```bash
composer dev
```

> `migrate:fresh --seed` can be re-run at any time; the seeder drops leftover tenant databases
> and storage first.

## Demo logins

All passwords are `password`.

| Where | URL | Login |
| --- | --- | --- |
| Central admin | http://blogs.test/admin | `admin@blogs.test` |
| Acme Corp blog | http://acme.blogs.test | public |
| Acme writers | http://acme.blogs.test/login | `wile@acme.test`, `roadrunner@acme.test` |
| Globex blog | http://globex.blogs.test | public |
| Globex writers | http://globex.blogs.test/login | `hank@globex.test`, `homer@globex.test` |

Each demo tenant has a post scheduled three minutes after seeding. With `composer dev` running
it appears on the homepage without any manual step.

New blogs created from the admin get the domain `{subdomain}.blogs.test` and a first writer
whose credentials you choose in the form.

## Tests

```bash
php artisan test
```

The suite runs against real MySQL databases and Redis (cache DB 15), because that's where
isolation can actually break. Highlights:

- `tests/Feature/TenantIsolationTest.php`: tenant B can't see tenant A's posts, files, cache or
  writers.
- `tests/Feature/Tenant/CoverImageTest.php`: covers land in the tenant's storage directory and
  can't be read from another tenant's domain.
- `tests/Feature/ScheduledPublishingTest.php`: a real `queue:work` run publishes due posts in the
  right tenant database only.

CI (GitHub Actions) runs Pint and the full suite with MySQL and Redis on every pull request.

## What I learned

- **Central vs tenant is a context, not just a folder.** The same `User` model is an admin in the
  central database and a writer inside a tenant. What it reads depends entirely on whether
  tenancy is initialized when the query runs.
- **Database per tenant buys strong isolation at the cost of plumbing.** A forgotten `where
  tenant_id = ?` can't leak data, but things like counting posts across tenants mean visiting
  every database (`$tenant->run(...)`), and migrations run once per tenant.
- **Implicit commits break the usual test setup.** `CREATE DATABASE` commits the open MySQL
  transaction, so `RefreshDatabase` can't roll tenant creation back. The tests truncate the
  central database and drop tenant databases directly instead.
- **Anything resolved outside a request can lose tenant context.** The database queue would have
  written jobs into the tenant's own database without pinning it to the central connection, and
  a job is only tenant-aware because the bootstrapper stores the tenant id in its payload.
- **Caching across tenants is subtle.** Tagged cache keys keep tenants apart, but flushing a shared
  extra tag would wipe other tenants' entries, so invalidation bumps a per-tenant version key.
  The in-memory `array` store also hid this in tests, because tenancy rebuilds the cache manager
  on every context switch; the tests only became meaningful on Redis.
- **Files need the same care as rows.** Uploads go to a tenant-suffixed disk and are served by a
  controller that resolves paths inside that tenant's root, so another tenant's domain can't
  reach them, and deleting a tenant also removes its storage directory.
