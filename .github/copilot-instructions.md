# GitHub Copilot Instructions — Student Management System

This file covers only what's specific to how Copilot should behave *mechanically* in this repo. The substantive architecture rules (auth model, database rules, authorization requirement, Service-layer scope, package version pins) live in `/AGENTS.md` — read that first; do not duplicate it here, and if it's ever unclear which file governs a rule, `AGENTS.md` wins.

## Environment facts Copilot should assume

- Laravel 10.50.3, PHP 8.1.10, MySQL, running under XAMPP on Windows (`C:\xampp\htdocs\`).
- Filament v3.x is installed — suggest v3 syntax (`Filament\Resources\Resource`, `Forms\Components\...` under the `filament/forms` v3 namespace). **Never suggest Filament v4 patterns** (e.g. v4's non-static `Page::$view`, or `filament/filament: ^4.0` in composer suggestions).
- Laravel Boost v1.x is installed for AI/MCP context — don't suggest upgrading it.
- Livewire v3 is present (a Filament v3 dependency) — use Livewire v3 component syntax if writing portal components.

## File/naming conventions to follow

- Model for the `classes` table is named `SchoolClass` (PHP reserves the word `Class`) — never generate a `Class` model.
- Controllers for the two authenticated portals live under `app/Http/Controllers/Student/` and `app/Http/Controllers/Teacher/`; public-site controllers under `app/Http/Controllers/Public/`.
- Form Requests are namespaced per module: `App\Http\Requests\Student\...`, `App\Http\Requests\Attendance\...`, etc. — match the existing folder for a module rather than dropping new requests flat into `Requests/`.
- Policies live in `app/Policies/`, one per model that a student/teacher can reach, named `{Model}Policy`.
- The three Services (`ResultCalculatorService`, `FeeAssignmentService`, `AttendanceBulkService`) live in `app/Services/`. Do not create additional Service classes for other modules — see `AGENTS.md`.

## Patterns to match, not reinvent

- When adding a new Form Request, follow the structure of an existing one in the same module folder rather than generating a novel validation style.
- When adding a new Policy method, follow the `role`-based `match()` pattern already used in existing Policies (super_admin → true, then role-specific ownership/assignment checks) rather than introducing Gates for object-level checks.
- Route files: student routes go in `routes/student.php` (required from `web.php` under the `role:student` middleware group), teacher routes in `routes/teacher.php` — don't add student/teacher routes directly into `web.php`.
- Migrations: check `docs/ARCHITECTURE.md` §7 for the exact column list of the table you're touching before writing the migration; don't add convenience columns that aren't listed there without flagging it first.

## Suggestions to avoid

- Don't suggest `$guarded = []` on any model.
- Don't suggest a controller method that acts on a route-bound model without a preceding `$this->authorize(...)` call.
- Don't suggest storing computed values (result totals/grades, fee outstanding balances) as new columns — compute them, per `AGENTS.md`.
- Don't suggest `spatie/laravel-permission`, a Repository interface/class, or a second auth guard — these are explicitly rejected patterns for this project (see `AGENTS.md` and `docs/ARCHITECTURE.md` §42).
- Don't auto-upgrade a composer constraint (e.g. widening `filament/filament` to `^4.0`) to resolve a dependency conflict — flag the conflict instead and let the developer decide.

## Path-specific instruction files

Not in use yet. A single instructions file is sufficient while conventions stay consistent across Models/Controllers/Migrations. If Copilot is repeatedly getting one area wrong despite this file (Filament Resources are the most likely candidate, given v3-vs-v4 API drift in Copilot's training data), add a targeted `.github/instructions/filament.instructions.md` at that point rather than expanding this file indefinitely.
