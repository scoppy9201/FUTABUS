# Side panel reference

The trip add/edit form in `packages/FuteBus/TripManagement/src/resources/views/trips/index.blade.php` is the reference implementation. Other forms can reuse `core::components.side-panel` and its stylesheet imported by Core.

```blade
<x-core::side-panel id="example-panel" x-ref="panel" title="Title" description="Description">
    <form class="flex min-h-0 flex-1 flex-col" method="post" action="...">
        @csrf
        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-6 py-6">
            {{-- Fields using the shared select/date controls --}}
        </div>
        <footer class="flex shrink-0 justify-end gap-3 border-t border-slate-200 px-6 py-4">
            {{-- Cancel and submit actions --}}
        </footer>
    </form>
</x-core::side-panel>
```

Open with `showModal()` after Alpine updates form values using `$nextTick`. Close with `close()` and handle the native `close` event to clear form state and transient messages. Escape and focus restoration use the native dialog behavior. Core manages background scroll locking and shared dropdown/calendar overlays.

The panel occupies the right edge on desktop and the available width on mobile. Header/footer remain visible while fields scroll. Keep server validation, ownership checks and HTTP methods in the owning module; only the presentation is shared. Respect reduced motion and provide a descriptive title. No centered overlay wrapper or orange header strip is needed.

## Reactive forms

Vehicle catalog forms and schedule/stop forms use `x-effect="window.FutaSidePanel.sync($el, showForm, $nextTick)"` with `@close="showForm = false"`. The state is read synchronously by Alpine, and opening waits for form updates. The helper cancels pending opens when state changes or the element is removed. Keep the footer outside the scrolling field container; use `space-y-5`, `gap-3`, `px-6 py-6` for fields, and `px-6 py-4` for actions.
