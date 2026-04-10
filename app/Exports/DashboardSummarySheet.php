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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DashboardSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        protected array $data,
        protected string $period
    ) {}

    public function title(): string
    {
        return 'Tổng quan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 25,
            'C' => 25,
            'D' => 25,
        ];
    }

    public function array(): array
    {
        $periodLabel = match($this->period) {
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_year'  => 'Năm nay',
            default      => 'Tất cả thời gian',
        };

        $totalIncome  = $this->data['totalIncome'] ?? 0;
        $totalExpense = $this->data['totalExpense'] ?? 0;
        $balance      = $this->data['balance'] ?? 0;

        $rows = [
            ['BÁO CÁO TÀI CHÍNH - MONEXA', '', '', ''],
            ['Kỳ báo cáo: ' . $periodLabel, '', 'Xuất ngày: ' . now()->format('d/m/Y H:i'), ''],
            ['', '', '', ''],

            // Tổng quan
            ['TỔNG QUAN', '', '', ''],
            ['Chỉ số', 'Giá trị', '', ''],
            ['Tổng thu nhập', number_format($totalIncome, 0, ',', '.') . ' VND', '', ''],
            ['Tổng chi tiêu', number_format($totalExpense, 0, ',', '.') . ' VND', '', ''],
            ['Số dư', number_format($balance, 0, ',', '.') . ' VND', '', ''],
            ['Tổng giao dịch', ($this->data['totalTransactions'] ?? 0) . ' giao dịch', '', ''],
            ['', '', '', ''],

            // Top danh mục
            ['TOP DANH MỤC CHI TIÊU', '', '', ''],
            ['Danh mục', 'Số tiền', 'Tỷ lệ', ''],
        ];

        foreach ($this->data['topCategories'] ?? [] as $cat) {
            $percent = $totalExpense > 0
                ? round(($cat['total_expense'] / $totalExpense) * 100, 1)
                : 0;
            $rows[] = [
                $cat['ten_danh_muc'],
                number_format($cat['total_expense'], 0, ',', '.') . ' VND',
                $percent . '%',
                '',
            ];
        }

        $rows[] = ['', '', '', ''];

        // Ngân sách
        $rows[] = ['TỔNG QUAN NGÂN SÁCH', '', '', ''];
        $rows[] = ['Tên ngân sách', 'Số dư', 'Đã dùng', ''];

        foreach ($this->data['activeWallets'] ?? [] as $wallet) {
            $rows[] = [
                $wallet['ten_ngan_sach'],
                number_format($wallet['so_du'], 0, ',', '.') . ' VND',
                round($wallet['spent_percentage'], 1) . '%',
                '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A90E2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '64748B']],
            ],
            4 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            ],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A90E2']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge title
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('C2:D2');

                // Style các section header
                foreach ([4, 11] as $sectionRow) {
                    $sheet->mergeCells("A{$sectionRow}:D{$sectionRow}");
                }

                // Tìm và style các dòng TỔNG QUAN NGÂN SÁCH
                $highestRow = $sheet->getHighestRow();
                for ($i = 1; $i <= $highestRow; $i++) {
                    $val = $sheet->getCell("A{$i}")->getValue();

                    if (in_array($val, ['TỔNG QUAN NGÂN SÁCH', 'TOP DANH MỤC CHI TIÊU'])) {
                        $sheet->mergeCells("A{$i}:D{$i}");
                        $sheet->getStyle("A{$i}:D{$i}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
                        ]);
                    }

                    // Style header row của bảng
                    if (in_array($val, ['Chỉ số', 'Danh mục', 'Tên ngân sách'])) {
                        $sheet->getStyle("A{$i}:D{$i}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A90E2']],
                        ]);
                    }
                }

                // Border toàn bộ
                $sheet->getStyle('A1:D' . $highestRow)->applyFromArray([
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