<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\MoneyWallet;
use App\Models\Budgets;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $uid = 12; // tài khoản test@monexa.test
        $now = now();

        // Bước 1: danh mục cha THU
        $thu = Category::create([
            'user_id' => $uid, 'ten_danh_muc' => 'Thu nhập',
            'loai_danh_muc' => 'THU', 'danh_muc_cha_id' => null, 'trang_thai' => true,
        ]);

        // Bước 2: danh mục cha CHI
        $chi = Category::create([
            'user_id' => $uid, 'ten_danh_muc' => 'Chi tiêu',
            'loai_danh_muc' => 'CHI', 'danh_muc_cha_id' => null, 'trang_thai' => true,
        ]);

        // Bước 3: danh mục con CHI -> ép id = 12 (đang trống)
        if (!Category::find(12)) {
            DB::table('categories')->insert([
                'id' => 12, 'user_id' => $uid, 'ten_danh_muc' => 'Ăn uống',
                'loai_danh_muc' => 'CHI', 'danh_muc_cha_id' => $chi->id,
                'bieu_tuong' => 'money.png', 'trang_thai' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $conChiId = 12;
        } else {
            $conChiId = Category::create([
                'user_id' => $uid, 'ten_danh_muc' => 'Ăn uống',
                'loai_danh_muc' => 'CHI', 'danh_muc_cha_id' => $chi->id, 'trang_thai' => true,
            ])->id;
        }

        // Bước 4: danh mục con THU (id tự tăng vì id=8 đã bị chiếm)
        $luong = Category::create([
            'user_id' => $uid, 'ten_danh_muc' => 'Lương',
            'loai_danh_muc' => 'THU', 'danh_muc_cha_id' => $thu->id, 'trang_thai' => true,
        ]);

        // Bước 5: ví tiền
        $vi = MoneyWallet::create([
            'user_id' => $uid, 'ten_vi' => 'Ví chính', 'loai_vi' => 'tien_mat',
            'so_du' => 10000000, 'so_du_ban_dau' => 10000000, 'trang_thai' => 'active',
        ]);

        // Bước 6: ngân sách cho danh mục id=12, active + còn hiệu lực tháng này
        $ns = Budgets::create([
            'user_id' => $uid, 'category_id' => $conChiId,
            'ten_ngan_sach' => 'Ngân sách Ăn uống',
            'ngan_sach_goc' => 5000000, 'so_du' => 5000000, 'trang_thai' => true,
            'loai_thoi_gian' => 'thang',
            'ngay_bat_dau' => $now->copy()->startOfMonth()->toDateString(),
            'ngay_ket_thuc' => $now->copy()->endOfMonth()->toDateString(),
            'tu_dong_reset' => true, 'da_het_han' => false,
        ]);

        $this->command->info('=== SEED DONE (user 12) ===');
        $this->command->info('Cha THU (Thu nhap) id = ' . $thu->id);
        $this->command->info('Cha CHI (Chi tieu) id = ' . $chi->id);
        $this->command->info('Con CHI (An uong)  id = ' . $conChiId . '  <-- danh muc CHI con');
        $this->command->info('Con THU (Luong)    id = ' . $luong->id . '  <-- DUNG ID NAY THAY CHO 8');
        $this->command->info('Vi chinh           id = ' . $vi->id . ' | so_du=' . $vi->so_du);
        $this->command->info('Ngan sach          id = ' . $ns->id . ' | category_id=' . $conChiId . ' | so_du=' . $ns->so_du);
    }
}
