<?php

namespace Tests\Feature\Core;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DataPaginationTest extends TestCase
{
    public function test_empty_tables_show_zero_records_and_disable_both_navigation_directions(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10, 1);
        $html = Blade::render('<x-core::data-pagination :paginator="$paginator" />', compact('paginator'));
        $this->assertStringContainsString(__('core::pagination.showing', ['from' => 0, 'to' => 0, 'total' => 0]), $html);
        $this->assertSame(2, substr_count($html, 'aria-disabled="true"'));
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
    }

    public function test_page_links_preserve_filters_and_out_of_range_pages_have_bounded_markup(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 10), 1000, 10, 50, ['path' => '/test']);
        $paginator->appends(['status' => 'active']);
        $html = Blade::render('<x-core::data-pagination :paginator="$paginator" />', compact('paginator'));
        $this->assertStringContainsString('status=active', $html);
        $this->assertStringContainsString('page=49', $html);
        $this->assertStringContainsString('page=51', $html);
        $this->assertStringNotContainsString('page=2&amp;', $html);
        $paginator = new LengthAwarePaginator([], 10, 10, 2000000);
        $html = Blade::render('<x-core::data-pagination :paginator="$paginator" />', compact('paginator'));
        $this->assertLessThan(5000, strlen($html));
    }
}
