# University — Project Scope

- **Baseline:** University Architecture v1.0
- **Status:** Draft for review; based on the agreed roadmap
- **Repository:** https://github.com/Amir-Hkn/university
- **Related:** [Development roadmap](../university-roadmap.md), [Business rules](business-rules.md)

## 1. Purpose

University is a modular university management and training system. It provides academic administration, student and teacher workflows, publications, a restricted store, content management, support and notifications. It also serves as a public engineering portfolio demonstrating maintainable architecture, testing, PostgreSQL, performance, concurrency control and delivery practices.

## 2. Actors

Each account has exactly one primary role. Staff permissions and applicable resource/program scopes restrict access further. The detailed permission matrix is maintained separately.

| Actor | Responsibilities |
|---|---|
| Student | Registration, verification, academic enrollment, tuition, classroom activities, grade appeals, publications, store purchases and support |
| Teacher | Assigned classes, attendance, assignments, grades, appeals, publications, approved book sales, purchases and support |
| Education | Scoped identity review, teacher provisioning, academic administration and academic approvals |
| Store Management | Authorized catalog approval, inventory, order, return and settlement operations |
| Site Admin (CMS) | Authorized public-page content management and publishing |
| Support | Program-scoped ticket and chat handling |
| Public visitor (unauthenticated, not an account role) | Access to published public pages, articles and approved public profiles; no restricted storefront or private records |

## 3. Functional scope and milestones

### v0.1.0 — Portfolio Ready

| Module | In scope |
|---|---|
| Identity | Student registration, OTP/password authentication, identity verification, private documents and account provisioning |
| Authorization | Single primary role, granular staff permissions and resource/program-scoped authorization |
| Academic | University hierarchy, programs, curricula, courses, prerequisites and academic history |
| Semester | Six-month terms, academic windows and configurable academic/tuition settings |
| Enrollment | Offerings, schedule validation, capacity, prerequisites, credits, registration and add/drop |
| Tuition | Fixed and variable tuition, simulated payment and financial snapshots |
| Classroom | Class dashboards, sessions, attendance, assignments, announcements and approved removals |
| Assessment | Final grades, publication, appeals, escalation and audited corrections |

Relevant notifications, file protection and other cross-cutting capabilities are introduced with the slices that require them. The portfolio milestone includes synthetic demo data, documented setup, automated tests, performance and concurrency evidence, CI and reproducible delivery. A live demo is conditional on VPS availability.

### v1.0.0 — Full Release

In addition to the academic core:

| Module | In scope |
|---|---|
| Publication | Article submission, scoped approval, scheduling, public articles and privacy-conscious profiles |
| Catalog | Restricted catalog, university books/equipment and approved teacher books, physical/digital products |
| Inventory | Stock movements, reservations, releases and overselling prevention |
| Commerce | Cart, checkout, simulated payments, fulfillment, downloads and reviewed returns |
| Finance | Teacher commission, ledger, balances and manual settlement |
| CMS | Permission-based public-page editing, validated blocks, preview, revision and publication |
| Support | Program-scoped tickets and persistent real-time chat |
| Notification | In-app, email and SMS channels, queued delivery and domain-event integration |

Operational scope includes reproducible deployment, security review, monitoring, recovery and backup/restore verification. Live VPS operation and real external provider verification depend on available infrastructure and credentials; simulated or local-only capabilities must be labeled accurately.

## 4. Explicit exclusions

- Buildings, rooms and physical room reservations (tentative v1.1.0).
- Course equivalency and extended transfer rules (tentative v1.2.0).
- Weighted assignment and midterm grade calculations (tentative v1.3.0).
- Optional public-profile visibility settings (tentative v1.4.0); mandatory privacy protections remain in v1.
- Real meeting-link generation and Google Meet integration; v1 records online/in-person delivery type only.
- Real payment gateway and automatic bank transfers; mock payments and manual settlements are in scope.
- Full accounting platform and waiting lists.
- Microservices, full CQRS and Event Sourcing are not v1 architecture requirements.
- Kubernetes is an independent learning lab, not a release dependency.

Future version labels are tentative. New requirements must be reviewed for scope and delivery impact.

## 5. Engineering and quality constraints

- Laravel 13 / PHP 8.4 backend; Next.js 16 / strict TypeScript frontend; PostgreSQL; Docker, Nginx, GitHub Actions and GHCR.
- Modular Monolith, Layered Architecture, pragmatic DDD-Lite and versioned REST API with OpenAPI; detailed decisions belong in architecture documents and ADRs.
- Persian RTL user interface; English code, technical documentation and commit messages.
- Enforce validation, authorization and applicable ownership/program scope server-side; protect sensitive data and files.
- Every new or modified behavior requiring tests must receive appropriate automated tests in the same implementation step and PR, in both frontend and backend. Trivial code may be covered by existing tests; meaningful omissions must be explained.
- PostgreSQL-specific constraints, transactions and concurrency must be verified against PostgreSQL rather than SQLite alone. Use synthetic data and fake providers for development and CI.
- Required CI checks and review must pass before merge. Retain evidence for relevant security, performance and release claims.

## 6. Acceptance and change control

The roadmap defines phase-specific exit criteria. A feature is complete only when its applicable rules, API/UI, authorization, tests and documentation are verified. v0.1.0 demonstrates the academic core; it is not the full v1 release. v1.0.0 requires the agreed full scope and documented verification of operational readiness, with unavailable live checks explicitly identified.

Open policies in business-rules.md must be resolved before their affected implementation. New scope or changes to accepted rules require review, updates to affected documents and tests, and a roadmap impact assessment.
