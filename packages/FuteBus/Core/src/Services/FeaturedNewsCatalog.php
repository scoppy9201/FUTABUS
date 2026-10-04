<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;

class FeaturedNewsCatalog
{
    public function __construct(private readonly FeaturedPromotionCatalog $featuredPromotionCatalog) {}

    public function all(): Collection
    {
        return collect([
            [
                'slug' => 'trai-nghiem-xe-sang-cho-hanh-trinh-ket-noi-da-lat',
                'title' => 'TRẢI NGHIỆM XE SANG CHO HÀNH TRÌNH KẾT NỐI ĐÀ LẠT',
                'image' => 'images/news/da-lat-kim-long-limousine.png',
                'published_at' => '11:05 16/09/2026',
                'intro' => 'Kể từ 20/09/2026 hành khách khởi hành từ Đà Lạt đến những thành phố biển nổi tiếng nay thêm thuận tiện với lựa chọn di chuyển cao cấp trên dòng xe KimLong 99 - N29.',
                'content_view' => 'core::partials.home.news.da-lat-kim-long',
            ],
            [
                'slug' => 'ket-noi-can-tho-nang-tam-trai-nghiem-cung-dong-xe-kim-long-hien-dai',
                'title' => 'KẾT NỐI CẦN THƠ: NÂNG TẦM TRẢI NGHIỆM CÙNG DÒNG XE KIM LONG HIỆN ĐẠI',
                'image' => 'images/news/can-tho-kim-long-n29.png',
                'published_at' => '14:27 14/09/2026',
                'intro' => 'Với mong muốn mang đến thêm lựa chọn chất lượng cho các tuyến kết nối Cần Thơ, Công Ty Phương Trang đưa vào khai thác dòng xe Kim Long N29 thế hệ mới.',
                'content_view' => 'core::partials.home.news.can-tho-kim-long',
            ],
            [
                'slug' => 'trung-chuyen-mien-phi-8-huong-tai-van-phong-nga-bay',
                'title' => 'TRUNG CHUYỂN MIỄN PHÍ 8 HƯỚNG TẠI VĂN PHÒNG NGÃ BẢY - ĐƯA ĐÓN TẬN NƠI, AN TÂM MỌI HÀNH TRÌNH',
                'image' => 'images/news/nga-bay-free-shuttle.png',
                'published_at' => '10:43 30/09/2026',
                'intro' => 'Sau mỗi chuyến xe đường dài, hành trình của Quý khách vẫn được nối tiếp thuận tiện với dịch vụ trung chuyển MIỄN PHÍ tại khu vực Văn phòng Ngã Bảy.',
                'content_view' => 'core::partials.home.news.nga-bay-free-shuttle',
            ],
            [
                'slug' => 'thanh-toan-khong-tien-mat-len-xe-khong-can-ve-giay-cung-cong-ty-phuong-trang',
                'title' => 'THANH TOÁN KHÔNG TIỀN MẶT - LÊN XE KHÔNG CẦN VÉ GIẤY CÙNG CÔNG TY PHƯƠNG TRANG',
                'image' => 'images/news/cashless-payment-eticket.png',
                'published_at' => '10:27 29/09/2026',
                'intro' => 'Không cần chuẩn bị tiền mặt, không cần cầm theo vé giấy, hành trình cùng Công ty Phương Trang nay được số hóa từ bước đặt vé đến khi lên xe.',
                'content_view' => 'core::partials.home.news.cashless-payment-eticket',
            ],
            [
                'slug' => 'luu-ngay-lo-trinh-trung-chuyen-tai-van-phong-nga-5-bac-lieu',
                'title' => 'LƯU NGAY LỘ TRÌNH TRUNG CHUYỂN TẠI VĂN PHÒNG NGÃ 5 - BẠC LIÊU',
                'image' => 'images/news/nga-nam-shuttle-routes.png',
                'published_at' => '08:35 29/09/2026',
                'intro' => 'Để hành trình của Quý khách thuận tiện hơn trong việc di chuyển, Công ty Phương Trang cập nhật các lộ trình trung chuyển đang phục vụ tại Văn phòng Ngã 5 – Bạc Liêu.',
                'content_view' => 'core::partials.home.news.nga-nam-shuttle-routes',
            ],
            [
                'slug' => 'thong-tin-hoat-dong-trung-chuyen-tai-vp-phuoc-long-bac-lieu',
                'title' => 'THÔNG TIN HOẠT ĐỘNG TRUNG CHUYỂN TẠI VP PHƯỚC LONG (BẠC LIÊU)',
                'image' => 'images/news/phuoc-long-shuttle-routes.png',
                'published_at' => '10:10 28/09/2026',
                'intro' => 'Mỗi hành trình thuận tiện cùng Công ty Phương Trang luôn được tiếp nối bằng dịch vụ trung chuyển miễn phí tại từng khu vực. Nhằm giúp Quý khách chủ động hơn về điểm đón, điểm trả và thời gian di chuyển, Công ty Phương Trang cập nhật các hướng trung chuyển đang phục vụ tại Văn phòng Phước Long.',
                'content_view' => 'core::partials.home.news.phuoc-long-shuttle-routes',
            ],
            $this->featuredPromotionCatalog->find('ve-mien-tay-tu-cua-ngo-phia-dong-chang-can-di-xa-cong-ty-phuong-trang-dua-quy-khach-ve-nha'),
            $this->featuredPromotionCatalog->find('chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa'),
            [
                'slug' => 'nha-trang-cam-ranh-phan-rang-xe-buyt-them-tuyen-hanh-trinh-them-tien',
                'title' => 'NHA TRANG - CAM RANH - PHAN RANG: XE BUÝT THÊM TUYẾN, HÀNH TRÌNH THÊM TIỆN',
                'image' => 'images/news/nha-trang-cam-ranh-phan-rang-bus.png',
                'published_at' => '16:07 31/08/2026',
                'intro' => 'Từ tuyến buýt số 11 quen thuộc, nay hành khách có thêm 2 lựa chọn hành trình mới: Tuyến 11.1 Nha Trang – Cam Ranh và Tuyến 11.2 Nha Trang – Phan Rang – Tháp Chàm.',
                'content_view' => 'core::partials.home.news.nha-trang-cam-ranh-phan-rang-bus',
            ],
            [
                'slug' => 'cong-ty-phuong-trang-thong-bao-lich-nghi-quoc-khanh-02-09-2026-tai-4-van-phong-benh-vien-tp-hcm',
                'title' => 'CÔNG TY PHƯƠNG TRANG THÔNG BÁO LỊCH NGHỈ QUỐC KHÁNH 02/9/2026 TẠI 4 VĂN PHÒNG BỆNH VIỆN TP. HCM',
                'image' => 'images/news/hospital-office-national-day-hours.png',
                'published_at' => '15:13 28/08/2026',
                'intro' => 'Nhằm chủ động phục vụ nhu cầu đi lại của Quý khách trong dịp Lễ Quốc khánh 02/9/2026, Công ty Phương Trang trân trọng thông báo lịch nghỉ tại 04 văn phòng đặt trong khuôn viên bệnh viện như sau:',
                'content_view' => 'core::partials.home.news.hospital-office-national-day-hours',
            ],
            [
                'slug' => 'chinh-thuc-nang-cap-xe-buyt-dien-tren-tuyen-141',
                'title' => 'CHÍNH THỨC NÂNG CẤP XE BUÝT ĐIỆN TRÊN TUYẾN 141: “XANH” HƠN – ÊM HƠN – HIỆN ĐẠI HƠN!',
                'image' => 'images/news/electric-bus-route-141.png',
                'published_at' => '15:10 28/08/2026',
                'intro' => 'Với mục tiêu nâng cao chất lượng dịch vụ và hướng tới một môi trường đô thị xanh – sạch – đẹp, Tuyến xe buýt 141: Khu du lịch BCR - Long Trường - Khu chế xuất Linh Trung II chính thức chuyển đổi hoàn toàn sang XE BUÝT ĐIỆN HIỆN ĐẠI!',
                'content_view' => 'core::partials.home.news.electric-bus-route-141',
            ],
            $this->featuredPromotionCatalog->find('dat-ve-xe-phuong-trang-nhanh-chong-tien-loi'),
            [
                'slug' => 'tang-tan-suat-tuyen-buyt-171-len-68-chuyen-ngay',
                'title' => 'TĂNG TẦN SUẤT TUYẾN BUÝT 171 LÊN 68 CHUYẾN/NGÀY!',
                'image' => 'images/news/electric-bus-route-171-frequency.png',
                'published_at' => '08:58 24/08/2026',
                'intro' => 'Quý khách hàng thân mến! Tuyến xe buýt điện 171: Bến xe Phú Chánh – Trung tâm hành chính Bình Dương – Bến Thành chính thức TĂNG TẦN SUẤT HOẠT ĐỘNG để phục vụ nhu cầu đi lại ngày càng cao của bà con!',
                'content_view' => 'core::partials.home.news.electric-bus-route-171-frequency',
            ],
            [
                'slug' => 'van-phong-ngo-gia-tu-phan-rang-cap-nhat-dia-chi-moi',
                'title' => 'VĂN PHÒNG NGÔ GIA TỰ - PHAN RANG CẬP NHẬT ĐỊA CHỈ MỚI',
                'image' => 'images/news/ngo-gia-tu-phan-rang-office.png',
                'published_at' => '10:29 19/08/2026',
                'intro' => 'Nhằm giúp Quý khách thuận tiện hơn trong việc liên hệ, đặt vé và gửi hàng, Công ty Phương Trang trân trọng thông báo Văn phòng Ngô Gia Tự - Phan Rang đã cập nhật địa chỉ mới.',
                'content_view' => 'core::partials.home.news.ngo-gia-tu-phan-rang-office',
            ],
            $this->featuredPromotionCatalog->find('van-phong-phuong-trang-486-486a-le-van-luong-chinh-thuc-khai-truong'),
        ])->map(fn (array $article) => [
            ...$article,
            'category' => match (true) {
                str_contains($article['slug'], 'buyt') => 'futa-city-bus',
                in_array($article['slug'], [
                    've-mien-tay-tu-cua-ngo-phia-dong-chang-can-di-xa-cong-ty-phuong-trang-dua-quy-khach-ve-nha',
                    'chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa',
                    'dat-ve-xe-phuong-trang-nhanh-chong-tien-loi',
                    'van-phong-phuong-trang-486-486a-le-van-luong-chinh-thuc-khai-truong',
                ], true) => 'khuyen-mai',
                default => 'futa-bus-lines',
            },
        ]);
    }

    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }
}
