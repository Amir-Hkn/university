# University — Phase 00 / Day 01 Review

- **Review date:** 2026-11-25
- **Status:** Reviewed; documentation baseline suitable for Day 2 modeling, subject to the clarification below. Not a schema freeze or implementation approval.
- **Repository:** `Amir-Hkn/university`
- **Reviewed branch:** `architecture-foundation`
- **Reviewed commit:** `ae8f00ca050d432cddb3d1366617c2bfcf678ab3`
- **Development snapshot:** `8c18995dd08dceb81b23a207b62a6c4c6fe82e52`
- **Merge base:** `bb0671da441f7f565c249627cbee607fed6314b9`

## 1. Deliverables and repository evidence

| Deliverable | Result |
|---|---|
| [Project scope](../requirements/project-scope.md) | Present; academic-core and full-release scope distinguished |
| [Business rules](../requirements/business-rules.md) | Present; 56 rule IDs and 10 open-decision IDs |
| [Module boundaries](../architecture/module-boundaries.md) | Present; 16 modules, 28 proposed direct business dependency edges |
| [ADR-001](../adr/0001-adopt-modular-monolith.md) | Present; accepted architecture style, alternatives and trade-offs |
| [ADR template](../adr/template.md) | Present |
| [Identity and Authorization domain model](../architecture/identity-authorization-domain-model.md) | Present; proposed design |
| [Identity and Authorization ERD](../database/erd/identity-authorization-erd.md) | Present; preliminary design, not migration-ready |

GitHub reported four foundation commits ahead and ten development commits behind (`diverged`). The recursive Git trees were not truncated: the merge base and development snapshot each contained 157 blobs with identical paths and hashes; the foundation snapshot contained 164 blobs. Only the seven documentation files above differ between the two branch tips. This is evidence for the reviewed snapshots, not a guarantee against future conflicts. Synchronize with `development` before final integration; do not rewrite shared history merely to eliminate the ahead/behind count.

## 2. Review checks

- The roadmap, scope and business rules retain the two release milestones, the agreed six roles, the one-primary-role invariant and the 16 logical modules.
- Identity owns account/verification behavior; Authorization owns role and permission semantics; Academic owns program membership and history. Staff scope is not academic membership.
- Semester owns timing and credit limits; Tuition owns monetary tariffs. Enrollment owns offering/roster mutations; Classroom owns actual sessions and removal requests; Assessment owns grade records.
- The proposed direct business dependency graph was checked programmatically: 28 directed edges, no cycle. This checks the documented graph, not PHP imports or implemented boundaries.
- Automated checks found no missing relative Markdown document targets within the reviewed document set and no unresolved standalone rule/decision references matching the checked ID pattern. Compressed rule ranges and prose semantics were reviewed separately; this is not a general Markdown or Mermaid renderer test.
- The ERD uses a mandatory role FK, scope-specific grant association and a composite FK to prevent a grant referencing another staff member's scope. These are sound preliminary design directions; role/action eligibility and scope semantics still need the permission matrix.
- Verification evidence and manual review are distinct; historical approval alone does not establish current eligibility. Private files and simulated providers remain restricted.
- Critical consistency is not delegated solely to asynchronous events. The mandatory test-as-you-build policy is retained throughout.

## 3. Review findings and actions

| ID | Priority / timing | Finding and recommended action |
|---|---|---|
| D01-R01 | Clarify before Phase 01; preferably now | The boundaries/domain-model wording describes creating the account and assigning its role in one transaction. The ERD requires `users.role_id NOT NULL`. Resolve and validate the role through Authorization before inserting the account with its role ID; coordinate other required records and OTP proof handling in the same transaction as applicable. Do not insert a roleless user and then update it. Align both architecture documents with the ERD. |
| D01-R02 | Day 2 / before Phase 01 migration | The Identity ERD references Academic-owned `academic_programs`. Model its actual parent hierarchy and dependencies first; Phase 01 must create/seed the minimum valid reference hierarchy required by foreign keys, not an orphan placeholder program table. Coordinate migration order with Academic and avoid duplicate migrations in Phase 02. |
| D01-R03 | Day 3 / before authorization implementation | Per-program vs global staff grants, staff scope cardinality, grant/revocation authority, default permissions and role changes remain provisional (OD-002). Finalize the permission matrix and update the ERD/Domain Model/ADR together. Missing scope must not authorize program-bound actions. |
| D01-R04 | Day 3 / Phase 01 | Establish effective identity approval, contact change/reverification, case resubmission, provider failure, document retention and credential requirements. No production mock evidence or implicit approval fallback. |
| D01-R05 | Day 3 / Phase 01 | Finalize identifiers, temporal conventions, sensitive data handling, national-ID uniqueness policy and retention; verify PostgreSQL-specific constraints in a dedicated test database during implementation. |

The above actions are review findings, not silently adopted new business rules. D01-R01 is a clarification of the existing single-role invariant. D01-R02–R05 are dependencies/open design decisions rather than evidence of implemented defects.

## 4. Foundation verification backlog

| ID | Evidence and remaining work |
|---|---|
| FOUNDATION-001 | Initial backend tests selected the development PostgreSQL database; explicit testing environment overrides and cleared config cache restored isolation for the observed smoke test. Establish permanent isolated testing configuration and safety guard before database-changing tests. |
| FOUNDATION-002 | A missing `.env` warning was cleared by a temporary `.env.testing` inside the container. Make the solution reproducible without embedding secrets in images. |
| FOUNDATION-003 | Frontend production build failed when invoked in the dev container (`NODE_ENV=development` suspected); proper production build verification was deferred to Day 4. Root cause not conclusively verified. Use the existing production Dockerfile or equivalent isolated production build. |
| FOUNDATION-004 | ESLint reported one unused `cardType` warning in `Slider.tsx`; resolve or document before final foundation quality checks. |

User-reported local evidence: three healthy dev containers, Laravel 13.42.0, Node.js v24.22.3, two backend example tests passed with explicit test environment and temporary `.env.testing`, TypeScript check passed, frontend lint had one warning and no errors. No project migrations were executed. Frontend automated test runner and PostgreSQL integration test environment remain to be established. No application tests were executed by the reviewer in this documentation review, and no current CI result or production build success is asserted.

## 5. Day 2 handoff

Proceed with Academic, Semester, Classroom and Enrollment domain modeling and preliminary ERDs. Start with University → Faculty → Department → Academic Program, curriculum versions and course/prerequisite ownership. Design student program history and teacher affiliation, then semester windows, offering schedules, enrollment/roster and actual sessions. Maintain all unresolved policies in the requirements backlog; do not freeze schema or implement affected behavior before resolution.

Day 1 documentation deliverables are present and reviewed. Record this review and the D01-R01 clarification on `architecture-foundation`; keep the remaining findings visible. Final integration, CI/quality checks, complete architecture baseline and Phase 00 acceptance occur at the end of the phase. No direct GitHub writes or merge were performed by the reviewer.
