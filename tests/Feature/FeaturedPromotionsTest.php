<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedPromotionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_six_image_only_promotions_in_the_requested_order(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'images/promotions/dong-thap-hospital-shuttle.png',
                'images/promotions/fraud-warning.png',
                'images/promotions/le-van-luong-office-opening.png',
                'images/promotions/booking-steps.png',
                'images/promotions/early-booking-app-guide.png',
                'images/promotions/mien-dong-moi-western-routes.png',
            ], false)
            ->assertSee('href="'.route('promotion-article', 'nguoi-dan-dong-thap-di-kham-benh-cong-ty-phuong-trang-lo-tron-tung-chang-duong').'"', false);
    }

    public function test_first_promotion_opens_the_supplied_article(): void
    {
        $this->get(route('promotion-article', 'nguoi-dan-dong-thap-di-kham-benh-cong-ty-phuong-trang-lo-tron-tung-chang-duong'))
            ->assertOk()
            ->assertSee('Ngày đăng: 14:55 14/07/2026')
            ->assertSee('DỊCH VỤ TRUNG CHUYỂN MIỄN PHÍ ĐẾN 21 BỆNH VIỆN LỚN TẠI TP.HCM')
            ->assertSee('Bệnh viện Đại học Y Dược')
            ->assertSee('Bệnh viện Nhân Dân Gia Định')
            ->assertSee('images/promotions/dong-thap-hospital-shuttle.png', false);

        $this->get(route('promotion-article', 'khong-ton-tai'))->assertNotFound();
    }

    public function test_new_promotion_articles_show_their_dates_and_supplied_content(): void
    {
        $articles = [
            ['canh-bao-tinh-trang-lua-dao-ve-xe-dip-le-quoc-khanh-02-09-2026', '15:13 17/08/2026', '4 DẤU HIỆU LỪA ĐẢO CẦN NÉ NGAY'],
            ['van-phong-phuong-trang-486-486a-le-van-luong-chinh-thuc-khai-truong', '09:22 19/08/2026', 'Thời gian hoạt động: 06h00 - 24h00'],
            ['dat-ve-xe-phuong-trang-nhanh-chong-tien-loi', '16:35 27/08/2026', 'Bước 4: Thanh toán'],
            ['chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa', '10:02 04/09/2026', 'Vé điện tử đầy tiện lợi'],
            ['ve-mien-tay-tu-cua-ngo-phia-dong-chang-can-di-xa-cong-ty-phuong-trang-dua-quy-khach-ve-nha', '10:33 04/09/2026', 'Bến xe Miền Đông mới ⇔ Bến xe Rạch Giá'],
        ];

        foreach ($articles as [$slug, $date, $content]) {
            $this->get(route('promotion-article', $slug))
                ->assertOk()
                ->assertSee('Ngày đăng: '.$date)
                ->assertSee($content);
        }

        $this->get(route('promotion-article', 'chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa'))
            ->assertOk()
            ->assertSeeInOrder([
                'images/promotions/app-booking-step-01.png',
                'images/promotions/app-booking-step-02.png',
                'images/promotions/app-booking-step-03.png',
                'images/promotions/app-booking-step-04.png',
                'images/promotions/app-booking-step-05.png',
            ], false);
    }
}
