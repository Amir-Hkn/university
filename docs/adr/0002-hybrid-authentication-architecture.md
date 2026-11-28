# ADR — Hybrid Authentication Architecture

**Status:** Proposed  
**Date:** 2026-11-28  
**Phase:** 01.03.01 — Hybrid Authentication Architecture

## Context

The University platform uses Laravel 13 as its backend, Next.js as its web application, and PostgreSQL as its primary database.

A React Native mobile application may be introduced in the future. The authentication architecture must support both first-party clients without duplicating identity management, credential verification, or authorization logic.

## Decision

The platform will adopt a hybrid authentication architecture based on Laravel Sanctum.

### Web Authentication

- Use stateful, session-based authentication.
- Use Laravel's existing web guard and Eloquent user provider.
- Store sessions in PostgreSQL.
- Use HTTP-only session cookies.
- Enforce CSRF protection.
- Use a configurable session idle lifetime of 120 minutes.
- Regenerate session identifiers after successful authentication.
- Invalidate the session and regenerate the CSRF token on logout.
- Do not expose personal access tokens to the web application.

### Mobile Authentication

- Use Laravel Sanctum personal access tokens.
- Use opaque bearer tokens rather than JWTs.
- Use a configurable access token lifetime of 15 minutes.
- Introduce a custom refresh token mechanism in a subsequent implementation phase.
- Enforce refresh token rotation and reuse detection.
- Apply a configurable absolute token-family lifetime of 30 days.
- Store only token hashes on the backend.
- Store mobile refresh tokens using platform-backed secure storage.
- Support device-specific credential revocation.

### Shared Authentication Logic

Both web and mobile clients will share:

- User accounts and identity data.
- Password verification.
- OTP verification.
- Account eligibility validation.
- Authentication rate limiting.
- Authorization rules and policies.

Protected API endpoints will use the Sanctum authentication guard. Client-specific endpoints must enforce their intended authentication mechanism.

### Authorization

Authentication and authorization must remain independent.

Roles, permissions, staff scopes, resource ownership, and identity verification requirements will be enforced by the backend authorization layer.

Token abilities must not replace domain-level authorization rules.

### Infrastructure

- PostgreSQL will provide shared session storage.
- Redis may be introduced when scalability or performance requirements justify it.
- Production traffic must use HTTPS.
- Production web and API traffic should use a shared origin through Nginx.
- Application replicas must share compatible authentication and encryption configuration.
- Development environments must explicitly configure stateful domains and CORS.

## Alternatives Considered

### JWT-based authentication

Not selected because the project does not currently require self-contained access tokens, and immediate revocation would introduce additional complexity.

### Laravel Passport

Not selected because a complete OAuth 2.0 authorization server is not currently required.

This decision may be revisited if third-party delegated authorization becomes a business requirement.

### Session-only authentication

Not selected as the sole authentication mechanism because the architecture must also accommodate native mobile clients.

## Security Requirements

- Enforce HTTPS in production.
- Enable secure and HTTP-only session cookies.
- Enforce CSRF protection for stateful requests.
- Apply authentication and OTP rate limiting.
- Prevent session fixation.
- Prevent refresh token replay.
- Handle concurrent token refresh operations atomically.
- Never log credentials, OTP values, session identifiers, or raw tokens.
- Support credential revocation across devices.
- Keep authentication secrets outside version control.

## Testing Strategy

Authentication features must include appropriate automated tests within the same implementation step.

Required coverage includes:

- Successful and unsuccessful authentication.
- Session regeneration and invalidation.
- CSRF protection.
- Expired and revoked access tokens.
- Refresh token rotation and reuse detection.
- Concurrent refresh requests.
- Device-specific and global logout.
- Account status and authorization enforcement.
- Authentication rate limiting.
- Frontend authentication state and integration.

Concurrency-sensitive database behavior must be tested against PostgreSQL using independent database connections.

## Consequences

### Positive

- Shared authentication and authorization infrastructure.
- Secure first-party web authentication.
- Future mobile application compatibility.
- Immediate access token revocation.
- Compatibility with horizontally scaled backend instances.

### Trade-offs

- Additional complexity for the custom refresh token lifecycle.
- Database access during token validation.
- Shared session storage introduces database workload.
- Client-specific authentication and logout behavior requires additional testing.

## Implementation Scope

This ADR defines architecture only.

Package installation, configuration changes, database migrations, authentication services, controllers, middleware, and automated implementation tests will be introduced in subsequent phases.

## References

- https://laravel.com/docs/13.x/authentication
- https://laravel.com/docs/13.x/sanctum
- https://laravel.com/docs/13.x/session
- https://www.rfc-editor.org/rfc/rfc9700