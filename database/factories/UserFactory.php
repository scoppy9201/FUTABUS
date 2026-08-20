<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static array $ho = [
        'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh',
        'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương',
    ];

    protected static array $dem = [
        'Văn', 'Đức', 'Minh', 'Quốc', 'Hữu', 'Thanh', 'Công', 'Anh', 'Tiến',
        'Thị', 'Ngọc', 'Thùy', 'Thanh', 'Minh', 'Phương',
    ];

    protected static array $ten = [
        'Hùng', 'Mạnh', 'Tuấn', 'Dũng', 'Long', 'Nam', 'Hải', 'Phong', 'Khoa',
        'Lan', 'Hoa', 'Mai', 'Linh', 'Trang', 'Hương', 'Vy', 'Nhi', 'Thảo',
    ];

    public function definition(): array
    {
        $name = $this->faker->randomElement(self::$ho) . ' '
              . $this->faker->randomElement(self::$dem) . ' '
              . $this->faker->randomElement(self::$ten);

        $dauSo = $this->faker->randomElement([
            '032', '033', '034', '035', '036', '037', '038', '039',
            '070', '076', '077', '078', '079',
            '056', '058',
            '081', '082', '083', '084', '085',
        ]);

        return [
            'name'              => $name,
            'email'             => $this->faker->unique()->safeEmail(),
            'password'          => Hash::make('password'),
            'phone'             => $dauSo . $this->faker->numerify('#######'),
            'remember_token'    => Str::random(10),
            'email_verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state([
            'name'  => 'Admin FUTABUS',
            'email' => 'admin@futabus.vn',
            'password' => Hash::make('admin123'),
        ]);
    }

    public function staff(): static
    {
        return $this->state([
            'name'  => 'Staff FUTABUS',
            'email' => 'staff@futabus.vn',
            'password' => Hash::make('staff123'),
        ]);
    }

    public function customer(): static
    {
        return $this->state([
            'name'  => 'Khách hàng',
            'email' => 'customer@futabus.vn',
            'password' => Hash::make('customer123'),
        ]);
    }
}