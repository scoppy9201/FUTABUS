# Implement a feature

1. Read AGENTS.md and the owning skill; inspect the route-to-view or route-to-data flow and current diff.
2. Identify owner, shared contracts, data changes, and UI regression risks.
3. Implement the smallest complete change in the owning module; add meaningful tests and migration/seed data where needed.
4. Run impacted tests, Pint, Blade cache or Vite build as applicable, then review the diff.
5. Report changed behavior, exact checks, and remaining risks. Prepare a PR only when requested; never merge it manually.
