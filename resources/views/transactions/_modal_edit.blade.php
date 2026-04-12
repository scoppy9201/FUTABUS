<div class="modal-overlay" id="edit-modal">
    <div class="modal-content" style="max-width:720px;">
        <div class="modal-header">
            <div class="modal-title">
                <div class="page-icon">
                    <img src="{{ asset('images/edit.png') }}" style="width:24px;filter:brightness(0) invert(1);">
                </div>
                Chỉnh sửa giao dịch
            </div>
            <div class="modal-close" onclick="document.getElementById('edit-modal').classList.remove('active')">
                <img src="{{ asset('images/close.png') }}" style="width:16px">
            </div>
        </div>

        <form id="edit-form">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Loại giao dịch <span class="required">*</span></label>
                    <select name="loai_giao_dich" id="edit-loai-giao-dich" class="form-select" required>
                        <option value="">-- Chọn loại --</option>
                        <option value="THU">Thu nhập</option>
                        <option value="CHI">Chi tiêu</option>
                    </select>
                    <span id="edit-error-loai_giao_dich" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Phương thức thanh toán <span class="required">*</span></label>
                    <select name="phuong_thuc_thanh_toan" class="form-select" required>
                        <option value="">-- Chọn phương thức --</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Chuyển khoản">Chuyển khoản</option>
                    </select>
                    <span id="edit-error-phuong_thuc_thanh_toan" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Danh mục <span class="required">*</span></label>
                    <select name="category_id" id="edit-category" class="form-select" required>
                        <option value="">-- Chọn loại giao dịch trước --</option>
                    </select>
                    <span id="edit-error-category_id" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Số tiền <span class="required">*</span></label>
                    <input type="text" id="edit-amount-display" class="form-control amount-display" placeholder="Ví dụ: 500,000">
                    <input type="hidden" name="so_tien">
                    <span id="edit-error-so_tien" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Ngày giao dịch <span class="required">*</span></label>
                    <input type="date" name="ngay_giao_dich" id="edit-ngay-giao-dich" class="form-control" required>
                    <span id="edit-error-ngay_giao_dich" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Ví thanh toán</label>
                    <select name="money_wallet_id" id="edit-wallet" class="form-select">
                        <option value="">-- Không chọn ví (tùy chọn) --</option>
                    </select>
                    <span id="edit-error-money_wallet_id" class="field-error"></span>
                </div>

                <div class="form-group" style="margin-bottom:0;grid-column:1/-1;">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghi_chu" class="form-control form-textarea" placeholder="Ghi chú về giao dịch này..."></textarea>
                </div>

            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('edit-modal').classList.remove('active')">Hủy bỏ</button>
                <button type="submit" class="btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>