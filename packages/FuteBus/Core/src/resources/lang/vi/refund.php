<?php

return [
    'meta' => [
        'title' => 'Chính sách đổi vé, hủy vé và hoàn tiền - FUTA Bus Lines',
        'description' => 'Quy định đổi vé, hủy vé và hoàn tiền khi sử dụng dịch vụ FUTA Bus Lines.',
    ],
    'brand' => 'FUTA Bus Lines',
    'heading' => 'Chính sách đổi vé, hủy vé và hoàn tiền',
    'transaction_errors' => [
        'title' => 'Điều 1. Quy định hoàn trả tiền mua vé Online do lỗi giao dịch',
        'introduction' => 'Các trường hợp hoàn trả tiền mua vé online cho khách hàng do lỗi giao dịch:',
        'items' => [
            'Khách hàng mua vé online nhưng giao dịch không thành công, chưa có mã đặt vé nhưng tài khoản đã bị trừ tiền;',
            'Một số thẻ ATM cũ chỉ hỗ trợ chuyển khoản và không có chức năng thanh toán trực tuyến. Khi giao dịch '
                . 'cuối tuần, ngày lễ hoặc Tết không được ngân hàng xác nhận kịp thời, khách hàng có thể phải thanh '
                . 'toán tại quầy để lấy vé; khoản đã trừ sẽ được kiểm tra và hoàn trả sau đối soát.',
        ],
    ],
    'processing' => [
        'title' => 'Điều 2. Thời gian hoàn trả tiền cho khách',
        'channels' => [
            ['name' => 'Bộ phận Tổng đài', 'time' => '03 – 05 ngày làm việc kể từ khi Ban Tài chính – Kế toán nhận đủ chứng từ thanh toán.'],
            ['name' => 'Quầy vé', 'time' => 'Giao dịch trực tiếp và hoàn trả ngay tại thời điểm xử lý giao dịch.'],
            ['name' => 'Ứng dụng (App)', 'time' => 'Theo chính sách và thời gian xử lý của từng nhà phát triển ứng dụng.'],
        ],
    ],
    'changes' => [
        'title' => 'Điều 3. Quy định thay đổi hoặc hủy vé',
        'items' => [
            'Mỗi vé chỉ được chuyển đổi tối đa 01 lần.',
            'Phí hủy vé từ 10% – 30% giá vé, tùy thời gian hủy so với giờ khởi hành, số lượng vé cá nhân hoặc '
                . 'tập thể và các quy định hiện hành tại thời điểm yêu cầu.',
            'Khách hàng có nhu cầu đổi hoặc hủy vé đã thanh toán cần liên hệ Tổng đài 1900 6067 hoặc quầy vé '
                . 'chậm nhất 24 giờ trước giờ xe khởi hành để được hướng dẫn.',
        ],
    ],
];
