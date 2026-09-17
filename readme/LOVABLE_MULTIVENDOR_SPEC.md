# SnapsBilling — Multi-Vendor Billing, CRM & Accounting SaaS

## 1. Product Summary

Build a multi-vendor SaaS billing/CRM/accounting platform. Each **vendor** (a business that signs up) gets a fully isolated back office to manage their own customers, invoices, estimates, contracts, subscriptions, inventory, projects, support tickets, staff, and full double-entry accounting — plus a branded client portal where *their* customers log in to view/pay invoices. Vendors must never see or query each other's data — not just row-filtered, but **data-isolated at the database level**.

Three distinct application surfaces:
1. **Platform Console** — super-admin app for the SaaS owner (manages vendors, plans, billing, platform health).
2. **Vendor Back Office** — the CRM/billing/accounting workspace each vendor's staff uses daily.
3. **Client Portal** — branded portal a vendor's own customers use to view estimates, pay invoices, raise tickets.

## 2. Multi-Vendor Data Isolation (core architectural requirement)

Every vendor must have **their own database, not a shared table with a vendor_id column.** This is a hard requirement — do not implement it as row-level `tenant_id` filtering on shared tables.

**Two-plane architecture:**

- **Control Plane DB** (one shared database): holds only platform-level data — vendor registry, subscription plans sold to vendors, platform billing/invoices for vendor subscriptions, domain/subdomain-to-vendor routing map, platform super-admin accounts, global feature flags, platform audit log. Nothing business-operational (no client invoices, no CRM data) ever lives here.
- **Vendor Databases** (one per vendor, provisioned dynamically): each vendor gets its own isolated database instance containing 100% of their operational data — customers, invoices, accounting ledgers, staff, everything in section 6b. No cross-database joins, no shared operational tables.

**Provisioning flow:** when a vendor signs up (or is approved by platform admin):
1. Create a new isolated database for that vendor (naming convention `vendor_<id>` or similar).
2. Run the full vendor schema migration against it.
3. Seed defaults: default chart of accounts, default roles/permissions, default invoice/quote number sequences, default email/SMS templates, default tax codes.
4. Store the new database's connection credentials (encrypted) in the Control Plane's vendor registry, keyed to that vendor's id/subdomain.
5. Assign the vendor a subdomain (`{vendor-slug}.yourapp.com`) or support custom domain mapping.

**Request-time tenant resolution:** every incoming request (subdomain, custom domain, or auth token) resolves to exactly one vendor id → the backend looks up that vendor's DB connection in the Control Plane registry → opens/uses a connection pool scoped to that vendor's database only for the rest of the request. The application code must never be able to accidentally query the wrong vendor's database — enforce this structurally (a single active tenant connection per request context), not just by convention.

**Implementation note for the builder (Laravel 12 backend):** Laravel has first-class support for exactly this pattern — use it directly instead of a workaround:
- Keep one `landlord`/`central` connection (defined in `config/database.php`) for the Control Plane tables (section 6a).
- For each vendor, store its DB credentials (host, database name, encrypted username/password) in the Control Plane's `vendors`/`vendor_db_connections` table.
- On each request, a `ResolveTenant` middleware determines the vendor from the subdomain/custom domain (or from the authenticated user's `vendor_id` for API/token auth), fetches that vendor's connection details from the Control Plane, and registers a runtime connection via `Config::set('database.connections.tenant', [...])` followed by `DB::purge('tenant')` / `DB::reconnect('tenant')` — then binds all Eloquent models for vendor-scoped modules to that `tenant` connection (either via a base model with `protected $connection = 'tenant';` or a global connection resolver).
- Each vendor gets a **real, separate physical MySQL/Postgres database** (not just a schema-within-one-DB trick) — i.e. true "own database" as required, achieved cleanly via Laravel's dynamic connection config rather than any Supabase-specific schema hack.
- Provisioning a new vendor = create the new database (`CREATE DATABASE vendor_123`), run `php artisan migrate --database=tenant --path=database/migrations/tenant` against it, then seed defaults (chart of accounts, roles, sequences, templates) — all triggerable from an Artisan command or a queued job kicked off at vendor signup.
- Consider building on top of a proven Laravel multi-database tenancy package (e.g. `stancl/tenancy` or `spatie/laravel-multitenancy`) rather than hand-rolling the connection-switching middleware — both support the "one full database per tenant" model natively and handle edge cases (queued jobs, cached config, storage paths) that are easy to get wrong by hand.
- Never allow a request to hold two tenant connections open at once, and never let a vendor-scoped Eloquent model fall back to the `landlord` connection (or vice versa) — enforce this with a base `TenantModel` class that all vendor-scoped models extend, so there is no code path where the wrong database gets queried.
- Do not implement isolation as a `vendor_id` column on shared tables under any circumstance — that does not meet the isolation requirement.

## 3. User Roles & Access

- **Platform Super Admin** (Control Plane): create/suspend/delete vendors, manage vendor subscription plans and platform billing, view platform-wide health/usage metrics, impersonate a vendor for support (with audit logging), manage global feature flags per plan tier.
- **Vendor Owner/Admin** (per vendor DB): full access to that vendor's workspace, manages their own staff, roles, settings, billing/payment gateway keys, branding.
- **Vendor Staff**: role-based permission matrix per module — `can_view` / `can_create` / `can_edit` / `can_delete` / `all_data` (own records vs all records), assignable per custom role, same granularity as the legacy system's `sys_roles`/`sys_permissions`/`sys_staffpermissions`.
- **Client (Customer of a vendor)**: portal-only login scoped to exactly one vendor's data — their own invoices, estimates, contracts, tickets, projects. A client account belongs to exactly one vendor database; it does not span vendors.

Auth: separate credential stores per surface — platform admins authenticate against the Control Plane; vendor staff and clients authenticate against their own vendor database. Support session cookies + optional API tokens (per-vendor API keys, like the legacy `sys_api` table) for integrations.

## 4. Tech Stack

**Backend: Laravel 12 (PHP 8.3+)**
- Eloquent ORM, with a dynamic multi-database connection layer (see section 2) — one `landlord` connection for the Control Plane, one runtime `tenant` connection per request resolved to the current vendor's own database.
- API: Laravel as a pure JSON API (`routes/api.php`), versioned (`/api/v1/...`), consumed by the separate React/Vite frontend — not Blade-rendered pages.
- Auth: Laravel Sanctum for token/SPA auth, three separate guards/providers — `platform` (super-admin, against the landlord DB), `vendor` (staff, against the resolved tenant DB), `client` (vendor's customers, against the resolved tenant DB). Each guard's user provider must only ever touch its own connection.
- Authorization: Laravel Policies/Gates per module, backed by the `roles`/`permissions`/`staff_permissions` tables in each tenant DB (mirrors legacy `sys_roles`/`sys_permissions`/`sys_staffpermissions`).
- Tenancy: `stancl/tenancy` or `spatie/laravel-multitenancy` (see section 2) for connection switching, per-tenant migrations, per-tenant queues/storage.
- Queues: Laravel Queues (database or Redis driver) for recurring invoice generation, scheduled reports, reminder emails/SMS — each job must carry and re-resolve its tenant context before touching any tenant-scoped model.
- Scheduler: Laravel Task Scheduling (`routes/console.php` / `Schedule::command(...)`) iterating all active vendors to run per-vendor recurring jobs (subscription renewals, overdue-invoice reminders).
- PDF: `barryvdh/laravel-dompdf` or `mpdf/mpdf` for invoices/quotes/contracts; `simplesoftwareio/simple-qrcode` or `picqer/php-barcode-generator` for QR/barcodes on invoices.
- File storage: Laravel Filesystem (`Storage` facade) with a per-vendor disk root/prefix (local or S3-compatible) — never a shared flat folder across vendors.
- Validation: Laravel Form Requests per endpoint; database transactions (`DB::connection('tenant')->transaction(...)`) wrapping every multi-table write (e.g. invoice + invoice_items + journal_entries).

**Frontend: React + Vite (TypeScript)**
- Vite for build/dev tooling, React Router for routing, TanStack Query (React Query) for API data-fetching/caching against the Laravel API.
- Tailwind CSS + shadcn/ui component library for the design system. Fully responsive, light/dark mode, RTL support (Arabic — see localization).
- Three separate route trees / build entry points (or one app with role-gated route groups): Platform Console, Vendor Back Office, Client Portal — sharing a common component library but distinct layouts/navigation per surface.
- Form handling: React Hook Form + Zod for client-side validation mirroring the Laravel Form Request rules.
- Auth: Sanctum SPA cookie-based auth (or bearer tokens for the client portal/API consumers) via an Axios/fetch client with an interceptor that attaches the current vendor's subdomain/tenant context to every request.
- State: React Query for server state; lightweight context/zustand only for local UI state (active theme, sidebar collapse, current impersonation banner, etc).

## 5. Core Modules (each vendor's back office)

For every module below, build both the backend data model and the vendor-facing UI (list/detail/create/edit views, filters, search, bulk actions where relevant):

- **CRM**: Leads (source, status, pipeline stages, conversion to customer), Customers/Companies, Contacts, custom fields.
- **Estimates & Proposals**: create, send, client e-sign/accept, convert to invoice.
- **Invoices & Credit Notes**: line items, tax, discounts, recurring invoices, partial payments, payment recording, credit notes (as a type of invoice, matching legacy convention, or a first-class linked entity — your call), public shareable invoice-view/pay link for clients.
- **Contracts**: templates, client e-signature, renewal reminders.
- **Subscriptions**: recurring billing plans, plan pricing tiers, auto-invoice generation on renewal.
- **Purchases / Expenses / Suppliers**: vendor bills (accounts payable side), expense categories, recurring bills.
- **Items / Inventory**: item catalog, categories, units, stock tracking, asset register.
- **Projects & Tasks**: project boards, task assignment, time logging, linked to customers/invoices.
- **Support Tickets & Knowledge Base**: ticket departments, canned responses, ticket-to-email threading, public KB articles.
- **Staff / HR**: staff accounts, attendance, time logs, roles & permissions matrix (see section 3).
- **Accounting (double-entry, full module)**: Chart of Accounts, General Ledger, Journal Entries (manual + auto-posted from invoices/bills/payments), Accounts Receivable ledger, Accounts Payable ledger, Bank Accounts & Reconciliation, Tax Codes, Accounting Periods (with period locking), Financial Reports (Trial Balance, Profit & Loss, Balance Sheet, Cash Flow, AR/AP Aging), full audit trail of every posting.
- **E-Invoicing / Tax Compliance**: pluggable per-region compliance module — ship with Saudi ZATCA e-invoicing support (QR code on invoices, XML/UBL generation, compliance CSID/production onboarding, invoice clearance/reporting logging) as an optional toggle per vendor, architected so other countries' e-invoicing mandates could be added later as additional pluggable modules rather than hardcoded.
- **Payments**: each vendor configures their own payment gateway credentials (Stripe, PayPal, Authorize.net, Braintree, manual/bank transfer) — gateway keys stored encrypted, scoped per vendor, never shared.
- **Notifications**: email templates (invoice sent, payment received, reminders) and SMS (Twilio, Vonage, or custom HTTP driver) — each vendor supplies their own provider credentials or uses a platform-shared default with usage metering.
- **Documents & Media**: file attachments on any entity, per-vendor storage scoping.
- **Reports & Dashboard**: revenue/expense charts, outstanding invoices, top customers, staff activity — dashboard widgets per role.
- **Calendar**: events, due dates for invoices/tasks/contracts, income/expense calendar views.
- **Settings**: branding (logo, colors, invoice templates), currency & multi-currency, tax rates, document numbering sequences, email/SMS provider config, payment gateway config, roles & permissions, integrations, custom domain mapping.
- **AI features (optional)**: marketing-strategy generator, dashboard insights — vendor supplies their own AI provider API key (OpenAI/Anthropic) or uses a platform-provided key with usage limits per plan tier.
- **Audit Log**: every create/update/delete on sensitive entities (invoices, journal entries, settings, staff) logged with actor, timestamp, before/after diff.
- **API Access**: per-vendor API key management for external integrations, scoped strictly to that vendor's database.

## 6. Database Design

### 6a. Control Plane (shared, platform-only)

- `vendors` (id, name, slug/subdomain, custom_domain, status, created_at, db connection reference)
- `vendor_db_connections` (encrypted connection details per vendor)
- `vendor_subscription_plans` (plan tiers sold to vendors — feature limits, seat limits, price)
- `vendor_subscriptions` (which vendor is on which plan, billing cycle, status)
- `platform_billing_invoices` (platform charging vendors for their subscription)
- `platform_admins` (super-admin accounts)
- `platform_feature_flags` (per-plan or per-vendor feature toggles, e.g. "zatca_enabled", "ai_enabled")
- `platform_audit_log` (super-admin actions, including impersonation events)

### 6b. Vendor Database Template (identical schema provisioned into every vendor's isolated DB/schema)

Group by domain (mirrors the legacy system's proven table set, renamed generically, no platform-wide sharing):

- **CRM**: customers, contacts, leads, lead_sources, lead_statuses, industries, custom_fields, custom_field_values
- **Sales documents**: quotes (estimates + proposals), quote_items, invoices, invoice_items, credit_notes (or invoice.type), orders, order_items, tax_codes, payment_methods, payment_gateway_configs
- **Purchases**: purchases, purchase_items, bills, expense_categories, suppliers
- **Items/Inventory**: items, item_categories, units, assets, asset_categories
- **Contracts**: contracts, contract_templates
- **Subscriptions**: subscriptions, subscription_plans, subscription_plan_prices
- **Projects/Tasks**: projects, tasks, time_logs
- **Support**: tickets, ticket_replies, ticket_departments, canned_responses, kb_articles, kb_groups
- **Staff/HR**: staff, roles, permissions, staff_permissions, attendance, time_logs
- **Accounting**: gl_accounts, gl_account_balances, journal_entries, journal_items, accounting_periods, ar_ledger, ap_ledger, bank_accounts, bank_reconciliations, bank_reconciliation_matches, currencies
- **E-invoicing**: zatca_logs, e_invoice_config
- **Notifications**: email_templates, email_config, email_logs, sms_templates, sms_config, sms_logs
- **Documents**: documents, media_files
- **Settings**: app_config (key/value, vendor's own settings only), numbering_sequences
- **System**: api_keys, audit_logs, activity_log, events/calendar

## 7. Vendor Onboarding Flow

1. Vendor signs up (business name, subdomain, admin email/password) → optionally goes through platform admin approval, or self-serve auto-approve depending on plan.
2. Backend provisions new isolated vendor database/schema, runs migrations, seeds defaults.
3. Vendor picks a subscription plan (or trial).
4. Vendor completes branding + first payment gateway/tax setup wizard.
5. Vendor invites staff, imports/creates customers, starts issuing invoices.

## 8. Non-Functional Requirements

- **Isolation**: structurally impossible for one vendor's request context to read/write another vendor's database — enforce at the connection-resolution layer, not by convention.
- **Security**: encrypt all stored secrets (DB credentials, payment gateway keys, SMS/email provider keys) per vendor; RBAC enforced server-side on every endpoint, not just hidden in UI; full audit trail on financial and settings changes.
- **Localization**: multi-currency, multi-language (English + Arabic at minimum, given ZATCA), RTL layout support.
- **Scalability**: vendor provisioning must be automatable (no manual DB creation steps) so onboarding new vendors is self-serve.
- **Branding**: each vendor can customize logo, colors, invoice/quote/contract templates, and portal domain.

## 9. Suggested Build Phases

1. Control Plane: vendor registry, provisioning pipeline, platform admin console, subdomain routing.
2. Vendor DB template + migrations, vendor auth, staff roles/permissions.
3. Core billing: customers, quotes, invoices, payments, credit notes.
4. Accounting module: chart of accounts, journal entries, AR/AP, bank reconciliation, financial reports.
5. Operational modules: projects/tasks, tickets/KB, inventory, contracts, subscriptions.
6. Client portal (per-vendor branded).
7. Integrations: payment gateways, SMS/email providers, e-invoicing (ZATCA), AI features.
8. Platform billing of vendors (subscription plans, plan-gated features).
