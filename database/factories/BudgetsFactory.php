<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Budgets;
use App\Models\User;
use App\Models\Category;

class BudgetsFactory extends Factory
{
    protected $model = Budgets::class;

    public function definition(): array
    {
        $nganSachGoc = $this->faker->randomElement([
            500000, 1000000, 1500000, 2000000, 3000000,
            3500000, 5000000, 8000000, 10000000,
        ]);

        // So du ngẫu nhiên từ âm (vượt ngân sách) đến bằng ngân sách gốc
        $soDu = $this->faker->randomFloat(2, -$nganSachGoc * 0.2, $nganSachGoc);

        $loaiThoiGian = $this->faker->randomElement(['thang', 'ngay']);
        $ngayBatDau   = $this->faker->dateTimeBetween('-6 months', 'now');
        $ngayKetThuc  = $loaiThoiGian === 'thang'
            ? (clone $ngayBatDau)->modify('last day of this month')
            : $this->faker->dateTimeBetween($ngayBatDau, '+6 months');

        $daHetHan  = now() > $ngayKetThuc;
        $trangThai = !$daHetHan;

        return [
            'user_id'        => User::factory(),
            'category_id'    => Category::factory(),
            'ten_ngan_sach'  => $this->faker->randomElement([
                'Ăn uống tháng này', 'Xăng xe', 'Tiền điện', 'Cafe',
                'Thuê nhà', 'Mua sắm', 'Giải trí', 'Du lịch',
                'Khám bệnh', 'Học hành', 'Gym', 'Làm đẹp',
            ]),
            'ngan_sach_goc'  => $nganSachGoc,
            'so_du'          => $soDu,
            'mo_ta'          => $this->faker->sentence(),
            'trang_thai'     => $trangThai,
            'loai_thoi_gian' => $loaiThoiGian,
            'ngay_bat_dau'   => $ngayBatDau->format('Y-m-d'),
            'ngay_ket_thuc'  => $ngayKetThuc->format('Y-m-d'),
            'tu_dong_reset'  => $this->faker->boolean(30), // 30% là true
            'da_het_han'     => $daHetHan,
        ];
    }

    /**
     * State: Ngân sách đang tốt (dưới 50% đã chi)
     */
    public function good(): static
    {
        return $this->state(function (array $attributes) {
            $goc = $attributes['ngan_sach_goc'];
            return [
                'so_du'      => $this->faker->randomFloat(2, $goc * 0.5, $goc),
                'trang_thai' => true,
                'da_het_han' => false,
            ];
        });
    }

    /**
     * State: Ngân sách sắp hết (80-90% đã chi)
     */
    public function lowBalance(): static
    {
        return $this->state(function (array $attributes) {
            $goc = $attributes['ngan_sach_goc'];
            return [
                'so_du'      => $this->faker->randomFloat(2, $goc * 0.1, $goc * 0.2),
                'trang_thai' => true,
                'da_het_han' => false,
            ];
        });
    }

    /**
     * State: Ngân sách nguy hiểm (>90% đã chi)
     */
    public function critical(): static
    {
        return $this->state(function (array $attributes) {
            $goc = $attributes['ngan_sach_goc'];
            return [
                'so_du'      => $this->faker->randomFloat(2, 0, $goc * 0.1),
                'trang_thai' => true,
                'da_het_han' => false,
            ];
        });
    }

    /**
     * State: Vượt ngân sách (số dư âm)
     */
    public function overBudget(): static
    {
        return $this->state(function (array $attributes) {
            $goc = $attributes['ngan_sach_goc'];
            return [
                'so_du'      => $this->faker->randomFloat(2, -$goc * 0.3, -1),
                'trang_thai' => true,
                'da_het_han' => false,
            ];
        });
    }

    /**
     * State: Ngân sách đã hết hạn
     */
    public function expired(): static
    {
        return $this->state(function () {
            $ngayBatDau  = $this->faker->dateTimeBetween('-6 months', '-2 months');
            $ngayKetThuc = $this->faker->dateTimeBetween($ngayBatDau, '-1 month');
            return [
                'trang_thai'     => false,
                'da_het_han'     => true,
                'ngay_bat_dau'   => $ngayBatDau->format('Y-m-d'),
                'ngay_ket_thuc'  => $ngayKetThuc->format('Y-m-d'),
            ];
        });
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
     * State: Loại tháng
     */
    public function monthly(): static
    {
        return $this->state(function () {
            $ngayBatDau  = now()->startOfMonth();
            $ngayKetThuc = now()->endOfMonth();
            return [
                'loai_thoi_gian' => 'thang',
                'ngay_bat_dau'   => $ngayBatDau->format('Y-m-d'),
                'ngay_ket_thuc'  => $ngayKetThuc->format('Y-m-d'),
                'trang_thai'     => true,
                'da_het_han'     => false,
            ];
        });
    }

    /**
     * State: Loại ngày (khoảng thời gian tùy chỉnh)
     */
    public function daily(): static
    {
        return $this->state(function () {
            $ngayBatDau  = now()->startOfMonth();
            $ngayKetThuc = now()->endOfMonth()->addMonths(2);
            return [
                'loai_thoi_gian' => 'ngay',
                'ngay_bat_dau'   => $ngayBatDau->format('Y-m-d'),
                'ngay_ket_thuc'  => $ngayKetThuc->format('Y-m-d'),
                'trang_thai'     => true,
                'da_het_han'     => false,
            ];
        });
    }
}

