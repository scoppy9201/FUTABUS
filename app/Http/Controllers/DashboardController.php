<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budgets;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    /*  GET /api/v1/dashboard → Lấy toàn bộ data dashboard theo kỳ*/
    public function index(Request $request) : JsonResponse
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        $data = $this->getDashboardData($userId, $period);

        return response()->json($data);
    }

    /* Hàm nội bộ, tổng hợp toàn bộ data, dùng chung cho index/export/sendReport */
    protected function getDashboardData(int $userId, string $period): array
    {
        $query = Transaction::where('user_id', $userId);
        $this->applyPeriodFilter($query, $period);

        $totalIncome  = (clone $query)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $totalExpense = (clone $query)->where('loai_giao_dich', 'CHI')->sum('so_tien');
        $balance      = $totalIncome - $totalExpense;

        // So với kỳ trước 
        $prevQuery = Transaction::where('user_id', $userId);
        $this->applyPreviousPeriodFilter($prevQuery, $period);

        $prevIncome  = (clone $prevQuery)->where('loai_giao_dich', 'THU')->sum('so_tien');
        $prevExpense = (clone $prevQuery)->where('loai_giao_dich', 'CHI')->sum('so_tien');

        $incomeChange  = $prevIncome  > 0 ? round((($totalIncome  - $prevIncome)  / $prevIncome)  * 100, 1) : null;
        $expenseChange = $prevExpense > 0 ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1) : null;

        // Tỷ lệ tiết kiệm 
        $savingRate = $totalIncome > 0
            ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1)
            : 0;

        // Cảnh báo chi tiêu tăng đột biêns
        $spikingCategories = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as current_expense' => function ($q) use ($period) {
                $q->where('loai_giao_dich', 'CHI');
                $this->applyPeriodFilter($q, $period);
            }], 'so_tien')
            ->withSum(['transactions as prev_expense' => function ($q) use ($period) {
                $q->where('loai_giao_dich', 'CHI');
                $this->applyPreviousPeriodFilter($q, $period);
            }], 'so_tien')
            ->having('current_expense', '>', 0)
            ->get()
            ->filter(function ($cat) {
                $prev = (float) $cat->prev_expense;
                $curr = (float) $cat->current_expense;
                if ($prev <= 0) return false;
                return (($curr - $prev) / $prev) * 100 >= 50;
            })
            ->map(function ($cat) {
                $prev   = (float) $cat->prev_expense;
                $curr   = (float) $cat->current_expense;
                $change = round((($curr - $prev) / $prev) * 100, 1);
                return [
                    'id'             => $cat->id,
                    'ten_danh_muc'   => $cat->ten_danh_muc,
                    'bieu_tuong'     => $cat->bieu_tuong ?? 'money.png',
                    'current_expense'=> (float) $curr,
                    'prev_expense'   => (float) $prev,
                    'change_percent' => $change,
                ];
            })
            ->sortByDesc('change_percent')
            ->values();

        // Ngày chi nhiều nhất trong tuần
        $dayNames = ['', 'Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];

        $expenseByDay = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'CHI')
            ->tap(fn($q) => $this->applyPeriodFilter($q, $period))
            ->selectRaw('DAYOFWEEK(ngay_giao_dich) as dow, SUM(so_tien) as total')
            ->groupBy('dow')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'dow'       => (int) $row->dow,
                'ten_ngay'  => $dayNames[$row->dow] ?? 'Không rõ',
                'total'     => (float) $row->total,
            ])
            ->values();

        // Dự báo cuối tháng 
        $forecast = null;
        if ($period === 'this_month') {
            $daysElapsed = now()->day;
            $totalDays   = now()->daysInMonth;
            $forecast    = $daysElapsed > 0
                ? round(($totalExpense / $daysElapsed) * $totalDays, 0)
                : 0;
        }
        $totalTransactions = (clone $query)->count();
        $incomeCount       = (clone $query)->where('loai_giao_dich', 'THU')->count();
        $expenseCount      = (clone $query)->where('loai_giao_dich', 'CHI')->count();

        // Danh sách giao dịch gần nhất
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('ngay_giao_dich', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id'             => $transaction->id,
                    'so_tien'        => (float) $transaction->so_tien,
                    'loai_giao_dich' => $transaction->loai_giao_dich,
                    'ngay_giao_dich' => optional($transaction->ngay_giao_dich)->format('Y-m-d'),
                    'category'       => [
                        'ten_danh_muc' => $transaction->category->ten_danh_muc ?? 'Không rõ',
                        'bieu_tuong'   => $transaction->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        // Danh sách ngân sách đang cảnh báo (đã chiếm >= 50% ngân sách)
        $warningWallets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->get()
            ->filter(fn($wallet) => $wallet->spent_percentage >= 50)
            ->sortByDesc('spent_percentage')
            ->take(5)
            ->map(function ($wallet) {
                return [
                    'id'               => $wallet->id,
                    'ten_ngan_sach'    => $wallet->ten_ngan_sach,
                    'spent_percentage' => (float) $wallet->spent_percentage,
                ];
            })
            ->values();

        // Danh mục chi tiêu nhiều nhất và có sự tăng giảm mạnh so với kỳ trước
        $topCategories = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total_expense' => function ($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                $this->applyPeriodFilter($query, $period);
            }], 'so_tien')
            ->having('total_expense', '>', 0)
            ->orderByDesc('total_expense')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id'            => $category->id,
                    'ten_danh_muc'  => $category->ten_danh_muc,
                    'bieu_tuong'    => $category->bieu_tuong ?? 'money.png',
                    'total_expense' => (float) $category->total_expense,
                ];
            })
            ->values();

        // Danh sách ngân sách đang hoạt động, sắp xếp theo số dư gốc giảm dần
        $activeWallets = Budgets::where('user_id', $userId)
            ->where('trang_thai', true)
            ->with('category')
            ->orderByDesc('ngan_sach_goc')
            ->get()
            ->map(function ($wallet) {
                return [
                    'id'               => $wallet->id,
                    'ten_ngan_sach'    => $wallet->ten_ngan_sach,
                    'so_du'            => (float) $wallet->so_du,
                    'spent_percentage' => (float) $wallet->spent_percentage,
                    'category'         => [
                        'bieu_tuong' => $wallet->category->bieu_tuong ?? 'money.png',
                    ],
                ];
            })
            ->values();

        // Dữ liệu thu chi - thay đổi theo $period
        switch ($period) {
            case 'this_month':
            case 'last_month':
                $targetDate = $period === 'this_month' ? now() : now()->subMonth();
                $monthly = Transaction::where('user_id', $userId)
                    ->whereMonth('ngay_giao_dich', $targetDate->month)
                    ->whereYear('ngay_giao_dich', $targetDate->year)
                    ->selectRaw('DAY(ngay_giao_dich) as day, loai_giao_dich, SUM(so_tien) as total')
                    ->groupBy('day', 'loai_giao_dich')
                    ->get()
                    ->groupBy('day');

                $monthlyData = [];
                for ($i = 1; $i <= $targetDate->daysInMonth; $i++) {
                    $group = $monthly[$i] ?? collect();
                    $monthlyData[] = [
                        'label'   => 'Ngày ' . $i,
                        'income'  => (float) $group->where('loai_giao_dich', 'THU')->sum('total'),
                        'expense' => (float) $group->where('loai_giao_dich', 'CHI')->sum('total'),
                    ];
                }
                break;

            case 'this_year':
                $monthly = Transaction::where('user_id', $userId)
                    ->whereYear('ngay_giao_dich', now()->year)
                    ->selectRaw('MONTH(ngay_giao_dich) as month, loai_giao_dich, SUM(so_tien) as total')
                    ->groupBy('month', 'loai_giao_dich')
                    ->get()
                    ->groupBy('month');

                $monthlyData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $group = $monthly[$i] ?? collect();
                    $monthlyData[] = [
                        'label'   => 'Tháng ' . $i,
                        'income'  => (float) $group->where('loai_giao_dich', 'THU')->sum('total'),
                        'expense' => (float) $group->where('loai_giao_dich', 'CHI')->sum('total'),
                    ];
                }
                break;

            default: // 'all' - hiện 12 tháng gần nhất
                $monthly = Transaction::where('user_id', $userId)
                    ->where('ngay_giao_dich', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
                    ->selectRaw('YEAR(ngay_giao_dich) as year, MONTH(ngay_giao_dich) as month, loai_giao_dich, SUM(so_tien) as total')
                    ->groupBy('year', 'month', 'loai_giao_dich')
                    ->get()
                    ->groupBy(fn($t) => $t->year . '-' . $t->month);

                $monthlyData = [];
                for ($i = 11; $i >= 0; $i--) {
                    $date  = now()->subMonths($i);
                    $key   = $date->year . '-' . $date->month;
                    $group = $monthly[$key] ?? collect();
                    $monthlyData[] = [
                        'label'   => 'T' . $date->format('n/Y'),
                        'income'  => (float) $group->where('loai_giao_dich', 'THU')->sum('total'),
                        'expense' => (float) $group->where('loai_giao_dich', 'CHI')->sum('total'),
                    ];
                }
                break;
        }

        // Danh mục chi tiêu nhiều nhất trong kỳ, sắp xếp theo tổng chi giảm dần, chỉ lấy những danh mục có chi tiêu > 0
        $categoryExpenses = Category::where('user_id', $userId)
            ->where('loai_danh_muc', 'CHI')
            ->withSum(['transactions as total' => function ($query) use ($period) {
                $query->where('loai_giao_dich', 'CHI');
                $this->applyPeriodFilter($query, $period);
            }], 'so_tien')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn($category) => [
                'name'  => $category->ten_danh_muc,
                'total' => (float) $category->total,
            ])
            ->values();

        // Dữ liệu heatmap: tổng chi tiêu theo ngày trong 30 ngày gần nhất
        $heatmap = Transaction::where('user_id', $userId)
            ->where('loai_giao_dich', 'CHI')
            ->where('ngay_giao_dich', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('DATE(ngay_giao_dich) as ngay, SUM(so_tien) as total')
            ->groupBy('ngay')
            ->orderBy('ngay')
            ->get()
            ->keyBy('ngay')
            ->map(fn($row) => (float) $row->total);

        // Tạo mảng đủ 30 ngày kể cả ngày không có giao dịch
        $heatmapData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $heatmapData[] = [
                'date'  => $date,
                'total' => $heatmap[$date] ?? 0,
            ];
        }

        return [
            'period'             => $period,
            'totalIncome'        => (float) $totalIncome,
            'totalExpense'       => (float) $totalExpense,
            'balance'            => (float) $balance,
            'totalTransactions'  => $totalTransactions,
            'incomeCount'        => $incomeCount,
            'expenseCount'       => $expenseCount,
            'recentTransactions' => $recentTransactions,
            'warningWallets'     => $warningWallets,
            'topCategories'      => $topCategories,
            'activeWallets'      => $activeWallets,
            'monthlyData'        => $monthlyData,
            'categoryExpenses'   => $categoryExpenses,
            'savingRate'         => $savingRate,
            'incomeChange'       => $incomeChange,
            'expenseChange'      => $expenseChange,
            'forecast'           => $forecast,
            'spikingCategories'  => $spikingCategories,
            'expenseByDay'       => $expenseByDay,
            'heatmap'            => $heatmapData,
        ];
    }

    /* Hàm nội bộ, lọc query theo kỳ hiện tại (tháng này/trước/năm nay/tất cả) */
    protected function applyPeriodFilter($query, string $period)
    {
        match($period) {
            'this_month' => $query
                ->whereMonth('ngay_giao_dich', now()->month)
                ->whereYear('ngay_giao_dich', now()->year),
            'last_month' => $query
                ->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                ->whereYear('ngay_giao_dich', now()->subMonth()->year),
            'this_year'  => $query->whereYear('ngay_giao_dich', now()->year),
             default => $query
            ->where('ngay_giao_dich', '>=', now()->subMonths(6)->startOfMonth()->toDateString()),
        };

        return $query;
    }

    /* Hàm nội bộ, lọc query theo kỳ trước để so sánh tăng/giảm */
    protected function applyPreviousPeriodFilter($query, string $period)
    {
        match($period) {
            'this_month' => $query
                ->whereMonth('ngay_giao_dich', now()->subMonth()->month)
                ->whereYear('ngay_giao_dich',  now()->subMonth()->year),
            'last_month' => $query
                ->whereMonth('ngay_giao_dich', now()->subMonths(2)->month)
                ->whereYear('ngay_giao_dich',  now()->subMonths(2)->year),
            'this_year'  => $query->whereYear('ngay_giao_dich', now()->subYear()->year),

            // Case này: so 6 tháng gần nhất vs 6 tháng trước đó
            default      => $query
                ->where('ngay_giao_dich', '>=', now()->subMonths(12)->startOfMonth()->toDateString())
                ->where('ngay_giao_dich', '<',  now()->subMonths(6)->startOfMonth()->toDateString()),
        };

        return $query;
    }

    /* Hàm nội bộ, tạo file Excel 3 sheet, dùng chung cho export/sendReport */
    private function buildExcelFile(array $data, string $periodLabel, string $tempPath): void
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Tổng quan
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('TongQuan');
        $sheet1->getColumnDimension('A')->setWidth(28);
        $sheet1->getColumnDimension('B')->setWidth(28);

        $sheet1->setCellValue('A1', 'BÁO CÁO TÀI CHÍNH - MONEXA');
        $sheet1->setCellValue('A2', 'Kỳ báo cáo: ' . $periodLabel . '   |   Xuất ngày: ' . now()->format('d/m/Y H:i'));
        $sheet1->setCellValue('A4', 'Chỉ số');
        $sheet1->setCellValue('B4', 'Giá trị');
        $sheet1->setCellValue('A5', 'Thu nhập');        $sheet1->setCellValue('B5', $data['totalIncome']);
        $sheet1->setCellValue('A6', 'Chi tiêu');        $sheet1->setCellValue('B6', $data['totalExpense']);
        $sheet1->setCellValue('A7', 'Số dư');           $sheet1->setCellValue('B7', $data['balance']);
        $sheet1->setCellValue('A8', 'Tỷ lệ tiết kiệm');$sheet1->setCellValue('B8', ($data['savingRate'] ?? 0) . '%');
        $sheet1->setCellValue('A9', 'Tổng giao dịch'); $sheet1->setCellValue('B9', $data['totalTransactions']);

        if (!empty($data['incomeChange'])) {
            $sheet1->setCellValue('A10', 'Thu nhập so kỳ trước');
            $sheet1->setCellValue('B10', $data['incomeChange'] . '%');
        }
        if (!empty($data['expenseChange'])) {
            $sheet1->setCellValue('A11', 'Chi tiêu so kỳ trước');
            $sheet1->setCellValue('B11', $data['expenseChange'] . '%');
        }
        if (!empty($data['forecast'])) {
            $sheet1->setCellValue('A12', 'Dự báo chi tiêu cuối tháng');
            $sheet1->setCellValue('B12', $data['forecast']);
        }

        $highestRow1 = $sheet1->getHighestRow();

        $sheet1->mergeCells('A1:B1');
        $sheet1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet1->getRowDimension(1)->setRowHeight(36);

        $sheet1->mergeCells('A2:B2');
        $sheet1->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
        ]);

        $sheet1->getStyle('A4:B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        for ($row = 5; $row <= $highestRow1; $row++) {
            $bg = ($row % 2 === 0) ? 'DBEAFE' : 'F0F9FF';
            $sheet1->getStyle("A{$row}:B{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
        }

        $sheet1->getStyle('B5')->getFont()->getColor()->setRGB('059669');
        $sheet1->getStyle('B6')->getFont()->getColor()->setRGB('DC2626');
        $sheet1->getStyle('B7')->getFont()->getColor()->setRGB($data['balance'] >= 0 ? '059669' : 'DC2626');

        $sheet1->getStyle('A4:B' . $highestRow1)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFDBFE']]],
        ]);

        // Sheet 2: Thu Chi 
        $sheet2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'ThuChi');
        $spreadsheet->addSheet($sheet2);
        $sheet2->getColumnDimension('A')->setWidth(15);
        $sheet2->getColumnDimension('B')->setWidth(22);
        $sheet2->getColumnDimension('C')->setWidth(22);

        $sheet2->setCellValue('A1', 'Tháng');
        $sheet2->setCellValue('B1', 'Thu nhap');
        $sheet2->setCellValue('C1', 'Chi tieu');

        $sheet2->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $monthlyData = $data['monthlyData'] ?? [];
        foreach ($monthlyData as $i => $item) {
            $row = $i + 2;
            $sheet2->setCellValue('A' . $row, 'Thang ' . $item['month']);
            $sheet2->setCellValue('B' . $row, (float) $item['income']);
            $sheet2->setCellValue('C' . $row, (float) $item['expense']);

            $bg = ($i % 2 === 0) ? 'ECFDF5' : 'FFFFFF';
            $sheet2->getStyle("A{$row}:C{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $sheet2->getStyle("B{$row}")->getFont()->getColor()->setRGB('059669');
            $sheet2->getStyle("C{$row}")->getFont()->getColor()->setRGB('DC2626');
        }

        $lastRow2 = count($monthlyData) + 1;
        $sheet2->getStyle('A1:C' . $lastRow2)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1FAE5']]],
        ]);

        if (count($monthlyData) > 0) {
            $labels = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'ThuChi!$A$2:$A$' . $lastRow2, null, count($monthlyData));
            $incomeValues  = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'ThuChi!$B$2:$B$' . $lastRow2, null, count($monthlyData));
            $expenseValues = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'ThuChi!$C$2:$C$' . $lastRow2, null, count($monthlyData));
            $seriesLabels  = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Thu nhap']),
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, null, null, 1, ['Chi tieu']),
            ];
            $lineChart = new Chart('line_chart', new Title('Thu chi 6 thang'), new Legend(Legend::POSITION_BOTTOM),
                new PlotArea(null, [new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, 1), $seriesLabels, [$labels], [$incomeValues, $expenseValues])])
            );
            $lineChart->setTopLeftPosition('E2');
            $lineChart->setBottomRightPosition('N20');
            $sheet2->addChart($lineChart);
        }

        // Sheet 3: Danh Mục
        $sheet3 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'DanhMuc');
        $spreadsheet->addSheet($sheet3);
        $sheet3->getColumnDimension('A')->setWidth(28);
        $sheet3->getColumnDimension('B')->setWidth(22);

        $sheet3->setCellValue('A1', 'Danh muc');
        $sheet3->setCellValue('B1', 'So tien');

        $sheet3->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $categoryData = $data['categoryExpenses'] ?? [];
        foreach ($categoryData as $i => $item) {
            $row = $i + 2;
            $sheet3->setCellValue('A' . $row, $item['name']);
            $sheet3->setCellValue('B' . $row, (float) $item['total']);

            $bg = ($i % 2 === 0) ? 'FFFBEB' : 'FFFFFF';
            $sheet3->getStyle("A{$row}:B{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            ]);
            $sheet3->getStyle("B{$row}")->getFont()->getColor()->setRGB('DC2626');
        }

        $lastRow3 = count($categoryData) + 1;
        $sheet3->getStyle('A1:B' . $lastRow3)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FDE68A']]],
        ]);

        if (count($categoryData) > 0) {
            $catLabels = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'DanhMuc!$A$2:$A$' . $lastRow3, null, count($categoryData));
            $catValues = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'DanhMuc!$B$2:$B$' . $lastRow3, null, count($categoryData));
            $pieChart  = new Chart('pie_chart', new Title('Phan bo chi tieu'), new Legend(Legend::POSITION_RIGHT),
                new PlotArea(null, [new DataSeries(DataSeries::TYPE_PIECHART, null, range(0, 0), [null], [$catLabels], [$catValues])])
            );
            $pieChart->setTopLeftPosition('D2');
            $pieChart->setBottomRightPosition('M22');
            $sheet3->addChart($pieChart);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($tempPath);
    }

    /* GET /api/v1/dashboard/export?period=this_month → Xuất báo cáo Excel */
    public function export(Request $request) : \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        $data = $this->getDashboardData($userId, $period);

        $periodLabel = match($period) {
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_year'  => 'Năm nay',
            default      => 'Tất cả',
        };

        $filename = "baocao_{$period}_" . now()->format('Y-m-d') . ".xlsx";
        $tempPath = storage_path('app/temp_' . $filename);

        $this->buildExcelFile($data, $periodLabel, $tempPath); // ← dùng hàm chung

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /* POST /api/v1/dashboard/export-pdf?period=this_month → Xuất báo cáo PDF */
    public function exportPdf(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->input('period', 'this_month');

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        $data = $this->getDashboardData($userId, $period);

        $periodLabel = match($period) {
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_year'  => 'Năm nay',
            default      => 'Tất cả',
        };

        // Nhận ảnh chart từ frontend
        $lineImg = $request->input('lineImg', '');
        $pieImg  = $request->input('pieImg',  '');
        $barImg  = $request->input('barImg',  '');

        $pdf = Pdf::loadView('dashboard_pdf', compact(
            'data', 'periodLabel', 'lineImg', 'pieImg', 'barImg'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("baocao_{$period}_" . now()->format('Y-m-d') . ".pdf");
    }

    /* POST /api/v1/dashboard/report → Gửi báo cáo qua email */
    public function sendReport(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::id();
        $period = $request->string('period')->toString() ?: 'this_month';
        $format = $request->string('format')->toString() ?: 'xlsx';
        $email  = $request->string('email')->toString();

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Email không hợp lệ'], 422);
        }

        if (!in_array($period, ['all', 'this_month', 'last_month', 'this_year'], true)) {
            $period = 'this_month';
        }

        if (!in_array($format, ['xlsx', 'pdf'], true)) {
            $format = 'xlsx';
        }

        $data = $this->getDashboardData($userId, $period);

        $periodLabel = match($period) {
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_year'  => 'Năm nay',
            default      => 'Tất cả',
        };

        $filename = "baocao_{$period}_" . now()->format('Y-m-d') . ".{$format}";
        $tempPath = storage_path('app/temp_' . $filename);

        try {
            if ($format === 'pdf') {
                // ← Tạo PDF
                $pdf = Pdf::loadView('dashboard_pdf', [
                    'data'        => $data,
                    'periodLabel' => $periodLabel,
                    'lineImg'     => '',
                    'pieImg'      => '',
                    'barImg'      => '',
                ])->setPaper('a4', 'portrait');

                file_put_contents($tempPath, $pdf->output());
                $mimeType = 'application/pdf';

            } else {
                // ← Dùng hàm chung thay vì viết lại toàn bộ code Excel
                $this->buildExcelFile($data, $periodLabel, $tempPath);
                $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }

            // Gửi email
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($email, $tempPath, $filename, $mimeType, $periodLabel) {
                $message->to($email)
                    ->subject("Báo cáo tài chính Monexa - {$periodLabel}")
                    ->html("
                        <h2>Báo cáo tài chính Monexa</h2>
                        <p>Xin chào,</p>
                        <p>Báo cáo tài chính kỳ <strong>{$periodLabel}</strong> đã được đính kèm trong email này.</p>
                        <p>Trân trọng,<br>Monexa</p>
                    ")
                    ->attach($tempPath, [
                        'as'   => $filename,
                        'mime' => $mimeType,
                    ]);
            });

            return response()->json(['message' => 'Đã gửi báo cáo đến ' . $email]);

        } catch (\Exception $e) {
            \Log::error('sendReport error: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi thất bại: ' . $e->getMessage()], 500);
        } finally {
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
