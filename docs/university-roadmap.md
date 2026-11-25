# University — Development Roadmap

**Architecture baseline:** University Architecture v1.0  
**Project:** University Training System  
**Repository:** https://github.com/Amir-Hkn/university  
**Planning status:** Approved scope with explicitly identified open decisions  
**Work schedule:** 6 focused hours per working day (two 3-hour sessions)  
**Target start:** November 26, 2026

## 1. Objectives and release milestones

- **Product:** Deliver a modular university management system with academic administration, learning management, commerce, publications, CMS, support, and notifications.
- **Learning:** Develop practical Laravel and PostgreSQL proficiency, including SQL, concurrency, security, testing, performance, and deployment.
- **Portfolio:** Demonstrate technical leadership through architecture decisions, maintainable code, reproducible experiments, documentation, and delivery evidence.

| Milestone | Target working day | Estimated range | Scope |
|---|---:|---|---|
| `v0.1.0` — Portfolio Ready | 35 | 30–40 days / 180–240 hours | Working academic core, tests, performance and concurrency evidence, demo data and documentation |
| `v1.0.0` — Full Release | 75 | 65–90 days / 390–540 hours | All agreed v1 modules and operational readiness |

These are planning estimates, not guaranteed deadlines. A deeper learning pace may extend the full release to 90–110 working days. Re-estimate after each phase. Day numbers are working days, and estimates assume incremental UI delivery using a component library. A public live deployment depends on VPS availability; a reproducible local demo can support the portfolio milestone without a VPS.

## 2. Architecture and engineering baseline

| Area | Decision |
|---|---|
| System | Modular Monolith, one Laravel application and one PostgreSQL database |
| Backend | Laravel 13, PHP 8.4, Layered Architecture, pragmatic DDD-Lite |
| Frontend | Next.js 16, TypeScript strict mode, App Router, Feature-Based Architecture |
| API | Versioned REST API, OpenAPI documentation, consistent errors |
| Authentication | Laravel Sanctum, secure session cookies and CSRF protection for first-party web clients |
| Authorization | One primary role per user, RBAC, granular staff permissions, resource/program scopes and Laravel Policies |
| Async processing | Laravel Events, Listeners, Jobs, Queues, Scheduler and Notifications; Redis when needed |
| Real-time | Laravel Reverb, authorized private channels and WebSocket chat |
| Infrastructure | Docker, Nginx, GitHub Actions, GHCR; VPS staging and production when available |
| Observability | Structured logs, correlation IDs, health checks; Prometheus and Grafana at operational maturity |
| Standards | SOLID, Clean Code, KISS, DRY, meaningful domain naming, minimal abstractions |
| Language | Persian/RTL UI; English code, technical documentation and commit messages |

### Logical modules

Identity, Authorization, Academic, Semester, Enrollment, Classroom, Assessment, Tuition, Publication, Catalog, Inventory, Commerce, Finance, CMS, Support, Notification.

Audit, private file storage, error handling and correlation IDs are shared cross-cutting concerns. Module boundaries and data ownership must be documented; do not create a service or repository for every table. Keep the existing `api/`, `front/`, `docker-setup/` and CI/CD structure unless a documented change is necessary.

### Design patterns and supporting techniques

| Pattern / technique | Intended use |
|---|---|
| MVC, Service Layer, Dependency Injection | HTTP boundary, business use cases and explicit dependencies |
| Specification / composable rules | Enrollment eligibility and prerequisite checks |
| State transitions | Identity verification, article publication, orders, returns, appeals |
| Observer / Publish-Subscribe | Domain events and notification listeners |
| Adapter | Identity verification, payment and SMS providers |
| Strategy | Interchangeable algorithms only where behavior genuinely differs |
| Factory, DTO, Repository, Pipeline | Selectively, when creation, data transfer, persistence boundaries or multi-step workflows justify them |

Use Eloquent directly for ordinary persistence. Prefer explicit state transitions and enums before introducing a complex State class hierarchy. Avoid Generic Repository, full CQRS, Event Sourcing and Microservices in v1. Patterns are tools, not quotas.

## 3. Mandatory test-as-you-build policy

**Every new or modified module, service, class, function, component or other code in either the frontend or backend must receive appropriate automated tests in the same implementation step and PR whenever meaningful testing is required. Tests are not a separate later implementation phase.**

- Write tests before or alongside implementation; update existing tests whenever behavior changes. A change is not complete while its required tests are missing or failing.
- Test observable behavior, contracts and business rules rather than private implementation details. Trivial wrappers or presentational code do not require standalone tests if they introduce no meaningful behavior or are already adequately covered. Explain material omissions in the PR.
- Backend: PHPUnit unit tests for isolated rules/calculations; Laravel feature tests for API, authentication, validation and authorization; integration tests against PostgreSQL for transactions, constraints and locking. Do not use SQLite as proof of PostgreSQL-specific behavior.
- Frontend: choose and configure a lightweight unit/component testing stack during Phase 0 (proposed: Vitest + React Testing Library); test user interactions, validation, permission-sensitive views, loading/error states and relevant hooks/utilities. Use Playwright for cross-layer critical user journeys.
- Cover happy paths, invalid inputs, unauthorized and out-of-scope access, and relevant boundaries/failures. Use separate database connections/processes for real concurrency experiments.
- Use deterministic fixtures, factories, fake providers, time control and isolated test data. No real identity documents, payments, SMS or secrets in CI/demo data.
- Run relevant local tests for every change; CI must enforce required checks on pull requests. Add regression tests for bugs. Keep tests readable and maintainable under the same Clean Code rules as production code.
- Testing/hardening phases consolidate evidence, address coverage gaps, and run system-level checks; they never postpone tests for new behavior.

## 4. Roadmap at a glance

| Phase | Working days | Deliverable | Milestone |
|---|---|---|---|
| 00 — Architecture & Foundation | 1–4 | Design baseline and engineering foundation | Portfolio |
| 01 — Identity & Authorization | 5–10 | Authentication, verification, scoped access | Portfolio |
| 02 — Academic & Semester | 11–16 | Academic structure, curriculum, terms | Portfolio |
| 03 — Enrollment & Tuition | 17–24 | Safe enrollment and mock tuition payments | Portfolio |
| 04 — Classroom & Assessment | 25–31 | Class dashboards, attendance, assignments, grades, appeals | Portfolio |
| 05 — Portfolio Hardening | 32–35 | Demo, evidence, release `v0.1.0` | Portfolio |
| 06 — Publication & Profiles | 36–39 | Articles, approval, scheduling, public profiles | Full |
| 07 — Catalog & Inventory | 40–46 | Approved products, physical/digital inventory | Full |
| 08 — Commerce & Finance | 47–54 | Orders, payments, fulfillment, returns, settlements | Full |
| 09 — CMS & Page Builder | 55–60 | Public pages and configurable content blocks | Full |
| 10 — Support & Notifications | 61–66 | Tickets, WebSocket chat, complete notifications | Full |
| 11 — Production & Monitoring | 67–75 | Operational readiness and `v1.0.0` | Full |

Implement each phase as small vertical slices: domain rules, database changes, backend/API, frontend, tests, documentation and PR review. The order inside a phase is a dependency guide, not a requirement to finish all backend code before starting frontend or tests.

## 5. Detailed phases

### Phase 00 — Architecture & Foundation (days 1–4)

**Chat title:** `University — Phase 00 — Architecture & Foundation`

**Goal:** Establish the engineering baseline without rebuilding completed Docker and CI/CD work.

| Day | Steps and deliverables |
|---|---|
| 1 | Review current repository and workflows; document project scope and business rules; map module boundaries; write initial ADR; model Identity and Authorization and draft their ERD. |
| 2 | Model Academic, Semester, Classroom and Enrollment; define relationships, cardinality, identifiers and preliminary constraints; review curriculum/prerequisite and history rules. |
| 3 | Define role/permission matrix, program scopes, database conventions, time and money conventions, privacy and storage rules; decide Laravel module organization. |
| 4 | Establish API conventions, error handling, correlation IDs, testing conventions and quality tools; verify existing CI/Docker baseline and add only necessary checks. Create initial issues/milestones. |

**Learning:** DDD-Lite, Domain Model vs. ERD, ADR, module boundaries, SOLID, dependency direction, Laravel conventions, PostgreSQL constraints.

**Tests alongside changes:** Existing smoke/build tests; new foundation utility/middleware behavior tests; frontend/backend test runner verification and CI test execution.

**Exit:** Reviewed architecture docs, initial logical/physical ERDs for the first modules, documented open decisions, working test baseline, no unnecessary framework abstractions. Later modules receive their detailed ERDs before implementation.

### Phase 01 — Identity & Authorization (days 5–10)

**Chat title:** `University — Phase 01 — Identity & Authorization`

**Goal:** Secure account lifecycle and role/program-scoped access.

1. Design migrations, models, factories and role/permission seeders; enforce exactly one primary role per account.
2. Implement student registration, mobile OTP verification and login via mobile OTP or email/mobile plus password; logout, password recovery and appropriate rate limits.
3. Implement student identity verification workflow: national ID, birth date, mobile number, national ID image and latest diploma image. Use an identity-provider contract and fake adapter; integrate API.ir national-ID/mobile matching only when verified credentials and access are available.
4. Implement private document upload and authorized Education review; record approval/rejection and reasons. Define the initial program-selection step needed to route the verification case.
5. Implement Education-created teacher accounts and staff permissions/scopes, Laravel Policies and resource-level access checks. Bootstrap privileged staff using a controlled seed/administrative process rather than public registration.
6. Deliver Persian auth screens, profile/verification screens and staff review UI; document API and test roles.

**Learning:** Sanctum, Form Requests, Middleware, Policies, Service Container, DI, Adapter, hashing, sessions, CSRF, OTP expiry/abuse prevention, secure uploads.

**Tests alongside changes:** Registration and login, expired/consumed OTP, throttling, invalid credentials, provider mismatch/failure, file access, verification transitions, role restrictions, cross-program access denial, frontend form behavior and auth E2E.

**Exit:** Student can authenticate and complete simulated verification with an authorized reviewer; teacher creation and role/scope enforcement work end to end; sensitive documents are never public. Use minimal seeded academic reference data until Phase 02 completes the management UI.

### Phase 02 — Academic & Semester (days 11–16)

**Chat title:** `University — Phase 02 — Academic & Semester`

**Goal:** Deliver the academic reference model and management workflows.

1. Implement University → Faculty → Department → Academic Program; include degree level and unique program code.
2. Implement curriculum versions, course catalog, credits, general/specialized classification, curriculum-course relationships and prerequisite definitions; prevent invalid dependency cycles.
3. Implement student program history and teacher assignment: one active program per student, one program per teacher in v1. Changes require Education approval outside the active term; retain withdrawal and historical records.
4. Implement six-month academic semesters using unique codes such as `1405_2`; store separate registration, teaching, exam, add/drop and grading windows as needed.
5. Configure semester tuition tariffs and credit limits; seed general and specialized course examples.
6. Implement scoped Education management screens, student curriculum view and API documentation.

**Learning:** Eloquent relations, pivot models, migrations, composite uniqueness, normalization, indexes, date/time handling, CTEs and joins.

**Tests alongside changes:** Unique codes, invalid relationships/cycles, one active student program, program changes inside/outside a term, historical data preservation, semester dates, role/program scope, tariff validation and frontend management flows.

**Exit:** Education can manage valid curricula, programs and semesters; students have traceable academic histories and correct curriculum assignments. Enrollment rules can rely on stable reference data.

### Phase 03 — Enrollment & Tuition (days 17–24)

**Chat title:** `University — Phase 03 — Enrollment & Tuition`

**Goal:** Implement a transactional, concurrency-safe enrollment workflow.

1. Implement course offerings, unique class codes in the agreed scope, teaching assignments, recurring weekly schedules, capacity and online/in-person delivery type. General courses may serve multiple eligible programs; specialized courses are program-restricted. Validate teacher scheduling conflicts.
2. Implement fixed tuition invoices and Mock Payment Gateway; only verified students who have paid fixed tuition can enroll during the registration window.
3. Implement enrollment rules: curriculum eligibility, prerequisite history, repeated prerequisite enrollment after failure, no overlapping schedule, hard capacity, configurable credit limits and student status. Apply minimum credits at the appropriate finalization stage rather than blocking the first course selection.
4. Use transactions, PostgreSQL constraints and a consistent locking strategy for capacity, concurrent enrollment and schedule conflicts. Validate rules again under the chosen concurrency control; prevent duplicate registrations.
5. Calculate variable tuition from credit counts and semester rates by course category; preserve monetary snapshots, idempotent payment confirmation and final registration status.
6. Implement add/drop and withdrawal within permitted windows, prerequisite-dependent removal rules, capacity release and financial adjustments. Confirm unresolved nonpayment and refund policies before implementing them.
7. Deliver Education offering UI, student enrollment/cart-like selection, tuition and payment screens; add notifications/events after successful commit.
8. Run controlled race-condition, N+1 and slow-query experiments with synthetic data; retain tests and before/after evidence.

**Learning:** Specification, transactions, isolation and locks, deadlock awareness, `EXISTS`, joins, range overlap, indexes, `EXPLAIN ANALYZE`, idempotency, financial snapshots.

**Tests alongside changes:** Paid/unpaid fixed tuition; prerequisite truth table; general vs. specialized eligibility; adjacent vs. overlapping schedules; capacity race; duplicate enrollment; concurrent conflicting selections; tuition rounding and payment replay; add/drop and frontend enrollment/payment E2E.

**Exit:** Student can enroll safely, pay mock tuition and view confirmed registration; no oversubscription or double booking under tested concurrency; financial and academic records remain consistent.

### Phase 04 — Classroom & Assessment (days 25–31)

**Chat title:** `University — Phase 04 — Classroom & Assessment`

**Goal:** Deliver operational classroom and assessment workflows.

1. Build classroom dashboards for students and teachers; show ongoing/completed classes and student outcomes (passed/failed) without exposing private records.
2. Generate or manage class sessions from recurring schedules; implement cancellations and makeup events, exams, homework and other announcements. Check conflicts for actual makeup sessions when applicable.
3. Implement per-session attendance, assignment uploads, submission status and teacher review with controlled file access.
4. Implement final grade (0–20, passing threshold 10), publication and audit trail; no weighted assignment or midterm grading in v1.
5. Implement grade appeals: student has three days after publication; teacher has three days after submission; overdue appeals escalate to Education. Finalized grade corrections require Education permission and a reason.
6. Implement teacher-requested student removal with Education approval and preserve historical records. Connect class notifications to relevant events.
7. Deliver dashboards, attendance and assignment UI, final grades and appeal flows; document state transitions and access rules.

**Learning:** State transitions, Laravel Scheduler, events, queue jobs, secure storage, temporal rules, audit logs, optimistic locking where concurrent edits warrant it.

**Tests alongside changes:** Attendance uniqueness, assignment ownership, grade range and pass boundary, appeal time boundaries, escalation idempotency, post-finalization edits, class membership permissions, event changes and UI/E2E flows.

**Exit:** Authorized teachers and students can complete a simulated class lifecycle including grade appeals; Education can review attendance and approved removals. No real meeting links or room allocation required.

### Phase 05 — Portfolio Hardening (days 32–35)

**Chat title:** `University — Phase 05 — Portfolio Hardening`

**Goal:** Publish an independently demonstrable academic-core release.

1. Review critical E2E flows and test gaps; run PostgreSQL integration and concurrency suites; verify permission boundaries and private data exposure.
2. Document N+1, slow-query/index and race-condition experiments, with reproducible commands, datasets, results and trade-offs. Verify Laravel rate limiting and frontend debouncing where appropriate.
3. Create synthetic demo data, role walkthroughs, screenshots if useful, setup instructions and API documentation. Ensure demo credentials are isolated from production.
4. Review CI, container images, smoke tests and rollback rehearsal; fix remaining critical defects; tag `v0.1.0` and record release notes. Deploy if infrastructure is ready, otherwise provide a reproducible Docker demo.

**Learning:** Performance evidence, automated quality gates, security review, regression testing, technical communication and release management.

**Tests alongside changes:** All new fixes include regression tests; run full existing suites, end-to-end demo flow, health checks and deployment smoke tests. This phase is verification, not first-time feature testing.

**Exit:** A reviewer can run the academic core and see architecture, tests, representative data and documented engineering trade-offs. Unfinished full-release features are explicitly listed. Portfolio release is suitable to present without claiming that all v1 functionality is complete.

### Phase 06 — Publication & Profiles (days 36–39)

**Chat title:** `University — Phase 06 — Publication & Profiles`

**Goal:** Deliver academic publications and privacy-conscious public profiles.

1. Implement article draft, submission, approval/rejection, scheduled publication and published states; only a student's or teacher's own Education scope approves articles.
2. Implement article editing and publication rules, scheduled jobs and private draft/public published access; sanitize rich text.
3. Build Persian article pages with SEO metadata and public student/teacher profiles. Expose only approved public fields, articles, completed class history and approved teacher books when Catalog is available; never expose grades, attendance, private contact details or current location/schedules.
4. Implement staff review and author article dashboards; publish approval notifications.

**Learning:** Policies, scheduled jobs, state transitions, cache invalidation, content security and SEO.

**Tests alongside changes:** Ownership and scope, invalid transitions, scheduled publishing, draft privacy, sanitization, public payload allowlists, frontend rendering and metadata behavior.

**Exit:** Articles can be approved and published; public profiles reveal only permitted fields. Teacher book display is completed when Catalog integrates in Phase 07.

### Phase 07 — Catalog & Inventory (days 40–46)

**Chat title:** `University — Phase 07 — Catalog & Inventory`

**Goal:** Deliver a restricted store catalog with reliable stock management.

1. Design categories, product types, unique SKU, prices, seller ownership and inventory movement records.
2. Implement university products (books and equipment) and teacher book submissions (teacher's own program only), including digital products; store management approves teacher listings.
3. Implement physical stock and inventory adjustments, reservation/release operations and protection against overselling; design digital entitlement boundaries for Commerce.
4. Implement product images/files and private digital assets; enforce upload validation and protected storage.
5. Build authenticated student/teacher catalog, search/filter/pagination, product details, teacher seller interface and store management UI. Staff access is restricted to authorized management operations; public catalog access is denied.
6. Integrate approved teacher books into public profile metadata without exposing restricted storefront routes or digital files.

**Learning:** Eloquent filtering, pagination, indexing, inventory transactions, role/ownership rules, file storage, debouncing and query performance.

**Tests alongside changes:** SKU uniqueness, listing eligibility/approval, public access denial, stock adjustment validation, concurrent last-unit reservation, protected assets, frontend catalog/search behavior.

**Exit:** Approved products are discoverable by authorized buyers; stock and seller scope are enforced. Commerce can reserve stock and issue digital entitlements without bypassing Catalog/Inventory boundaries.

### Phase 08 — Commerce & Finance (days 47–54)

**Chat title:** `University — Phase 08 — Commerce & Finance`

**Goal:** Deliver safe purchase-to-settlement flows without a full accounting platform.

1. Implement shopping cart and checkout with university and teacher products; only students and teachers can purchase. Confirm multi-seller cart policy before final implementation.
2. Implement order/item snapshots, pricing and stock reservation; reuse Mock Payment Gateway; validate payment callbacks idempotently and expire unpaid reservations safely.
3. Implement fulfillment/shipping, manual tracking codes and protected downloads after confirmed purchase. Track order, payment, shipping, return and settlement states separately.
4. Implement physical return requests within the proposed seven-day delivery window and store-manager review; digital exceptions for inaccessible or faulty files. Support consistent refund and inventory changes, with replay protection.
5. Implement fixed 15% university commission on teacher sales, financial ledger entries, pending/available seller balance, manual settlement requests and store-management confirmation. Define commission rounding and prevent duplicate or excessive settlements.
6. Deliver checkout, buyer orders, seller sales/settlement dashboards and store management workflows; emit relevant notifications after commit.
7. Document failure recovery, transaction boundaries and concurrency tests for stock, payment, refund and settlement.

**Learning:** Transactions, idempotency, Adapter, state transitions, ledger design, integer money, aggregate SQL and race-condition control.

**Tests alongside changes:** Authorization, stock reservation race, price snapshot, repeated payment callback, paid/unpaid digital access, return deadlines, refund replay, ledger consistency, commission split, concurrent settlement, checkout E2E.

**Exit:** End-to-end simulated purchases and manual teacher settlements work; no negative stock or duplicate financial effects under tested scenarios. No real gateway or automatic bank transfer required.

### Phase 09 — CMS & Page Builder (days 55–60)

**Chat title:** `University — Phase 09 — CMS & Page Builder`

**Goal:** Provide a constrained, maintainable content editing system.

1. Implement CMS admin/editor/viewer permissions and public-page create/edit/delete workflows; separate CMS access from academic/financial data access.
2. Design block-based pages with validated block type, order and settings; use PostgreSQL JSONB for appropriate block configuration and schema/version conventions.
3. Build allowlisted blocks (Hero, text, images, CTA, feature grid) and matching Next.js renderer. Do not execute arbitrary user-provided JavaScript/HTML.
4. Implement draft, preview, publish, revisions and relevant SEO metadata/cache invalidation.
5. Build Persian editing UI, configurable Hero Section and public-page renderer; document content limits and extension procedure.

**Learning:** JSONB, structured validation, component registry, revision history, scoped permissions, XSS prevention and caching.

**Tests alongside changes:** Block schema validation, unknown/unsafe block rejection, permissions, draft visibility, preview/publish, revisions, frontend block rendering and editor E2E.

**Exit:** Authorized editors can create and publish public pages using validated blocks; published rendering and private previews behave correctly.

### Phase 10 — Support & Notifications (days 61–66)

**Chat title:** `University — Phase 10 — Support & Notifications`

**Goal:** Complete program-scoped support and reliable multi-channel notifications.

1. Implement support tickets, conversation history, assignment/status and scoped access. Students/teachers route to support for their program; staff permissions control visibility and replies.
2. Implement persistent chat messages and Laravel Reverb broadcasting with authorized private channels; authenticate subscriptions, save before broadcast, handle reconnect/history and duplicate delivery appropriately.
3. Complete Laravel Notification channels for database/in-app, email and SMS; use queued delivery, fake providers for CI/dev and real integrations only when available.
4. Integrate outstanding domain events across Identity, Enrollment, Classroom, Publication, Commerce and Support. Add retries, failure handling, deduplication where necessary and post-commit dispatch for transactional operations.
5. Build ticket/chat dashboards, unread indicators and notification UI; test relevant abuse/rate limits and WebSocket authorization.

**Learning:** Events, listeners, jobs, queues, retries, broadcasting, Reverb, private channels, asynchronous testing, idempotent consumers.

**Tests alongside changes:** Cross-program ticket/chat denial, private-channel authorization, persisted message history, notification recipients, queued delivery/retry, fake email/SMS, frontend unread/chat behavior and representative real-time integration checks.

**Exit:** Scoped tickets and real-time chat work with stored history; in-app/email/SMS channels are testable, with unavailable external services transparently simulated.

### Phase 11 — Production & Monitoring (days 67–75)

**Chat title:** `University — Phase 11 — Production & Monitoring`

**Goal:** Validate operational readiness and release the complete agreed v1 scope.

1. Review system-wide permissions, input handling, private files, secrets, dependency risk and relevant security tests; fix defects with regression tests.
2. Review performance on representative data, Redis caching/queues, Nginx rate limiting, worker/scheduler lifecycle and PostgreSQL indexes. Record measurable results, not assumed improvements.
3. Complete structured logging, correlation IDs, health/readiness checks and Prometheus/Grafana monitoring for selected actionable metrics; protect monitoring endpoints.
4. Configure backups and verify restoration; rehearse migration compatibility, rollback and failure handling. Do not assume an application rollback reverses database migrations.
5. Complete staging/production CD when VPS is available: immutable GHCR images, automated staging deployment and smoke tests, human acceptance, production release approval and retention of two release image versions. Verify live deployment rather than claiming it from CI alone.
6. Run full regression and E2E suites, perform user acceptance walkthroughs, finalize architecture/API/operational documentation and CHANGELOG, tag `v1.0.0` after release criteria pass.

**Learning:** Nginx, CI/CD, monitoring, incident diagnosis, rollback, backups, security hardening and operational trade-offs.

**Tests alongside changes:** New infrastructure/configuration changes receive appropriate smoke/integration checks; defects get regression tests; verify health, worker/scheduler, rate limits, restore, deployment and rollback procedures.

**Exit:** All agreed v1 features pass their acceptance criteria, critical defects are resolved, and deployment/operations are reproducible. If VPS or external services are unavailable, explicitly label the release as locally validated and list outstanding live-production verification; do not claim production readiness beyond evidence.

## 6. Definition of Done (every slice and phase)

- [ ] Requirements and acceptance criteria are clear; unresolved rules are not silently invented.
- [ ] Relevant architecture/domain/ERD decisions are reviewed before implementing new data structures.
- [ ] Code is readable, domain-named, appropriately typed, and follows SOLID/Clean Code without overengineering.
- [ ] Validation, authorization and program/ownership scope are enforced server-side.
- [ ] Database constraints, transaction boundaries, indexes and migration safety are considered where relevant.
- [ ] Required frontend/backend tests are written or updated in the same change; relevant tests pass.
- [ ] Security, failure paths and meaningful edge cases are tested; regressions have tests.
- [ ] Related UI/API contract and documentation are completed; no undocumented breaking changes.
- [ ] Code review, applicable static analysis/style checks and required CI checks pass before merge.
- [ ] Phase exit criteria and outstanding risks are documented; commit/PR and evidence references are recorded.

## 7. Learning and evidence plan

| Topic | Main phase / evidence |
|---|---|
| Laravel fundamentals, authentication, DI and policies | 01–02; explain and implement core request/auth flows |
| Eloquent and SQL | 02–03 onward; handwritten JOIN, EXISTS, CTE and aggregates, parameter binding, compare ORM/query builder where useful |
| N+1 and slow queries/indexing | 03, 05, 07, 11; before/after query counts and execution plans |
| Race conditions | 03, 07, 08; concurrent enrollment, inventory and payment/settlement regression tests |
| Backend rate limiting | 01, 05, 10; OTP and abuse protection |
| Frontend debouncing | 03 or 07; tested search behavior |
| Events, queues, scheduler and WebSockets | Introduce with relevant features; complete in 10 |
| Nginx and edge rate limiting | 11; configuration and repeatable checks |
| CI/CD | Existing baseline, continuously extended; releases in 05 and 11 |
| Monitoring | 11; dashboards, logs, health and failure exercises |
| Kubernetes | Independent post-v1 learning lab; not a release dependency |

Practice SQL directly in PostgreSQL when useful, then compare with Laravel Query Builder/Eloquent. Use parameter binding, inspect `EXPLAIN (ANALYZE, BUFFERS)` on safe test workloads, and document actual measurements. Do not force Raw SQL into ordinary CRUD. Learn by implementing and explaining decisions independently, not merely copying generated code.

## 8. Business-rule decisions and outstanding details

### Confirmed baseline

- Roles: Student, Teacher, Education, Store Management, Site Admin (CMS), Support. Exactly one primary role; granular permissions for staff, program scope for Education and Support.
- Student registration is public; Education creates teachers. Identity verification combines phone/national-ID matching and manual document review. Students can change program/degree outside the term with Education approval and recorded withdrawal/history; teachers belong to one program in v1.
- A prerequisite is satisfied by a prior pass, or by prior failure followed by re-enrollment in that prerequisite in the current term. No overlapping class times; hard capacity, no waiting list; adjustable credit limits. General courses may serve multiple programs.
- Fixed tuition must be paid before enrollment, followed by credit-based variable tuition and final registration. General/specialized rates configurable per semester; money stored in integer IRR and displayed in toman. Mock payment first.
- Semester codes such as `1405_2` represent six-month academic terms. Class delivery type is online or in-person only; real meeting links and physical rooms are out of scope.
- Final grade is 0–20, pass at 10; three-day appeal and three-day teacher response; overdue appeals go to Education; finalized corrections require Education and a recorded reason.
- Store purchase is restricted to students/teachers; teacher sales limited to books of their own teaching program; store-management approval; digital products supported; commission fixed at 15%; manual settlement and shipping management.
- Articles approved only by the author's Education scope; published articles public. Public profiles exclude grades, attendance and other private records. Persian UI; WebSocket chat and email/SMS/in-app notifications.

### Open or proposed details (confirm before affected implementation)

| Topic | Working proposal / unresolved detail | Resolve by |
|---|---|---|
| Program routing and staff bootstrap | Initial program request before identity review; controlled privileged staff provisioning | 00–01 |
| Program scope and codes | Exact staff assignment cardinality, class code uniqueness scope, shared-class ownership | 00–03 |
| Calendar | Asia/Tehran university timezone, Jalali display, UTC timestamps; exact term/window boundary rules | 00–02 |
| Tuition rates | Shared semester tariff across programs unless program-specific rates are required | 02 |
| Enrollment | Nonpayment deadline, reservation expiry, minimum credits at finalization, handling dependent-course removal | 03 |
| Tuition adjustment | Proposed tuition credit after paid class removal; confirm refund/accounting behavior | 03 |
| Appeal deadlines | Proposed 72-hour elapsed windows; confirm exact boundary and escalation/finalization details | 04 |
| Profiles | Proposed completed-class history only; use synthetic demo identities and a public-data allowlist | 06 |
| Commerce | Multi-seller cart, seven-day physical return, digital exceptions, commission rounding and settlement eligibility details | 07–08 |
| External integrations | API.ir, email/SMS provider access, real payment credentials and VPS availability are not verified | Relevant phase |

## 9. Explicitly deferred scope

| Proposed release | Scope |
|---|---|
| `v1.1.0` | Buildings, rooms and physical classroom reservation/conflict prevention |
| `v1.2.0` | Course equivalency and extended program-transfer rules |
| `v1.3.0` | Midterm, assignment and weighted grade calculations |
| `v1.4.0` | Optional public-profile visibility/privacy settings |
| Future | Real Google Meet integration, real payment gateway, other approved enhancements |
| Independent lab | Kubernetes deployment after the application core is ready |

Future version numbers are tentative. Record new requirements in the backlog and assess impact before expanding v1.

## 10. Repository documentation and phase handoff

Maintain these documents incrementally in the existing repository:

```text
docs/
  requirements/
    project-scope.md
    business-rules.md
    roles-and-permissions.md
  architecture/
    overview.md
    module-boundaries.md
    domain-model.md
    design-patterns.md
  adr/
  database/
    erd/
    conventions.md
  api/
  testing/
  performance/
  deployment/
  roadmap.md
README.md
CONTRIBUTING.md
CHANGELOG.md
```

Initial ADR topics: Modular Monolith; Layered Architecture/DDD-Lite; single-role scoped authorization; PostgreSQL/persistence; Sanctum/authentication; enrollment concurrency; financial transactions; events/queues/notifications; block-based CMS; Reverb/WebSockets. Each ADR records Context, Decision, Alternatives, Consequences and Status. Update or supersede rather than silently rewriting accepted decisions.

### Starting a separate phase chat

Open a new chat within the University project using the chat title listed under the phase. Attach/reference this roadmap and relevant latest repository documents, then use the following prompt:

> Start University Phase [NN] — [Phase Name] using the approved University roadmap and latest phase handoff. Review the repository state, completed dependencies and open decisions before implementation. Work interactively in small steps: explain the concept and trade-offs, let me implement/run the code, review results, and proceed after verification. Respect SOLID, Clean Code, module boundaries and the mandatory test-as-you-build policy for both frontend and backend. Do not defer required tests to another phase. Use handwritten SQL when it has genuine learning or engineering value. Keep technical documentation in English and coaching explanations in Persian.

At the end of each phase, record a handoff (suggested path `docs/handoffs/phase-NN.md`):

```markdown
# Phase NN Handoff
- Status: Complete / Partial / Blocked
- Completed scope:
- Acceptance criteria verified:
- Key decisions and ADR references:
- Migrations / API changes:
- Frontend and backend tests added or updated:
- Test commands and actual results:
- Performance / security evidence:
- PRs, commits and release tags:
- Known issues, risks and deferred work:
- Actual hours vs. estimate:
- Next phase prerequisites and first task:
```

This roadmap records the agreed plan, not proof of implementation. Verify the repository and test results at the beginning and end of each phase. Keep this file current when approved scope or estimates change.
