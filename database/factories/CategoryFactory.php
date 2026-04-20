<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\User;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    // Danh mục CHI cha và con thực tế
    protected static array $chiCategories = [
        'Ăn uống'             => ['Cafe', 'Ăn tiệm', 'Đi chợ/siêu thị', 'Ăn sáng', 'Ăn trưa', 'Ăn tối'],
        'Dịch vụ sinh hoạt'  => ['Internet', 'Nước', 'Điện', 'Điện thoại', 'Gas', 'Truyền hình'],
        'Đi lại'              => ['Xăng xe', 'Bảo hiểm xe', 'Gửi xe', 'Rửa xe', 'Sửa chữa xe', 'Taxi/thuê xe'],
        'Trang phục'          => ['Quần áo', 'Giày dép', 'Phụ kiện'],
        'Hiếu hỉ'             => ['Biếu tặng', 'Cưới xin', 'Ma chay', 'Thăm hỏi'],
        'Hưởng thụ'           => ['Vui chơi giải trí', 'Du lịch', 'Làm đẹp', 'Phim ảnh ca nhạc'],
        'Sức khỏe'            => ['Khám chữa bệnh', 'Thuốc men', 'Thể thao'],
        'Nhà cửa'             => ['Mua sắm đồ đạc', 'Sửa chữa nhà cửa', 'Thuê nhà'],
        'Phát triển bản thân' => ['Giao lưu quan hệ', 'Học hành'],
        'Con cái'             => ['Học phí', 'Sách vở', 'Sữa', 'Tiền tiêu vặt', 'Đồ chơi'],
    ];

    // Danh mục THU cha và con thực tế
    protected static array $thuCategories = [
        'Lương'    => ['Lương hàng tháng', 'Lương thưởng'],
        'Tiền lãi' => ['Lãi tiết kiệm', 'Lãi doanh thu'],
    ];

    // Icon map theo tên danh mục
    protected static array $iconMap = [
        'Ăn uống'             => 'food.png',
        'Cafe'                => 'coffee.png',
        'Ăn tiệm'             => 'restaurant.png',
        'Ăn sáng'             => 'breakfast.png',
        'Ăn trưa'             => 'lunch.png',
        'Ăn tối'              => 'dinner.png',
        'Đi chợ/siêu thị'    => 'shopping.png',
        'Dịch vụ sinh hoạt'  => 'home.png',
        'Internet'            => 'internet.png',
        'Điện'                => 'electric.png',
        'Nước'                => 'water.png',
        'Gas'                 => 'gas.png',
        'Điện thoại'          => 'phone.png',
        'Truyền hình'         => 'tv.png',
        'Đi lại'              => 'transport.png',
        'Xăng xe'             => 'fuel.png',
        'Bảo hiểm xe'         => 'insurance.png',
        'Gửi xe'              => 'parking.png',
        'Taxi/thuê xe'        => 'taxi.png',
        'Trang phục'          => 'fashion.png',
        'Quần áo'             => 'clothes.png',
        'Giày dép'            => 'shoes.png',
        'Phụ kiện'            => 'accessories.png',
        'Hiếu hỉ'             => 'gift.png',
        'Biếu tặng'           => 'gift.png',
        'Cưới xin'            => 'wedding.png',
        'Hưởng thụ'           => 'entertainment.png',
        'Vui chơi giải trí'   => 'game.png',
        'Du lịch'             => 'travel.png',
        'Làm đẹp'             => 'beauty.png',
        'Phim ảnh ca nhạc'    => 'movie.png',
        'Sức khỏe'            => 'health.png',
        'Khám chữa bệnh'      => 'hospital.png',
        'Thuốc men'           => 'medicine.png',
        'Thể thao'            => 'sport.png',
        'Nhà cửa'             => 'house.png',
        'Thuê nhà'            => 'rent.png',
        'Phát triển bản thân' => 'growth.png',
        'Học hành'            => 'study.png',
        'Con cái'             => 'child.png',
        'Học phí'             => 'school.png',
        'Lương'               => 'salary.png',
        'Lương hàng tháng'    => 'salary.png',
        'Lương thưởng'        => 'bonus.png',
        'Tiền lãi'            => 'interest.png',
        'Lãi tiết kiệm'       => 'saving.png',
        'Lãi doanh thu'       => 'revenue.png',
    ];

    public function definition(): array
    {
        $loai = $this->faker->randomElement(['THU', 'CHI']);
        $pool  = $loai === 'CHI' ? self::$chiCategories : self::$thuCategories;

        // Mặc định lấy danh mục cha ngẫu nhiên
        $tenDanhMuc = $this->faker->randomElement(array_keys($pool));

        return [
            'user_id'        => User::factory(),
            'ten_danh_muc'   => $tenDanhMuc,
            'loai_danh_muc'  => $loai,
            'danh_muc_cha_id'=> null,
            'bieu_tuong'     => self::$iconMap[$tenDanhMuc] ?? 'money.png',
            'mo_ta'          => $this->faker->sentence(),
            'trang_thai'     => true,
        ];
    }

    /**
     * State: Danh mục CHI cha
     */
    public function chiParent(): static
    {
        return $this->state(function () {
            $ten = $this->faker->randomElement(array_keys(self::$chiCategories));
            return [
                'ten_danh_muc'    => $ten,
                'loai_danh_muc'   => 'CHI',
                'danh_muc_cha_id' => null,
                'bieu_tuong'      => self::$iconMap[$ten] ?? 'money.png',
            ];
        });
    }

    /**
     * State: Danh mục THU cha
     */
    public function thuParent(): static
    {
        return $this->state(function () {
            $ten = $this->faker->randomElement(array_keys(self::$thuCategories));
            return [
                'ten_danh_muc'    => $ten,
                'loai_danh_muc'   => 'THU',
                'danh_muc_cha_id' => null,
                'bieu_tuong'      => self::$iconMap[$ten] ?? 'money.png',
            ];
        });
    }

    /**
     * State: Danh mục CHI con (cần truyền parent_id)
     */
    public function chiChild(int $parentId, string $parentName): static
    {
        return $this->state(function () use ($parentId, $parentName) {
            $children = self::$chiCategories[$parentName] ?? ['Chi khác'];
            $ten      = $this->faker->randomElement($children);
            return [
                'ten_danh_muc'    => $ten,
                'loai_danh_muc'   => 'CHI',
                'danh_muc_cha_id' => $parentId,
                'bieu_tuong'      => self::$iconMap[$ten] ?? 'money.png',
            ];
        });
    }

    /**
     * State: Danh mục THU con (cần truyền parent_id)
     */
    public function thuChild(int $parentId, string $parentName): static
    {
        return $this->state(function () use ($parentId, $parentName) {
            $children = self::$thuCategories[$parentName] ?? ['Thu khác'];
            $ten      = $this->faker->randomElement($children);
            return [
                'ten_danh_muc'    => $ten,
                'loai_danh_muc'   => 'THU',
                'danh_muc_cha_id' => $parentId,
                'bieu_tuong'      => self::$iconMap[$ten] ?? 'money.png',
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
     * State: Danh mục không hoạt động
     */
    public function inactive(): static
    {
        return $this->state(['trang_thai' => false]);
    }
}

