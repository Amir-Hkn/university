# University — Business Rules

- **Baseline:** University Architecture v1.0
- **Status:** Draft for review; confirmed rules and open decisions are distinguished
- **Related:** [Project scope](project-scope.md), [Development roadmap](../university-roadmap.md)

Rule IDs are proposed stable references for requirements, acceptance criteria and tests. They are not database identifiers. Rules below reflect the agreed roadmap; detailed permissions, state transitions and database constraints are refined in their relevant phases.

## 1. Identity and Authorization

| ID | Rule |
|---|---|
| BR-IDN-001 | Every account has exactly one primary role: Student, Teacher, Education, Store Management, Site Admin (CMS), or Support. |
| BR-IDN-002 | Students may register publicly. Teacher accounts are created by authorized Education staff; privileged staff are not publicly self-registered. |
| BR-IDN-003 | Login supports mobile OTP or email/mobile plus password. Student registration includes mobile OTP verification. |
| BR-IDN-004 | Student identity verification uses national ID, birth date, mobile number, national ID image and latest diploma image; national-ID/mobile matching is combined with authorized Education document review. |
| BR-IDN-005 | Identity review records approval or rejection and relevant reasons. Identity documents are private and accessible only to authorized users. |
| BR-IDN-006 | API.ir matching is conditional on verified access; a fake provider supports development and tests without representing real verification. |
| BR-AUT-001 | Staff permissions are granular; Education and Support access is constrained by assigned program scope and applicable resource permissions. |
| BR-AUT-002 | Authorization is enforced on the server; UI visibility alone does not grant access. Unauthorized and out-of-scope access must be denied. |

## 2. Academic structure and Semester

| ID | Rule |
|---|---|
| BR-ACD-001 | Academic hierarchy is University → Faculty → Department → Academic Program. Programs have a degree level, unique program code and curriculum; curricula identify courses, credits and prerequisites. |
| BR-ACD-002 | A student has one active program; a teacher belongs to one program in v1. |
| BR-ACD-003 | A student may change program/degree only outside the active term and with Education approval. Withdrawal and prior academic history must be retained. |
| BR-ACD-004 | General courses may serve multiple eligible programs; specialized courses are restricted to their eligible program. |
| BR-ACD-005 | Curriculum/prerequisite definitions must not permit invalid dependency cycles. Curriculum versions and history must remain traceable. |
| BR-SEM-001 | A semester represents a six-month academic term and has a unique code, e.g. `1405_2`. |
| BR-SEM-002 | Registration, teaching, exams, add/drop and grading have configurable windows as needed. |
| BR-SEM-003 | Credit limits and tuition tariffs are configurable per semester. |

## 3. Enrollment

| ID | Rule |
|---|---|
| BR-ENR-001 | Only eligible, verified students who have paid fixed tuition may enroll during the permitted registration window. |
| BR-ENR-002 | An offering must satisfy the student's applicable curriculum/program eligibility. General offerings may admit multiple eligible programs. |
| BR-ENR-003 | A prerequisite is satisfied by a prior pass, or a prior failure followed by re-enrollment in that prerequisite during the current term. A first-time concurrent prerequisite enrollment alone is insufficient. |
| BR-ENR-004 | A student may not enroll in overlapping classes; teacher schedule conflicts must also be prevented. |
| BR-ENR-005 | Offering capacity is a hard limit, including under concurrency. There is no waiting list. Duplicate enrollment is prohibited. |
| BR-ENR-006 | Enrollment respects configurable semester credit limits. Minimum-credit enforcement timing is to be finalized. |
| BR-ENR-007 | Add/drop and withdrawal are subject to authorized academic windows; capacity and applicable financial consequences must remain consistent. Exact dependent-course removal and refund/nonpayment rules remain open. |
| BR-ENR-008 | Offerings record recurring schedules and delivery type (online or in-person); room allocation and meeting links are outside v1 scope. |

## 4. Tuition

| ID | Rule |
|---|---|
| BR-TUI-001 | Fixed tuition must be paid before enrollment; variable tuition is calculated from selected credits and applicable semester tariffs. |
| BR-TUI-002 | General and specialized course tariffs are configurable. Money is stored as integer IRR and displayed in toman. |
| BR-TUI-003 | Final registration follows the required variable-tuition payment; monetary snapshots preserve the applicable prices and payment history. |
| BR-TUI-004 | Payment confirmation must be idempotent. The initial payment implementation uses a mock gateway. |

## 5. Classroom and Assessment

| ID | Rule |
|---|---|
| BR-CLS-001 | Authorized students and teachers access their relevant classroom dashboards and class history. |
| BR-CLS-002 | Classes support sessions, cancellations, makeup events, exams, homework and announcements. Actual makeup sessions must respect applicable scheduling conflicts. |
| BR-CLS-003 | Attendance is recorded per session; assignment submissions and reviews are restricted to authorized participants. Private attachments must remain protected. |
| BR-CLS-004 | Teacher-requested student removal requires Education approval; academic history is preserved. |
| BR-ASM-001 | Final grades range from 0 to 20 inclusive. A grade of 10 or above passes; a lower grade fails. Weighted assignment and midterm grading are deferred. |
| BR-ASM-002 | Students have three days after grade publication to appeal. Teachers have three days after appeal submission to respond. Exact time-boundary semantics remain open. |
| BR-ASM-003 | Unanswered overdue appeals escalate to Education. Finalized grade corrections require authorized Education action and a recorded reason; grade changes must be auditable. |

## 6. Publication and public profiles

| ID | Rule |
|---|---|
| BR-PUB-001 | Students and teachers may submit articles; approval is restricted to Education staff within the author's applicable scope. |
| BR-PUB-002 | Articles support draft, submission, approval/rejection, scheduling and publication. Unpublished content is restricted; published articles are public. |
| BR-PUB-003 | Public profiles may expose only approved public data, including permitted publications and approved teacher books. Grades, attendance, private contact details and current location/schedules must not be exposed. The exact allowlist and completed-class history details remain open. |

## 7. Catalog and Inventory

| ID | Rule |
|---|---|
| BR-CAT-001 | The university may offer books and equipment. Teachers may submit books related to their own teaching program; teacher listings require Store Management approval. |
| BR-CAT-002 | Physical and digital products are supported. The restricted storefront is not available to public visitors; buyer access is restricted to students and teachers, with staff access limited to authorized management functions. |
| BR-CAT-003 | Digital product files are private; public profile book metadata must not expose protected files or restricted storefront access. |
| BR-INV-001 | Physical stock movements and reservations must be traceable; stock may not become negative or be oversold under concurrent operations. |

## 8. Commerce and Finance

| ID | Rule |
|---|---|
| BR-COM-001 | Only students and teachers can purchase store products. Multi-seller cart policy remains open. |
| BR-COM-002 | Orders preserve item and price snapshots; stock reservation and payment confirmation must remain consistent. Payment callbacks must not cause duplicate financial effects. |
| BR-COM-003 | Digital downloads are authorized only after confirmed purchase. Physical fulfillment supports manually recorded tracking codes. |
| BR-COM-004 | Store Management reviews applicable returns/refunds; deadline and digital-return exceptions remain open. Refund and stock changes must be consistent and protected against replay. |
| BR-FIN-001 | The university commission on teacher sales is fixed at 15%. Commission rounding remains open. |
| BR-FIN-002 | Teacher seller balances and ledger entries are traceable. Settlements are requested manually and confirmed by authorized Store Management; duplicate or excessive settlements must be prevented. |

## 9. CMS, Support and Notification

| ID | Rule |
|---|---|
| BR-CMS-001 | CMS permissions control authorized public-page editing, preview and publication; CMS access alone does not grant academic or financial access. |
| BR-CMS-002 | Page blocks have validated types, settings and order. Arbitrary user-provided JavaScript/HTML must not be executed. Unpublished previews are restricted. |
| BR-SUP-001 | Student and teacher support requests route to their applicable program. Ticket and chat access and replies are governed by authorized staff scope. |
| BR-SUP-002 | Chat uses WebSockets with authorized private channels; message history is persisted and unauthorized subscriptions are denied. |
| BR-NTF-001 | Notifications support in-app, email and SMS channels; recipients must be authorized for the event. Unavailable providers are simulated in development/CI and labeled accordingly. |

## 10. Cross-cutting integrity and privacy

| ID | Rule |
|---|---|
| BR-XCT-001 | Sensitive documents, private attachments and protected digital files must not be exposed publicly. |
| BR-XCT-002 | Relevant historical academic records, approvals, grade corrections and financial records must remain traceable. |
| BR-XCT-003 | Capacity, payment, refund and settlement invariants must hold under concurrent operations and retries where applicable. |

## 11. Open decisions

These proposals are not approved business rules. Resolve each before the affected implementation; update rule IDs and acceptance tests accordingly.

| ID | Decision to resolve | Working proposal / dependency | Due phase |
|---|---|---|---|
| OD-001 | Initial program selection and identity-review routing; privileged staff bootstrap | Initial program request before review; controlled provisioning | 00–01 |
| OD-002 | Exact staff scope cardinality, class-code uniqueness scope and shared-class ownership | Define in permission matrix and academic/enrollment model | 00–03 |
| OD-003 | Calendar, timezone and exact term/window boundaries | Asia/Tehran university timezone, Jalali display, UTC timestamps | 00–02 |
| OD-004 | Semester tuition tariff scope | Shared tariff across programs unless program-specific tariffs are required | 02 |
| OD-005 | Enrollment nonpayment deadline, reservation expiry, minimum-credit enforcement, dependent-course removal | Minimum credits at finalization proposed | 03 |
| OD-006 | Financial adjustment after paid course removal | Tuition credit proposed; refund/accounting behavior unresolved | 03 |
| OD-007 | Appeal deadline semantics and escalation/finalization boundaries | Elapsed 72-hour windows proposed | 04 |
| OD-008 | Exact public profile allowlist and historical class fields | Completed-class history only proposed; synthetic demo data | 06 |
| OD-009 | Multi-seller cart, return deadline, digital exceptions, commission rounding and settlement eligibility | Seven-day physical return and inaccessible/faulty digital-file exceptions proposed | 07–08 |
| OD-010 | External provider and infrastructure availability | API.ir, email/SMS, payment credentials and VPS access unverified | Relevant phase |

## 12. Traceability example

BR-ASM-001 can be referenced by acceptance tests for a passing grade of 10, a failing grade below 10, boundary grades 0 and 20, and rejection of grades outside that range. When implementation begins, appropriate backend and frontend tests are created or updated in the same change. Rule changes require review and updates to affected tests and documentation.
