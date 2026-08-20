# Security Policy

We take the security of FUTEBUS seriously. Thank you for helping us keep the project and its users safe.

---

## Supported Versions

The table below lists the releases that currently receive security updates.

| Branch | Supported |
| --- | --- |
| `main` (latest release) | :white_check_mark: |
| `staging` | :white_check_mark: |
| `dev` | :x: (development only, no security guarantees) |

Security fixes are applied to the latest stable release and backported where practical. If you are running an older release, please upgrade to the latest supported version.

---

## Reporting a Vulnerability

**Please do not disclose security vulnerabilities publicly.** Do not open a public issue for a suspected vulnerability.

Instead, report it privately to the maintainers with as much detail as possible:

> **Email:** support@futabus.io

Please include in your report:

- The affected version(s) and branch.
- A clear, concise description of the vulnerability.
- Steps to reproduce it, including any proof-of-concept code.
- The impact you believe the vulnerability has (data exposure, privilege escalation, denial of service, etc.).

### What to expect

- We will acknowledge your report within **5 business days**.
- We will investigate and provide a status update as soon as possible.
- We will coordinate a fix and a disclosure date with you.
- We will credit you in the release notes if you wish to be acknowledged (unless you prefer to stay anonymous).

If you have not received a reply within 5 business days, please follow up on your report.

---

## Responsible Disclosure Guidelines

- Give us a reasonable period to fix the issue before publishing any details.
- Do not access, modify, or delete data other than what is necessary to demonstrate the vulnerability.
- Do not exploit the vulnerability beyond proof of concept.
- Stop testing once you have confirmed the issue and reported it.

---

## Scope

This policy applies to FUTEBUS source code and its official deployment configuration (Docker, Nginx). Third-party dependencies and external services are out of scope unless they expose a vulnerability through FUTEBUS.

---

## Security Notes

FUTEBUS handles customer and payment data. Use HTTPS in production, protect `.env`, rotate OAuth secrets when needed, limit database access, and review permission checks for booking, ticket, and payment operations before deployment.

---

Developed and maintained by **FUTEBUS**.