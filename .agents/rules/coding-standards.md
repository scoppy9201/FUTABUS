# Coding standards

- Read nearby code and the current diff first. Preserve unrelated user changes.
- Follow PHP 8.3, Laravel 12, existing namespaces, strict validation, and the repository's Pint configuration.
- Keep controllers thin and name services for domain actions. Do not duplicate existing services or hide failures with broad catches or suppression comments.
- Put user-facing copy in owning translations or seeded editorial data. Keep Blade focused on presentation.
- Do not edit historical migrations to change deployed schema; add a new reversible migration.
- Record changed behavior, checks run, and limits of verification in the final report.
