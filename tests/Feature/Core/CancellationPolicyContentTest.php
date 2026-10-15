<?php

namespace Tests\Feature\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationPolicyContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_surfaces_use_the_same_cancellation_terms_in_both_languages(): void
    {
        foreach (['vi', 'en'] as $locale) {
            $items = trans('core::cancellation-policy.items', [], $locale);
            $this->assertIsArray($items);
            $this->assertSame($items, trans('core::refund.changes.items', [], $locale));
            $this->assertSame($items, trans('core::trip-search.cancellation_items', [], $locale));
            $articles = trans('core::service-conditions.articles', [], $locale);
            $this->assertSame($items, $articles[1]['items']);
            $chapters = trans('core::terms.chapters', [], $locale);
            $details = [];
            array_walk_recursive($chapters, function ($value) use (&$details): void {
                $details[] = $value;
            });
            foreach ($items as $item) {
                $this->assertContains($item, $details);
            }
            $this->assertStringContainsString('10%', $items[0]);
            $this->assertStringContainsString('30%', $items[1]);
        }
    }

    public function test_public_policy_pages_show_exact_boundaries_and_pending_refund_explanation(): void
    {
        $items = trans('core::cancellation-policy.items', [], 'vi');
        foreach (['refund', 'service-conditions', 'terms'] as $page) {
            $response = $this->get(route($page))->assertOk();
            foreach ($items as $item) {
                $response->assertSee($item);
            }
            $response->assertDontSee('chậm nhất 24 giờ trước');
        }
    }
}
