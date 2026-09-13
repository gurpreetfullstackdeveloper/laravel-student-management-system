# AGENTS.md — Student Management System

Project-wide rules for any AI assistant (GitHub Copilot, ChatGPT, Laravel Boost-connected agents) working on this codebase. Full rationale for every rule below is in `docs/ARCHITECTURE.md` — this file is the enforceable summary, not the explanation.

## Stack — do not silently change these

- Laravel **10.50.3**, PHP **8.1.10**, MySQL, XAMPP (Windows), VS Code.
- Do not upgrade Laravel or PHP. Do not suggest code that requires PHP 8.2+ or Laravel 11+ syntax/features.
- Before installing or suggesting any package, check its `composer.json` `require.php` and `require.laravel/framework` constraints against `8.1.10`/`^10.0`. If incompatible, say so and propose the compatible version — never install the newest tag by default.
- **Filament: v3.x only** (`^3.0-stable`). Never suggest or scaffold Filament v4 syntax/APIs.
- **Laravel Boost: v1.x only** (`^1.8`). Never let a bare `composer require laravel/boost` run — it may resolve to 2.x, which needs PHP 8.2+/Laravel 11+.

## Architecture rules

- **One `users` table, one `web` guard, `role` enum column** (`super_admin`, `teacher`, `student`). Never create a second guard, a second users-like table, or a separate Filament-only auth table.
- Filament access is controlled by `User::canAccessPanel()`, never by a separate guard.
- **Never store a student's "current class" on the `students` table.** Current/historical class is always resolved through `student_enrollments` scoped by `academic_year_id`.
- **Never store computed exam totals/percentage/grade or fee outstanding-balance as columns.** Compute from `marks`/`fee_payments` on read (via `ResultCalculatorService` for results). Storing derived values that can drift is a rejected pattern in this project.
- Controllers stay thin: Route → Middleware → Controller → Form Request → (Service, only if one already exists for this module) → Eloquent.
- **Do not introduce a Repository pattern.** Eloquent models are the data layer.
- **Do not add `spatie/laravel-permission` or any dynamic permission system** unless explicitly asked — three static roles are handled by the `role` column + Policies.
- Only these modules get a Service class: Results (`ResultCalculatorService`), Fee assignment (`FeeAssignmentService`), bulk Attendance (`AttendanceBulkService`). Every other module is Controller+Request+Eloquent — do not add a Service "for consistency."

## Database conventions

- Every migration for a Model must be reviewed against `docs/ARCHITECTURE.md` §7 before creating it — **do not invent a column or table that isn't listed there.** If a genuinely new table/column is needed, say so explicitly and ask before creating it; do not add it silently.
- Every FK must be indexed; use the composite `unique()` constraints specified in the architecture doc (e.g. one attendance row per student per day) — these are business rules, not just performance indexes.
- `$fillable`, never `$guarded = []`.
- Soft-delete `users`, `students`, `teachers`, `fee_payments`. Hard-delete everything else.

## Authorization — non-negotiable

- **Every controller method that receives a model via route binding must call `$this->authorize()` against a Policy before returning data or performing an action.** A route parameter is never, by itself, proof the requester is allowed to see that record. This is the single most important rule in this file — a student must never be able to view another student's data by changing an ID in the URL.
- Every new model that a student or teacher can reach through a route needs a Policy, even a simple one.
- Never bypass a Policy "temporarily for testing" and leave it in committed code.

## Validation

- Every mutating action goes through a Form Request. No inline `$request->validate()` in controllers for anything beyond a one-off, non-reused check.

## Testing

- Any new route reachable by a student or teacher needs at least one authorization Feature test ("user A cannot access user B's record of this type") before the PR is considered done.
- Run relevant tests after any change; don't leave a red test suite in a commit.

## Git / secrets

- Never commit `.env`. Never hardcode a credential, API key, or token in any file — always read from `config()`/`env()`.
- `.env.example` must list every key the app uses, with empty/dummy values, and must be kept in sync when a new config key is introduced.

## General AI behavior rules

1. Inspect existing code/migrations/docs before changing or adding anything.
2. Never rewrite unrelated files in the same change.
3. Don't install packages unless the task genuinely needs one — and check version compatibility first (see Stack section).
4. Prefer native Laravel/Eloquent features over a new package or custom abstraction.
5. Follow `docs/ARCHITECTURE.md` — don't invent an alternative structure because it seems cleaner.
6. Don't invent database columns or relationships not listed in the architecture doc; ask instead.
7. Ask for clarification when architectural information is genuinely missing rather than guessing.
8. Explain any non-trivial architectural change before implementing it.
9. Add or update tests for any new behavior with security or data-correctness implications (see Testing section).
10. Never bypass authorization, even in a draft/first-pass implementation.
11. Never expose sensitive data (student PII, fee amounts, marks) to a role that shouldn't see it, including in error messages/logs.
12. Never trust an ID from a request without an authorization check.
13. Avoid overengineering — see the Service-layer rule above; resist adding patterns "just in case."
14. Preserve backward compatibility when modifying existing functionality — don't silently change a route, column, or method signature something else depends on.
