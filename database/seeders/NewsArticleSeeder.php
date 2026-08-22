<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuteBus\Core\Models\NewsArticle;
use Illuminate\Database\Seeder;

class NewsArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Trải nghiệm dịch vụ trung chuyển đón trả điểm tại TP. Hồ Chí Minh',
                'slug' => 'trai-nghiem-dich-vu-trung-chuyen-tp-ho-chi-minh',
                'summary' => 'Dịch vụ trung chuyển thuận tiện giúp hành khách di chuyển an tâm và nhanh chóng.',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Trung chuyển miễn phí từ bến xe miền Đông mới',
                'slug' => 'trung-chuyen-mien-phi-ben-xe-mien-dong-moi',
                'summary' => 'Thông tin các điểm đón và khung giờ trung chuyển miễn phí dành cho hành khách.',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Văn phòng FUTA mới chính thức đi vào hoạt động',
                'slug' => 'van-phong-futa-moi-chinh-thuc-hoat-dong',
                'summary' => 'Mở rộng mạng lưới phục vụ và nâng cao trải nghiệm mua vé cho khách hàng.',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Hướng dẫn đặt vé xe trực tuyến nhanh chóng và an toàn',
                'slug' => 'huong-dan-dat-ve-xe-truc-tuyen',
                'summary' => 'Các bước tìm chuyến, chọn ghế và thanh toán vé xe ngay tại nhà.',
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'FUTA Bus Lines nâng cấp chất lượng phục vụ hành khách',
                'slug' => 'futa-bus-lines-nang-cap-chat-luong-phuc-vu',
                'summary' => 'Những cải tiến mới giúp mỗi chuyến đi thoải mái, đúng giờ và thuận tiện hơn.',
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articles as $index => $article) {
            NewsArticle::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    ...$article,
                    'image' => null,
                    'status' => 'published',
                    'is_featured' => $index < 3,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
