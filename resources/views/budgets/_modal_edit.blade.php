<div class="modal-overlay" id="edit-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <div class="page-icon">
                    <img src="{{ asset('images/edit.png') }}" alt="Edit">
                </div>
                Chỉnh sửa ngân sách
            </div>
            <div class="modal-close" onclick="document.getElementById('edit-modal').classList.remove('active')">
                <img src="{{ asset('images/close.png') }}" style="width:16px">
            </div>
        </div>

        <form id="edit-form">
            <div class="modal-body">

                <div class="form-group-compact">
                    <label class="form-label"><strong>Tên ngân sách</strong> <span class="required">*</span></label>
                    <input type="text" name="ten_ngan_sach" class="form-control"
                        placeholder="Ví dụ: Ngân sách ăn uống tháng 1" required>
                    <span id="edit-error-ten_ngan_sach" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Danh mục</strong> <span class="required">*</span></label>
                    <select name="category_id" id="edit-category" class="form-select" required>
                        <option value="">-- Chọn danh mục --</option>
                    </select>
                    <div class="form-help-compact">Chỉ hiển thị danh mục con loại Chi</div>
                    <span id="edit-error-category_id" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Hạn mức ngân sách</strong> <span class="required">*</span></label>
                    <input type="text" id="edit-amount-display" class="form-control amount-display"
                        placeholder="Ví dụ: 10,000,000">
                    <input type="hidden" name="ngan_sach_goc">
                    <div class="form-help-compact">Số tiền tối đa bạn muốn chi tiêu</div>
                    <span id="edit-error-ngan_sach_goc" style="color:#dc2626;font-size:12px;display:none;"></span>
                </div>

                <div class="form-group-compact">
                    <label class="form-label"><strong>Mô tả</strong></label>
                    <textarea name="mo_ta" class="form-textarea"
                        placeholder="Ghi chú thêm về ngân sách này..."
                        style="min-height:90px;"></textarea>
                </div>

            </div>
            <div class="modal-actions-fixed">
                <button type="button" class="btn-secondary"
                    onclick="document.getElementById('edit-modal').classList.remove('active')">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>