# Fix a bug

1. Reproduce or identify the failing behavior and inspect the current diff.
2. Trace cause through owning module and affected callers. Preserve unrelated changes.
3. Fix the cause and add a regression test when it guards a real failure.
4. Run focused and required checks; review UI or persistence effects.
5. Report root cause and evidence. Do not claim a live service was verified by a mocked test.
