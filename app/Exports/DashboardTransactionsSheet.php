<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DashboardTransactionsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected array $data) {}

    public function title(): string
    {
        return 'Chi tiết giao dịch';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 15,
            'D' => 25,
            'E' => 20,
            'F' => 30,
        ];
    }

    public function array(): array
    {
        $rows = [
            ['CHI TIẾT GIAO DỊCH', '', '', '', '', ''],
            ['STT', 'Ngày', 'Loại', 'Danh mục', 'Số tiền (VND)', 'Ghi chú'],
        ];

        foreach ($this->data['recentTransactions'] ?? [] as $index => $item) {
            $rows[] = [
                $index + 1,
                $item['ngay_giao_dich'] ?? '--',
                $item['loai_giao_dich'] === 'THU' ? 'Thu nhập' : 'Chi tiêu',
                $item['category']['ten_danh_muc'] ?? 'Không rõ',
                number_format($item['so_tien'], 0, ',', '.'),
                $item['ghi_chu'] ?? '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A90E2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:F1');

                // Zebra stripe + color cho loại giao dịch
                for ($i = 3; $i <= $highestRow; $i++) {
                    $type = $sheet->getCell("C{$i}")->getValue();
                    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';

                    $sheet->getStyle("A{$i}:F{$i}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    ]);

                    if ($type === 'Thu nhập') {
                        $sheet->getStyle("E{$i}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '10B981']],
                        ]);
                    } elseif ($type === 'Chi tiêu') {
                        $sheet->getStyle("E{$i}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'EF4444']],
                        ]);
                    }
                }

                // Border
                $sheet->getStyle('A1:F' . $highestRow)->applyFromArray([
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