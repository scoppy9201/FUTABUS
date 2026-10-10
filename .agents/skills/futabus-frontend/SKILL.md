---
name: futabus-frontend
description: Build or refine FUTABUS Blade and Alpine interfaces using Tailwind CSS v4, module-scoped assets, shared theme tokens, and the established SVG icon system.
---

# FUTABUS frontend

1. Inspect the target view, its module `app.css`/`app.js`, root Tailwind theme, and adjacent components. Preserve the current interaction contract, keyboard behavior, responsive layout, and supplied orange banner artwork.
2. Prefer Tailwind v4 utilities and `futa-*` theme colors. Use a module stylesheet for exact effects or complex selectors that utilities cannot express clearly. Keep global `resources/css/app.css` limited to imports, content sources, theme tokens, and truly shared utilities.
3. Keep module JS inside the owning package. Use the existing Blade/Alpine approach. Notifications and confirmation dialogs are separate components; use the shared one appropriate to the interaction.
4. Use installed Heroicons or existing SVG asset files for icons. Do not place emoji in executable UI templates or embed hand-drawn SVG paths in Blade/JS. Preserve emoji that are part of editorial article content by storing that content as data.
5. Verify affected Blade compiles, Vite builds, and relevant UI tests pass. When browser verification is available, inspect the changed screen at desktop and narrow widths; do not claim visual parity from compilation alone.

Never manually merge or push to protected branches. The repository's `AGENTS.md` remains binding.
