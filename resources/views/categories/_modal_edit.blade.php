{{-- Modal Chỉnh Sửa --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <div class="page-icon">
                    <img src="{{ asset('images/edit.png') }}" style="width:24px; filter: brightness(0) invert(1);">
                </div>
                Chỉnh sửa danh mục
            </div>
            <div class="modal-close" onclick="closeModal('edit-modal')">
                <img src="{{ asset('images/close.png') }}" style="width:16px">
            </div>
        </div>

        {{-- Bỏ action/method/csrf — id category được lưu vào data-id, submit xử lý qua fetch trong JS --}}
        <form id="edit-form" data-id="">
            <div class="modal-body">

                {{-- Left: Form Fields --}}
                <div class="modal-left">

                    {{-- Tên danh mục --}}
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Tên danh mục</strong> <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            name="ten_danh_muc"
                            id="edit-ten"
                            class="form-control"
                            placeholder="Ví dụ: Lương tháng, Ăn uống, Du lịch..."
                            required
                        >
                        <span id="edit-error-ten_danh_muc" class="invalid-feedback" style="display:none;"></span>
                    </div>

                    {{-- Loại danh mục --}}
                    <div class="form-group-compact">
                        <label class="form-label">
                            <strong>Loại danh mục</strong> <span class="required">*</span>
                        </label>
                        <div class="radio-group-compact">
                            <div class="radio-item">
                                <input type="radio" id="thu-edit" name="edit_loai" value="THU" class="radio-input">
                                <label for="thu-edit" class="radio-label-compact">
                                    <img src="{{ asset('images/icome.png') }}" alt="Thu nhập">
                                    Thu nhập
                                </label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="chi-edit" name="edit_loai" value="CHI" class="radio-input">
                                <label for="chi-edit" class="radio-label-compact">
                                    <img src="{{ asset('images/expense.png') }}" alt="Chi tiêu">
                                    Chi tiêu
                                </label>
                            </div>
                        </div>
                        <span id="edit-error-loai_danh_muc" class="invalid-feedback" style="display:none;"></span>
                    </div>

                    {{-- Danh mục cha — options được fill bởi JS (renderParentOptions) --}}
                    <div class="form-group-compact">
                        <label class="form-label"><strong>Danh mục cha</strong></label>
                        <select name="danh_muc_cha_id" id="edit-parent" class="form-select">
                            <option value="">-- Không chọn danh mục cha --</option>
                        </select>
                        <div class="form-help-compact">Dùng để tạo danh mục con</div>
                        <span id="edit-error-danh_muc_cha_id" class="invalid-feedback" style="display:none;"></span>
                    </div>

                    {{-- Mô tả --}}
                    <div class="form-group-compact">
                        <label class="form-label"><strong>Mô tả</strong></label>
                        <textarea
                            name="mo_ta"
                            id="edit-mota"
                            class="form-textarea"
                            placeholder="Ghi chú thêm về danh mục này..."
                            style="min-height: 90px;"
                        ></textarea>
                        <span id="edit-error-mo_ta" class="invalid-feedback" style="display:none;"></span>
                    </div>

                </div>

                {{-- Right: Icon & Preview --}}
                <div class="modal-right">

                    {{-- Icon Picker --}}
                    <div class="upload-section">
                        <div class="upload-section-title">
                            <img src="{{ asset('images/image.png') }}" alt="Icon">
                            Biểu tượng danh mục
                        </div>
                        <input type="hidden" name="bieu_tuong" id="edit-selected-icon-input" value="money.png">
                        <button type="button" class="icon-select-btn" onclick="openEditIconPicker()">
                            <div class="icon-select-preview">
                                <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="edit-current-icon-preview">
                            </div>
                            <div class="icon-select-text">
                                <div class="icon-select-name" id="edit-current-icon-name">Tiền mặt</div>
                                <div class="icon-select-hint">Nhấp để thay đổi biểu tượng</div>
                            </div>
                            <img src="{{ asset('images/edit.png') }}" alt="Change" class="icon-select-arrow" style="width:16px;opacity:0.5;">
                        </button>
                        <span id="edit-error-bieu_tuong" class="invalid-feedback" style="display:none;"></span>
                    </div>

                    {{-- Preview Card --}}
                    <div class="preview-card">
                        <div class="preview-title">
                            <img src="{{ asset('images/eye.png') }}" alt="Preview">
                            Xem trước
                        </div>
                        <div class="category-preview">
                            <div class="category-preview-icon" id="edit-preview-icon">
                                <img src="{{ asset('images/category-icons/money.png') }}" alt="Icon" id="edit-preview-icon-img">
                            </div>
                            <div class="category-preview-text">
                                <div class="category-preview-name" id="edit-preview-name">Tên danh mục</div>
                                <div class="category-preview-type">
                                    <span class="badge badge-income" id="edit-preview-badge">THU NHẬP</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-actions-fixed">
                <button type="button" class="btn-secondary" onclick="closeModal('edit-modal')">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-primary" id="edit-submit-btn">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Icon Picker Modal (Edit) --}}
<div class="icon-picker-modal" id="edit-icon-picker-modal">
    <div class="icon-picker-overlay" onclick="closeEditIconPicker()"></div>
    <div class="icon-picker-content">
        <div class="icon-picker-header">
            <div class="icon-picker-header-title">
                <img src="{{ asset('images/image.png') }}" alt="Icon" style="width:20px;">
                Chọn biểu tượng
            </div>
            <button type="button" class="icon-picker-close" onclick="closeEditIconPicker()">
                <img src="{{ asset('images/close.png') }}" alt="Close" style="width:16px;">
            </button>
        </div>
        <div class="icon-picker-body">
            <div class="icon-search">
                <img src="{{ asset('images/search.png') }}" class="icon-search-icon" alt="Search">
                <input
                    type="text"
                    id="edit-icon-search-input"
                    placeholder="Tìm kiếm biểu tượng đẹp cho danh mục..."
                    autocomplete="off"
                >
            </div>
            <div class="icon-grid" id="edit-icon-grid"></div>
        </div>
        <div class="icon-picker-footer">
            <button type="button" class="btn-secondary" onclick="closeEditIconPicker()">Hủy bỏ</button>
            <button type="button" class="btn-primary" onclick="confirmEditIconSelection()">Xác nhận</button>
        </div>
    </div>
</div>