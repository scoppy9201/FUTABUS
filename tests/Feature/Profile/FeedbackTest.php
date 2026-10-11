<?php

namespace Tests\Feature\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesFeedbackFixtures;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use CreatesFeedbackFixtures, RefreshDatabase;

    public function test_guests_cannot_access_or_submit_private_feedback(): void
    {
        $this->get(route('profile.feedback.index'))->assertRedirect(route('login'));
        $this->post(route('profile.feedback.store'), $this->validFeedback())
            ->assertRedirect(route('login'));
        $this->get(route('profile.feedback.show', 1))->assertRedirect(route('login'));
        $this->get(route('profile.feedback.attachment', ['report' => 1, 'attachment' => 1]))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('customer_feedback_reports', 0);
    }

    public function test_feedback_is_persisted_separately_with_server_controlled_identity_and_initial_status(): void
    {
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $bookingId = $this->createBooking($owner);
        $bookingBefore = (array) DB::table('bookings')->find($bookingId);

        $this->actingAs($owner)->post(route('profile.feedback.store'), $this->validFeedback([
            'booking_id'    => $bookingId,
            'category'      => 'booking_payment',
            'urgency'       => 'urgent',
            'user_id'       => $other->id,
            'status'        => 'resolved',
            'response'      => 'Forged response',
            'resolved_at'   => now()->toDateTimeString(),
            'booking_code'  => 'FORGED-CODE',
            'allow_contact' => false,
        ]))->assertRedirect(route('profile.feedback.index'))
            ->assertSessionHas('status');

        $report = DB::table('customer_feedback_reports')->first();
        $this->assertSame($owner->id, $report->user_id);
        $this->assertSame($bookingId, $report->booking_id);
        $this->assertSame($bookingBefore['booking_code'], $report->booking_code);
        $this->assertSame('booking_payment', $report->category);
        $this->assertSame('urgent', $report->urgency);
        $this->assertSame('received', $report->status);
        $this->assertNull($report->response);
        $this->assertNull($report->reviewing_at);
        $this->assertNull($report->resolved_at);
        $this->assertTrue((bool) $report->truth_confirm);
        $this->assertFalse((bool) $report->allow_contact);
        $this->assertSame('Customer Example', $report->contact_name);
        $this->assertSame('+84901234567', $report->contact_phone);
        $this->assertSame('customer@example.test', $report->contact_email);
        $this->assertSame($bookingBefore, (array) DB::table('bookings')->find($bookingId));
        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertDatabaseCount('booking_reviews', 0);
        $this->assertDatabaseCount('payments', 0);

        $this->get(route('profile.feedback.show', $report->id))
            ->assertOk()->assertSeeText($report->subject)->assertSeeText($report->message);
    }

    #[DataProvider('supportedFeedbackOptions')]
    public function test_all_supported_categories_and_urgencies_can_be_submitted(array $options): void
    {
        $this->actingAs($this->createTestUser())
            ->post(route('profile.feedback.store'), $this->validFeedback($options))
            ->assertRedirect(route('profile.feedback.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customer_feedback_reports', $options + ['status' => 'received']);
    }

    public static function supportedFeedbackOptions(): array
    {
        return [
            'trip quality'        => [['category' => 'trip_service']],
            'staff'               => [['category' => 'staff']],
            'schedule'            => [['category' => 'schedule']],
            'booking and payment' => [['category' => 'booking_payment']],
            'lost belongings'     => [['category' => 'lost_property']],
            'pickup'              => [['category' => 'pickup']],
            'other'               => [['category' => 'other']],
            'suggestion'          => [['urgency' => 'suggestion']],
            'complaint'           => [['urgency' => 'complaint']],
            'urgent'              => [['urgency' => 'urgent']],
        ];
    }

    #[DataProvider('invalidFeedback')]
    public function test_invalid_feedback_is_rejected_without_persisting_reports(array $attributes, string $field): void
    {
        $this->actingAs($this->createTestUser())
            ->from(route('profile.feedback.index'))
            ->post(route('profile.feedback.store'), $this->validFeedback($attributes))
            ->assertRedirect(route('profile.feedback.index'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('customer_feedback_reports', 0);
        $this->assertDatabaseCount('customer_feedback_attachments', 0);
    }

    public static function invalidFeedback(): array
    {
        return [
            'missing category'        => [['category' => ''], 'category'],
            'unknown category'        => [['category' => 'admin'], 'category'],
            'invalid urgency'         => [['urgency' => 'critical'], 'urgency'],
            'missing urgency'         => [['urgency' => ''], 'urgency'],
            'short subject'           => [['subject' => 'four'], 'subject'],
            'long subject'            => [['subject' => str_repeat('x', 151)], 'subject'],
            'short message'           => [['message' => 'Too short'], 'message'],
            'long message'            => [['message' => str_repeat('x', 3001)], 'message'],
            'non text message'        => [['message' => ['invalid']], 'message'],
            'missing contact name'    => [['contact_name' => ''], 'contact_name'],
            'long contact name'       => [['contact_name' => str_repeat('x', 121)], 'contact_name'],
            'missing phone'           => [['contact_phone' => ''], 'contact_phone'],
            'malformed phone'         => [['contact_phone' => 'abc'], 'contact_phone'],
            'non text phone'          => [['contact_phone' => ['0901234567']], 'contact_phone'],
            'invalid email'           => [['contact_email' => 'invalid'], 'contact_email'],
            'unconfirmed truth'       => [['truth_confirm' => false], 'truth_confirm'],
            'invalid contact consent' => [['allow_contact' => 'maybe'], 'allow_contact'],
            'non numeric booking'     => [['booking_id' => 'someone'], 'booking_id'],
            'unknown booking'         => [['booking_id' => 99999], 'booking_id'],
        ];
    }

    public function test_reports_allow_optional_booking_email_and_contact_consent(): void
    {
        $payload = $this->validFeedback(['booking_id' => null, 'contact_email' => null]);
        unset($payload['allow_contact']);

        $this->actingAs($this->createTestUser())->post(route('profile.feedback.store'), $payload)
            ->assertRedirect(route('profile.feedback.index'))->assertSessionHasNoErrors();

        $report = DB::table('customer_feedback_reports')->first();
        $this->assertNull($report->booking_id);
        $this->assertNull($report->booking_code);
        $this->assertNull($report->contact_email);
        $this->assertFalse((bool) $report->allow_contact);
    }

    #[DataProvider('phoneFormats')]
    public function test_contact_phone_and_text_are_normalized_before_the_report_is_saved(string $phone): void
    {
        $this->actingAs($this->createTestUser())->post(route('profile.feedback.store'), $this->validFeedback([
            'contact_phone' => $phone,
            'contact_name'  => '  Customer Example  ',
            'contact_email' => '  customer@example.test  ',
            'subject'       => '  An issue during the trip  ',
            'message'       => '  The pickup time changed and I would like assistance with this issue.  ',
        ]))->assertRedirect(route('profile.feedback.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customer_feedback_reports', [
            'contact_phone' => '+84901234567',
            'contact_name'  => 'Customer Example',
            'contact_email' => 'customer@example.test',
            'subject'       => 'An issue during the trip',
            'message'       => 'The pickup time changed and I would like assistance with this issue.',
        ]);
    }

    public static function phoneFormats(): array
    {
        return [
            'local formatted phone'     => ['090 123 4567'],
            'country code without plus' => ['84901234567'],
            'country code with plus'    => ['+84 (90) 123-4567'],
        ];
    }

    public function test_customer_can_link_legacy_booking_through_the_owned_customer_record(): void
    {
        $owner = $this->createTestUser();
        $bookingId = $this->createBooking($owner, ['user_id' => null]);

        $this->actingAs($owner)->post(route('profile.feedback.store'), $this->validFeedback([
            'booking_id' => $bookingId,
        ]))->assertRedirect(route('profile.feedback.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customer_feedback_reports', ['user_id' => $owner->id, 'booking_id' => $bookingId]);
    }

    public function test_customer_cannot_link_another_account_booking_even_with_an_owned_customer_record(): void
    {
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $foreignBooking = $this->createBooking($other);
        $mismatchedBooking = $this->createBooking($owner, ['user_id' => $other->id]);
        $this->actingAs($owner);

        foreach ([$foreignBooking, $mismatchedBooking] as $bookingId) {
            $this->post(route('profile.feedback.store'), $this->validFeedback(['booking_id' => $bookingId]))
                ->assertSessionHasErrors('booking_id');
        }

        $this->assertDatabaseCount('customer_feedback_reports', 0);
    }

    public function test_booking_selector_only_offers_bookings_owned_by_the_signed_in_customer(): void
    {
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $ownBooking = $this->createBooking($owner);
        $foreignBooking = $this->createBooking($other);
        $legacyBooking = $this->createBooking($owner, ['user_id' => null]);

        $this->actingAs($owner)->get(route('profile.feedback.index'))
            ->assertOk()
            ->assertSeeText(DB::table('bookings')->find($ownBooking)->booking_code)
            ->assertSeeText(DB::table('bookings')->find($legacyBooking)->booking_code)
            ->assertDontSeeText(DB::table('bookings')->find($foreignBooking)->booking_code);
    }

    public function test_validation_errors_preserve_the_customer_form_input_for_correction(): void
    {
        $payload = $this->validFeedback(['subject' => 'four']);
        $this->actingAs($this->createTestUser())
            ->from(route('profile.feedback.index'))
            ->post(route('profile.feedback.store'), $payload)
            ->assertRedirect(route('profile.feedback.index'))->assertSessionHasErrors('subject');

        $error = session('errors')->first('subject');
        $this->get(route('profile.feedback.index'))->assertOk()
            ->assertSee($error)
            ->assertSee('data-open-on-load', false)
            ->assertSee('value="four"', false)
            ->assertSee($payload['message'])
            ->assertSee($payload['contact_name']);
        $this->assertDatabaseCount('customer_feedback_reports', 0);
    }

    public function test_invalid_list_filter_does_not_reopen_the_create_feedback_dialog(): void
    {
        $this->actingAs($this->createTestUser())
            ->from(route('profile.feedback.index'))
            ->get(route('profile.feedback.index', ['status' => 'unknown']))
            ->assertRedirect(route('profile.feedback.index'))->assertSessionHasErrors('status');

        $this->get(route('profile.feedback.index'))->assertOk()
            ->assertSee('data-feedback-create', false)
            ->assertDontSee('data-open-on-load', false)
            ->assertDontSeeText(__('Profile::feedback.reselect_images'));
    }

    public function test_malformed_array_values_are_rejected_and_do_not_break_the_reopened_form(): void
    {
        $fields = ['category', 'urgency', 'booking_id', 'subject', 'message', 'contact_name', 'contact_phone', 'contact_email'];
        $payload = $this->validFeedback(array_fill_keys($fields, ['unexpected' => '<script>malformed</script>']));

        $this->actingAs($this->createTestUser())
            ->from(route('profile.feedback.index'))
            ->post(route('profile.feedback.store'), $payload)
            ->assertRedirect(route('profile.feedback.index'))->assertSessionHasErrors($fields);

        $phoneError = session('errors')->first('contact_phone');
        $this->get(route('profile.feedback.index'))->assertOk()
            ->assertSee($phoneError)
            ->assertDontSee('<script>malformed</script>', false);
        $this->assertDatabaseCount('customer_feedback_reports', 0);
    }

    public function test_list_and_details_are_private_and_filters_only_return_owned_matching_reports(): void
    {
        $owner = $this->createTestUser();
        $other = $this->createTestUser();
        $matchingId = $this->createReport($owner, ['subject' => 'Delay at pickup', 'status' => 'reviewing']);
        $this->createReport($owner, ['subject' => 'Delay already fixed', 'status' => 'resolved']);
        $this->createReport($owner, ['subject' => 'Different problem', 'status' => 'reviewing']);
        $foreignId = $this->createReport($other, ['subject' => 'Delay private to other account', 'status' => 'reviewing']);

        $this->actingAs($owner)->get(route('profile.feedback.index', ['status' => 'reviewing', 'search' => 'Delay']))
            ->assertOk()->assertSeeText('Delay at pickup')
            ->assertDontSeeText('Delay already fixed')->assertDontSeeText('Different problem')
            ->assertDontSeeText('Delay private to other account');
        $this->get(route('profile.feedback.show', $matchingId))->assertOk()->assertSeeText('Delay at pickup');
        $this->get(route('profile.feedback.show', $foreignId))->assertNotFound();
        $this->get(route('profile.feedback.show', ['report' => $foreignId, 'modal' => 1]))->assertNotFound();
        $this->get(route('profile.feedback.show', 99999))->assertNotFound();
        $this->get(route('profile.feedback.index', ['status' => 'admin']))->assertSessionHasErrors('status');
    }

    public function test_report_and_staff_response_content_are_escaped_in_customer_views(): void
    {
        $owner = $this->createTestUser();
        $subject = '<script>alert("subject")</script>';
        $message = '<img src=x onerror="alert(1)"> Customer issue details';
        $response = '<script>alert("response")</script>';
        $reportId = $this->createReport($owner, compact('subject', 'message', 'response') + ['status' => 'resolved']);

        $this->actingAs($owner)->get(route('profile.feedback.show', $reportId))->assertOk()
            ->assertSee($subject)->assertSee($message)->assertSee($response)
            ->assertDontSee($subject, false)->assertDontSee($message, false)->assertDontSee($response, false);
        $this->get(route('profile.feedback.index'))->assertOk()->assertSee($subject)->assertDontSee($subject, false);
    }

    public function test_repeated_submissions_are_rate_limited(): void
    {
        $this->actingAs($this->createTestUser());

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('profile.feedback.store'), $this->validFeedback())
                ->assertRedirect(route('profile.feedback.index'));
        }

        $this->post(route('profile.feedback.store'), $this->validFeedback())->assertStatus(429);
        $this->assertDatabaseCount('customer_feedback_reports', 5);
    }
}
