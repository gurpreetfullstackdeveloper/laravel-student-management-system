# Student Management System — Architecture Document

Target stack: **Laravel 10.50.3 / PHP 8.1.10 / MySQL / XAMPP (Windows)**, developed in VS Code with GitHub Copilot + Laravel Boost, reviewed with ChatGPT, versioned on GitHub.

This document is architecture-first. It is meant to be read by you, and referenced by Copilot/ChatGPT during implementation. It does not contain the application.

---

## 1. Executive Summary

One Laravel application, one MySQL database, three human-facing surfaces (public website, student portal, teacher portal) plus one back-office surface (Filament admin). One `users` table with a `role` column is the authentication backbone; separate `students` and `teachers` tables hold role-specific data. One Laravel guard (`web`) is used everywhere — Filament access is gated by `canAccessPanel()`, not by a second guard. No package is installed unless PHP 8.1.10/Laravel 10.50.3 compatibility is confirmed — this ruled out **Filament v4** and **Laravel Boost v2.x**, both of which require Laravel 11+/PHP 8.2+. Business logic sits mostly in thin Controllers + Form Requests + Eloquent; a Service layer is introduced only for Fees and Results, where real calculation/orchestration logic exists. No repository pattern, no `spatie/laravel-permission` at launch — a role enum plus Policies is sufficient for three static roles.

---

## 2. Recommended Architecture

- **Monolith, single database.** A school's data is inherently relational (a result depends on a student, a class, a subject, an exam) — splitting this into multiple apps/databases would mean constant cross-service joins for no isolation benefit at this scale. Reject multi-app split.
- **Single `users` table, single `web` guard, role column.** Details in §4.
- **Layering:** Route → Middleware → Controller (thin) → Form Request (validation) → Service (only where genuine logic exists) → Eloquent Model → MySQL.
- **Filament v3** for the admin back office; **Blade + minimal Livewire** for the public site and both portals (see §19–21).
- **API is scaffolded, not built.** Route file and versioning convention exist from day one; no endpoints beyond auth until a mobile client is actually being built.

---

## 3. Application Architecture Diagram

```
                         STUDENT MANAGEMENT SYSTEM (1 Laravel app)
                                        |
        +-------------------+-------------------+-------------------+
        |                   |                   |                   |
        v                   v                   v                   v
  PUBLIC WEBSITE      STUDENT PORTAL      TEACHER PORTAL      FILAMENT ADMIN
  no auth             guard: web          guard: web          guard: web
  Blade               role: student       role: teacher       canAccessPanel():
  /                   /student/*          /teacher/*          role === super_admin
                       middleware:         middleware:         /admin
                       auth, role:student  auth, role:teacher
        |                   |                   |                   |
        +-------------------+-------------------+-------------------+
                                        |
                                        v
                              SINGLE MySQL DATABASE
                            (users, students, teachers,
                             academic data, fees, etc.)
```

One guard, one session mechanism, one login table. The three portals are separated by **route prefix + role middleware + Policies**, not by authentication mechanism.

---

## 4. Authentication Architecture

### The core question: one users table vs. separate tables; one guard vs. multiple

| Option | Pros | Cons |
|---|---|---|
| **A. One `users` table + `role` column, one `web` guard** | Laravel's built-in password reset, email verification, "remember me", and session handling all work unmodified. Filament, student, and teacher logins share one code path. Adding a 4th role later (e.g. Accountant) is a one-line enum addition. | Slightly wider `users` table; requires discipline in scoping queries by role. |
| B. Separate tables/guards per role (`admin`, `teacher`, `student` guards) | Conceptually "clean" separation | Laravel's password-reset broker, email verification, and `Auth::routes()` conveniences are built around one `Authenticatable` per context — three guards means three password-reset flows, three "remember me" cookies, and you must manually decide which guard is "active" when a user could theoretically match more than one table. Real risk of session bugs (a browser can hold three simultaneous logged-in identities). Filament panel guard must be pinned to one specific guard, so you'd still need role logic somewhere for the admin case. This is more code, not more security. |
| C. Filament's own separate auth (Filament ships its own default `User` model reference) | N/A | Filament v3 doesn't require this — it authenticates against whatever guard/model you configure. Using a second table here just recreates option B's problems specifically for the admin surface. |

**Recommendation: Option A.** One `users` table, one `web` guard, one `EloquentUserProvider`. This is also the pattern the Filament team itself documents for "one app, multiple user types."

```php
// users table (simplified)
id, name, email (nullable, unique when present), username (unique, nullable),
password, role enum('super_admin','teacher','student'),
is_active boolean default true, email_verified_at, remember_token, timestamps, deleted_at (soft delete)
```

**Login-by-username caveat (real school requirement):** many students won't have a personal email. Don't force email-based login. Add a nullable `username` column (e.g. admission number) and a custom `username()` override so `LoginRequest` accepts either:

```php
// App\Http\Requests\Auth\LoginRequest (or your own StudentLoginRequest)
public function authenticate(): void
{
    $field = filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    if (! Auth::attempt([$field => $this->input('login'), 'password' => $this->input('password')], $this->boolean('remember'))) {
        throw ValidationException::withMessages(['login' => trans('auth.failed')]);
    }
}
```

### Preventing Filament ↔ application-auth conflicts

Both use the **same guard**, so the only conflict risk is: *"can a logged-in student open `/admin`?"* This is solved entirely by `canAccessPanel()` on the `User` model — Filament calls this on every panel request:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->role === 'super_admin' && $this->is_active;
}
```

A student/teacher session hitting `/admin` is authenticated but **not authorized** → Filament redirects/403s automatically. No guard collision, no double-login, no separate cookie.

### Session, redirects, password reset, email verification

- One session cookie, one `remember_token` column — a user logged in as themselves everywhere; there is no "logged in as student AND teacher simultaneously" scenario to design for, which is correct (a person is only ever one role).
- **Post-login redirect** is role-based, decided once in a single `RedirectIfAuthenticated`-style resolver: `super_admin` → `/admin`, `teacher` → `/teacher/dashboard`, `student` → `/student/dashboard`.
- **Password reset**: Laravel's default `Password` broker works as-is against the one `users` table. Reset email requires the user to *have* an email — for username-only students, provide an admin-initiated reset (Super Admin resets from Filament) as the primary path, self-service reset only where email exists.
- **Email verification**: enable `MustVerifyEmail` only for roles that have real emails (teachers, super admin) — check `$user->email !== null` before enforcing the `verified` middleware, or simply don't require verification for students at all in v1.

---

## 5. Authorization Architecture

Two layers, used together, never one instead of the other:

1. **Middleware (coarse, route-level):** "is this a teacher at all?" — a custom `EnsureUserHasRole` middleware (`role:teacher`, `role:student`) gates entire route groups.
2. **Policies (fine, object-level):** "is this *specific* record this user's own, or one they're assigned to?" This is the actual security boundary and the answer to the "`/student/results/15` → `/student/results/16`" problem in §23.

```php
// app/Policies/ResultPolicy.php
public function view(User $user, Result $result): bool
{
    return match ($user->role) {
        'super_admin' => true,
        'student' => $result->student->user_id === $user->id,
        'teacher' => $result->student->classAssignments()
                        ->where('academic_year_id', $result->academic_year_id)
                        ->whereIn('class_id', $user->teacher->assignedClassIds())
                        ->exists(),
        default => false,
    };
}
```

Controller always calls `$this->authorize('view', $result)` (or `Gate::authorize`) — **never** relies on the route parameter alone being "safe." Route-model binding gives convenience, Policy gives security; conflating the two is exactly how IDOR bugs happen.

Gates are used only for a handful of non-model, whole-app actions (e.g. `Gate::define('access-reports')`); everything else is a Policy tied to an Eloquent model, which is more testable and keeps authorization co-located with the resource it protects.

**No `spatie/laravel-permission` at launch.** Three static roles with a handful of Policies is simple, fast, and fully covered by native Laravel authorization. Introduce permission packages only if you need admin-configurable, dynamic permission sets (e.g. "some teachers can also manage fees") — that's a real future possibility, not a v1 requirement, and adding it later is a additive migration (`role` stays, you layer `permissions` on top), not a rewrite.

---

## 6. User / Role Architecture

```
users (auth identity: login, password, role, status)
 ├── hasOne → students   (role = student profile data)
 └── hasOne → teachers   (role = teacher profile data)
```

- `role` is a single source of truth for "what kind of account is this."
- `students`/`teachers` never duplicate auth fields (no password, no email dupe) — they hold only domain data (admission number, class, guardian info / employee ID, qualifications).
- A `User` can have **at most one** of `student`/`teacher` populated in practice; `super_admin` users have neither. This is enforced at the application/seed level, not the DB level (a DB-level XOR constraint across two nullable FKs is possible via a check constraint in MySQL 8, but adds fragility for little benefit at this scale — validate at creation time instead, e.g. in a `RegisterStudentAction`/Filament resource `mutateFormDataBeforeCreate`).

---

## 7. Database Architecture

Tables actually justified for v1 (each explained): `users`, `students`, `teachers`, `academic_years`, `classes` (model: `SchoolClass` — `Class` is a reserved PHP word, table name is fine), `sections`, `subjects`, `class_subject`, `student_enrollments`, `teacher_assignments`, `attendances`, `exams`, `exam_subjects`, `marks`, `exam_results`, `fee_types`, `fee_structures`, `fee_structure_items`, `student_fees`, `fee_payments`, `notices`, `notice_targets`, `events`, `news`, `galleries`, `gallery_images`, `admission_enquiries`, `school_settings`, `audit_logs`.

Deliberately **not created**: a `roles`/`permissions` table pair (role is an enum column — see §5), a standalone `results` table storing pre-computed totals (see §13), a `payment_gateway_transactions` table (no gateway yet — see §14).

### Table-by-table

**`users`** — PK `id`. Unique: `email` (nullable unique), `username` (nullable unique). Fields: `name`, `password`, `role`, `is_active`, `email_verified_at`, timestamps, `deleted_at` (soft-delete: never hard-delete an account tied to historical academic/financial records).

**`students`** — PK `id`. FK `user_id` → `users` (unique, one-to-one). Fields: `admission_no` (unique), `date_of_birth`, `gender`, `guardian_name`, `guardian_phone`, `address`, `admission_date`, `photo_path`. No `class_id` here — current class is derived from the *current* `student_enrollments` row (see Academic Year design, §9) so history isn't overwritten on promotion.

**`teachers`** — PK `id`. FK `user_id` → `users` (unique). Fields: `employee_code` (unique), `qualification`, `phone`, `joining_date`, `photo_path`.

**`academic_years`** — PK `id`. Fields: `name` (e.g. "2025-2026", unique), `start_date`, `end_date`, `is_current` (boolean; enforce only one true via application logic/DB trigger — simplest: a small service method `AcademicYear::setCurrent()` that transactionally flips the flag).

**`classes`** (model `SchoolClass`) — PK `id`. Fields: `name` (e.g. "Class 10"), `order` (int, for sorting). No academic-year FK — a class is a standing label; what's year-specific is *who's enrolled in it*.

**`sections`** — PK `id`. FK `class_id`. Fields: `name` (e.g. "A"). Unique(`class_id`,`name`).

**`subjects`** — PK `id`. Fields: `name`, `code` (unique).

**`class_subject`** (pivot) — `class_id`, `subject_id`, `academic_year_id` — which subjects are taught in which class in which year (curriculum can change year to year). Unique composite key on all three.

**`student_enrollments`** — the historical record solving "which class was this student in, in which year." PK `id`. FK `student_id`, `class_id`, `section_id`, `academic_year_id`. Fields: `roll_no`, `status` enum(`active`,`promoted`,`transferred`,`left`). Unique(`student_id`,`academic_year_id`) — a student has exactly one enrollment per year. "Current class" = enrollment where `academic_year_id` = current year.

**`teacher_assignments`** — replaces a plain `teacher_subject` pivot; this is what a teacher is *authorized* to touch (used directly by `AttendancePolicy`/`ResultPolicy`). FK `teacher_id`, `subject_id`, `class_id`, `section_id`, `academic_year_id`. Unique on the combination.

**`attendances`** — PK `id`. FK `student_id`, `class_id`, `section_id`, `academic_year_id`, `recorded_by` (→ users/teacher). Fields: `date`, `status` enum(`present`,`absent`,`late`,`leave`). **Unique(`student_id`,`date`)** — hard constraint preventing duplicate attendance for the same student/day, enforced at the DB level, not just application code (catches race conditions/double-submits).

**`exams`** — PK `id`. FK `academic_year_id`, `class_id`. Fields: `name`, `type` enum(`unit_test`,`mid_term`,`final`), `start_date`, `end_date`. (A separate `exam_types` table was considered and rejected — an enum is sufficient; a lookup table would only earn its place if exam types needed admin-configurable extra metadata, which isn't a stated requirement.)

**`exam_subjects`** — PK `id`. FK `exam_id`, `subject_id`. Fields: `max_marks`, `pass_marks`. Unique(`exam_id`,`subject_id`).

**`marks`** — PK `id`. FK `exam_subject_id`, `student_id`, `entered_by` (teacher). Fields: `obtained_marks`, `remarks`. Unique(`exam_subject_id`,`student_id`). This is the only place raw scores live.

**`exam_results`** — **not** a pre-computed totals table (see §13 for why). Stores only what genuinely can't be derived: `student_id`, `exam_id`, `is_published` (boolean — results hidden from student portal until this flips), `published_at`, `overall_remarks`. Total/percentage/grade/pass-fail are computed on read from `marks` (cached, see §26).

**`fee_types`** — PK `id`. Fields: `name` (tuition, transport, admission, exam, other), `is_recurring`.

**`fee_structures`** — PK `id`. FK `academic_year_id`, `class_id` (nullable = applies to all classes). Fields: `name`.

**`fee_structure_items`** — FK `fee_structure_id`, `fee_type_id`. Fields: `amount`, `due_date`.

**`student_fees`** — the fee actually assigned to one student (structure + any individual discount). FK `student_id`, `fee_structure_item_id`, `academic_year_id`. Fields: `amount_payable` (structure amount minus discount), `discount_amount`, `discount_reason`.

**`fee_payments`** — FK `student_fee_id`, `received_by`. Fields: `amount_paid`, `paid_at`, `payment_method` enum(`cash`,`cheque`,`bank_transfer`,`online` — `online` reserved for a future gateway), `reference_no`, `receipt_no`. Outstanding balance = `amount_payable` − Σ`amount_paid` (derived, not stored).

**`notices`**, **`events`**, **`news`** — straightforward CMS-style tables: `title`, `slug`, `body`, `published_at`, `is_published`, `created_by`, timestamps. `notices` additionally needs **`notice_targets`** (pivot: `notice_id` + polymorphic-ish audience — `audience_type` enum(`all`,`class`,`role`) + `audience_id` nullable) so a notice can target "everyone," "Class 10," or "all teachers."

**`galleries`** (album) + **`gallery_images`** (FK `gallery_id`, `path`, `caption`) — one-to-many, simple.

**`admission_enquiries`** — public-facing form submissions: `student_name`, `parent_name`, `phone`, `email`, `class_applied_for`, `message`, `status` enum(`new`,`contacted`,`converted`,`rejected`). Not linked to `students` until actually admitted.

**`school_settings`** — key-value table (`key` unique, `value` text/json) for site-wide config (school name, address, logo, contact info) editable from Filament without a deploy.

**`audit_logs`** — see §25.

**Indexes/constraints applied throughout:** every FK is indexed (MySQL does this automatically for InnoDB FKs); composite uniques as noted above are the real integrity guarantees (attendance-per-day, one-mark-per-exam-subject-per-student, one-enrollment-per-year). Soft deletes (`deleted_at`) on `users`, `students`, `teachers`, and financial tables (`fee_payments`) — never hard-delete something a report or receipt might reference later; everything else can hard-delete since it has no downstream financial/legal weight.

---

## 8. Database ER / Relationship Design

```
User 1—1 Student            User 1—1 Teacher
Student 1—* StudentEnrollment  (history across years)
StudentEnrollment *—1 SchoolClass, *—1 Section, *—1 AcademicYear
SchoolClass 1—* Section
SchoolClass *—* Subject (via class_subject, scoped per AcademicYear)
Teacher *—* (Subject, SchoolClass, Section) via TeacherAssignment, scoped per AcademicYear
Student 1—* Attendance
Student 1—* Mark            Exam 1—* ExamSubject 1—* Mark
Student 1—* ExamResult (per exam)
Student 1—* StudentFee 1—* FeePayment
Notice *—* audience via NoticeTarget (polymorphic-style)
```

Key relationship rule enforced everywhere: **a Student's "current class" is never a column on `students`** — it's always resolved through `student_enrollments` for the current (or a specified) academic year. This is what makes historical records survive promotion, and it's the single most important relational decision in the whole schema — get this wrong and every downstream report (attendance, results, fees) breaks the moment you promote a cohort.

---

## 9. Academic Year Architecture

Every academically-scoped fact (`student_enrollments`, `class_subject`, `teacher_assignments`, `attendances`, `exams`, `fee_structures`) carries an explicit `academic_year_id`. Nothing academic is ever scoped only to "now." Promotion = creating a **new** `student_enrollments` row for the new year (new class/section), leaving prior years' rows untouched — a student's Class-9 attendance/results/fees remain queryable forever under the Class-9 enrollment. `academic_years.is_current` drives default UI scoping ("this year" views) without ever deleting or overwriting past-year data.

---

## 10. Student Architecture

- Auth via `users` (role=student) + profile in `students` + per-year facts in `student_enrollments`.
- Portal is read-mostly: attendance, results (only where `exam_results.is_published`), fees/payment history, notices/events targeted at their class or "all."
- Every query a student can trigger is scoped through `auth()->user()->student` — never through a raw route ID trusted at face value (see §23).
- Students can edit: password, limited profile fields (phone/address) — not admission_no, not class, not fee data.

## 11. Teacher Architecture

- Auth via `users` (role=teacher) + `teachers` + `teacher_assignments` (their actual authorization boundary).
- Portal actions (mark attendance, enter marks) are scoped to `teacher_assignments` for the current academic year — enforced by `AttendancePolicy`/`ResultPolicy`, not just hidden UI.
- Teachers cannot see other teachers' classes' data, cannot touch fees, cannot open `/admin` (blocked by `canAccessPanel`).

---

## 12. Attendance Architecture

One row per student per day (`attendances`, unique on student+date — §7). Recorded by a teacher assigned to that class/section for the current year (enforced by `AttendancePolicy::create`). Bulk-entry UI (mark a whole section present, flip individual absentees) is a single form posting many rows in one DB transaction — see §26 for the bulk-insert pattern. Editing a past attendance record beyond a short window (e.g. same day only) should require a Policy check that also verifies the academic year is still current, otherwise historical records could be silently rewritten.

## 13. Examination / Result Architecture

**Store:** raw `marks` per student per exam-subject, plus a per-exam `exam_results` row holding only `is_published`/`remarks` (§7).
**Compute, don't store:** total marks, percentage, grade, pass/fail — these are pure functions of the `marks` rows for that student+exam and the `exam_subjects.max_marks`/`pass_marks`. Storing them risks drift (someone corrects one subject's mark and the "total" silently goes stale) — this is exactly the kind of derived-data duplication the brief told me to avoid.
**Grade calculation** lives in a small `ResultCalculator` service (not a model method, not a controller) so it's independently unit-testable: `calculate(Student $student, Exam $exam): ResultData` (DTO with total, percentage, grade, pass/fail).
**Publishing** is a deliberate, explicit action (Super Admin/Filament flips `is_published`) — students never see marks the moment a teacher enters them; this also gives you a natural point to fire a `ResultPublished` event → notification.

## 14. Fee Architecture

`fee_structures`/`fee_structure_items` define what's charged per class per year; `student_fees` is the per-student assignment (allowing per-student discounts without touching the shared structure); `fee_payments` records actual money received. Outstanding balance is always **computed** (`amount_payable − sum(payments)`), never stored, for the same staleness reason as results. No payment gateway integration now — `payment_method` enum already reserves an `online` slot, and `fee_payments.reference_no` is generic enough to later hold a gateway transaction ID without a schema change. When a gateway is added later, it plugs in as a new payment-creation path, not a rearchitecture.

## 15. Notification Architecture

Use Laravel's built-in **Notification** system from day one, even for database+email only — this is the extensibility point the brief asked for. A notification class (e.g. `FeeReceiptNotification`) defines `via()` returning `['database', 'mail']` today; adding `'whatsapp'`/`'nexmo'`(SMS) later is adding a channel to that array plus a `toWhatsApp()` method — zero changes to the code that *triggers* the notification. Trigger points: `ResultPublished`, `FeePaymentRecorded`, `NoticePublished`, `AttendanceMarkedAbsent` (optional, guardian alert). Queue all notifications (`ShouldQueue`) so a slow SMS/email provider never blocks a teacher's request.

## 16. Event / Listener / Observer Architecture

Used only where they earn their place — not by default:

| Use it for | Don't use it for |
|---|---|
| `StudentEnrolled` → `SendWelcomeCredentials` listener (decouples "a student was created" from "how they're notified") | Simple CRUD where nothing else needs to react (most `notices`/`events`/`news` CRUD — just save it in the controller/Service) |
| `FeePaymentRecorded` → `GenerateReceipt` job → `SendReceiptNotification` (multi-step side effects, one of which is slow enough to queue) | Attendance saving (single write, nothing downstream reacts) |
| A `StudentObserver::deleting()` guarding against deleting a student with payment history (soft-delete instead) | Business logic that belongs in the `ResultCalculator` service — don't hide calculation inside an observer where it's hard to find/test |

## 17. Service Layer Architecture

Thin controllers everywhere. A Service class is introduced **only** where real orchestration/calculation exists:

- `ResultCalculatorService` (§13) — genuine logic, needs unit tests independent of HTTP.
- `FeeAssignmentService` — applying a fee structure + discount to produce `student_fees` rows for a whole class at once; multi-step, transactional.
- `AttendanceBulkService` — validating and inserting a whole section's attendance in one transaction.

**Everything else (Notices, Events, News, Gallery, Subjects, Classes/Sections CRUD, Admission enquiries)** goes straight from Controller → Form Request → Eloquent. Adding a Service class around a single `Model::create($validated)` call is exactly the overengineering the brief warned against — resist it even when it feels "more architectural." No Repository layer anywhere: Eloquent models already are the repository abstraction for a single-database Laravel app; a Repository interface here would just wrap Eloquent with no swappable implementation ever coming.

---

## 18. Filament Architecture

**Version: Filament v3.x** (`composer require filament/filament:"^3.0-stable" -W`). Verified requirements: PHP 8.1+, Laravel 10.0+, Livewire v3.0+, Tailwind v3 — all satisfied by your stack. **Do not install Filament v4** — it requires Laravel 11.28+ and PHP 8.2+, neither of which you have; Composer will refuse the install or (worse, if version constraints are loose) pull in packages that break the rest of the app.

**Panel:** single `AdminPanelProvider` at `/admin`. Auth: `->authGuard('web')` (default — same guard as the rest of the app, per §4) plus `canAccessPanel()` on `User` (§4) as the actual gate.

**Resources** (Filament CRUD screens) for: Students, Teachers, Classes, Sections, Subjects, AcademicYears, Exams, ExamSubjects (relation manager under Exam, not a top-level resource), Fees (FeeTypes/FeeStructures as resources, FeeStructureItems as a relation manager), FeePayments, Notices, Events, News, Gallery, AdmissionEnquiries, SchoolSettings (a single-record "settings" page, not a resource — Filament's `Page` class fits this better than a Resource since there's exactly one row).

**Relation managers** over separate resources where the child only makes sense in the parent's context: `ExamSubjects` under `Exam`, `FeeStructureItems` under `FeeStructure`, `GalleryImages` under `Gallery`.

**Widgets/Dashboard:** a small stats-overview widget (student count, today's attendance %, pending fees) — resist building a full analytics dashboard in v1.

**Authorization inside Filament:** Filament resources respect the same Policies as the rest of the app automatically (it calls `StudentPolicy` etc. for its own CRUD actions) — you do not write separate Filament-specific authorization rules, which is another reason the single-guard/single-Policy design (§5) pays off.

## 19. Public Website Architecture

**Blade, no Livewire needed** — this is static-ish content (About, Academics, Facilities, FAQ, Policies) plus simple listings (News, Notices, Events, Gallery) with standard pagination. A CMS-style Controller per resource (`NewsController@index/show`) reading from the same `news`/`notices`/`events`/`galleries` tables Filament manages. No JS framework — reject React/Vue per the brief's own instruction; nothing here needs client-side state.

## 20. Student Portal Architecture

**Blade + Livewire** where genuine interactivity exists (attendance calendar view with month navigation, a searchable/filterable fee-payment history) — plain Blade+Controller for everything else (viewing a single result, editing profile). Livewire is the right middle ground here: it avoids hand-rolling a JS SPA while still giving reactive filtering/pagination without full page reloads.

## 21. Teacher Portal Architecture

Same stack as the student portal. The one place Livewire earns clear value: **attendance entry** (a live-updating list of students in a section where a teacher taps present/absent/late per row) and **marks entry** (a spreadsheet-like grid across students for one exam-subject) — both benefit from reactive UI without a full form-per-student-per-submit round trip.

## 22. API Architecture

Not built now — scaffolded so it's a pure addition later:

- Route file exists but near-empty: `routes/api.php` with a versioned prefix reserved (`Route::prefix('v1')->group(...)`).
- **Sanctum** (already ships with Laravel 10, compatible) is the auth mechanism when API work starts — token-based, works cleanly against the same single `users` table/role column.
- API Resources (`StudentResource`, etc.) wrap Eloquent models for JSON output — write these lazily, only when the first endpoint is actually needed.
- Same Policies reused for API authorization — this is the payoff of putting authorization in Policies rather than in controllers: the mobile API calls the identical `$this->authorize()` checks the web portals already use.
- Rate limiting via Laravel's default `throttle` middleware once endpoints exist. Standard `{data, message}` JSON envelope, versioned by URL prefix (`/api/v1/...`) so a breaking v2 doesn't disturb the mobile app mid-flight.

## 23. Security Architecture

- **Authentication/hashing:** Laravel default `bcrypt`/`Hash::make` — no custom hashing.
- **CSRF:** on by default for all Blade/Livewire forms; nothing to add.
- **SQL injection:** Eloquent/query builder parameter binding throughout — no raw string-concatenated SQL, ever, even for "just an internal admin filter."
- **XSS:** Blade's `{{ }}` auto-escapes; only use `{!! !!}` for genuinely trusted admin-authored HTML (e.g. a rich-text notice body from Filament's WYSIWYG), and even then, sanitize on save.
- **Mass assignment:** every model declares `$fillable` explicitly (never `$guarded = []`); Form Requests validate the exact field set before it reaches the model.
- **IDOR — the `/student/results/15` → `16` problem, directly answered:** route-model binding resolves the `Result` model from the URL fine, but the controller **must** call `$this->authorize('view', $result)` before returning anything (§5's `ResultPolicy`). The URL is never the security boundary; the Policy is. This applies to every single-resource route in both portals (`/student/results/{result}`, `/student/fees/{payment}`, `/teacher/attendance/{attendance}` for edits, etc.) — treat "did I add the authorize() call" as a checklist item on every new controller method that takes a model parameter, and cover it with a Feature test (§25).
- **Session security:** default Laravel session config (HttpOnly, `SameSite=lax`) is sufficient; set `secure` cookies once deployed over HTTPS.
- **File uploads:** see §24.
- **Rate limiting:** login routes get `throttle:5,1`-style limiting (via `RateLimiter::for` in a service provider) to blunt credential stuffing against student/teacher logins.
- **Sensitive info/logging:** never log request bodies for login/password-reset routes; scrub `password`/`password_confirmation` if you ever log full requests for debugging.
- **Database:** XAMPP MySQL root user for local dev only — production `.env` must use a dedicated DB user with only the privileges the app needs (no `DROP`/`GRANT` on a shared host), covered in §27's `.env` guidance.

## 24. File Upload Architecture

- **Storage:** Laravel's `Storage` facade, `local` disk (via `public` symlink, `php artisan storage:link`) for anything that must be browser-viewable (student photos, gallery images, teacher photos); a **non-symlinked private disk path** for anything sensitive (admission documents, certificates) served only through an authorized controller action (`Storage::disk('private')->download(...)` gated by a Policy) — never a direct public URL for private documents.
- **Validation:** every upload Form Request validates `mimes:jpg,jpeg,png` (photos) or `mimes:pdf,jpg,png` (documents) plus `max:2048` (KB) — reject by extension **and** MIME sniffing (Laravel's `mimes` rule does both).
- **Naming:** never trust the original filename — store as `Str::uuid().'.'.$extension`, keep the original name only as a display-label DB column if needed. Prevents path traversal and filename collisions.
- **Image handling:** no image-manipulation package needed at v1 (no resizing/thumbnailing requirement stated) — if added later, `intervention/image` is Laravel-10-compatible.

## 25. Testing Architecture

Priority order (most important first, matching the brief's own examples):

1. **Authorization/Feature tests** — the highest-value tests in this app: "a student cannot view another student's result" (assert 403 on `GET /student/results/{other}`), "a teacher cannot mark attendance for a class they're not assigned to," "a non-super_admin gets redirected/403 from `/admin`." These directly test the IDOR concern from §23.
2. **Feature tests for core flows** — login (student/teacher/admin, correct redirect per role), attendance submission, marks entry, fee payment recording.
3. **Unit tests** for the two Services with real logic — `ResultCalculatorService` (grade boundaries, pass/fail edge cases) and `FeeAssignmentService` (discount math).
4. **Database tests** — the unique constraints actually work (duplicate attendance for same student/day is rejected at the DB level, not just the app level).
5. API tests — written once §22 actually has endpoints.

Use Laravel's built-in testing (`RefreshDatabase`, factories) — no need for a separate testing package.

## 26. Performance Architecture

- **Eager loading everywhere a list is displayed with a relation:** e.g. `Student::with('enrollments.section', 'enrollments.class')` for a class roster — never let a Blade `@foreach` trigger N+1 by lazily touching `$student->enrollments` per row. Enable `Model::preventLazyLoading()` in `AppServiceProvider::boot()` in local/testing environments so N+1s throw loudly during development instead of silently degrading production.
- **Indexes:** all FK columns (automatic under InnoDB) plus the composite uniques already specified (§7) double as the indexes that make "attendance for this student this month" and "marks for this exam" fast.
- **Pagination:** every list view (students, attendance history, fee payments, notices) uses `paginate()`, never `get()` on a potentially-thousands-of-rows table.
- **Caching:** cache `academic_years` "current year" lookup and `school_settings` (both read constantly, change rarely) with a short TTL or tag-based invalidation on save.
- **Queues:** notifications (§15) and receipt generation (§16) run on the queue so a teacher submitting attendance for 40 students isn't blocked on 40 emails.
- **Bulk operations:** attendance-for-a-whole-section and marks-for-a-whole-exam are inserted via `insert()`/`upsert()` inside a single DB transaction, not one `save()` call per student in a loop — this is both a performance and a data-integrity concern (partial failure should roll back the whole batch).

## 27. Git / GitHub Architecture

- `main` = always deployable. Feature branches (`feature/attendance-module`, `feature/fee-management`) merged via PR even solo — PRs give you a review checkpoint and a clean history, and are exactly where you'd paste a ChatGPT architecture-review comment before merging.
- `.gitignore`: standard Laravel ignore (`/vendor`, `/node_modules`, `.env`, `/storage/*.key`, `/public/storage`, `/.phpunit.cache`) — confirm `.env` is listed *before* the first commit, not after.
- **`.env.example`** committed with every key present but empty/dummy values (`DB_PASSWORD=`, `MAIL_PASSWORD=`) — this is both onboarding documentation and the thing that stops Copilot/AI tools from "helpfully" inventing plausible-looking real credentials in a committed file.
- **Preventing AI tools from committing secrets:** (1) `.env` is git-ignored, full stop — no AI tool can commit what git won't track; (2) add a pre-commit check (a simple `git diff --cached | grep -E 'DB_PASSWORD|API_KEY'`-style hook, or a tool like `gitleaks` if you want it automated) that blocks a commit containing an obvious secret pattern; (3) AGENTS.md explicitly instructs Copilot/AI never to hardcode credentials in any file — always read from `config()`/`.env`.

## 28. VS Code + Copilot Workflow

Copilot reads `.github/copilot-instructions.md` automatically for every suggestion in this repo — that file is your baseline behavioral contract with it (§33). For a specific module you're about to build, open the relevant `docs/*.md` section plus the target files in the editor so Copilot's context window includes them, then let Copilot draft; you review, run tests, commit. Copilot is the implementer; this document and AGENTS.md are what keep it inside the architecture rather than inventing its own.

## 29. Laravel Boost Workflow

**Install the v1.x line, not v2.x** (`composer require laravel/boost:"^1.8" --dev` — v2.x requires PHP 8.2+/Laravel 11+, which your `8.1.10`/`10.50.3` stack doesn't satisfy; a bare `composer require laravel/boost` today would grab the latest 2.x and fail or silently pull in incompatible sub-dependencies). Boost gives your AI tools (Copilot, and any MCP-aware client) live, accurate knowledge of *your actual installed package versions* and Laravel-specific conventions via its MCP server and generated guideline files (`php artisan boost:install`) — it complements, it doesn't replace, AGENTS.md: Boost's guidelines are about "how Laravel/this package version works," your AGENTS.md is about "how *this project specifically* is structured and what rules apply here." Don't hand-edit Boost's generated files; treat them as regenerable, and keep your own architectural rules in AGENTS.md/`docs/`.

## 30. ChatGPT + Copilot Workflow

Division of labour: **Copilot writes code inline in VS Code** against the architecture in this repo; **ChatGPT is the second-opinion reviewer** — paste a diff or a new file and ask it to check against `AGENTS.md`'s rules (does this bypass a Policy? does it invent a column? does it introduce a Service where a plain Eloquent call would do?) before you merge a PR. Neither tool has the full picture alone — Copilot has your open files, ChatGPT has whatever you paste — so `docs/ARCHITECTURE.md` and `AGENTS.md` are the shared ground truth you paste into ChatGPT when a review needs project context it wouldn't otherwise have.

## 31. Markdown Documentation Architecture

| File | Purpose | Copilot reads it? | ChatGPT uses it? |
|---|---|---|---|
| `AGENTS.md` | Project-wide AI behavior rules (§32) | Yes (general context) | Yes — paste at the start of a review session |
| `.github/copilot-instructions.md` | Copilot-specific, repo-scoped instructions (§33) | Yes, automatically, every suggestion | No (Copilot-only mechanism) |
| `docs/ARCHITECTURE.md` (this file) | The full design — why, not just what | On demand (open it when working a module) | Yes — the primary reference for review |
| `docs/DATABASE.md` | Extract of §7–9 — table list, relationships, migration order | On demand | Yes, for schema review |
| `docs/AUTHENTICATION.md` | Extract of §4–5 | On demand | Yes |
| `docs/MODULES.md` | The module build-order/checklist from §38 | On demand | Yes |
| `docs/API.md` | Extract of §22, written once the API actually starts | On demand | Yes |
| `docs/SECURITY.md` | Extract of §23–24, the checklist form | On demand, especially pre-merge | Yes, for security review |
| `docs/DEVELOPMENT.md` | Local setup (XAMPP paths, `.env` keys, seed commands) | Rarely relevant to code generation | Occasionally |

Recommendation: **keep this document (`ARCHITECTURE.md`) as the single source of truth**, and only split out `DATABASE.md`/`AUTHENTICATION.md`/`SECURITY.md`/`MODULES.md` as short *extracts* once you notice yourself repeatedly scrolling to the same section — don't pre-create all of them today; that's exactly the kind of premature structure the brief warned against. `docs/DEVELOPMENT.md` (env setup) is worth creating immediately since it's genuinely different content (commands, not architecture).

## 32. AGENTS.md Design

See the companion file `AGENTS.md` (repo root) — kept deliberately short: version pins, layering rules, the "don't invent columns/relationships" rule, and the authorization-first rule. Full rationale lives here in `ARCHITECTURE.md`; AGENTS.md is the *enforceable checklist* distilled from it.

## 33. Copilot Instructions Design

See the companion file `.github/copilot-instructions.md`. **No duplication with AGENTS.md** — `copilot-instructions.md` contains only what's mechanically specific to how Copilot should behave inside this repo (file-naming conventions it should follow, "don't suggest Filament v4 syntax," "match existing Form Request patterns") and *references* AGENTS.md for the substantive rules rather than repeating them. **Path-specific instruction files (`.github/instructions/`)**: not recommended yet — with one Laravel app and consistent conventions across Models/Controllers/Migrations, a single instructions file covers it; introduce a path-specific file only if you find Copilot consistently getting one specific area wrong (e.g. Filament Resources) despite the general instructions, at which point a targeted `.github/instructions/filament.instructions.md` earns its place.

## 34. Prompt Files Design

Not needed at project start — introduce `.github/prompts/` only once you notice yourself typing the same multi-step instruction to Copilot Chat repeatedly. When you do, the highest-value ones given this project's actual shape:

- `create-module.prompt.md` — walks Copilot through the §38 module checklist for a brand-new resource (migration → model → policy → request → controller → routes → tests) so it doesn't skip steps.
- `security-review.prompt.md` — prompts Copilot/Copilot Chat to specifically check a diff against §23's IDOR/authorization checklist before you open a PR.

These *complement* AGENTS.md/copilot-instructions.md rather than duplicating them: AGENTS.md states the rules once; a prompt file is a reusable *workflow invocation* that reminds Copilot to apply them in a specific multi-step task. Laravel Boost's own guideline files are a third, separate thing again — package-level facts, not project workflow.

## 35. Skills Strategy

- **Markdown docs** (`docs/*.md`) = the *why* — read occasionally, by a human or pasted into ChatGPT.
- **AGENTS.md** = the *rules* — short, always-relevant, framework-agnostic.
- **Copilot instructions** = the *Copilot-specific mechanics* of applying those rules in this repo.
- **Prompt files** = *reusable multi-step invocations* of a workflow (build a module, run a security review).
- **Skills** (in the Copilot/agent-skill sense) = *packaged, reusable procedures* an AI agent can invoke by name across tasks — genuinely useful once you have a repeated, well-defined multi-step procedure you invoke often enough that "just describe it in AGENTS.md" stops scaling.
- **Laravel Boost** = *live package/framework knowledge*, not project rules at all.

Recommendation: **don't adopt Skills for this project's v1.** The four sources above already cover a solo developer's needs, and the brief's own "avoid unnecessary complexity" principle applies directly here. If you later find yourself repeating the exact same "build a new CRUD module" procedure five-plus times with enough friction that a prompt file isn't enough (e.g. you want it invoked identically across VS Code and another agent surface), a `laravel-module` Skill wrapping the §38 checklist is the one worth creating first — not `laravel-api`, `code-review`, or `security-review`, since those are lower-frequency and served fine by the two prompt files above.

## 36. Development Workflow

`AGENTS.md`/`docs` define the architecture → you open the relevant doc section + files in VS Code → Copilot drafts code against that context → you run the relevant tests locally under XAMPP → ChatGPT reviews the diff against `AGENTS.md` for anything Copilot missed → commit on a feature branch → PR into `main` → next module. Laravel Boost sits underneath this whole loop, keeping Copilot's package-level facts accurate without you having to explain "we're on Laravel 10, not 11" every session.

## 37. Development Phases

Your proposed order is sound with two adjustments: **Filament (Super Admin) is moved before Student/Teacher Management**, because the fastest way to create and inspect test data for every later module is through the admin panel you're about to build anyway — building Student Management's *portal* before you have any way to create students via Filament means hand-seeding everything. **Notifications move earlier**, right after Fees, since Fees is the first module with an obvious, high-value notification (`FeePaymentRecorded`) — bolting Notifications on at phase 16 after Public Website means retrofitting events into three already-built modules instead of building them in from Attendance/Exams/Fees onward.

1. Project foundation (Laravel install, `.env`, XAMPP wiring)
2. Git/GitHub setup
3. AI configuration (`AGENTS.md`, Copilot instructions, Boost install)
4. Database architecture (all migrations from §7, in dependency order)
5. Authentication (single guard, roles, login/redirect logic — §4)
6. **Filament Super Admin panel** (moved up — your data-entry tool for everything after)
7. Student Management (model/policy/CRUD, exercised via Filament first)
8. Teacher Management
9. Academic Management (Academic Years, Classes, Sections, Subjects, enrollments/assignments)
10. Attendance
11. Examinations and Results
12. Fees
13. **Notifications** (moved up — wire in on the back of Fees/Results events)
14. Student Portal (now has real data + events to surface)
15. Teacher Portal
16. Public School Website
17. API scaffolding (§22 — routes/Sanctum wired, no real endpoints yet)
18. Testing pass (the §25 priority-ordered suite, now that everything exists to test)
19. Security review (§23 checklist against the finished app)
20. Performance optimization (§26 — eager loading audit, cache the hot lookups)
21. Deployment preparation

## 38. Module Development Workflow

For a new module, in order — **mandatory** steps in bold, conditional ones marked:

1. **Requirements** (what does this module actually need to do)
2. **Database design** (does it need new tables, or does it hang off existing ones?)
3. **Migration**
4. **Model** (+ relationships)
5. Form Request (conditional — skip only for a module with zero user input, essentially never applicable)
6. **Policy** — mandatory for anything a student or teacher can reach; skip only for pure-admin, Filament-only resources where Filament's own resource-level policy call is sufficient (still write the Policy, just simpler).
7. Service (conditional — only if genuine orchestration/calculation exists, per §17)
8. **Controller**
9. **Routes**
10. Views/Livewire (conditional — not needed for an admin-only, Filament-only module)
11. Filament Resource (conditional — needed for anything Super Admin manages directly)
12. **Tests** — at minimum the authorization test ("can X access Y's data") per §25
13. Documentation update (conditional — only if the module changes something `docs/` already claims)
14. Code review (self or ChatGPT, against AGENTS.md)
15. Security review (conditional — mandatory for anything touching student PII or money; a quick pass otherwise)

---

## 39. Recommended Folder Structure

Standard Laravel 10 structure, with these deliberate additions:

```
app/
  Http/
    Controllers/
      Admin/            (thin wrappers if any custom admin controllers exist outside Filament)
      Student/           (student-portal controllers)
      Teacher/           (teacher-portal controllers)
      Public/            (public-website controllers)
    Requests/
      Student/  Teacher/  Attendance/  Exam/  Fee/  ...
    Middleware/
      EnsureUserHasRole.php
  Models/
  Policies/
  Services/
    ResultCalculatorService.php
    FeeAssignmentService.php
    AttendanceBulkService.php
  Notifications/
  Events/  Listeners/  Observers/
  Filament/
    Resources/
    Pages/
    Widgets/
resources/
  views/
    public/  student/  teacher/  livewire/
routes/
  web.php        (public + shared)
  student.php    (required via web.php, prefix /student)
  teacher.php    (required via web.php, prefix /teacher)
  api.php        (scaffolded, near-empty per §22)
docs/
  ARCHITECTURE.md
  DEVELOPMENT.md
.github/
  copilot-instructions.md
AGENTS.md
```

Splitting `routes/web.php` into `student.php`/`teacher.php` (required from `web.php` inside their respective middleware groups) keeps each portal's routes reviewable in isolation without needing separate route *files registered separately* in `bootstrap/app.php` — simpler than Laravel 11's multi-file routing, appropriate for 10.x.

## 40. Package Recommendations

All verified against **Laravel 10.50.3 / PHP 8.1.10**:

| Package | Version constraint | Purpose | Verified compatible? |
|---|---|---|---|
| `filament/filament` | `^3.0-stable` | Admin panel | Yes — Filament v3 requires PHP 8.1+/Laravel 10+. **Do not use v4** (needs Laravel 11.28+/PHP 8.2+). |
| `laravel/boost` | `^1.8` (dev) | AI dev-context/MCP | Yes — Boost 1.x supports Laravel 10.x. **Do not let Composer pull 2.x** (needs Laravel 11+/PHP 8.2+); pin the constraint explicitly. |
| `laravel/sanctum` | ships with Laravel 10 | Future API auth | Yes, already present |
| `spatie/laravel-activitylog` | `^4.x` (check current tag against PHP 8.1 at install time) | Optional audit logging (§25) | Verify at install time — check its composer.json `require.php` before running `composer require` |
| `livewire/livewire` | `^3.0` | Reactive portal components | Yes — Filament v3 itself requires Livewire v3, so this is already a transitive dependency; no separate version conflict risk |
| `intervention/image` | only if/when image resizing is actually needed | Not installed for v1 | N/A yet |

**General rule going forward, not just for this table:** before `composer require`-ing anything, run `composer why-not <package> <version>` or simply check the package's `composer.json` `require.php`/`require.laravel/framework` fields against `8.1.10`/`10.50.3` — this is the exact check that caught Filament v4 and Boost v2.x above, and it should be a standing habit, not a one-time audit.

## 41. Version Compatibility Review

- **Filament v4 rejected** — requires `laravel/framework: ^11.28|^12.0|^13.0` and PHP 8.2+. Confirmed via Filament's own release notes and third-party packages' composer constraints. Use v3.x.
- **Laravel Boost v2.x rejected** — its own upgrade guide states PHP 8.2 is now the minimum and Laravel 11.x is now the minimum for 2.x. Use v1.x (the line that documents Laravel 10.x support in its guideline matrix).
- **Livewire v3** — required by Filament v3 anyway, and itself requires PHP 8.1+/Laravel 10+ — no conflict.
- **PHP 8.1.10 / Laravel 10.50.3 themselves** — Laravel 10 requires PHP ^8.1, so you're on the minimum-supported PHP for this Laravel version; nothing exotic here, but it does mean many newer community packages (built Laravel-11-first) will need this same check before installing — treat every future `composer require` as a compatibility check first, install second.

## 42. Risks and Architectural Decisions

| Decision | Risk if done differently | Why this call |
|---|---|---|
| One `users` table/one guard vs. multiple guards | Multiple guards: session/reset-flow bugs, more code | §4 |
| No `spatie/laravel-permission` at v1 | Adding it "just in case": unused complexity for 3 static roles | §5 |
| `student_enrollments` history table vs. `class_id` on `students` | A flat `class_id`: promotion silently destroys historical records — the single highest-impact schema mistake possible here | §7–9 |
| Computed results/fee-balances vs. stored | Storing them: silent staleness when an underlying mark/payment is corrected later | §13–14 |
| Filament v3, not v4 | v4: install failure or forced, unwanted PHP/Laravel upgrade | §18, §41 |
| Boost v1.x, not v2.x | v2.x: same as above | §29, §41 |
| Service layer only for Results/Fees/Attendance-bulk | Services everywhere: overengineered CRUD, harder to read | §17 |
| Filament moved to Phase 6 | Building portals before an admin data-entry tool exists: manual seeding overhead throughout development | §37 |

## 43. Final Recommended Architecture

One Laravel 10.50.3 application. One MySQL database. One `users` table with a `role` column and one `web` guard, gating Filament via `canAccessPanel()` rather than a second guard. `students`/`teachers` hold profile data; `student_enrollments`/`teacher_assignments` hold the year-scoped facts that make history and authorization both work correctly. Thin controllers, Form Requests for validation, Policies for every object a student or teacher can reach, Services only for Results/Fees/bulk-Attendance. Filament v3 for the back office, Blade+Livewire for the portals and public site, no frontend framework. API scaffolded, not built. Documentation lives in `AGENTS.md` (rules), `.github/copilot-instructions.md` (Copilot mechanics), and this file (rationale) — no Skills, no prompt files, no path-specific Copilot instructions until real repetition justifies them.

---

# DELIVERABLES

### 1. Folder structure — see §39.

### 2. Markdown documentation structure — see §31. Create now: `AGENTS.md`, `.github/copilot-instructions.md`, `docs/ARCHITECTURE.md` (this file), `docs/DEVELOPMENT.md`. Defer: `DATABASE.md`, `AUTHENTICATION.md`, `MODULES.md`, `API.md`, `SECURITY.md` until you're re-reading the same section of this file often enough to want a shorter extract.

### 3. Complete database table list
`users, students, teachers, academic_years, classes, sections, subjects, class_subject, student_enrollments, teacher_assignments, attendances, exams, exam_subjects, marks, exam_results, fee_types, fee_structures, fee_structure_items, student_fees, fee_payments, notices, notice_targets, events, news, galleries, gallery_images, admission_enquiries, school_settings, audit_logs` — 29 tables. See §7 for every table's purpose/keys/constraints.

### 4. Relationship map — see §8.

### 5. Authentication & authorization architecture — see §4–5.

### 6. Filament architecture — see §18. **Filament v3, not v4.**

### 7. VS Code + Copilot + Laravel Boost + ChatGPT workflow — see §28–30, §36. **Boost v1.x, not v2.x.**

### 8. AGENTS.md contents — see companion file `AGENTS.md`.

### 9. `.github/copilot-instructions.md` contents — see companion file.

### 10. Recommended future prompt files — see §34 (`create-module.prompt.md`, `security-review.prompt.md` — build these later, not now).

### 11. Skills strategy — see §35: **none adopted for v1.**

### 12. Phased development roadmap — see §37 (21 phases, Filament and Notifications reordered earlier than your draft).

### 13. Architecture decisions you should approve before implementation

1. **One `users` table + one `web` guard**, role column, Filament gated via `canAccessPanel()` — not separate tables/guards per role.
2. **No `spatie/laravel-permission`** at launch — role enum + Policies only.
3. **`student_enrollments` as the source of "current class"** — nothing on `students` itself stores current class/section.
4. **Results and fee balances are computed, not stored** — only publish-state and payment transactions are persisted.
5. **Filament v3.x, explicitly pinned** — confirm you're comfortable staying off v4 until you're ready to move the whole stack to Laravel 11+.
6. **Laravel Boost v1.x, explicitly pinned** — same tradeoff as above, scoped to the AI-tooling package.
7. **Service layer limited to three classes** (Results, Fee assignment, Attendance bulk) — everything else is Controller+Request+Eloquent.
8. **Login accepts username OR email** (not email-only) — confirm students will actually log in via admission number, since this changes the `users` schema and the login form.
9. **Development-phase reordering**: Filament moved to Phase 6, Notifications moved to Phase 13 — confirm this order works for you before Copilot starts scaffolding phase-by-phase.
10. **No API endpoints, no payment gateway, no repository pattern, no Skills** at v1 — all deliberately deferred; confirm none of these is secretly a near-term requirement I should design more concretely into v1 now rather than later.
