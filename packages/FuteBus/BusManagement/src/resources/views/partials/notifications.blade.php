<script>
document.addEventListener('DOMContentLoaded', () => {
    const title = @js(__('BusManagement::app.notice_title'));
    @if(session('success'))
        window.FutaNotify?.success(@json(session('success'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { title, key: 'bus-management-success' });
    @endif
    @if(session('warning'))
        window.FutaNotify?.warning(@json(session('warning'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { title, key: 'bus-management-warning' });
    @endif
    @if($errors->any())
        window.FutaNotify?.error(@json(implode(' • ', $errors->all()), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), { title, key: 'bus-management-error', duration: 8000 });
    @endif
});
</script>
