<div class="modal-overlay" id="edit-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <div class="page-icon">
                    <img src="{{ asset('images/edit.png') }}" style="width:24px; filter:brightness(0) invert(1);">
                </div>
                Chỉnh sửa giao dịch
            </div>
            <div class="modal-close" onclick="document.getElementById('edit-modal').classList.remove('active')">
                <img src="{{ asset('images/close.png') }}" style="width:16px">
            </div>
        </div>

        <form id="edit-form">
            <div class="modal-body">

                <div class="form-group">
                    <label class="form-label"><strong>Loại giao dịch</strong> <span class="required">*</span></label>
                    <select name="loai_giao_dich" id="edit-loai-giao-dich" class="form-select" required>
                        <option value="">-- Chọn loại --</option>
                        <option value="THU">Thu nhập</option>
                        <option value="CHI">Chi tiêu</option>
                    </select>
                    <span id="edit-error-loai_giao_dich" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Phương thức thanh toán</strong> <span class="required">*</span></label>
                    <select name="phuong_thuc_thanh_toan" class="form-select" required>
                        <option value="">-- Chọn phương thức --</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Chuyển khoản">Chuyển khoản</option>
                    </select>
                    <span id="edit-error-phuong_thuc_thanh_toan" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Danh mục</strong> <span class="required">*</span></label>
                    <select name="category_id" id="edit-category" class="form-select" required>
                        <option value="">-- Chọn loại giao dịch trước --</option>
                    </select>
                    <span id="edit-error-category_id" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Số tiền</strong> <span class="required">*</span></label>
                    <input type="text" id="edit-amount-display" class="form-control amount-display" placeholder="Ví dụ: 500,000">
                    <input type="hidden" name="so_tien">
                    <span id="edit-error-so_tien" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Ngày giao dịch</strong> <span class="required">*</span></label>
                    <input type="date" name="ngay_giao_dich" class="form-control" required>
                    <span id="edit-error-ngay_giao_dich" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Ghi chú</strong></label>
                    <textarea name="ghi_chu" class="form-control form-textarea" placeholder="Ghi chú về giao dịch này..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><strong>Ví thanh toán</strong></label>
                    <select name="money_wallet_id" id="edit-wallet" class="form-select">
                        <option value="">-- Không chọn ví (tùy chọn) --</option>
                    </select>
                </div>

            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('edit-modal').classList.remove('active')">Hủy bỏ</button>
                <button type="submit" class="btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>