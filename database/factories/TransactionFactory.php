<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use App\Models\Budgets;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    // Ghi chú thực tế theo loại danh mục
    protected static array $ghiChuMap = [
        'Ăn sáng'           => ['Ăn sáng bánh mì', 'Ăn sáng phở', 'Ăn sáng bún', 'Ăn sáng xôi'],
        'Ăn trưa'           => ['Cơm văn phòng', 'Cơm bình dân', 'Bún bò', 'Phở trưa'],
        'Ăn tối'            => ['Ăn tối gia đình', 'Ăn tối cuối tuần', 'Nấu ăn tại nhà'],
        'Cafe'              => ['Cafe sáng', 'Cafe với bạn bè', 'Cafe chiều', 'Trà sữa'],
        'Ăn tiệm'           => ['Ăn nhà hàng', 'Liên hoan đồng nghiệp', 'Ăn tiệm cuối tuần'],
        'Xăng xe'           => ['Đổ xăng lần 1', 'Đổ xăng lần 2', 'Đổ xăng đầy bình'],
        'Gửi xe'            => ['Phí gửi xe tuần 1', 'Phí gửi xe tháng', 'Gửi xe công ty'],
        'Điện'              => ['Hóa đơn tiền điện', 'Thanh toán tiền điện tháng này'],
        'Nước'              => ['Hóa đơn tiền nước', 'Thanh toán nước tháng này'],
        'Internet'          => ['Phí internet tháng này', 'Gia hạn internet'],
        'Điện thoại'        => ['Nạp tiền điện thoại', 'Cước điện thoại tháng này'],
        'Thuê nhà'          => ['Tiền thuê nhà tháng này', 'Tiền trọ'],
        'Quần áo'           => ['Mua áo thun', 'Mua quần jean', 'Mua áo khoác'],
        'Giày dép'          => ['Mua giày thể thao', 'Mua dép', 'Mua giày công sở'],
        'Du lịch'           => ['Vé máy bay', 'Đặt khách sạn', 'Chi phí du lịch'],
        'Làm đẹp'           => ['Cắt tóc', 'Nhuộm tóc', 'Chăm sóc da', 'Spa'],
        'Vui chơi giải trí' => ['Xem phim', 'Karaoke', 'Chơi game', 'Xem concert'],
        'Khám chữa bệnh'    => ['Khám sức khỏe định kỳ', 'Khám bệnh', 'Khám răng'],
        'Thuốc men'         => ['Mua thuốc', 'Mua vitamin', 'Mua thuốc bổ'],
        'Thể thao'          => ['Phí tập gym', 'Mua dụng cụ thể thao', 'Phí bơi lội'],
        'Học hành'          => ['Học phí khóa học', 'Mua sách', 'Học tiếng Anh'],
        'Biếu tặng'         => ['Mua quà sinh nhật', 'Quà biếu', 'Mua quà tết'],
        'Lương hàng tháng'  => ['Lương tháng này', 'Nhận lương', 'Lương cơ bản'],
        'Lương thưởng'      => ['Thưởng quý', 'Thưởng dự án', 'Thưởng cuối năm'],
        'Lãi tiết kiệm'     => ['Lãi tiết kiệm tháng này', 'Lãi ngân hàng'],
        'Lãi doanh thu'     => ['Lãi kinh doanh', 'Doanh thu tháng này'],
    ];

    public function definition(): array
    {
        $loaiGiaoDich = $this->faker->randomElement(['THU', 'CHI']);

        // Số tiền thực tế theo loại giao dịch
        $soTien = $loaiGiaoDich === 'THU'
            ? $this->faker->randomElement([
                5000000, 8000000, 10000000, 12000000,
                15000000, 20000000, 500000, 1000000,
            ])
            : $this->faker->randomElement([
                50000, 100000, 150000, 200000, 250000,
                300000, 500000, 800000, 1000000, 3500000,
            ]);

        return [
            'user_id'               => User::factory(),
            'category_id'           => Category::factory(),
            'wallet_id'             => null,
            'money_wallet_id'       => null,
            'so_tien'               => $soTien,
            'loai_giao_dich'        => $loaiGiaoDich,
            'phuong_thuc_thanh_toan'=> $this->faker->randomElement(['Tiền mặt', 'Chuyển khoản']),
            'ngay_giao_dich'        => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'ghi_chu'               => $this->faker->randomElement(array_merge(...array_values(self::$ghiChuMap))),
        ];
    }

    /**
     * State: Giao dịch CHI
     */
    public function chi(): static
    {
        return $this->state(function () {
            return [
                'loai_giao_dich' => 'CHI',
                'so_tien'        => $this->faker->randomElement([
                    50000, 100000, 150000, 200000, 250000,
                    300000, 500000, 800000, 1000000, 3500000,
                ]),
            ];
        });
    }

    /**
     * State: Giao dịch THU
     */
    public function thu(): static
    {
        return $this->state(function () {
            return [
                'loai_giao_dich' => 'THU',
                'so_tien'        => $this->faker->randomElement([
                    5000000, 8000000, 10000000, 12000000,
                    15000000, 20000000,
                ]),
            ];
        });
    }

    /**
     * State: Thanh toán tiền mặt
     */
    public function tienMat(): static
    {
        return $this->state(['phuong_thuc_thanh_toan' => 'Tiền mặt']);
    }

    /**
     * State: Thanh toán chuyển khoản
     */
    public function chuyenKhoan(): static
    {
        return $this->state(['phuong_thuc_thanh_toan' => 'Chuyển khoản']);
    }

    /**
     * State: Gán cho user cụ thể
     */
    public function forUser(int $userId): static
    {
        return $this->state(['user_id' => $userId]);
    }

    /**
     * State: Gán cho category cụ thể
     */
    public function forCategory(int $categoryId): static
    {
        return $this->state(['category_id' => $categoryId]);
    }

    /**
     * State: Gán cho ngân sách cụ thể
     */
    public function forBudget(int $budgetId): static
    {
        return $this->state(['wallet_id' => $budgetId]);
    }

    /**
     * State: Giao dịch trong tháng hiện tại
     */
    public function thisMonth(): static
    {
        return $this->state(function () {
            return [
                'ngay_giao_dich' => $this->faker
                    ->dateTimeBetween(now()->startOfMonth(), now())
                    ->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: Giao dịch trong tháng trước
     */
    public function lastMonth(): static
    {
        return $this->state(function () {
            return [
                'ngay_giao_dich' => $this->faker
                    ->dateTimeBetween(
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    )
                    ->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: Giao dịch trong năm nay
     */
    public function thisYear(): static
    {
        return $this->state(function () {
            return [
                'ngay_giao_dich' => $this->faker
                    ->dateTimeBetween(now()->startOfYear(), now())
                    ->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: Giao dịch với ghi chú theo danh mục cụ thể
     */
    public function withNote(string $categoryName): static
    {
        return $this->state(function () use ($categoryName) {
            $notes = self::$ghiChuMap[$categoryName] ?? ['Giao dịch'];
            return ['ghi_chu' => $this->faker->randomElement($notes)];
        });
    }
}
