# University — Technical Debt Register

## Active items

| ID | Description | Priority | Target |
|---|---|---|---|
| TD-001 | Add Laravel Pint to the Backend CI workflow | Medium | Before the portfolio release |
| TD-002 | Remove the migration lifecycle test's dependency on the hardening migration being the latest migration | Medium | Before extending the database schema |
| TD-003 | Validate database indexes using representative workloads and EXPLAIN ANALYZE | Medium | Database performance phase |

## Management rules

- Each item must have a clear description and target resolution phase.
- Technical debt must be reviewed before release.
- Security vulnerabilities must not be deferred as ordinary technical debt.
- Resolved items must include implementation and verification evidence.
- New functionality must include appropriate automated tests.