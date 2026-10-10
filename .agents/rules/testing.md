# Testing

- Put feature tests under `tests/Feature/<OwningModule>/` and unit tests under `tests/Unit/<OwningModule>/`.
- Test observable behavior and persistence. Cover success, invalid input, authorization, and meaningful retry or failure paths for the changed feature.
- Run impacted PHPUnit tests, Pint for changed PHP, Blade cache for view structure, and Vite build for frontend assets.
- For a PR, run the three changed-file CI scripts against the correct base SHA. Do not suppress failures or delete coverage to make CI green.
- Report exactly which checks ran. Compilation does not prove browser layout; mocked payment tests do not prove bank settlement.
