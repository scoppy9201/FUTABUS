@extends('Dashboard::layouts.admin')

@section('title', __('Dashboard::app.overview'))
@section('page_title', __('Dashboard::app.overview'))

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div class="flex flex-col h-full justify-between">
                    <p class="text-sm text-gray-500 font-medium">{{ __('Dashboard::app.bus_count') }}</p>
                    <h3 class="text-3xl font-extrabold text-[#1f2937] my-2">{{ $overview['bus_count'] ?? 0 }}</h3>
                    <span class="text-xs font-semibold text-emerald-600 flex items-center">
                        <x-heroicon-o-check-circle class="w-3 h-3 mr-1" /> {{ $overview['active_buses'] ?? 0 }} xe đang hoạt động
                    </span>
                </div>
                <div class="w-14 h-14 bg-[#fff3ed] rounded-xl flex items-center justify-center shrink-0">
                    <x-heroicon-o-truck class="w-7 h-7 text-[#F26522]" />
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div class="flex flex-col h-full justify-between">
                    <p class="text-sm text-gray-500 font-medium">{{ __('Dashboard::app.upcoming_count') }}</p>
                    <h3 class="text-3xl font-extrabold text-[#1f2937] my-2">{{ $overview['upcoming_count'] ?? 0 }}</h3>
                    <span class="text-xs font-semibold text-blue-600 flex items-center">
                        <x-heroicon-o-clock class="w-3 h-3 mr-1" /> Lịch trình sắp khởi hành
                    </span>
                </div>
                <div class="w-14 h-14 bg-[#eff6ff] rounded-xl flex items-center justify-center shrink-0">
                    <x-heroicon-o-map class="w-7 h-7 text-blue-600" />
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div class="flex flex-col h-full justify-between">
                    <p class="text-sm text-gray-500 font-medium">{{ __('Dashboard::app.booking_count') }}</p>
                    <h3 class="text-3xl font-extrabold text-[#1f2937] my-2">{{ number_format($overview['booking_count'] ?? 0) }}</h3>
                    <span class="text-xs font-semibold text-amber-600 flex items-center">
                        <x-heroicon-o-information-circle class="w-3 h-3 mr-1" /> {{ $overview['pending_count'] ?? 0 }} đơn chờ xử lý
                    </span>
                </div>
                <div class="w-14 h-14 bg-[#ecfdf5] rounded-xl flex items-center justify-center shrink-0">
                    <x-heroicon-o-ticket class="w-7 h-7 text-emerald-600" />
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition">
                <div class="flex flex-col h-full justify-between">
                    <p class="text-sm text-gray-500 font-medium">{{ __('Dashboard::app.paid_amount') }}</p>
                    <h3 class="text-3xl font-extrabold text-[#F26522] my-2">{{ number_format($overview['paid_amount'] ?? 0, 0, ',', '.') }} ₫</h3>
                    <span class="text-xs font-semibold text-emerald-600 flex items-center">
                        <x-heroicon-o-currency-dollar class="w-3 h-3 mr-1" /> Đã thanh toán
                    </span>
                </div>
                <div class="w-14 h-14 bg-[#fefce8] rounded-xl flex items-center justify-center shrink-0">
                    <x-heroicon-o-currency-dollar class="w-7 h-7 text-[#d97706]" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-extrabold text-[#111827]">Doanh Thu 7 Ngày Gần Nhất</h2>
                    <span class="text-sm font-medium text-gray-500">Đơn vị: Triệu VNĐ</span>
                </div>
                <div class="h-64 relative w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Vehicle Status -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
                <h2 class="text-lg font-extrabold text-[#111827] mb-6">Trạng Thái Phương Tiện</h2>
                <div class="space-y-4 flex-1">
                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-[15px] font-medium text-[#4b5563]">Đang hoạt động</span>
                        </div>
                        <span class="font-extrabold text-[#111827] text-base">{{ $overview['active_buses'] ?? 0 }} xe</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-[15px] font-medium text-[#4b5563]">Bảo trì / Sửa chữa</span>
                        </div>
                        <span class="font-extrabold text-[#111827] text-base">{{ ($overview['bus_count'] ?? 0) - ($overview['active_buses'] ?? 0) }} xe</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <span class="text-[15px] font-medium text-[#4b5563]">Lộ trình đã phục vụ</span>
                        </div>
                        <span class="font-extrabold text-[#111827] text-base">{{ $overview['route_count'] ?? 0 }} tuyến</span>
                    </div>
                </div>
                <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                    <a href="{{ route('dashboard.section', 'buses') }}" class="text-[#F26522] text-sm font-bold hover:text-[#d95318] inline-flex items-center">
                        Xem chi tiết danh sách xe <x-heroicon-o-arrow-right class="w-4 h-4 ml-1" />
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Bookings Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-[#111827]">{{ __('Dashboard::app.recent_bookings') }}</h2>
                    <a href="{{ route('dashboard.section', 'bookings') }}" class="text-[#F26522] hover:text-[#d95318] text-sm font-bold">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-[#4b5563]">
                        <thead class="bg-[#f9fafb] text-[11px] font-bold uppercase text-[#6b7280] tracking-wider">
                            <tr>
                                <th class="px-5 py-4">MÃ ĐẶT VÉ</th>
                                <th class="px-5 py-4">KHÁCH HÀNG</th>
                                <th class="px-5 py-4">TUYẾN ĐƯỜNG</th>
                                <th class="px-5 py-4 text-right">TRẠNG THÁI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($overview['recent_bookings'] as $booking)
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-5 py-4 font-bold text-[#F26522]">{{ $booking->booking_code }}</td>
                                    <td class="px-5 py-4 font-bold text-[#111827]">{{ $booking->full_name }}</td>
                                    <td class="px-5 py-4">{{ $booking->origin_city }} ➔ {{ $booking->destination_city }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <span @class([
                                            'px-3 py-1 text-xs font-bold rounded-full',
                                            'bg-amber-100 text-amber-700' => $booking->status === 'pending',
                                            'bg-emerald-100 text-emerald-700' => in_array($booking->status, ['confirmed', 'completed'], true),
                                            'bg-gray-100 text-gray-700' => $booking->status === 'cancelled',
                                        ])>{{ __('Dashboard::app.status.'.$booking->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">{{ __('Dashboard::app.no_bookings') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Trips Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-extrabold text-[#111827]">{{ __('Dashboard::app.upcoming_trips') }}</h2>
                    <a href="{{ route('dashboard.section', 'trips') }}" class="text-[#F26522] hover:text-[#d95318] text-sm font-bold">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm text-[#4b5563]">
                        <thead class="bg-[#f9fafb] text-[11px] font-bold uppercase text-[#6b7280] tracking-wider">
                            <tr>
                                <th class="px-5 py-4">TUYẾN ĐƯỜNG</th>
                                <th class="px-5 py-4">BIỂN SỐ XE</th>
                                <th class="px-5 py-4">GIỜ XUẤT BẾN</th>
                                <th class="px-5 py-4 text-right">TRẠNG THÁI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($overview['upcoming_trips'] as $trip)
                                <tr class="hover:bg-orange-50/40 transition">
                                    <td class="px-5 py-4 font-bold text-[#111827]">{{ $trip->origin_city }} ➔ {{ $trip->destination_city }}</td>
                                    <td class="px-5 py-4">{{ $trip->license_plate }}</td>
                                    <td class="px-5 py-4">{{ \Illuminate\Support\Carbon::parse($trip->departure_time)->format('H:i (d/m)') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-[#d1fae5] text-[#065f46]">Còn {{ $trip->available_seats }} ghế</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">{{ __('Dashboard::app.no_trips') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js configuration -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Create gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(242, 101, 34, 0.2)');
            gradient.addColorStop(1, 'rgba(242, 101, 34, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                    datasets: [{
                        label: 'Doanh thu',
                        data: [280, 310, 290, 350, 420, 480, 342.5],
                        borderColor: '#F26522',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#F26522',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            titleFont: { size: 13, family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            displayColors: false,
                            callbacks: {
                                label: function(context) { return context.parsed.y + ' Triệu VNĐ'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 280,
                            max: 480,
                            grid: { color: '#f3f4f6', drawBorder: false },
                            ticks: { font: { family: "'Inter', sans-serif", size: 12 }, color: '#6b7280', stepSize: 20 }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { family: "'Inter', sans-serif", size: 13 }, color: '#6b7280' }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        });
    </script>
@endsection
