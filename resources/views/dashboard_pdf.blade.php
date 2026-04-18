<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

  /* HEADER */
  .header { background: #1E40AF; padding: 24px 28px; color: #fff; margin-bottom: 24px; }
  .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .header .sub { font-size: 11px; opacity: 0.85; }
  .header-badge {
    background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);
    border-radius: 6px; padding: 6px 14px; font-size: 11px; font-weight: 600; text-align: center;
  }
  .header-badge .period { font-size: 13px; font-weight: 700; }

  .body { padding: 0 28px 28px; }

  /* STAT BOXES */
  .stats-row { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 20px; }
  .stat-box { width: 23%; padding: 14px 12px; border-radius: 10px; text-align: center; border: 1px solid #e2e8f0; }
  .stat-box.green  { background: #F0FDF4; border-color: #BBF7D0; }
  .stat-box.red    { background: #FEF2F2; border-color: #FECACA; }
  .stat-box.blue   { background: #EFF6FF; border-color: #BFDBFE; }
  .stat-box.purple { background: #F5F3FF; border-color: #DDD6FE; }
  .stat-label { font-size: 10px; color: #64748b; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
  .stat-value { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
  .stat-value.green  { color: #059669; }
  .stat-value.red    { color: #DC2626; }
  .stat-value.blue   { color: #1D4ED8; }
  .stat-value.purple { color: #7C3AED; }
  .stat-change { font-size: 9px; }
  .stat-change.up   { color: #059669; }
  .stat-change.down { color: #DC2626; }

  /* FORECAST */
  .forecast-box {
    background: #FFFBEB; border: 1px solid #FDE68A; border-left: 4px solid #F59E0B;
    border-radius: 6px; padding: 10px 14px; font-size: 11px; color: #92400E; margin-bottom: 20px;
  }

  /* SECTION */
  .section { margin-bottom: 24px; }
  .section-title {
    font-size: 13px; font-weight: 700; color: #1E40AF;
    border-bottom: 2px solid #BFDBFE; padding-bottom: 6px; margin-bottom: 12px;
  }

  /* CHART */
  .chart-wrap {
    border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;
    margin-bottom: 12px; background: #fafafa; text-align: center;
  }
  .chart-wrap img { max-width: 100%; height: auto; max-height: 220px; }
  .charts-grid { width: 100%; border-collapse: separate; border-spacing: 12px; margin-bottom: 8px; }
  .chart-cell {
    width: 50%; vertical-align: top; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 14px; background: #fafafa; text-align: center;
  }
  .chart-cell img { max-width: 100%; height: auto; max-height: 180px; }
  .chart-label { font-size: 10px; color: #64748b; margin-bottom: 8px; font-weight: 600; text-transform: uppercase; }

  /* TABLE */
  table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
  table.data th { background: #1E40AF; color: #fff; padding: 8px 12px; text-align: left; font-weight: 600; }
  table.data td { padding: 8px 12px; border-bottom: 1px solid #F1F5F9; }
  table.data tr.odd td { background: #F8FAFC; }

  /* BADGES */
  .badge { padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
  .badge-thu    { background: #D1FAE5; color: #065F46; }
  .badge-chi    { background: #FEE2E2; color: #991B1B; }
  .badge-warn   { background: #FEF3C7; color: #92400E; }
  .badge-danger { background: #FEE2E2; color: #991B1B; }

  .text-green { color: #059669; font-weight: 700; }
  .text-red   { color: #DC2626; font-weight: 700; }
  .text-muted { color: #94a3b8; }

  /* PROGRESS */
  .progress-wrap { background: #e2e8f0; border-radius: 4px; height: 6px; margin-top: 4px; }
  .progress-fill { height: 6px; border-radius: 4px; }

  /* NO DATA */
  .no-data { text-align: center; padding: 16px; color: #94a3b8; font-size: 11px; font-style: italic; }

  /* FOOTER */
  .footer {
    font-size: 9px; color: #94a3b8; text-align: center;
    margin-top: 28px; border-top: 1px solid #E2E8F0; padding-top: 10px;
  }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
  <div class="header-top">
    <div>
      <h1>Bao cao tai chinh - Monexa</h1>
      <div class="sub">Xuat luc: {{ now()->format('H:i d/m/Y') }}</div>
    </div>
    <div class="header-badge">
      <div style="font-size:10px;opacity:0.8;">Ky bao cao</div>
      <div class="period">{{ $periodLabel }}</div>
    </div>
  </div>
</div>

<div class="body">

{{-- STAT BOXES --}}
<table class="stats-row">
<tr>
  <td class="stat-box green">
    <div class="stat-label">Thu nhap</div>
    <div class="stat-value green">{{ number_format($data['totalIncome'], 0, ',', '.') }} d</div>
    @if(!is_null($data['incomeChange'] ?? null))
      <div class="stat-change {{ $data['incomeChange'] >= 0 ? 'up' : 'down' }}">
        {{ $data['incomeChange'] >= 0 ? '+' : '' }}{{ $data['incomeChange'] }}% so ky truoc
      </div>
    @endif
  </td>
  <td width="2%"></td>
  <td class="stat-box red">
    <div class="stat-label">Chi tieu</div>
    <div class="stat-value red">{{ number_format($data['totalExpense'], 0, ',', '.') }} d</div>
    @if(!is_null($data['expenseChange'] ?? null))
      <div class="stat-change {{ $data['expenseChange'] <= 0 ? 'up' : 'down' }}">
        {{ $data['expenseChange'] >= 0 ? '+' : '' }}{{ $data['expenseChange'] }}% so ky truoc
      </div>
    @endif
  </td>
  <td width="2%"></td>
  <td class="stat-box blue">
    <div class="stat-label">So du</div>
    <div class="stat-value {{ $data['balance'] >= 0 ? 'green' : 'red' }}">
      {{ number_format($data['balance'], 0, ',', '.') }} d
    </div>
  </td>
  <td width="2%"></td>
  <td class="stat-box purple">
    <div class="stat-label">Tiet kiem</div>
    <div class="stat-value {{ ($data['savingRate'] ?? 0) >= 20 ? 'green' : 'red' }}">
      {{ $data['savingRate'] ?? 0 }}%
    </div>
    <div class="stat-change {{ ($data['savingRate'] ?? 0) >= 20 ? 'up' : 'down' }}">
      {{ ($data['savingRate'] ?? 0) >= 20 ? 'Tot' : 'Can cai thien' }}
    </div>
  </td>
</tr>
</table>

{{-- FORECAST --}}
@if(!empty($data['forecast']))
<div class="forecast-box">
  Du bao chi tieu cuoi thang: <strong>{{ number_format($data['forecast'], 0, ',', '.') }} d</strong>
  — Hay kiem soat chi tieu de khong vuot ngan sach.
</div>
@endif

{{-- BIEU DO --}}
@if($lineImg || $pieImg || $barImg)
<div class="section">
  <div class="section-title">Bieu do thong ke</div>

  @if($lineImg)
  <div class="chart-wrap">
    <div class="chart-label">Bieu do thu chi theo thang</div>
    <img src="{{ $lineImg }}">
  </div>
  @endif

  @if($pieImg || $barImg)
  <table class="charts-grid">
    <tr>
      @if($pieImg)
      <td class="chart-cell">
        <div class="chart-label">Phan bo chi tieu</div>
        <img src="{{ $pieImg }}">
      </td>
      @endif
      @if($barImg)
      <td class="chart-cell">
        <div class="chart-label">Top danh muc chi tieu</div>
        <img src="{{ $barImg }}">
      </td>
      @endif
    </tr>
  </table>
  @endif
</div>
@endif

{{-- TOP DANH MUC --}}
<div class="section">
  <div class="section-title">Top danh muc chi tieu</div>
  @if(!empty($data['topCategories']))
  <table class="data">
    <tr><th>#</th><th>Danh muc</th><th>So tien</th><th>Ty le</th><th>Muc do</th></tr>
    @foreach($data['topCategories'] as $i => $cat)
    @php $pct = $data['totalExpense'] > 0 ? round(($cat['total_expense'] / $data['totalExpense']) * 100, 1) : 0; @endphp
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
      <td class="text-muted">{{ $i + 1 }}</td>
      <td><strong>{{ $cat['ten_danh_muc'] }}</strong></td>
      <td class="text-red">{{ number_format($cat['total_expense'], 0, ',', '.') }} d</td>
      <td>{{ $pct }}%</td>
      <td style="width:120px;">
        <div class="progress-wrap">
          <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $pct >= 50 ? '#DC2626' : ($pct >= 30 ? '#F59E0B' : '#10B981') }};"></div>
        </div>
      </td>
    </tr>
    @endforeach
  </table>
  @else
  <div class="no-data">Khong co du lieu</div>
  @endif
</div>

{{-- GIAO DICH GAN DAY --}}
<div class="section">
  <div class="section-title">Giao dich gan day</div>
  @if(!empty($data['recentTransactions']))
  <table class="data">
    <tr><th>Ngay</th><th>Danh muc</th><th>Ghi chu</th><th>Loai</th><th>So tien</th></tr>
    @foreach($data['recentTransactions'] as $i => $t)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
      <td class="text-muted">{{ \Carbon\Carbon::parse($t['ngay_giao_dich'])->format('d/m/Y') }}</td>
      <td><strong>{{ $t['category']['ten_danh_muc'] ?? '—' }}</strong></td>
      <td class="text-muted">{{ $t['ghi_chu'] ?? '—' }}</td>
      <td>
        <span class="badge {{ $t['loai_giao_dich'] === 'THU' ? 'badge-thu' : 'badge-chi' }}">
          {{ $t['loai_giao_dich'] === 'THU' ? 'Thu' : 'Chi' }}
        </span>
      </td>
      <td class="{{ $t['loai_giao_dich'] === 'THU' ? 'text-green' : 'text-red' }}">
        {{ $t['loai_giao_dich'] === 'THU' ? '+' : '-' }}{{ number_format($t['so_tien'], 0, ',', '.') }} d
      </td>
    </tr>
    @endforeach
  </table>
  @else
  <div class="no-data">Khong co giao dich nao</div>
  @endif
</div>

{{-- CANH BAO TANG DOT BIEN --}}
@if(!empty($data['spikingCategories']))
<div class="section">
  <div class="section-title">Chi tieu tang dot bien</div>
  <table class="data">
    <tr><th>Danh muc</th><th>Ky nay</th><th>Ky truoc</th><th>Tang</th></tr>
    @foreach($data['spikingCategories'] as $i => $cat)
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
      <td><strong>{{ $cat['ten_danh_muc'] }}</strong></td>
      <td class="text-red">{{ number_format($cat['current_expense'], 0, ',', '.') }} d</td>
      <td class="text-muted">{{ number_format($cat['prev_expense'], 0, ',', '.') }} d</td>
      <td><span class="badge badge-danger">+{{ $cat['change_percent'] }}%</span></td>
    </tr>
    @endforeach
  </table>
</div>
@endif

{{-- CANH BAO NGAN SACH --}}
@if(!empty($data['warningWallets']))
<div class="section">
  <div class="section-title">Canh bao ngan sach</div>
  <table class="data">
    <tr><th>Ngan sach</th><th>Da su dung</th><th>Muc do</th><th>Trang thai</th></tr>
    @foreach($data['warningWallets'] as $i => $w)
    @php $pct = round($w['spent_percentage'], 1); @endphp
    <tr class="{{ $i % 2 === 0 ? 'odd' : '' }}">
      <td><strong>{{ $w['ten_ngan_sach'] }}</strong></td>
      <td class="{{ $pct >= 90 ? 'text-red' : '' }}">{{ $pct }}%</td>
      <td style="width:120px;">
        <div class="progress-wrap">
          <div class="progress-fill" style="width:{{ min($pct,100) }}%;background:{{ $pct >= 90 ? '#DC2626' : '#F59E0B' }};"></div>
        </div>
      </td>
      <td>
        <span class="badge {{ $pct >= 90 ? 'badge-danger' : 'badge-warn' }}">
          {{ $pct >= 90 ? 'Nguy hiem' : 'Canh bao' }}
        </span>
      </td>
    </tr>
    @endforeach
  </table>
</div>
@endif

<div class="footer">
  Bao cao duoc tao tu dong boi <strong>Monexa</strong> &copy; {{ now()->year }}
  &nbsp;·&nbsp; Du lieu chi mang tinh tham khao tai thoi diem xuat bao cao.
</div>

</div>
</body>
</html>