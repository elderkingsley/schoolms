# SchoolMS SaaS Migration Plan

Prepared for: Elder Kingsley
Date: 2026-05-19
Source app: `/var/www/backyardfarms/schoolms`
Target: duplicate the current SchoolMS codebase into a SaaS workstream, then convert it from a single-school install into a multi-tenant product.

## Executive Summary

SchoolMS is currently built as one school running one application and one database. To become SaaS, the system needs a tenant layer so every student, parent, teacher, invoice, payment, result, setting, upload, queue job, and webhook belongs to exactly one school.

The recommended launch architecture is:

- One codebase.
- One shared database.
- A `schools` table as the tenant registry.
- A `school_id` column on tenant-owned tables.
- Global Eloquent tenant scoping through a `HasSchool` trait.
- Subdomain-based tenant resolution, for example `nurtureville.schoolms.ng`.
- Platform-level billing and admin tools separated from school-level portals.
- PayGrid made tenant-aware from day one.

This keeps the first SaaS version operationally simple while preserving a future path to dedicated databases for large schools.

## First Principle

No SaaS work should begin inside the live Nurtureville production folder. Duplicate the folder or create a new git branch/worktree first, then migrate the duplicate until it can run Nurtureville as tenant 1 without changing visible behaviour.

Recommended duplicate target:

```bash
/var/www/backyardfarms/schoolms-saas
```

Do not copy runtime/generated directories unless needed:

- Exclude `vendor`.
- Exclude `node_modules`.
- Exclude `storage/framework/cache`, `storage/framework/sessions`, `storage/framework/views`.
- Exclude old local test dumps and accidental scratch files.
- Keep `.env` out of git, and create a fresh SaaS `.env`.

## Current Codebase Facts

The current Laravel/Livewire app already contains:

- school operations: students, parents, staff, classes, subjects, sessions, terms, results, report cards;
- fee operations: fee items, fee structures, invoices, payments, parent credit;
- wallet providers: BudPay, Korapay, JuicyWay;
- PayGrid integration:
  - `app/Jobs/PushInvoiceToPayGridJob.php`
  - `app/Jobs/ProcessPayGridInflowJob.php`
  - `app/Http/Controllers/PayGridInflowController.php`
  - PayGrid config in `config/services.php`

This means PayGrid is not a later add-on. It must be included in the tenant architecture, queue job context, settings model, and webhook security model.

## Product Decision

SchoolMS SaaS should separate three identities:

1. Platform owner: manages all schools, plans, subscriptions, and support.
2. School tenant: owns its students, staff, settings, wallet credentials, invoices, and report cards.
3. Parent/student payment identity: linked to a specific school's invoices and wallet accounts.

The platform should not mix funds between schools. Each school should either connect its own provider credentials or explicitly use a platform-managed PayGrid flow with clear accounting boundaries.

## Multi-Tenancy Model

Use a single shared database with `school_id` scoping for launch.

Why:

- Fastest to build and operate.
- Simple migrations.
- Works with Laravel/Livewire patterns already in the app.
- Matches the likely early SaaS scale.
- Can later evolve to dedicated databases for premium schools.

Avoid separate databases per school at launch unless there is a hard enterprise requirement. Separate databases multiply migrations, backups, support, provisioning, and debugging.

## Core Data Changes

Create a `schools` table:

```sql
schools
- id
- name
- slug
- subdomain
- custom_domain
- plan
- plan_expires_at
- trial_ends_at
- is_active
- owner_user_id
- created_at
- updated_at
```

Add `school_id` to every tenant-owned table, including:

- users
- students
- parents
- parent_student
- parent_credits
- parent_credit_applications
- school_classes
- academic_sessions
- terms
- subjects
- class_subjects
- enrolments
- results
- student_term_comments
- student_trait_scores
- fee_items
- fee_structures
- fee_invoices
- fee_invoice_items
- fee_payments
- payment_references
- messages
- message_recipients
- lesson_notes
- teacher_registrations
- school_settings
- juicyway_webhook_events
- budpay_webhook_events
- korapay_webhook_events

Do not add `school_id` to Laravel infrastructure tables such as jobs, cache, sessions, password reset tokens, failed jobs, or migrations.

Spatie roles need special treatment. Role definitions can remain platform-wide, but role assignments must be school-scoped using Spatie teams with `school_id` as the team key.

## Tenant Runtime

Add:

- `app/Models/School.php`
- `app/Traits/HasSchool.php`
- `app/Scopes/SchoolScope.php`
- `app/Http/Middleware/ResolveTenant.php`
- `app/Http/Middleware/CheckSubscription.php`

`ResolveTenant` should:

1. Read the request host.
2. Recognize platform hosts like `www.schoolms.ng` and `admin.schoolms.ng`.
3. Resolve school subdomains like `nurtureville.schoolms.ng`.
4. Bind the current school into the Laravel container.
5. Set Spatie's active team id.
6. Run before authentication and route model binding.

`HasSchool` should:

- add a global scope for `school_id`;
- automatically set `school_id` while creating records;
- fail loudly if tenant data is created without tenant context.

## Authentication and Authorization

School-level login should happen on the school subdomain:

```text
nurtureville.schoolms.ng/login
greenfield.schoolms.ng/login
```

Platform login should be separate:

```text
admin.schoolms.ng/login
```

Important rules:

- A user belongs to one school unless a deliberate cross-school identity system is built later.
- Platform admins should not be ordinary `super_admin` users inside a school.
- Password reset lookup must be tenant-scoped.
- Route model binding must never expose records from another school.
- Impersonation must record platform admin id, school id, target user id, timestamp, and reason.

## School Settings and Storage

`SchoolSetting` currently acts like one global key/value store. Convert it to per-school settings.

New shape:

```text
school_settings
- id
- school_id
- key
- value
unique(school_id, key)
```

Cache keys must include `school_id`, for example:

```text
school_settings:1
school_settings:27
```

All uploads should be stored under a tenant prefix:

```text
schools/{school_id}/logos/...
schools/{school_id}/students/...
schools/{school_id}/documents/...
```

This applies to school logos, student photos, generated PDFs if persisted, and any future files.

## PayGrid Architecture

PayGrid must be treated as tenant-aware infrastructure.

Current single-school assumptions to remove:

- PayGrid API key in `.env` applies to one organisation only.
- PayGrid webhook secret in `.env` applies to one organisation only.
- PayGrid inflow processing can find a parent by account number without `school_id`.
- `PushInvoiceToPayGridJob` can run without restoring tenant context.

Recommended SaaS shape:

Each school has PayGrid settings:

```text
paygrid_enabled
paygrid_organisation_id
paygrid_api_base_url
paygrid_api_key
paygrid_webhook_secret
paygrid_inflow_secret
paygrid_accounting_mode
```

`paygrid_accounting_mode` should be explicit:

- `school_owned`: school has its own PayGrid organisation/ledger.
- `platform_managed`: platform manages PayGrid on behalf of the school.
- `disabled`: no PayGrid sync.

`PushInvoiceToPayGridJob` should:

- receive `invoice_id` and `school_id`, not a serialized model alone;
- restore the school context in `handle()`;
- read PayGrid credentials from school settings;
- include `school_id`, `school_slug`, or `paygrid_organisation_id` in the payload;
- skip cleanly when PayGrid is disabled for that school.

`ProcessPayGridInflowJob` should:

- receive `payload` and `school_id` or resolve school from a signed tenant identifier;
- restore tenant context before queries;
- search `ParentGuardian` and `FeePayment` inside that school only;
- make idempotency tenant-aware;
- record unmatched inflows for school review instead of silently losing them.

`PayGridInflowController` should:

- identify the target school before verifying the signature;
- use the school's webhook secret;
- dispatch the job with `school_id`;
- return quickly.

Possible webhook URL models:

```text
https://{school}.schoolms.ng/api/paygrid/inflow
https://api.schoolms.ng/webhooks/paygrid/{school_slug}
```

Recommended launch choice: school subdomain webhook URLs, because tenant resolution is simpler and consistent with BudPay/Korapay.

## Wallet Providers

BudPay, Korapay, and JuicyWay credentials should move from environment-level config into tenant settings where the school owns the merchant relationship.

Per-school settings:

```text
wallet_provider
budpay_public_key
budpay_secret_key
korapay_public_key
korapay_secret_key
korapay_bank_code
juicyway_api_key
juicyway_business_id
```

Keep platform fallback credentials only for development, demos, or explicitly platform-managed schools.

## Queue Jobs

Every queued job that touches tenant data must carry `school_id`.

Likely affected jobs:

- SendInvoiceJob
- PushInvoiceToPayGridJob
- ProcessPayGridInflowJob
- ProcessBudPayWebhookJob
- ProcessKorapayWebhookJob
- ProcessJuicyWayDepositJob
- ProcessJuicyWayPaymentJob
- PollJuicyWayDepositsJob
- ProvisionParentWalletJob
- ProvisionJuicyWayWalletJob
- SendBulkMessageJob
- SendReportCardJob

Pattern:

```php
public function __construct(
    public readonly int $schoolId,
    public readonly int $recordId,
) {}

public function handle(): void
{
    TenantContext::set($this->schoolId);
    // reload tenant-scoped models after context is set
}
```

Prefer passing ids over serialized Eloquent models for tenant jobs.

## Billing and Plans

Add platform SaaS billing:

```text
subscriptions
- id
- school_id
- plan
- status
- billing_cycle
- amount_ngn
- current_period_start
- current_period_end
- payment_provider
- provider_customer_id
- provider_subscription_id
- created_at
- updated_at
```

```text
billing_events
- id
- school_id
- type
- amount_ngn
- reference
- payload
- created_at
```

Launch plans can be simple:

- Trial: 30 days, up to 50 students.
- Basic: up to 200 students.
- Standard: up to 500 students.
- Premium: unlimited students and future custom domains.

Do not build complex metered billing first. Enforce student-count limits and subscription expiry.

## Platform Admin

Create a platform admin portal on `admin.schoolms.ng`.

Minimum launch features:

- list schools;
- view plan and subscription status;
- view student count;
- activate/suspend a school;
- extend trial or subscription;
- impersonate a school admin with audit log;
- view failed tenant jobs/webhooks;
- view PayGrid sync status per school.

Keep this separate from school `super_admin` accounts.

## School Onboarding

Onboarding should run on `www.schoolms.ng`.

Flow:

1. Owner enters school name, owner name, email, phone, and password.
2. System suggests a subdomain.
3. Owner confirms or edits subdomain.
4. System creates `schools` record.
5. System creates first school `super_admin`.
6. System seeds default roles, permissions, classes, subjects, fee items, and settings for that school.
7. Owner lands on a setup checklist.

Setup checklist:

- Upload logo.
- Confirm school name/address/phone/email.
- Create academic session and term.
- Configure classes and arms.
- Configure subjects.
- Configure fee items and structures.
- Configure wallet provider.
- Configure PayGrid if enabled.
- Invite teachers/accountants.

## Migration Phases

### Phase 0: Duplicate and Prepare

Goal: create a SaaS work area without touching live SchoolMS.

Tasks:

- Duplicate folder to a new path.
- Create a new git branch, for example `codex/saas-migration`.
- Create a separate database for SaaS development.
- Create fresh `.env`.
- Confirm baseline tests or smoke routes still run.
- Remove accidental scratch files from the duplicate before serious work.

Exit criteria:

- Duplicate app boots.
- Login page loads.
- Existing Nurtureville data can be connected safely in a dev copy.

### Phase 1: Tenant Foundation

Goal: Nurtureville runs as school id 1 with no visible change.

Tasks:

- Create `schools` table.
- Seed Nurtureville as tenant 1.
- Add `school_id` columns and indexes.
- Backfill all existing data to school id 1.
- Add `School`, `HasSchool`, `SchoolScope`, and tenant context helper.
- Add `ResolveTenant`.
- Enable Spatie teams.
- Update auth/password reset scoping.
- Update route model binding risk points.
- Update `SchoolSetting` and file storage.

Exit criteria:

- Existing Nurtureville workflows work under tenant context.
- Queries for tenant models are scoped.
- Creating tenant records without context fails.

### Phase 2: Payments and PayGrid

Goal: all payment flows are safe for more than one school.

Tasks:

- Move wallet credentials into school settings.
- Move PayGrid credentials into school settings.
- Update PayGrid push job.
- Update PayGrid inflow controller/job.
- Update BudPay/Korapay/JuicyWay webhook jobs with `school_id`.
- Add unmatched inflow logging.
- Add payment idempotency indexes with tenant awareness.

Exit criteria:

- Nurtureville invoices still sync to PayGrid.
- Inflows cannot be applied to another school's parent/account.
- Disabled PayGrid schools do not dispatch PayGrid jobs.

### Phase 3: Platform SaaS Layer

Goal: operate schools as customers.

Tasks:

- Add subscriptions and billing events.
- Add `CheckSubscription` middleware.
- Add platform admin portal.
- Add onboarding wizard.
- Add setup checklist.
- Add trial limits.

Exit criteria:

- A new school can be created without manual database edits.
- Expired schools are restricted to billing/settings/logout.
- Platform admin can support schools without using school accounts directly.

### Phase 4: Pilot School

Goal: prove isolation with one real external school.

Tasks:

- Onboard one pilot manually or through the new onboarding flow.
- Run Nurtureville and pilot school side by side.
- Test users, students, fees, invoices, report cards, wallet provisioning, and PayGrid.
- Monitor logs for unscoped queries and payment mismatches.
- Fix leaks before public launch.

Exit criteria:

- No cross-school data exposure.
- Payment reconciliation works for both schools.
- Support workflows are clear.

## Grill-Me Stress Test

Question 1: What exactly is being sold: hosted SchoolMS software only, or hosted SchoolMS plus PayGrid-backed accounting/payment operations?

Recommended answer: hosted SchoolMS with optional PayGrid integration per school. This keeps SchoolMS usable for schools that do not use PayGrid, while making PayGrid a premium/managed integration for schools that need accounting sync.

Question 2: Who owns school fee money?

Recommended answer: the school owns the funds. SchoolMS should avoid commingling school fees. If PayGrid or wallet providers are platform-managed for a school, that must be explicitly marked and auditable.

Question 3: Can one email exist in multiple schools?

Recommended answer: yes, but as separate `users` rows per school for launch. A cross-school identity layer can come later.

Question 4: What is the hard isolation rule?

Recommended answer: every tenant-owned query must be scoped by `school_id`; every queued job must restore `school_id`; every webhook must resolve `school_id` before touching tenant data.

Question 5: What is the riskiest part of the migration?

Recommended answer: payments and webhooks, especially PayGrid inflows, because a wrong tenant match can record money against the wrong school or student.

Question 6: What must be true before onboarding a second school?

Recommended answer: Nurtureville must run as tenant 1, PayGrid must be tenant-aware, and automated tests must prove that students, invoices, payments, settings, and users cannot cross school boundaries.

Question 7: What should not be built in v1?

Recommended answer: custom domains, separate database per tenant, complex usage billing, shared identity across schools, and deep self-service payment-provider onboarding.

## Immediate Next Actions

1. Choose the duplicate destination folder.
2. Create a SaaS branch/worktree.
3. Create a dev database and `.env` for the duplicate.
4. Add the tenant foundation migrations.
5. Backfill Nurtureville as tenant 1.
6. Make PayGrid tenant-aware before any pilot school goes live.

