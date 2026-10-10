# Database and seed data

- Add a forward migration and a practical rollback for schema changes. Inspect existing rows and foreign keys before changing constraints.
- Keep editorial seed payloads in `database/seeders/data/`; make seeders idempotent so a fresh environment can reproduce content.
- Never run destructive migrations, reseed a live database, or alter payment records without explicit authorization.
- Use isolated test data. Include a deployment sequence when a new table or column is required before application code can read it.
