<?php

return [
    'meta' => [
        'title'       => 'Chính sách đổi vé, hủy vé và hoàn tiền - FUTA Bus Lines',
        'description' => 'Quy định đổi vé, hủy vé và hoàn tiền khi sử dụng dịch vụ FUTA Bus Lines.',
    ],
    'brand'              => 'FUTA Bus Lines',
    'heading'            => 'Chính sách đổi vé, hủy vé và hoàn tiền',
    'transaction_errors' => [
        'title'        => 'Điều 1. Quy định hoàn trả tiền mua vé Online do lỗi giao dịch',
        'introduction' => 'Các trường hợp hoàn trả tiền mua vé online cho khách hàng do lỗi giao dịch:',
        'items'        => [
            'Khách hàng mua vé online nhưng giao dịch không thành công, chưa có mã đặt vé nhưng tài khoản đã bị trừ tiền;',
            'Một số thẻ ATM cũ chỉ hỗ trợ chuyển khoản và không có chức năng thanh toán trực tuyến. Khi giao dịch '
                .'cuối tuần, ngày lễ hoặc Tết không được ngân hàng xác nhận kịp thời, khách hàng có thể phải thanh '
                .'toán tại quầy để lấy vé; khoản đã trừ sẽ được kiểm tra và hoàn trả sau đối soát.',
        ],
    ],
    'processing' => [
        'title'    => 'Điều 2. Thời gian hoàn trả tiền cho khách',
        'channels' => [
            ['name' => 'Website', 'time' => 'Theo dõi yêu cầu tại Lịch sử mua vé. Nhân viên đối soát và ghi nhận hoàn tiền sau khi ngân hàng xác nhận chuyển trả thành công.'],
            ['name' => 'Tổng đài / quầy vé', 'time' => 'Liên hệ 1900 6067 hoặc quầy vé để được kiểm tra hồ sơ; thời gian hoàn phụ thuộc đối soát và ngân hàng.'],
            ['name' => 'Kênh khác', 'time' => 'Liên hệ nơi đã mua vé để được hướng dẫn theo chính sách của kênh đó.'],
        ],
    ],
    'changes' => [
        'title' => 'Điều 3. Quy định thay đổi hoặc hủy vé',
        'items' => (require __DIR__.'/cancellation-policy.php')['items'],
    ],
];
