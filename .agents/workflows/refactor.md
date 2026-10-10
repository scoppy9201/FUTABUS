# Refactor

1. Capture existing behavior and module ownership before moving code.
2. Change one boundary at a time and update call sites, translations, assets, and tests together.
3. Avoid changing UI or domain behavior unless requested; compare affected screens when browser access exists.
4. Run relevant tests, Pint, Blade cache and build, then inspect the final diff for stale references.
