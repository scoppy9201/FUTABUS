# Frontend and UI

- Use Blade and Alpine already present. Prefer Tailwind CSS v4 utilities and `futa-*` theme tokens.
- Use module CSS only for behavior or visual effects that utilities cannot express cleanly. Scope selectors to the component; avoid broad selectors that change unrelated pages.
- Reuse Core's global loader, notifications, and confirmation dialog. Do not duplicate them locally.
- Use installed icon components or existing SVG assets. Do not use emoji as interface icons or hand-drawn inline SVG.
- Preserve the supplied orange banner artwork. Check keyboard behavior, desktop and narrow layouts, and visible interaction states for changed screens.
- Compile Blade and build Vite for UI changes. Screenshots are evidence only when an actual browser check was performed.
