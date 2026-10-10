<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_guide_link_opens_the_public_guide_page(): void
    {
        app()->setLocale('vi');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('booking-guide').'"', false)
            ->assertSee('href="https://apps.apple.com/vn/app/futa/id1126633800"', false)
            ->assertSee('href="https://play.google.com/store/apps/details?id=client.facecar.com"', false);

        $this->get('/huong-dan-dat-ve-tren-web')
            ->assertOk()
            ->assertSee('Hướng dẫn mua vé xe trên website')
            ->assertSee('images/booking-guide/futa-logo.png', false)
            ->assertSee('images/booking-guide/futa-app-qr.png', false)
            ->assertSee('href="https://apps.apple.com/vn/app/futa/id1126633800"', false)
            ->assertSee('href="https://play.google.com/store/apps/details?id=client.facecar.com"', false)
            ->assertSee('1900 6067')
            ->assertSee('Bước 1: Những trải nghiệm nổi bật')
            ->assertSeeInOrder([
                'flexible-schedule.png',
                'seat-selection.png',
                'skip-queues.png',
                'partner-offers.png',
                'member-gifts.png',
                'customer-feedback.png',
            ], false)
            ->assertSee('Bước 2: Những bước để giúp khách hàng trải nghiệm mua vé nhanh')
            ->assertSee('Bước 2: Chọn thông tin hành trình')
            ->assertSeeInOrder([
                'booking-steps-timeline.png',
                'website-devices.png',
                'booking-steps-timeline-active-02.png',
                'journey-selection-form.png',
                'trip-search-results.png',
                'booking-steps-timeline-active-03.png',
                'seat-and-passenger-selection.png',
                'booking-steps-timeline-active-04.png',
                'payment-method-selection.png',
                'booking-steps-timeline-active-05.png',
                'ticket-booking-success.png',
            ], false)
            ->assertSeeInOrder([
                'Chọn điểm khởi hành',
                'Chọn điểm đến',
                'Chọn ngày đi',
                'Chọn ngày về',
            ])
            ->assertSeeInOrder([
                'Chọn giờ đi',
                'Chọn loại xe',
                'Chọn điểm đón',
                'Chọn chuyến đi',
                'Chọn nhanh số ghế',
                'Bước 3: Chọn ghế, điểm đón trả, thông tin hành khách',
                'Bước 4: Chọn phương thức thanh toán',
                'Bước 5: Mua vé thành công',
                'Bước 3: Vé xe sẽ được gửi về Email. Quý khách vui lòng kiểm tra Email để nhận vé',
            ])
            ->assertSeeInOrder([
                'Bước 3: Vé xe sẽ được gửi về Email. Quý khách vui lòng kiểm tra Email để nhận vé',
                'ticket-email-preview.png',
            ], false);
    }

    public function test_guide_page_has_english_copy(): void
    {
        $this->get('/huong-dan-dat-ve-tren-web?lang=en')
            ->assertOk()
            ->assertSee('How to book bus tickets on');
    }
}
