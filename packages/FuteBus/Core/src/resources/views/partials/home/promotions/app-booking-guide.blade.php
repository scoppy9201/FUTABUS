<div class="mt-8 space-y-6 text-base leading-7 text-gray-950">
    <div class="flex flex-col items-center gap-6" aria-label="Năm bước đặt vé qua App FUTA">
        @foreach ([
            ['app-booking-step-01.png', 'Bước 1: Tải ứng dụng FUTA'],
            ['app-booking-step-02.png', 'Bước 2: Đăng nhập ứng dụng FUTA'],
            ['app-booking-step-03.png', 'Bước 3: Chọn hành trình'],
            ['app-booking-step-04.png', 'Bước 4: Chọn chuyến xe và ghế'],
            ['app-booking-step-05.png', 'Bước 5: Xác nhận và thanh toán'],
        ] as [$file, $alt])
            <img
                src="{{ asset('images/promotions/'.$file) }}"
                alt="{{ $alt }}"
                class="h-auto w-full max-w-216.5"
                loading="lazy"
            >
        @endforeach
    </div>

    <p class="font-bold">Với giao diện hiện đại cùng quy trình tối ưu hóa, App FUTA sẽ mang đến trải nghiệm đặt vé nhanh chóng và chuyên nghiệp, giúp mỗi chuyến đi của Quý khách thêm phần trọn vẹn.</p>

    <div class="space-y-2 font-bold">
        <p>✅ Đặt vé mọi lúc, mọi nơi chỉ với vài phút.</p>
        <p>✅ Tự do lựa chọn vị trí ghế ngồi và xem giá vé của tuyến.</p>
        <p>✅ Vé điện tử đầy tiện lợi, chỉ cần đưa mã QR cho tiếp viên trên tuyến xe đã đặt là có thể nhanh chóng lên xe.</p>
        <p>✅ Quản lý lịch sử chuyến đi và vé điện tử ngay trên điện thoại.</p>
    </div>

    <p class="font-bold">Hãy để Công ty Phương Trang đồng hành và chăm sóc hành trình của Quý khách, bắt đầu từ sự tiện lợi ngay trên chính chiếc điện thoại thân thuộc.</p>
    <p class="font-bold">🌈 Tải App FUTA ngay hôm nay để tận hưởng dịch vụ vận chuyển chuyên nghiệp và hiện đại nhất!</p>

    <div class="font-bold">
        <p>❤️Công Ty Phương Trang hân hạnh được phục vụ Quý Khách!</p>
        <p>📌Thông tin chi tiết xin vui lòng liên hệ:</p>
        <p>☎ Trung Tâm Tổng Đài &amp; CSKH: 𝟏𝟗𝟎𝟎.𝟔𝟎𝟔𝟕</p>
    </div>
</div>
