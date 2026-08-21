<?php

declare(strict_types=1);

namespace Database\Seeders;

use FuteBus\Core\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'title' => 'Giảm 50% phí đổi trả vé online',
                'slug' => 'giam-50-phi-doi-tra-ve-online',
                'description' => 'Đổi vé nhanh gọn, nhận hoàn tiền trong 24h khi đặt qua ứng dụng FUTA.',
                'image' => 'promotions/promo-doi-tra-ve.jpg',
                'link' => '#',
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 1,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(30),
            ],
            [
                'title' => 'Ưu đãi Visa Futamember - Giảm đến 150K',
                'slug' => 'uu-dai-visa-futamember-giam-den-150k',
                'description' => 'Áp dụng cho khách hàng thanh toán bằng thẻ Visa Futamember trên mọi tuyến.',
                'image' => 'promotions/promo-visa-futa.jpg',
                'link' => '#',
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 2,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(45),
            ],
            [
                'title' => 'Đặt vé trên VNPAY - Nhận ngay 150K',
                'slug' => 'dat-ve-tren-vnpay-nhan-ngay-150k',
                'description' => 'Tải VNPAY, đặt vé xe Phương Trang, nhận ưu đãi lên đến 150.000đ.',
                'image' => 'promotions/promo-vnpay.jpg',
                'link' => '#',
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 3,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(60),
            ],
            [
                'title' => 'Combofruits - Giảm 10% khi mua combo trái cây',
                'slug' => 'combofruits-giam-10-mua-combo-trai-cay',
                'description' => 'Mua combo trái cây tươi tại bến xe FUTA, nhận ngay ưu đãi 10%.',
                'image' => 'promotions/promo-combofruits.jpg',
                'link' => '#',
                'status' => 'active',
                'is_featured' => false,
                'sort_order' => 4,
                'start_date' => now(),
                'end_date' => now()->addDays(20),
            ],
            [
                'title' => 'Tích điểm FUTA Points - Đổi quà hấp dẫn',
                'slug' => 'tich-diem-futa-points-doi-qua-hap-dan',
                'description' => 'Mỗi chuyến đi giúp bạn tích lũy điểm thưởng, đổi quà miễn phí.',
                'image' => 'promotions/promo-futa-points.jpg',
                'link' => '#',
                'status' => 'active',
                'is_featured' => false,
                'sort_order' => 5,
                'start_date' => now(),
                'end_date' => now()->addDays(90),
            ],
        ];

        foreach ($promotions as $promo) {
            Promotion::updateOrCreate(
                ['slug' => $promo['slug']],
                $promo
            );
        }
    }
}
