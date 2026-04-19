<div class="modal-overlay" id="create-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <div class="page-icon">
                    <img src="{{ asset('images/wallet.png') }}" alt="Wallet">
                </div>
                Thêm ngân sách mới
            </div>
            <div class="modal-close" onclick="document.getElementById('create-modal').classList.remove('active')">
                <img src="{{ asset('images/close.png') }}" alt="Close">
            </div>
        </div>

        <form id="create-form" class="budget-modal-form">
            <div class="modal-body">
                <div class="form-group-compact">
                    <label class="form-label"><strong>Tên ngân sách</strong> <span class="required">*</span></label>
                    <input type="text" name="ten_ngan_sach" class="form-control"
                        placeholder="Ví dụ: Ngân sách ăn uống tháng 1" required>
                    <span id="create-error-ten_ngan_sach" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Danh mục</strong> <span class="required">*</span></label>
                    <select name="category_id" id="create-category" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                    </select>
                    <div class="form-help-compact">Chỉ hiển thị danh mục con loại Chi</div>
                    <span id="create-error-category_id" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Hạn mức ngân sách</strong> <span class="required">*</span></label>
                    <input type="text" id="create-amount-display" class="form-control amount-display"
                        placeholder="Ví dụ: 10,000,000">
                    <input type="hidden" name="ngan_sach_goc">
                    <div class="form-help-compact">Số tiền tối đa bạn muốn chi tiêu</div>
                    <span id="create-error-ngan_sach_goc" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Loại thời gian</strong> <span class="required">*</span></label>
                    <select name="loai_thoi_gian" id="create-loai-thoi-gian" class="form-select" required>
                        <option value="thang">Theo tháng (tự động reset)</option>
                        <option value="ngay">Theo ngày (tối đa 30 ngày)</option>
                    </select>
                    <span id="create-error-loai_thoi_gian" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div id="create-section-thang" class="budget-modal-span-2">
                    <div class="budget-time-grid">
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Tháng áp dụng</strong> <span class="required">*</span></label>
                            <input type="month" name="thang_ap_dung" id="create-thang-ap-dung" class="form-control">
                            <div class="form-help-compact">Ngân sách sẽ tự động reset vào đầu tháng tiếp theo</div>
                            <span id="create-error-ngay_bat_dau" style="color:#dc2626;font-size:12px;display:none;"></span>
                        </div>

                        <div class="form-group-compact budget-toggle-card">
                            <label class="form-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="tu_dong_reset" id="create-tu-dong-reset"
                                    value="1" checked
                                    style="width:16px;height:16px;cursor:pointer;">
                                <strong>Tự động reset sang tháng mới</strong>
                            </label>
                            <div class="form-help-compact">Đầu tháng mới số dư sẽ được reset về hạn mức ban đầu</div>
                        </div>
                    </div>
                </div>

                <div id="create-section-ngay" class="budget-modal-span-2" style="display:none;">
                    <div class="budget-date-grid">
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Ngày bắt đầu</strong> <span class="required">*</span></label>
                            <input type="date" name="ngay_bat_dau_custom" id="create-ngay-bat-dau" class="form-control">
                            <span id="create-error-ngay_bat_dau_custom" style="color:#dc2626;font-size:12px;display:none;"></span>
                        </div>
                        <div class="form-group-compact">
                            <label class="form-label"><strong>Ngày kết thúc</strong> <span class="required">*</span></label>
                            <input type="date" name="ngay_ket_thuc_custom" id="create-ngay-ket-thuc" class="form-control">
                            <div class="form-help-compact">Tối đa 30 ngày kể từ ngày bắt đầu</div>
                            <span id="create-error-ngay_ket_thuc" style="color:#dc2626;font-size:12px;display:none;"></span>
                        </div>
                    </div>
                </div>

                <div class="form-group-compact budget-modal-span-2">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea name="mo_ta" class="form-textarea"
                        placeholder="Ghi chú thêm về ngân sách này..."
                        style="min-height:90px;"></textarea>
                </div>
            </div>

            <div class="modal-actions-fixed">
                <button type="button" class="btn-secondary"
                    onclick="document.getElementById('create-modal').classList.remove('active')">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-primary">Lưu ngân sách</button>
            </div>
        </form>
    </div>
</div>
