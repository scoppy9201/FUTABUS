<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    // Tên Việt Nam thực tế
    protected static array $hoNam = [
        'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh',
        'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương',
    ];

    protected static array $tenDemNam = [
        'Văn', 'Đức', 'Minh', 'Quốc', 'Hữu', 'Thanh', 'Công', 'Anh', 'Tiến',
    ];

    protected static array $tenNam = [
        'Hùng', 'Mạnh', 'Tuấn', 'Dũng', 'Long', 'Nam', 'Hải',
        'Phong', 'Khoa', 'Bình', 'Hưng', 'Đạt', 'Tùng', 'Kiên',
    ];

    protected static array $tenDemNu = [
        'Thị', 'Ngọc', 'Thùy', 'Thanh', 'Minh', 'Phương', 'Thúy',
    ];

    protected static array $tenNu = [
        'Lan', 'Hoa', 'Mai', 'Linh', 'Trang', 'Hương', 'Nga',
        'Vy', 'Nhi', 'Châu', 'Yến', 'Thảo', 'Hiền', 'Ánh',
    ];

    public function definition(): array
    {
        $gioiTinh = $this->faker->randomElement(['nam', 'nu', 'khac']);

        if ($gioiTinh === 'nam') {
            $name = $this->faker->randomElement(self::$hoNam) . ' '
                  . $this->faker->randomElement(self::$tenDemNam) . ' '
                  . $this->faker->randomElement(self::$tenNam);
        } elseif ($gioiTinh === 'nu') {
            $name = $this->faker->randomElement(self::$hoNam) . ' '
                  . $this->faker->randomElement(self::$tenDemNu) . ' '
                  . $this->faker->randomElement(self::$tenNu);
        } else {
            $name = $this->faker->randomElement(self::$hoNam) . ' '
                  . $this->faker->randomElement(self::$tenDemNam) . ' '
                  . $this->faker->randomElement(self::$tenNam);
        }

        // Số điện thoại Việt Nam thực tế
        $dauSo = $this->faker->randomElement([
            '032', '033', '034', '035', '036', '037', '038', '039', // Viettel
            '070', '076', '077', '078', '079',                       // Mobifone
            '056', '058',                                             // Vietnamobile
            '081', '082', '083', '084', '085',                       // Vinaphone
        ]);
        $phone = $dauSo . $this->faker->numerify('#######');

        return [
            'name'           => $name,
            'email'          => $this->faker->unique()->safeEmail(),
            'password'       => Hash::make('password'),
            'phone'          => $phone,
            'ngay_sinh'      => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'gioi_tinh'      => $gioiTinh,
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),
        ];
    }

    /**
     * State: User nam
     */
    public function nam(): static
    {
        return $this->state(function () {
            $name = $this->faker->randomElement(self::$hoNam) . ' '
                  . $this->faker->randomElement(self::$tenDemNam) . ' '
                  . $this->faker->randomElement(self::$tenNam);
            return [
                'name'      => $name,
                'gioi_tinh' => 'nam',
            ];
        });
    }

    /**
     * State: User nữ
     */
    public function nu(): static
    {
        return $this->state(function () {
            $name = $this->faker->randomElement(self::$hoNam) . ' '
                  . $this->faker->randomElement(self::$tenDemNu) . ' '
                  . $this->faker->randomElement(self::$tenNu);
            return [
                'name'      => $name,
                'gioi_tinh' => 'nu',
            ];
        });
    }

    /**
     * State: User chưa xác thực email
     */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    /**
     * State: User với mật khẩu tùy chỉnh
     */
    public function withPassword(string $password): static
    {
        return $this->state(['password' => Hash::make($password)]);
    }

    /**
     * State: User trẻ (18-25 tuổi)
     */
    public function young(): static
    {
        return $this->state(function () {
            return [
                'ngay_sinh' => $this->faker
                    ->dateTimeBetween('-25 years', '-18 years')
                    ->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: User trung niên (26-40 tuổi)
     */
    public function middleAge(): static
    {
        return $this->state(function () {
            return [
                'ngay_sinh' => $this->faker
                    ->dateTimeBetween('-40 years', '-26 years')
                    ->format('Y-m-d'),
            ];
        });
    }

    /**
     * State: Admin (email cố định để test)
     */
    public function admin(): static
    {
        return $this->state([
            'name'     => 'Admin Monexa',
            'email'    => 'admin@monexa.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}

