# Changelog

All notable changes to FUTEBUS are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Modular monolith skeleton with 22 business packages under `packages/FuteBus` (shared, all-role) and `packages/Customer` (customer-facing).
- Central database schema: users, roles/permissions, bus companies, buses, routes, seat layouts, trips, customers, bookings, booked seats, tickets, payments, notifications, and AI chat history.
- Seeders for roles/permissions, bus companies, and demo data.
- AI assistant foundation with chat history persistence (Google Gemini).
- Shared platform code: base controller, API response trait, role-based middleware, and role redirector.
- Vite + Tailwind CSS 4 build with automatic package asset collection.
- Docker Compose and GitHub Actions CI/CD scaffolding.
- Heroicons SVG set published to `public/vendor/blade-heroicons`.

## [v0.1.0] - 2026

### Added
- Initial scaffold of the online bus-ticket booking platform.
- Base Laravel 12 application setup with Sanctum, Socialite, Excel/PDF/QR tooling.
- Repository-level documentation (README, contributing, security, code of conduct).

---

Developed and maintained by **FUTEBUS**.