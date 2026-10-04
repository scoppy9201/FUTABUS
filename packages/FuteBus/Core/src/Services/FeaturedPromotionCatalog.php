<?php

declare(strict_types=1);

namespace FuteBus\Core\Services;

use Illuminate\Support\Collection;

class FeaturedPromotionCatalog
{
    public function all(): Collection
    {
        return collect([
            [
                'slug' => 'nguoi-dan-dong-thap-di-kham-benh-cong-ty-phuong-trang-lo-tron-tung-chang-duong',
                'title' => 'NGƯỜI DÂN ĐỒNG THÁP ĐI KHÁM BỆNH - CÔNG TY PHƯƠNG TRANG LO TRỌN TỪNG CHẶNG ĐƯỜNG',
                'image' => 'images/promotions/dong-thap-hospital-shuttle.png',
                'published_at' => '14:55 14/07/2026',
                'intro' => '🚌 Mỗi lần từ Đồng Tháp lên TP.HCM khám bệnh, nỗi lo lớn nhất của bà con không chỉ là việc di chuyển xa xôi, mà còn là làm sao để từ bến xe đến được bệnh viện mà không phải lo tìm đường, lo nắng mưa hay tốn thêm chi phí bắt xe.',
                'content_view' => 'core::partials.home.promotions.dong-thap-hospital-shuttle',
            ],
            [
                'slug' => 'canh-bao-tinh-trang-lua-dao-ve-xe-dip-le-quoc-khanh-02-09-2026',
                'title' => 'CẢNH BÁO: TÌNH TRẠNG LỪA ĐẢO VÉ XE DỊP LỄ QUỐC KHÁNH 02/09/2026',
                'image' => 'images/promotions/fraud-warning.png',
                'published_at' => '15:13 17/08/2026',
                'intro' => 'Lợi dụng nhu cầu đi lại tăng cao trong dịp Quốc khánh 02/09, các đối tượng xấu đang giả mạo Công ty Phương Trang với nhiều thủ đoạn vô cùng tinh vi. Quý khách hàng hãy cực kỳ nâng cao cảnh giác để bảo vệ tài sản của mình!',
                'content_view' => 'core::partials.home.promotions.fraud-warning',
            ],
            [
                'slug' => 'van-phong-phuong-trang-486-486a-le-van-luong-chinh-thuc-khai-truong',
                'title' => 'VĂN PHÒNG PHƯƠNG TRANG 486 - 486A LÊ VĂN LƯƠNG CHÍNH THỨC KHAI TRƯƠNG',
                'image' => 'images/promotions/le-van-luong-office-opening.png',
                'published_at' => '09:22 19/08/2026',
                'intro' => 'Nhằm mang đến nhiều tiện ích hơn cho Quý khách, Công ty Phương Trang chính thức đưa vào hoạt động Văn phòng 486 - 486A Lê Văn Lương, Phường Tân Hưng, TP.HCM từ ngày 22/8/2026 - tọa lạc tại vị trí thuận lợi trên trục đường Lê Văn Lương, gần Lotte Mart Quận 7 và kết nối nhanh với các tuyến đường lớn như Nguyễn Hữu Thọ, Nguyễn Thị Thập, Nguyễn Văn Linh.',
                'content_view' => 'core::partials.home.promotions.le-van-luong-office-opening',
            ],
            [
                'slug' => 'dat-ve-xe-phuong-trang-nhanh-chong-tien-loi',
                'title' => 'ĐẶT VÉ XE PHƯƠNG TRANG NHANH CHÓNG - TIỆN LỢI',
                'image' => 'images/promotions/booking-steps.png',
                'published_at' => '16:35 27/08/2026',
                'intro' => 'Quý khách có thể dễ dàng lựa chọn chuyến xe phù hợp, đặt vé nhanh chóng và nhận vé điện tử ngay lập tức, thuận tiện cho mọi hành trình, dù là đi công tác hay về thăm gia đình.',
                'content_view' => 'core::partials.home.promotions.booking-steps',
            ],
            [
                'slug' => 'chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa',
                'title' => 'CHỦ ĐỘNG HÀNH TRÌNH, AN TÂM GIỮ CHỖ CÙNG APP FUTA',
                'image' => 'images/promotions/early-booking-app-guide.png',
                'published_at' => '10:02 04/09/2026',
                'intro' => 'Mời Quý khách tham khảo hướng dẫn 05 bước đặt vé qua hình ảnh dưới đây để sở hữu ngay tấm vé hành trình chỉ trong tích tắc!',
                'content_view' => 'core::partials.home.promotions.app-booking-guide',
            ],
            [
                'slug' => 've-mien-tay-tu-cua-ngo-phia-dong-chang-can-di-xa-cong-ty-phuong-trang-dua-quy-khach-ve-nha',
                'title' => 'VỀ MIỀN TÂY TỪ CỬA NGÕ PHÍA ĐÔNG: CHẲNG CẦN ĐI XA, CÔNG TY PHƯƠNG TRANG ĐƯA QUÝ KHÁCH VỀ NHÀ',
                'image' => 'images/promotions/mien-dong-moi-western-routes.png',
                'published_at' => '10:33 04/09/2026',
                'intro' => 'Quý khách đang ở Thủ Đức, Quận 9 hay các khu công nghiệp tại Bình Dương, Đồng Nai? Quý khách muốn về các tỉnh miền Tây nhưng lại ngại cảnh kẹt xe hàng giờ đồng hồ để sang tận Bến xe Miền Tây?',
                'content_view' => 'core::partials.home.promotions.mien-dong-moi-western-routes',
            ],
        ]);
    }

    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }
}
