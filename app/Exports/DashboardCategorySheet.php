<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class DashboardCategorySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithCharts, WithEvents
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Danh mục chi tiêu'; }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 20, 'C' => 15];
    }

    public function array(): array
    {
        $rows = [
            ['PHÂN BỐ CHI TIÊU THEO DANH MỤC', '', ''],
            ['Danh mục', 'Số tiền (VND)', 'Tỷ lệ %'],
        ];

        $total = collect($this->data['categoryExpenses'] ?? [])->sum('total');

        foreach ($this->data['categoryExpenses'] ?? [] as $item) {
            $percent = $total > 0 ? round(($item['total'] / $total) * 100, 1) : 0;
            $rows[] = [
                $item['name'],
                $item['total'],
                $percent . '%',
            ];
        }

        return $rows;
    }

    public function charts(): array
    {
        $count = count($this->data['categoryExpenses'] ?? []);
        if ($count === 0) return [];

        $labels = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            'Danh mục chi tiêu!$A$3:$A$' . ($count + 2),
            null, $count
        );

        $values = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            'Danh mục chi tiêu!$B$3:$B$' . ($count + 2),
            null, $count
        );

        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, 0),
            [null],
            [$labels],
            [$values]
        );

        $chart = new Chart(
            'chart_category',
            new Title('Phân bố chi tiêu theo danh mục'),
            new Legend(Legend::POSITION_RIGHT),
            new PlotArea(null, [$series])
        );

        $chart->setTopLeftPosition('E2');
        $chart->setBottomRightPosition('N22');

        return [$chart];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:C1');

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:C' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}