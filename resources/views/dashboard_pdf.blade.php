<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body  { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 20px; }
  h1    { font-size: 20px; margin: 0; color: #1D4ED8; }
  .sub  { font-size: 10px; color: #64748b; margin: 4px 0 20px; }

  .stats { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
  .stat-box { width: 23%; padding: 12px; border: 1px solid #BFDBFE;
               border-radius: 6px; background: #EFF6FF; text-align: center; }
  .stat-label { font-size: 10px; color: #64748b; margin-bottom: 4px; }
  .stat-value { font-size: 16px; font-weight: bold; }
  .green { color: #059669; } .red { color: #DC2626; }

  h3 { font-size: 13px; color: #1E40AF; border-bottom: 2px solid #BFDBFE;
       padding-bottom: 4px; margin: 16px 0 8px; }

  table  { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
  th     { background: #1E40AF; color: #fff; padding: 7px 10px; text-align: left; }
  td     { padding: 6px 10px; border-bottom: 1px solid #E2E8F0; }
  tr.odd { background: #F0F9FF; }

  .badge-up   { color: #059669; font-size: 10px; }
  .badge-down { color: #DC2626; font-size: 10px; }
  .badge-warn { background: #FEF3C7; color: #92400E; padding: 2px 6px;
                border-radius: 4px; font-size: 10px; }
  .footer { font-size: 9px; color: #94a3b8; text-align: center; margin-top: 24px;
            border-top: 1px solid #E2E8F0; padding-top: 8px; }
</style>
</head>
<body>

<h1>📊 Báo cáo tài chính - Monexa</h1>
<div class="sub">
    Kỳ báo cáo: <strong>{{ $periodLabel }}</strong>
    &nbsp;|&nbsp; Xuất lúc: {{ now()->format('d/m/Y H:i') }}
</div>

{{-- STAT BOXES --}}
<table class="stats">
<tr>
    <td class="stat-box">
        <div class="stat-label">Thu nhập</div>
        <div class="stat-value green">{{ number_format($data['totalIncome'], 0, ',', '.') }} ₫</div>
        @if(!is_null($data['incomeChange'] ?? null))
            <div class="{{ ($data['incomeChange'] >= 0) ? 'badge-up' : 'badge-down' }}">
                {{ $data['incomeChange'] >= 0 ? '▲' : '▼' }} {{ abs($data['incomeChange']) }}% so kỳ trước
            </div>
        @endif
    </td>
    <td width="2%"></td>
    <td class="stat-box">
        <div class="stat-label">Chi tiêu</div>
        <div class="stat-value red">{{ number_format($data['totalExpense'], 0, ',', '.') }} ₫</div>
        @if(!is_null($data['expenseChange'] ?? null))
            <div class="{{ ($data['expenseChange'] <= 0) ? 'badge-up' : 'badge-down' }}">
                {{ $data['expenseChange'] >= 0 ? '▲' : '▼' }} {{ abs($data['expenseChange']) }}% so kỳ trước
            </div>
        @endif
    </td>
    <td width="2%"></td>
    <td class="stat-box">
        <div class="stat-label">Số dư</div>
        <div class="stat-value {{ $data['balance'] >= 0 ? 'green' : 'red' }}">
            {{ number_format($data['balance'], 0, ',', '.') }} ₫
        </div>
    </td>
    <td width="2%"></td>
    <td class="stat-box">
        <div class="stat-label">Tỷ lệ tiết kiệm</div>
        <div class="stat-value {{ ($data['savingRate'] ?? 0) >= 20 ? 'green' : 'red' }}">
            {{ $data['savingRate'] ?? 0 }}%
        </div>
    </td>
</tr>
</table>

@if(!empty($data['forecast']))
<p style="font-size:11px;color:#92400E;background:#FEF3C7;padding:8px 12px;border-radius:4px">
    ⚠ Dự báo chi tiêu cuối tháng: <strong>{{ number_format($data['forecast'], 0, ',', '.') }} ₫</strong>
</p>
@endif

{{-- TOP DANH MỤC --}}
<h3>Top danh mục chi tiêu</h3>
<table>
    <tr><th>Danh mục</th><th>Số tiền</th><th>Tỷ lệ</th></tr>
    @foreach($data['topCategories'] ?? [] as $i => $cat)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
        <td>{{ $cat['ten_danh_muc'] }}</td>
        <td class="red">{{ number_format($cat['total_expense'], 0, ',', '.') }} ₫</td>
        <td>{{ $data['totalExpense'] > 0 ? round(($cat['total_expense'] / $data['totalExpense']) * 100, 1) : 0 }}%</td>
    </tr>
    @endforeach
</table>

{{-- CẢNH BÁO TĂNG ĐỘT BIẾN --}}
@if(!empty($data['spikingCategories']))
<h3>⚠ Chi tiêu tăng đột biến</h3>
<table>
    <tr><th>Danh mục</th><th>Kỳ này</th><th>Kỳ trước</th><th>Tăng</th></tr>
    @foreach($data['spikingCategories'] as $i => $cat)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
        <td>{{ $cat['ten_danh_muc'] }}</td>
        <td class="red">{{ number_format($cat['current_expense'], 0, ',', '.') }} ₫</td>
        <td>{{ number_format($cat['prev_expense'], 0, ',', '.') }} ₫</td>
        <td><span class="badge-warn">▲ {{ $cat['change_percent'] }}%</span></td>
    </tr>
    @endforeach
</table>
@endif

{{-- GIAO DỊCH GẦN ĐÂY --}}
<h3>Giao dịch gần đây</h3>
<table>
    <tr><th>Ngày</th><th>Danh mục</th><th>Loại</th><th>Số tiền</th></tr>
    @foreach($data['recentTransactions'] ?? [] as $i => $t)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
        <td>{{ $t['ngay_giao_dich'] }}</td>
        <td>{{ $t['category']['ten_danh_muc'] }}</td>
        <td>{{ $t['loai_giao_dich'] === 'THU' ? 'Thu' : 'Chi' }}</td>
        <td class="{{ $t['loai_giao_dich'] === 'THU' ? 'green' : 'red' }}">
            {{ number_format($t['so_tien'], 0, ',', '.') }} ₫
        </td>
    </tr>
    @endforeach
</table>

{{-- NGÂN SÁCH --}}
@if(!empty($data['warningWallets']))
<h3>Cảnh báo ngân sách</h3>
<table>
    <tr><th>Ngân sách</th><th>Đã dùng</th></tr>
    @foreach($data['warningWallets'] as $i => $w)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
        <td>{{ $w['ten_ngan_sach'] }}</td>
        <td class="{{ $w['spent_percentage'] >= 90 ? 'red' : '' }}">
            {{ round($w['spent_percentage'], 1) }}%
        </td>
    </tr>
    @endforeach
</table>
@endif

<div class="footer">Báo cáo được tạo tự động bởi Monexa &copy; {{ now()->year }}</div>
</body>
</html>