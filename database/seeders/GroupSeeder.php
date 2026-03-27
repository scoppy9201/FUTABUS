<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\SplitGroup;
use App\Models\SplitGroupMember;
use App\Models\GroupInvitation;
use App\Models\GroupBalanceProposal;
use App\Models\GroupBalanceSplit;
use App\Models\GroupApproval;
use App\Models\GroupExpenseProposal;
use App\Models\GroupExpenseSplit;
use App\Models\GroupExpenseDebt;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Bắt đầu seed dữ liệu nhóm chia tiền...');

        // ── 1. TẠO USERS TEST ─────────────────────────────────────────
        $this->command->info('   👤 Tạo users...');

        $users = $this->createUsers();

        // ── 2. TẠO CATEGORIES CHO TỪNG USER ──────────────────────────
        $this->command->info('   📂 Tạo categories...');

        foreach ($users as $user) {
            $this->createCategoriesForUser($user);
        }

        // ── 3. TẠO TRANSACTIONS (số dư) ──────────────────────────────
        $this->command->info('   💰 Tạo transactions...');

        $this->createTransactions($users);

        // ── 4. NHÓM 1: GIA ĐÌNH (chế độ balance) ────────────────────
        $this->command->info('   👨‍👩‍👧 Tạo nhóm Gia đình (chế độ balance)...');

        $this->createFamilyGroup($users);

        // ── 5. NHÓM 2: SINH VIÊN (chế độ expense + debt) ────────────
        $this->command->info('   🎓 Tạo nhóm Sinh viên (chế độ expense)...');

        $this->createStudentGroup($users);

        // ── 6. NHÓM 3: DU LỊCH (chế độ both) ───────────────────────
        $this->command->info('   ✈️  Tạo nhóm Du lịch (chế độ both)...');

        $this->createTripGroup($users);

        $this->command->info('');
        $this->command->info('✅ Seed xong! Tài khoản test:');
        $this->command->table(
            ['Tên', 'Email', 'Mật khẩu', 'Vai trò'],
            [
                ['Nguyễn Văn Bố', 'bo@test.com',   'Test@1234', 'Admin nhóm Gia đình'],
                ['Trần Thị Mẹ',  'me@test.com',   'Test@1234', 'Member nhóm Gia đình'],
                ['Lê Văn Con',   'con@test.com',  'Test@1234', 'Member nhóm Gia đình'],
                ['Phạm Văn An',  'an@test.com',   'Test@1234', 'Admin nhóm Sinh viên'],
                ['Hoàng Thị Bình','binh@test.com','Test@1234', 'Member nhóm Sinh viên'],
                ['Đỗ Văn Cường', 'cuong@test.com','Test@1234', 'Member nhóm Sinh viên'],
            ]
        );
    }

    // ── USERS ──────────────────────────────────────────────────────────
    private function createUsers(): array
    {
        $userData = [
            ['name' => 'Nguyễn Văn Bố',   'email' => 'bo@test.com'],
            ['name' => 'Trần Thị Mẹ',     'email' => 'me@test.com'],
            ['name' => 'Lê Văn Con',       'email' => 'con@test.com'],
            ['name' => 'Phạm Văn An',      'email' => 'an@test.com'],
            ['name' => 'Hoàng Thị Bình',   'email' => 'binh@test.com'],
            ['name' => 'Đỗ Văn Cường',     'email' => 'cuong@test.com'],
        ];

        $users = [];
        foreach ($userData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'       => $data['name'],
                    'password'   => Hash::make('Test@1234'),
                    'created_at' => now()->subDays(rand(30, 90)),
                    'updated_at' => now(),
                ]
            );
        }

        return $users;
    }

    // ── CATEGORIES ────────────────────────────────────────────────────
    private function createCategoriesForUser(User $user): void
    {
        // Danh mục cha THU
        $thu = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Thu nhập', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'money.png', 'trang_thai' => true]
        );

        // Danh mục cha CHI
        $chi = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Chi tiêu', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'budget.png', 'trang_thai' => true]
        );

        // Danh mục con THU
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Lương', 'danh_muc_cha_id' => $thu->id],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'money.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Thưởng', 'danh_muc_cha_id' => $thu->id],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'profits.png', 'trang_thai' => true]
        );

        // Danh mục con CHI
        foreach (['Ăn uống', 'Điện nước', 'Di chuyển', 'Giải trí', 'Mua sắm'] as $name) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'ten_danh_muc' => $name, 'danh_muc_cha_id' => $chi->id],
                ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'budget.png', 'trang_thai' => true]
            );
        }

        // Danh mục nhóm (tạo sẵn để GroupExpenseController dùng)
        $nhom = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Nhóm', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Chi nhóm', 'danh_muc_cha_id' => $nhom->id],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Trả nợ nhóm', 'danh_muc_cha_id' => $nhom->id],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );

        $nhomThu = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Nhóm Thu', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Thu nợ nhóm', 'danh_muc_cha_id' => $nhomThu->id],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Điều chỉnh nhóm (THU)', 'danh_muc_cha_id' => $nhomThu->id],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        $chiDieuChinh = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Nhóm Chi', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Điều chỉnh nhóm (CHI)', 'danh_muc_cha_id' => $chiDieuChinh->id],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
    }

    // ── TRANSACTIONS (tạo số dư khác nhau) ────────────────────────────
    private function createTransactions(array $users): void
    {
        // [user_index, THU, CHI] → số dư = THU - CHI
        $balanceData = [
            0 => ['thu' => 25_000_000, 'chi' => 18_000_000], // Bố: +7tr
            1 => ['thu' => 20_000_000, 'chi' => 15_500_000], // Mẹ: +4.5tr
            2 => ['thu' => 8_000_000,  'chi' => 7_500_000],  // Con: +500k
            3 => ['thu' => 12_000_000, 'chi' => 9_000_000],  // An: +3tr
            4 => ['thu' => 10_000_000, 'chi' => 8_200_000],  // Bình: +1.8tr
            5 => ['thu' => 9_000_000,  'chi' => 7_800_000],  // Cường: +1.2tr
        ];

        foreach ($users as $i => $user) {
            $data = $balanceData[$i] ?? ['thu' => 10_000_000, 'chi' => 8_000_000];

            // Lấy category lương của user
            $catThu = Category::where('user_id', $user->id)
                ->where('ten_danh_muc', 'Lương')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            $catChi = Category::where('user_id', $user->id)
                ->where('ten_danh_muc', 'Ăn uống')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            if (!$catThu || !$catChi) continue;

            // Chỉ tạo nếu chưa có transaction
            if (Transaction::where('user_id', $user->id)->exists()) continue;

            // THU: 2 tháng lương
            Transaction::create([
                'user_id'                => $user->id,
                'category_id'            => $catThu->id,
                'loai_giao_dich'         => 'THU',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $data['thu'],
                'ngay_giao_dich'         => now()->subDays(60)->toDateString(),
                'ghi_chu'                => 'Lương tháng ' . now()->subMonths(2)->format('n/Y'),
            ]);

            // CHI: chi tiêu hàng tháng
            Transaction::create([
                'user_id'                => $user->id,
                'category_id'            => $catChi->id,
                'loai_giao_dich'         => 'CHI',
                'phuong_thuc_thanh_toan' => 'Tiền mặt',
                'so_tien'                => $data['chi'],
                'ngay_giao_dich'         => now()->subDays(30)->toDateString(),
                'ghi_chu'                => 'Chi tiêu sinh hoạt tháng ' . now()->subMonth()->format('n/Y'),
            ]);
        }
    }

    // ── NHÓM 1: GIA ĐÌNH ──────────────────────────────────────────────
    private function createFamilyGroup(array $users): void
    {
        [$bo, $me, $con] = $users; // index 0, 1, 2

        // Tạo nhóm
        $group = SplitGroup::firstOrCreate(
            ['ten_nhom' => 'Gia đình Nguyễn', 'created_by' => $bo->id],
            [
                'mo_ta'      => 'Quản lý tài chính gia đình, phân phối chi tiêu hàng tháng',
                'che_do'     => 'balance',
                'hien_so_du' => true,
                'trang_thai' => 'active',
                'created_at' => now()->subDays(45),
            ]
        );

        // Thêm thành viên
        $this->addMember($group, $bo,  'admin');
        $this->addMember($group, $me,  'member');
        $this->addMember($group, $con, 'member');

        // ── Đề xuất 1: Đã thực hiện (approved) ──
        $p1 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $bo->id, 'mo_ta' => 'Phân phối tháng 1/2026'],
            [
                'tong_so_du' => 12_000_000,
                'trang_thai' => 'approved',
                'executed_at'=> now()->subDays(30),
                'created_at' => now()->subDays(32),
            ]
        );

        $splitData1 = [
            [$bo->id,  7_000_000, 7_000_000],  // Bố: không đổi
            [$me->id,  4_000_000, 3_500_000],  // Mẹ: giảm 500k
            [$con->id, 1_000_000, 1_500_000],  // Con: tăng 500k
        ];
        $this->createBalanceSplits($p1, $splitData1, 12_000_000);
        $this->createApprovals($p1, [$bo, $me, $con], GroupBalanceProposal::class);

        // ── Đề xuất 2: Đang chờ duyệt (pending) ──
        $currentBalance = [
            $bo->id  => 7_000_000,
            $me->id  => 4_500_000,
            $con->id => 500_000,
        ];

        $p2 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $bo->id, 'mo_ta' => 'Phân phối tháng 3/2026'],
            [
                'tong_so_du' => 12_000_000,
                'trang_thai' => 'pending',
                'created_at' => now()->subDays(1),
            ]
        );

        $splitData2 = [
            [$bo->id,  7_000_000, 6_000_000],  // Bố giảm 1tr
            [$me->id,  4_500_000, 4_500_000],  // Mẹ không đổi
            [$con->id, 500_000,   1_500_000],  // Con tăng 1tr
        ];
        $this->createBalanceSplits($p2, $splitData2, 12_000_000);

        // Chỉ Bố đã approve, Mẹ và Con chưa
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p2->id, 'user_id' => $bo->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subHours(20)]
        );

        // ── Đề xuất 3: Bị từ chối (rejected) ──
        $p3 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $bo->id, 'mo_ta' => 'Thử nghiệm phân phối mới'],
            [
                'tong_so_du' => 12_000_000,
                'trang_thai' => 'rejected',
                'created_at' => now()->subDays(15),
            ]
        );
        $this->createBalanceSplits($p3, [
            [$bo->id,  3_000_000, 3_000_000],
            [$me->id,  4_000_000, 4_000_000],
            [$con->id, 5_000_000, 5_000_000],
        ], 12_000_000);
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p3->id, 'user_id' => $bo->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(15)]
        );
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p3->id, 'user_id' => $me->id],
            ['quyet_dinh' => 'rejected', 'ghi_chu' => 'Tỷ lệ không hợp lý', 'created_at' => now()->subDays(14)]
        );
    }

    // ── NHÓM 2: SINH VIÊN ─────────────────────────────────────────────
    private function createStudentGroup(array $users): void
    {
        [$bo, $me, $con, $an, $binh, $cuong] = $users;

        $group = SplitGroup::firstOrCreate(
            ['ten_nhom' => 'Phòng trọ 3 người', 'created_by' => $an->id],
            [
                'mo_ta'      => 'Chia sẻ chi phí phòng trọ: điện, nước, mạng internet',
                'che_do'     => 'expense',
                'hien_so_du' => false,
                'trang_thai' => 'active',
                'created_at' => now()->subDays(60),
            ]
        );

        $this->addMember($group, $an,    'admin');
        $this->addMember($group, $binh,  'member');
        $this->addMember($group, $cuong, 'member');

        // Lấy category chi nhóm
        $getCat = fn($user) => Category::where('user_id', $user->id)
            ->where('ten_danh_muc', 'Chi nhóm')
            ->whereNotNull('danh_muc_cha_id')
            ->first();

        // ── Khoản chi 1: Tiền phòng tháng 2 (approved, chia đều) ──
        $p1 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $an->id, 'mo_ta' => 'Tiền phòng tháng 2/2026'],
            [
                'category_id' => $getCat($an)?->id,
                'tong_tien'   => 3_000_000,
                'ngay_chi'    => now()->subDays(45)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'approved',
                'executed_at' => now()->subDays(44),
                'created_at'  => now()->subDays(46),
            ]
        );

        $this->createExpenseSplits($p1, [
            [$an->id,    1_000_000, null],
            [$binh->id,  1_000_000, null],
            [$cuong->id, 1_000_000, null],
        ], [$an, $binh, $cuong], $getCat);
        $this->createApprovals($p1, [$an, $binh, $cuong], GroupExpenseProposal::class);

        // ── Khoản chi 2: Tiền điện tháng 2 (approved, custom) ──
        $p2 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $binh->id, 'mo_ta' => 'Tiền điện tháng 2/2026'],
            [
                'category_id' => $getCat($binh)?->id,
                'tong_tien'   => 450_000,
                'ngay_chi'    => now()->subDays(40)->toDateString(),
                'kieu_chia'   => 'custom',
                'trang_thai'  => 'approved',
                'executed_at' => now()->subDays(39),
                'created_at'  => now()->subDays(41),
            ]
        );
        $this->createExpenseSplits($p2, [
            [$an->id,    200_000, null],  // An dùng máy tính nhiều hơn
            [$binh->id,  150_000, null],
            [$cuong->id, 100_000, null],
        ], [$an, $binh, $cuong], $getCat);
        $this->createApprovals($p2, [$an, $binh, $cuong], GroupExpenseProposal::class);

        // ── Khoản chi 3: Tiền mạng tháng 3 (pending, chia theo %) ──
        $p3 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $an->id, 'mo_ta' => 'Internet + điện thoại tháng 3/2026'],
            [
                'category_id' => $getCat($an)?->id,
                'tong_tien'   => 300_000,
                'ngay_chi'    => now()->subDays(3)->toDateString(),
                'kieu_chia'   => 'percentage',
                'trang_thai'  => 'pending',
                'created_at'  => now()->subDays(3),
            ]
        );
        $this->createExpenseSplitsWithRate($p3, [
            [$an->id,    100_000, 33.33],
            [$binh->id,  100_000, 33.33],
            [$cuong->id, 100_000, 33.34],
        ]);
        // An đã approve, Bình và Cường chưa
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p3->id, 'user_id' => $an->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(2)]
        );

        // ── Khoản chi 4: Bị từ chối ──
        $p4 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $cuong->id, 'mo_ta' => 'Mua máy lọc không khí chung'],
            [
                'tong_tien'   => 2_000_000,
                'ngay_chi'    => now()->subDays(20)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'rejected',
                'created_at'  => now()->subDays(22),
            ]
        );
        $this->createExpenseSplits($p4, [
            [$an->id,    666_667, null],
            [$binh->id,  666_667, null],
            [$cuong->id, 666_666, null],
        ], [$an, $binh, $cuong], $getCat);
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p4->id, 'user_id' => $cuong->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(21)]
        );
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p4->id, 'user_id' => $an->id],
            ['quyet_dinh' => 'rejected', 'ghi_chu' => 'Không cần thiết', 'created_at' => now()->subDays(21)]
        );

        // ── Ghi nợ thẳng ──
        // Cường nợ An tiền đi chợ
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $an->id, 'nguoi_no_id' => $cuong->id, 'so_tien' => 150_000],
            ['ghi_chu' => 'Đi chợ hộ tuần trước', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(7)]
        );
        // Bình nợ Cường tiền grab
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $cuong->id, 'nguoi_no_id' => $binh->id, 'so_tien' => 80_000],
            ['ghi_chu' => 'Grab đi làm hôm qua', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
        // An nợ Bình tiền cafe
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $binh->id, 'nguoi_no_id' => $an->id, 'so_tien' => 55_000],
            ['ghi_chu' => 'Cafe sáng thứ 6', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(2)]
        );
        // Đã settled
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $an->id, 'nguoi_no_id' => $binh->id, 'so_tien' => 200_000, 'trang_thai' => 'settled'],
            ['ghi_chu' => 'Tiền thuốc tháng trước', 'settled_at' => now()->subDays(10), 'created_at' => now()->subDays(15)]
        );
    }

    // ── NHÓM 3: DU LỊCH ──────────────────────────────────────────────
    private function createTripGroup(array $users): void
    {
        [$bo, $me, $con, $an, $binh, $cuong] = $users;

        $group = SplitGroup::firstOrCreate(
            ['ten_nhom' => 'Du lịch Đà Lạt 2026', 'created_by' => $an->id],
            [
                'mo_ta'      => 'Chuyến đi Đà Lạt tháng 4/2026 — 6 người, 3 ngày 2 đêm',
                'che_do'     => 'both',
                'hien_so_du' => false,
                'trang_thai' => 'active',
                'created_at' => now()->subDays(10),
            ]
        );

        // Tất cả 6 user đều tham gia
        $this->addMember($group, $an,    'admin');
        $this->addMember($group, $binh,  'member');
        $this->addMember($group, $cuong, 'member');
        $this->addMember($group, $bo,    'member');
        $this->addMember($group, $me,    'member');
        $this->addMember($group, $con,   'member');

        // Lời mời pending (chưa chấp nhận)
        GroupInvitation::firstOrCreate(
            ['group_id' => $group->id, 'email' => 'friend@gmail.com'],
            [
                'invited_by' => $an->id,
                'token'      => \Illuminate\Support\Str::random(64),
                'trang_thai' => 'pending',
                'expires_at' => now()->addHours(36),
                'created_at' => now()->subHours(12),
            ]
        );

        $getCat = fn($user) => Category::where('user_id', $user->id)
            ->where('ten_danh_muc', 'Chi nhóm')
            ->whereNotNull('danh_muc_cha_id')
            ->first();

        // ── Khoản chi: Khách sạn (approved, chia đều) ──
        $allMembers = [$an, $binh, $cuong, $bo, $me, $con];
        $perPerson  = 500_000;

        $p1 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $an->id, 'mo_ta' => 'Khách sạn 2 đêm'],
            [
                'tong_tien'   => $perPerson * 6,
                'ngay_chi'    => now()->addDays(14)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'approved',
                'executed_at' => now()->subDays(2),
                'created_at'  => now()->subDays(5),
            ]
        );
        $splits = [];
        foreach ($allMembers as $m) {
            $splits[] = [$m->id, $perPerson, null];
        }
        $this->createExpenseSplits($p1, $splits, $allMembers, $getCat);
        $this->createApprovals($p1, $allMembers, GroupExpenseProposal::class);

        // ── Khoản chi: Thuê xe (pending, chờ 3 người duyệt) ──
        $p2 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'proposed_by' => $binh->id, 'mo_ta' => 'Thuê xe 7 chỗ khứ hồi'],
            [
                'tong_tien'   => 2_400_000,
                'ngay_chi'    => now()->addDays(15)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'pending',
                'created_at'  => now()->subDays(1),
            ]
        );
        $splits2 = array_map(fn($m) => [$m->id, 400_000, null], $allMembers);
        $this->createExpenseSplitsRaw($p2, $splits2);
        // 3 người đã approve
        foreach ([$an, $binh, $bo] as $approver) {
            GroupApproval::firstOrCreate(
                ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p2->id, 'user_id' => $approver->id],
                ['quyet_dinh' => 'approved', 'created_at' => now()->subHours(rand(2, 20))]
            );
        }

        // ── Ghi nợ: Bố ứng tiền mua nước / đồ ăn ──
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $bo->id, 'nguoi_no_id' => $an->id, 'so_tien' => 120_000],
            ['ghi_chu' => 'An nợ Bố tiền mua snack', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $bo->id, 'nguoi_no_id' => $me->id, 'so_tien' => 85_000],
            ['ghi_chu' => 'Mẹ nợ Bố tiền nước suối', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
    }

    // ── HELPER METHODS ─────────────────────────────────────────────────

    private function addMember(SplitGroup $group, User $user, string $role): void
    {
        SplitGroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => $user->id],
            [
                'vai_tro'    => $role,
                'trang_thai' => 'active',
                'joined_at'  => $group->created_at->addDays(rand(0, 3)),
                'created_at' => $group->created_at->addDays(rand(0, 3)),
            ]
        );
    }

    private function createBalanceSplits(GroupBalanceProposal $proposal, array $data, float $total): void
    {
        foreach ($data as [$userId, $soDuCu, $soDuMoi]) {
            GroupBalanceSplit::firstOrCreate(
                ['proposal_id' => $proposal->id, 'user_id' => $userId],
                [
                    'so_du_cu'          => $soDuCu,
                    'so_du_moi'         => $soDuMoi,
                    'chenh_lech'        => $soDuMoi - $soDuCu,
                    'trang_thai_dong_y' => $proposal->trang_thai === 'approved' ? 'approved' : 'pending',
                    'responded_at'      => $proposal->trang_thai === 'approved' ? $proposal->executed_at : null,
                ]
            );
        }
    }

    private function createExpenseSplits(
        GroupExpenseProposal $proposal,
        array $data,
        array $users,
        callable $getCat
    ): void {
        foreach ($data as [$userId, $soTien, $tyLe]) {
            $user = collect($users)->firstWhere('id', $userId);
            $cat  = $user ? $getCat($user) : null;

            $transactionId = null;
            if ($proposal->trang_thai === 'approved' && $cat) {
                $tx = Transaction::firstOrCreate(
                    [
                        'user_id'       => $userId,
                        'category_id'   => $cat->id,
                        'so_tien'       => $soTien,
                        'ngay_giao_dich'=> $proposal->ngay_chi,
                        'ghi_chu'       => 'Chi nhóm: ' . $proposal->mo_ta,
                    ],
                    [
                        'loai_giao_dich'         => 'CHI',
                        'phuong_thuc_thanh_toan' => 'Tiền mặt',
                    ]
                );
                $transactionId = $tx->id;
            }

            GroupExpenseSplit::firstOrCreate(
                ['proposal_id' => $proposal->id, 'user_id' => $userId],
                [
                    'so_tien'           => $soTien,
                    'ty_le'             => $tyLe,
                    'transaction_id'    => $transactionId,
                    'trang_thai_dong_y' => $proposal->trang_thai === 'approved' ? 'approved' : 'pending',
                    'responded_at'      => $proposal->trang_thai === 'approved' ? $proposal->executed_at : null,
                ]
            );
        }
    }

    private function createExpenseSplitsWithRate(GroupExpenseProposal $proposal, array $data): void
    {
        foreach ($data as [$userId, $soTien, $tyLe]) {
            GroupExpenseSplit::firstOrCreate(
                ['proposal_id' => $proposal->id, 'user_id' => $userId],
                [
                    'so_tien'           => $soTien,
                    'ty_le'             => $tyLe,
                    'transaction_id'    => null,
                    'trang_thai_dong_y' => 'pending',
                ]
            );
        }
    }

    private function createExpenseSplitsRaw(GroupExpenseProposal $proposal, array $data): void
    {
        foreach ($data as [$userId, $soTien, $tyLe]) {
            GroupExpenseSplit::firstOrCreate(
                ['proposal_id' => $proposal->id, 'user_id' => $userId],
                [
                    'so_tien'           => $soTien,
                    'ty_le'             => $tyLe,
                    'trang_thai_dong_y' => 'pending',
                ]
            );
        }
    }

    private function createApprovals(
        GroupBalanceProposal|GroupExpenseProposal $proposal,
        array $users,
        string $type
    ): void {
        foreach ($users as $user) {
            GroupApproval::firstOrCreate(
                ['approvable_type' => $type, 'approvable_id' => $proposal->id, 'user_id' => $user->id],
                [
                    'quyet_dinh' => 'approved',
                    'created_at' => $proposal->executed_at ?? now()->subDays(rand(1, 5)),
                ]
            );
        }
    }
}
