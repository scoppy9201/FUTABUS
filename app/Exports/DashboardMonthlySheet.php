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

class DashboardMonthlySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithCharts, WithEvents
{
    public function __construct(protected array $data) {}

    public function title(): string { return 'Thu chi tháng'; }

    public function columnWidths(): array
    {
        return ['A' => 15, 'B' => 20, 'C' => 20];
    }

    public function array(): array
    {
        $rows = [
            ['THU CHI 6 THÁNG GẦN NHẤT', '', ''],
            ['Tháng', 'Thu nhập (VND)', 'Chi tiêu (VND)'],
        ];

        foreach ($this->data['monthlyData'] ?? [] as $item) {
            $rows[] = [
                'Tháng ' . $item['month'],
                $item['income'],
                $item['expense'],
            ];
        }

        return $rows;
    }

    public function charts(): array
    {
        $count = count($this->data['monthlyData'] ?? []);
        if ($count === 0) return [];

        $seriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Thu nhập']),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Chi tiêu']),
        ];

        $labels = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            'Thu chi tháng!$A$3:$A$' . ($count + 2),
            null, $count
        );

        $income = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            'Thu chi tháng!$B$3:$B$' . ($count + 2),
            null, $count
        );

        $expense = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            'Thu chi tháng!$C$3:$C$' . ($count + 2),
            null, $count
        );

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, 1),
            $seriesLabels,
            [$labels],
            [$income, $expense]
        );

        $chart = new Chart(
            'chart_monthly',
            new Title('Biểu đồ thu chi 6 tháng'),
            new Legend(Legend::POSITION_BOTTOM),
            new PlotArea(null, [$series])
        );

        $chart->setTopLeftPosition('E2');
        $chart->setBottomRightPosition('N20');

        return [$chart];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
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