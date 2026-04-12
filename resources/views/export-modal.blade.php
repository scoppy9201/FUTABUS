<div class="modal-overlay" id="export-modal">
    <div class="modal-content" style="max-width:500px">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-title">
                Xuất báo cáo
            </div>
            <div class="modal-close" id="close-export-modal">
                <img src="{{ asset('images/close.png') }}" style="width:14px;opacity:0.5">
            </div>
        </div>

        <div class="modal-body">

            <!-- Subtitle -->
            <p style="margin:0;font-size:13px;color:var(--color-text-secondary);line-height:1.6">
                Xuất và tải về báo cáo tài chính của bạn
                (<strong id="export-user-email" style="color:var(--color-text-primary)"></strong>).
                Tệp sẽ được tải xuống ngay sau khi xuất.
            </p>

            <!-- Định dạng tệp -->
            <div class="form-group-compact">
                <label class="form-label"><strong>Định dạng tệp</strong></label>
                <select id="export-format-select" class="form-select">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="pdf">PDF (.pdf)</option>
                </select>
            </div>

            <!-- Phạm vi xuất -->
            <div class="form-group-compact">
                <label class="form-label"><strong>Xuất khẩu</strong></label>
                <div style="display:flex;flex-direction:column;gap:10px">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--color-text-primary)">
                        <input type="radio" name="export-scope" value="all" checked
                            style="accent-color:var(--primary);width:18px;height:18px">
                        Bao gồm tất cả dữ liệu có sẵn
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--color-text-primary)">
                        <input type="radio" name="export-scope" value="selected"
                            style="accent-color:var(--primary);width:18px;height:18px">
                        Chỉ bao gồm dữ liệu đã chọn
                    </label>
                </div>
            </div>

            <!-- Lưu ý -->
            <div style="background:var(--color-background-secondary);border-radius:10px;padding:14px 16px">
                <p style="font-size:12px;font-weight:700;color:var(--color-text-primary);margin:0 0 8px">Xin lưu ý:</p>
                <ul style="margin:0;padding-left:16px;display:flex;flex-direction:column;gap:6px">
                    <li style="font-size:12px;color:var(--color-text-secondary);line-height:1.5">
                        Bạn có thể xuất chi tiết thu chi và tất cả dữ liệu hiển thị trên các tab theo kỳ đã chọn.
                    </li>
                    <li style="font-size:12px;color:var(--color-text-secondary);line-height:1.5">
                        Xin lưu ý rằng PDF là định dạng duy nhất hỗ trợ biểu đồ, tổng chi phí và ngân sách còn lại.
                    </li>
                </ul>
            </div>

            <!-- Gửi email -->
            <p style="margin:0;font-size:12px;color:var(--color-text-secondary);line-height:1.6">
                Bạn không nhận được email của chúng tôi? Hãy chắc chắn rằng bạn đã thêm địa chỉ email vào
                <label style="color:var(--primary);cursor:pointer;text-decoration:underline">
                    <input type="checkbox" id="send-export-email" style="display:none">
                    danh sách cho phép của bạn
                </label>
                và bật máy chủ lên
                <span id="toggle-email-notify"
                    style="color:var(--primary);cursor:pointer;text-decoration:underline"
                    onclick="document.getElementById('email-input-wrap').style.display = document.getElementById('email-input-wrap').style.display === 'none' ? 'block' : 'none'">
                    thông báo xuất khẩu
                </span>.
            </p>

            <!-- Email input (ẩn mặc định) -->
            <div id="email-input-wrap" style="display:none">
                <input
                    type="email"
                    id="export-email-input"
                    class="form-control"
                    placeholder="Nhập địa chỉ email nhận báo cáo"
                >
            </div>

        </div>

        <!-- Footer -->
        <div class="modal-actions-fixed">
            <button type="button" class="btn-secondary" id="cancel-export-btn">
                Huỷ bỏ
            </button>
            <button type="button" class="btn-primary" id="confirm-export-btn">
                <img src="{{ asset('images/export.png') }}" style="width:16px;filter:brightness(10)">
                Xuất báo cáo
            </button>
        </div>

    </div>
</div>

<div id="export-modal-root"
    data-export-icon="{{ asset('images/export.png') }}">
</div>

<script>
    // Hiển thị email user trong subtitle
    window.addEventListener('DOMContentLoaded', () => {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const emailEl = document.getElementById('export-user-email');
        if (emailEl && user.email) emailEl.textContent = user.email;
    });
</script>