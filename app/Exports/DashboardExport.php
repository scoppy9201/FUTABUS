<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExport implements WithMultipleSheets
{
    public function __construct(
        protected array $data,
        protected string $period
    ) {}

    public function sheets(): array
    {
        return [
            new DashboardSummarySheet($this->data, $this->period),
            new DashboardTransactionsSheet($this->data),
            new DashboardMonthlySheet($this->data),      
            new DashboardCategorySheet($this->data),     
        ];
    }
}