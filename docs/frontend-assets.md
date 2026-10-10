# Frontend assets in FUTABUS

- `resources/css/app.css` contains only Tailwind v4, shared design tokens, content sources, and baseline utilities. `resources/js/app.js` contains only shared bootstrap code.
- Put page and component CSS/JS in the package that owns the feature, under `packages/FuteBus/<Module>/src/resources/{css,js}`. Import custom CSS through that module's `app.css`; import its scripts through that module's `app.js`.
- Build layout, spacing, type, colors, responsive rules, interaction states, and print styles with Tailwind utilities in Blade first. Keep module CSS for effects or selectors that utilities cannot express clearly, such as generated notification markup, complex seat maps, scrollbars, and animation.
- Core owns site-wide behavior such as notifications, confirmation dialogs, and the loader. Auth owns form validation. Payment owns its payment pages and ticket actions.
- Public layouts load the Core entries. Payment pages push their own entries through the Core layout's style/script stacks. Auth pages load Auth's script entry.
- This frontend currently renders Blade views and uses Alpine for interaction. There is no Vue dependency or Vue entrypoint.

## Brand colors

- Use Tailwind v4 tokens from `resources/css/app.css`: `futa-orange` (`#ef5222`), `futa-orange-dark` (`#d94317`), `futa-orange-soft` (`#fff3ed`), and `futa-green` (`#00613d`).
- Use a solid `bg-futa-orange` for primary actions. New decorative gradients should blend these brand tokens. Preserve the supplied orange banner asset and existing booking banner gradient as design references.
- Keep semantic red, amber, green, and blue where they communicate errors, warnings, success, seat availability, or payment provider identity.
