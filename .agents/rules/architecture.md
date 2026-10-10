# Architecture

- Identify the owning module by tracing route, controller, service, persistence, view, translations, and assets before editing.
- Keep domain behavior in `packages/FuteBus/<Module>/src` or `packages/Customer/<Module>/src`. Core owns shared customer-facing behavior, not a default home for features.
- Keep root `resources/css/app.css` and `resources/js/app.js` as baseline entry points. Module views, CSS, and JS stay with their owner.
- Reuse an existing contract or shared component before creating another. Explain any cross-module dependency in the PR.
- Avoid unrelated module edits. If a shared contract requires them, list every affected caller and test the integration.
