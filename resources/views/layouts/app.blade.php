<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monexa') - Quản lý chi tiêu</title>
    <link rel="icon" type="images/png" href="{{ asset('favicon.png') }}">
</body>
    <div id="toastContainer" style="
        position: fixed;
        top: 84px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 360px;
        pointer-events: none;
    "></div>

    <style>
        .g-toast {
            background: white;
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border-left: 4px solid;
            pointer-events: all;
            position: relative;
            overflow: hidden;
            animation: toastIn 0.35s cubic-bezier(0.4,0,0.2,1);
            transition: opacity 0.3s, transform 0.3s;
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(50px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .g-toast.hiding { opacity: 0; transform: translateX(50px); pointer-events: none; }
        .g-toast-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            border-radius: 0;
            animation: progressShrink linear forwards;
            opacity: 0.35;
        }
        @keyframes progressShrink {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .g-toast.success { border-left-color: #10b981; }
        .g-toast.error   { border-left-color: #ef4444; }
        .g-toast.warning { border-left-color: #f59e0b; }
        .g-toast.info    { border-left-color: #4a90e2; }
        .g-toast.success .g-toast-progress { background: #10b981; }
        .g-toast.error   .g-toast-progress { background: #ef4444; }
        .g-toast.warning .g-toast-progress { background: #f59e0b; }
        .g-toast.info    .g-toast-progress { background: #4a90e2; }
        .g-toast-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .g-toast.success .g-toast-icon { background: #d1fae5; }
        .g-toast.error   .g-toast-icon { background: #fee2e2; }
        .g-toast.warning .g-toast-icon { background: #fef3c7; }
        .g-toast.info    .g-toast-icon { background: #dbeafe; }
        .g-toast-body { flex: 1; min-width: 0; }
        .g-toast-title { font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 2px; line-height: 1.3; }
        .g-toast-msg   { font-size: 13px; color: #6b7280; line-height: 1.5; font-weight: 500; }
        .g-toast-action {
            display: inline-block; margin-top: 7px; padding: 4px 12px;
            border-radius: 8px; font-size: 12px; font-weight: 700;
            text-decoration: none; background: var(--primary); color: white !important;
            transition: opacity 0.2s;
        }
        .g-toast-action:hover { opacity: 0.85; }
        .g-toast-close {
            background: none; border: none; font-size: 20px; color: #9ca3af;
            cursor: pointer; padding: 0; line-height: 1; flex-shrink: 0;
            transition: color 0.2s; margin-top: -2px;
        }
        .g-toast-close:hover { color: #374151; }

        /* Dark mode */
        body.dark .g-toast         { background: #1e2433; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
        body.dark .g-toast-title   { color: #f3f4f6; }
        body.dark .g-toast-msg     { color: #9ca3af; }
        body.dark .g-toast-close:hover { color: #e5e7eb; }
        body.dark .g-toast.success .g-toast-icon { background: rgba(16,185,129,0.15); }
        body.dark .g-toast.error   .g-toast-icon { background: rgba(239,68,68,0.15); }
        body.dark .g-toast.warning .g-toast-icon { background: rgba(245,158,11,0.15); }
        body.dark .g-toast.info    .g-toast-icon { background: rgba(74,144,226,0.15); }
        </style>

        <script>
        // Đọc settings từ localStorage
        function _getToastSettings() {
            try { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false, ...JSON.parse(localStorage.getItem('monexa_settings') || '{}') }; }
            catch { return { toastEnabled: true, toastPosition: 'top-right', toastDuration: 5, toastSound: false }; }
        }

        function _applyToastPosition(pos) {
            const c = document.getElementById('toastContainer');
            if (!c) return;
            c.style.top    = pos.includes('bottom') ? 'auto' : '84px';
            c.style.bottom = pos.includes('bottom') ? '20px' : 'auto';
            c.style.left   = pos.includes('left')   ? '20px' : 'auto';
            c.style.right  = pos.includes('left')   ? 'auto' : '20px';
        }

        // Áp dụng vị trí ngay khi load
        _applyToastPosition(_getToastSettings().toastPosition);

        window.showToast = function({ type = 'info', title, message = '', action = null, id = null, duration = null }) {
            const s = _getToastSettings();
            if (s.toastEnabled === false) return;

            const ms = duration ?? (s.toastDuration * 1000);
            if (id && document.querySelector(`[data-toast-id="${id}"]`)) return;
            if (id && sessionStorage.getItem('tdismiss_' + id)) return;

            _applyToastPosition(s.toastPosition);

            const icons = {
            success: '<img src="/images/check.png"   style="width:20px;height:20px;object-fit:contain;">',
            error:   '<img src="/images/warning.png" style="width:20px;height:20px;object-fit:contain;">',
            warning: '<img src="/images/alert.png"   style="width:20px;height:20px;object-fit:contain;">',
            info:    '<img src="/images/info.png"    style="width:20px;height:20px;object-fit:contain;">',
        };
            const el = document.createElement('div');
            el.className = `g-toast ${type}`;
            if (id) el.dataset.toastId = id;

            el.innerHTML = `
                <div class="g-toast-icon">${icons[type] || 'ℹ️'}</div>
                <div class="g-toast-body">
                    <div class="g-toast-title">${title}</div>
                    ${message ? `<div class="g-toast-msg">${message}</div>` : ''}
                    ${action ? `<a href="${action.url}" class="g-toast-action">${action.label}</a>` : ''}
                </div>
                <button class="g-toast-close" onclick="dismissToast(this,'${id}')">&times;</button>
                <div class="g-toast-progress" style="animation-duration:${ms}ms"></div>
            `;

            document.getElementById('toastContainer')?.appendChild(el);

            // Sound
            if (s.toastSound) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.frequency.value = type === 'error' ? 300 : 600;
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                    osc.start(); osc.stop(ctx.currentTime + 0.3);
                } catch {}
            }

            if (ms > 0) setTimeout(() => dismissToast(el.querySelector('.g-toast-close'), id), ms);
        };

        window.dismissToast = function(btn, id = null) {
            const toast = btn?.closest?.('.g-toast');
            if (!toast) return;
            toast.classList.add('hiding');
            if (id && id !== 'null') sessionStorage.setItem('tdismiss_' + id, '1');
            setTimeout(() => toast.remove(), 320);
        };

        // Đọc flash session từ Laravel
        @if(session('toast'))
            showToast(@json(session('toast')));
        @endif
        @if(session('success'))
            showToast({ type: 'success', title: 'Thành công', message: @json(session('success')) });
        @endif
        @if(session('error'))
            showToast({ type: 'error', title: 'Lỗi', message: @json(session('error')) });
        @endif
        @if(session('warning'))
            showToast({ type: 'warning', title: 'Cảnh báo', message: @json(session('warning')) });
        @endif
        @if(session('info'))
            showToast({ type: 'info', title: 'Thông báo', message: @json(session('info')) });
        @endif
    </script>
</html>
