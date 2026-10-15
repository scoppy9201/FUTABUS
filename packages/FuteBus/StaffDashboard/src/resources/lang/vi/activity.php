<?php

return [
    'title'     => 'Lịch hoạt động',
    'today'     => 'Hôm nay',
    'previous'  => 'Tháng trước',
    'next'      => 'Tháng sau',
    'open_day'  => 'Xem hoạt động trong ngày',
    'weekdays'  => ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
    'legend'    => ['issued' => 'Vé đặt', 'cancelled' => 'Vé hủy', 'pending' => 'Cần xử lý'],
    'day_title' => 'Hoạt động trong ngày',
    'day_intro' => 'Số liệu của nhà xe theo quyền truy cập của bạn.',
    'counts'    => [
        'issued'          => 'Vé đặt đã phát hành', 'cancelled' => 'Vé đã hủy',
        'support_pending' => 'Khiếu nại / hỗ trợ đang chờ', 'refund_pending' => 'Hoàn tiền chưa hoàn tất',
    ],
    'basis'        => 'Vé được tính theo ngày phát hành hoặc hủy; vé cấp lại do đổi chuyến không tính là vé đặt mới. Yêu cầu chờ là hồ sơ tạo trong ngày đã chọn và hiện chưa hoàn tất. Dấu “—” nghĩa là chưa có quyền xem.',
    'open_support' => 'Mở danh sách hỗ trợ',
    'open_refunds' => 'Mở quản lý hoàn tiền',
    'queue_title'  => 'Việc cần theo dõi',
    'queue_kinds'  => ['support' => 'Khiếu nại / hỗ trợ', 'refund' => 'Xử lý hoàn tiền'],
    'queue_empty'  => 'Chưa có yêu cầu chờ được giao cho bạn hoặc chưa phân công trong phạm vi quyền truy cập.',
    'auto_refresh' => 'Cập nhật mỗi 15 giây',
    'stale'        => 'Chưa cập nhật được dữ liệu mới. Đang hiển thị lần tải gần nhất.',
    'retry'        => 'Không tải được lịch. Bấm để thử lại.',
];
