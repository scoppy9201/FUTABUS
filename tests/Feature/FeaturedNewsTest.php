<?php

namespace Tests\Feature;

use FuteBus\Core\Services\FeaturedNewsCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_five_complete_news_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cập nhật những thông tin mới từ Phương Trang')
            ->assertSeeInOrder([
                'images/news/da-lat-kim-long-limousine.png',
                'images/news/can-tho-kim-long-n29.png',
                'images/news/nga-bay-free-shuttle.png',
                'images/news/cashless-payment-eticket.png',
                'images/news/nga-nam-shuttle-routes.png',
                'images/news/phuoc-long-shuttle-routes.png',
                'images/promotions/mien-dong-moi-western-routes.png',
                'images/promotions/early-booking-app-guide.png',
                'images/news/nha-trang-cam-ranh-phan-rang-bus.png',
                'images/news/hospital-office-national-day-hours.png',
                'images/news/electric-bus-route-141.png',
                'images/promotions/booking-steps.png',
                'images/news/electric-bus-route-171-frequency.png',
                'images/news/ngo-gia-tu-phan-rang-office.png',
                'images/promotions/le-van-luong-office-opening.png',
            ], false)
            ->assertSee('aria-label="Trang 5"', false)
            ->assertSee('aria-label="Trang 2"', false)
            ->assertSee('aria-label="Trang 3"', false)
            ->assertSee('aria-label="Trang 4"', false)
            ->assertSee('PHƯƠNG...', false)
            ->assertSee('href="'.route('promotion-article', 'trai-nghiem-xe-sang-cho-hanh-trinh-ket-noi-da-lat').'"', false);
    }

    public function test_new_articles_open_with_their_content_and_images(): void
    {
        $articles = [
            ['trai-nghiem-xe-sang-cho-hanh-trinh-ket-noi-da-lat', '11:05 16/09/2026', 'Đà Lạt ↔ Nha Trang', 'images/news/da-lat-kim-long-limousine.png'],
            ['ket-noi-can-tho-nang-tam-trai-nghiem-cung-dong-xe-kim-long-hien-dai', '14:27 14/09/2026', 'Cần Thơ ⇆ Hồng Ngự', 'images/news/can-tho-kim-long-n29.png'],
            ['trung-chuyen-mien-phi-8-huong-tai-van-phong-nga-bay', '10:43 30/09/2026', 'CÂY DƯƠNG (gồm 4 lộ trình)', 'images/news/nga-bay-free-shuttle.png'],
            ['thanh-toan-khong-tien-mat-len-xe-khong-can-ve-giay-cung-cong-ty-phuong-trang', '10:27 29/09/2026', 'VÉ ĐIỆN TỬ - KHÔNG CẦN VÉ GIẤY', 'images/news/cashless-payment-eticket.png'],
            ['luu-ngay-lo-trinh-trung-chuyen-tai-van-phong-nga-5-bac-lieu', '08:35 29/09/2026', '5. TRÀ BAN - VĨNH QUỚI', 'images/news/nga-nam-shuttle-routes.png'],
            ['thong-tin-hoat-dong-trung-chuyen-tai-vp-phuoc-long-bac-lieu', '10:10 28/09/2026', 'Lộ trình 2: VP Phước Long', 'images/news/phuoc-long-shuttle-routes.png'],
            ['nha-trang-cam-ranh-phan-rang-xe-buyt-them-tuyen-hanh-trinh-them-tien', '16:07 31/08/2026', 'TUYẾN 11.2: NHA TRANG - PHAN RANG - THÁP CHÀM', 'images/news/nha-trang-cam-ranh-phan-rang-bus.png'],
            ['cong-ty-phuong-trang-thong-bao-lich-nghi-quoc-khanh-02-09-2026-tai-4-van-phong-benh-vien-tp-hcm', '15:13 28/08/2026', 'VĂN PHÒNG BỆNH VIỆN UNG BƯỚU CƠ SỞ 1', 'images/news/hospital-office-national-day-hours.png'],
            ['chinh-thuc-nang-cap-xe-buyt-dien-tren-tuyen-141', '15:10 28/08/2026', '88 tuyến xe buýt', 'images/news/electric-bus-route-141.png'],
            ['tang-tan-suat-tuyen-buyt-171-len-68-chuyen-ngay', '08:58 24/08/2026', '68 chuyến/ngày', 'images/news/electric-bus-route-171-frequency.png'],
            ['van-phong-ngo-gia-tu-phan-rang-cap-nhat-dia-chi-moi', '10:29 19/08/2026', '287 Ngô Gia Tự, phường Phan Rang', 'images/news/ngo-gia-tu-phan-rang-office.png'],
        ];

        foreach ($articles as [$slug, $date, $body, $image]) {
            $this->get(route('promotion-article', $slug))
                ->assertOk()
                ->assertSee('Ngày đăng: '.$date)
                ->assertSee($body)
                ->assertSee($image, false);
        }

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Tin tức nổi bật')
            ->assertSee('Tiêu điểm')
            ->assertSee('Tất cả tin tức')
            ->assertSee('FUTA City Bus')
            ->assertDontSee('Tin tức mới')
            ->assertViewHas('featuredArticles', fn ($articles) => $articles->count() === 5)
            ->assertViewHas('spotlightArticles', fn ($articles) => $articles->count() === 3)
            ->assertSee('TRẢI NGHIỆM XE SANG CHO HÀNH TRÌNH KẾT NỐI ĐÀ LẠT')
            ->assertSee('images/news/nga-bay-free-shuttle.png', false);
    }

    public function test_third_news_page_reuses_the_existing_promotion_articles(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('promotion-article', 've-mien-tay-tu-cua-ngo-phia-dong-chang-can-di-xa-cong-ty-phuong-trang-dua-quy-khach-ve-nha').'"', false)
            ->assertSee('href="'.route('promotion-article', 'chu-dong-hanh-trinh-an-tam-giu-cho-cung-app-futa').'"', false);
    }

    public function test_fourth_news_page_reuses_the_booking_guide_article(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('promotion-article', 'dat-ve-xe-phuong-trang-nhanh-chong-tien-loi').'"', false);
    }

    public function test_fifth_news_page_reuses_the_le_van_luong_article(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('promotion-article', 'van-phong-phuong-trang-486-486a-le-van-luong-chinh-thuc-khai-truong').'"', false);
    }

    public function test_news_article_shows_four_other_related_articles(): void
    {
        $slug = 'van-phong-ngo-gia-tu-phan-rang-cap-nhat-dia-chi-moi';

        $this->get(route('promotion-article', $slug))
            ->assertOk()
            ->assertSee('Tin tức liên quan')
            ->assertSee('href="'.route('news').'"', false)
            ->assertSeeInOrder([
                'images/news/nga-bay-free-shuttle.png',
                'images/news/cashless-payment-eticket.png',
                'images/news/nga-nam-shuttle-routes.png',
                'images/news/phuoc-long-shuttle-routes.png',
            ], false)
            ->assertViewHas('relatedArticles', fn ($articles) => $articles->count() === 4
                && $articles->every(fn ($article) => $article['slug'] !== $slug));
    }

    public function test_all_news_uses_only_complete_articles_and_real_pagination(): void
    {
        $catalog = app(FeaturedNewsCatalog::class)->all();
        $this->assertCount(15, $catalog);
        foreach ($catalog as $article) {
            $this->assertFileExists(public_path($article['image']));
            $this->assertTrue(view()->exists($article['content_view']));
        }

        foreach ([1 => 4, 2 => 4, 3 => 4, 4 => 3] as $page => $expectedCount) {
            $this->get(route('news', ['page' => $page]))
                ->assertOk()
                ->assertViewHas('articles', fn ($articles) => $articles->total() === 15
                    && $articles->count() === $expectedCount
                    && $articles->lastPage() === 4);
        }

        $this->get(route('news', ['q' => '171']))
            ->assertOk()
            ->assertViewHas('articles', fn ($articles) => $articles->total() === 1)
            ->assertSee('TĂNG TẦN SUẤT TUYẾN BUÝT 171');

        $this->get(route('news', ['category' => 'futa-city-bus']))
            ->assertOk()
            ->assertViewHas('articles', fn ($articles) => $articles->total() === 3)
            ->assertSee('TĂNG TẦN SUẤT TUYẾN BUÝT 171');
    }

    public function test_homepage_carousels_advance_every_five_seconds(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('setInterval(', false)
            ->assertSee('}, 5000)', false)
            ->assertSee('goTo(page - 1)', false)
            ->assertSee('goTo(4)', false);
    }
}
