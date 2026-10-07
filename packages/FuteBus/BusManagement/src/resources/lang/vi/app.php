<?php

return [
    // Page
    'vehicle_types_title'       => 'Quản lý loại phương tiện',
    'vehicle_types_subtitle'    => 'Xem, thêm, sửa và ngừng sử dụng các loại phương tiện làm cơ sở gán cho chuyến xe.',

    // Table columns
    'col_stt'                   => 'STT',
    'col_name'                  => 'Tên loại phương tiện',
    'col_description'           => 'Mô tả',
    'col_capacity'              => 'Sức chứa mặc định',
    'col_status'                => 'Trạng thái',
    'col_actions'               => 'Thao tác',

    // Status
    'status_active'             => 'Đang sử dụng',
    'status_inactive'           => 'Ngừng sử dụng',

    // Toolbar
    'search_placeholder'        => 'Tìm theo tên loại phương tiện...',
    'btn_search'                => 'Tìm kiếm',
    'btn_clear_filter'          => 'Xóa bộ lọc',
    'btn_add'                   => 'Thêm loại phương tiện',
    'btn_edit'                  => 'Sửa',
    'btn_delete'                => 'Xóa',
    'btn_cancel'                => 'Hủy bỏ',
    'btn_save'                  => 'Lưu cập nhật',
    'btn_confirm_delete'        => 'Xác nhận xóa',
    'btn_add_now'               => '+ Thêm mới ngay',

    // Empty
    'empty'                     => 'Chưa có loại phương tiện nào.',

    // Add modal
    'modal_add_title'           => 'Thêm loại phương tiện',
    'field_name'                => 'Tên loại phương tiện',
    'field_name_placeholder'    => 'VD: Giường nằm 40 chỗ',
    'field_name_hint'           => 'Tên loại phương tiện phải là duy nhất trong hệ thống.',
    'field_description'         => 'Mô tả',
    'field_description_placeholder' => 'Mô tả ngắn về loại phương tiện...',
    'field_capacity'            => 'Sức chứa mặc định',
    'field_capacity_unit'       => 'ghế',
    'field_required'            => '*',
    'btn_store'                 => 'Thêm mới',

    // Edit modal
    'modal_edit_title'          => 'Cập nhật loại phương tiện',

    // Delete modal
    'modal_delete_title'        => 'Xác nhận xóa loại phương tiện',
    'modal_delete_message'      => 'Bạn có chắc chắn muốn xóa loại phương tiện',
    'modal_delete_note_title'   => '⚠ Lưu ý về nghiệp vụ:',
    'modal_delete_note_1'       => 'Nếu loại phương tiện đang được gán cho xe, hệ thống sẽ ngừng sử dụng thay vì xóa hoàn toàn.',
    'modal_delete_note_2'       => 'Nếu chưa được gán, hệ thống sẽ xóa hoàn toàn khỏi dữ liệu.',

    // Flash messages (used in controller)
    'flash_created'             => 'Thêm loại phương tiện thành công.',
    'flash_updated'             => 'Cập nhật loại phương tiện thành công.',
    'flash_deleted'             => 'Xóa loại phương tiện thành công.',
    'flash_deactivated'         => 'Loại phương tiện đang được sử dụng, đã chuyển sang trạng thái ngừng sử dụng.',

    'bus_title'            => 'Quản lý thông tin xe',
'bus_subtitle'         => 'Tìm kiếm, thêm, sửa và ngừng sử dụng từng xe cụ thể của nhà xe.',
'bus_search_ph'        => 'Tìm theo biển số, số khung, hãng...',
'bus_f_plate'          => 'Biển số',
'bus_f_chassis'        => 'Số khung',
'bus_f_type'           => 'Loại phương tiện',
'bus_f_type_select'    => '-- Chọn loại phương tiện --',
'bus_f_brand'          => 'Hãng',
'bus_f_color'          => 'Màu xe',
'bus_f_year'           => 'Năm sản xuất',
'bus_f_rows'           => 'Số hàng ghế',
'bus_f_cols'           => 'Số cột ghế',
'bus_modal_add'        => 'Thêm xe',
'bus_modal_edit'       => 'Cập nhật thông tin xe',
'bus_modal_delete'     => 'Xác nhận ngừng sử dụng xe',
'bus_delete_msg'       => 'Bạn có chắc chắn muốn ngừng sử dụng xe',
'bus_flash_created'    => 'Thêm xe thành công.',
'bus_flash_updated'    => 'Cập nhật thông tin xe thành công.',
'bus_flash_deactivated'=> 'Xe đã chuyển sang trạng thái ngừng sử dụng.',
'bus_err_active_trip'  => 'Xe đang được gán cho chuyến xe đang hoạt động, không thể sửa hoặc xóa.',
];

