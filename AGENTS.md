# SidangApp - Laravel 13 + Livewire 4 + Flux 2

## Project Overview
Thesis defense scheduling system (Sidang = thesis defense). Roles: `mahasiswa` (student), `dosen` (lecturer), `admin`.

## Key Commands
```bash
# Testing
php artisan test --compact                    # run all tests
php artisan test --compact --filter=TestName  # filter tests

# Lint/Format
vendor/bin/pint --format agent                # format PHP (run after edits)

# Frontend
npm run dev         # dev server (Vite)
npm run build       # production build
composer run dev    # full dev (php artisan serve + npm run dev + queue)

# Setup
composer run setup  # install deps, migrate, build
```

## Testing
- **Framework**: Pest 4 (feature tests use `RefreshDatabase`)
- **Factories**: `UserFactory` has states: `->dosen()`, `->admin()`, `->unverified()`, `->withTwoFactor()`
- **Create test**: `php artisan make:test --pest Feature/MyFeatureTest`
- **Run single test**: `php artisan test --compact --filter=testName`
- **Test DB**: SQLite (`database/database.sqlite`)

## Code Style
- **PHP**: Laravel Pint (run `vendor/bin/pint --format agent` after edits)
- **PHP 8.4**: Constructor property promotion, typed properties, return types
- **Models**: Use `#[Fillable]` / `#[Hidden]` attributes + `casts()` method

## Architecture
- **Auth**: Laravel Fortify + Passkeys (Laravel\Fortify\PasskeyAuthenticatable)
- **Frontend**: Livewire 4 + Flux 2 (no Vue/React)
- **Roles**: `mahasiswa` | `dosen` | `admin` (stored on `users.role`)
- **Models**: `User`, `Schedule`, `Submission`, `RevisionNote`, `RevisionAttachment`
- **Relations**: `Schedule` belongsToMany `User` (dosen) via `schedule_dosen`

## Key Conventions
- **Routes**: Named routes in `routes/web.php` + `routes/settings.php` (Fortify)
- **Controllers**: Standard resource controllers in `app/Http/Controllers`
- **Livewire Components**: `app/Livewire/` (Flux components in `resources/views/components/flux/`)
- **Factories**: Define states for roles (`dosen()`, `admin()`)
- **Tests**: Feature tests in `tests/Feature/`, use factories, extend `Tests\TestCase`

## Common Artisan Commands
```bash
php artisan make:model Model -mf    # model + migration + factory
php artisan make:controller Controller --resource
php artisan make:livewire Component
php artisan make:test --pest Feature/NameTest
php artisan route:list --name=submissions
```

## Frontend
- **Build**: `npm run build` (Vite + Laravel Vite Plugin)
- **Dev**: `npm run dev` or `composer run dev`
- **CSS**: Tailwind 4 via `@tailwindcss/vite`
- **Assets**: `resources/css/app.css`, `resources/js/app.js`

## Database
- **SQLite** for local/testing (`database/database.sqlite`)
- **Migrations**: `database/migrations/`
- **Seeders**: `database/seeders/DatabaseSeeder.php`

## Common Gotchas
- Run `npm run build` after frontend changes if not using `npm run dev`
- Run `vendor/bin/pint --format agent` after PHP edits
- Use `php artisan test --compact --filter=Name` for focused test runs
- Fortify features gated in `config/fortify.php` - check `Tests\TestCase::skipUnlessFortifyHas()`
- User roles: `mahasiswa` (default), `dosen`, `admin`
- Gates in `AppServiceProvider::configureGates()` (not Policy classes)

## Project Docs (`docs/`)
- `docs/prd.md` - product requirements (FR + MVP specs)
- `docs/design.md` - frontend design system (Flux 2 + Tailwind, shadcn-inspired)
- `docs/phase.md` - phase-by-phase feature checklist
- `docs/progress.md` - Phase 9 notes
- `docs/memory.md` - agent work log (update after significant work)