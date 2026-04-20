<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $this->command->info('Bắt đầu seed dữ liệu nhóm chia tiền...');

        $this->command->info(' Chuẩn bị users...');
        $users = $this->prepareUsers();

        $this->command->info(' Tạo categories...');
        foreach ($users as $user) {
            $this->createCategoriesForUser($user);
        }

        $this->command->info(' Tạo transactions...');
        $this->createTransactions($users);

        $this->command->info('Tạo nhóm Gia đình (balance)...');
        $this->createFamilyGroup($users);

        $this->command->info('Tạo nhóm Phòng trọ (expense)...');
        $this->createStudentGroup($users);

        $this->command->info('Tạo nhóm Du lịch (both)...');
        $this->createTripGroup($users);

        $this->command->info('');
        $this->command->info('Seed xong! Tài khoản test:');
        $this->command->table(
            ['Tên', 'Email', 'Mật khẩu', 'Vai trò'],
            [
                ['Hưng Mạnh (cũ)',    'buimanhhung3105@gmail.com', '(giữ nguyên)', 'User thật id=1'],
                ['Nguyễn Văn Bố',     'bo@test.com',               'Test@1234',    'Admin nhóm Gia đình'],
                ['Trần Thị Mẹ',       'me@test.com',               'Test@1234',    'Member nhóm Gia đình'],
                ['Lê Văn Con',        'con@test.com',              'Test@1234',    'Member nhóm Gia đình'],
                ['Phạm Văn An',       'an@test.com',               'Test@1234',    'Admin nhóm Sinh viên'],
                ['Hoàng Thị Bình',    'binh@test.com',             'Test@1234',    'Member nhóm Sinh viên'],
                ['Đỗ Văn Cường',      'cuong@test.com',            'Test@1234',    'Member nhóm Sinh viên'],
            ]
        );
    }

    // USERS 
    private function prepareUsers(): array
    {
        // Lấy user thật id=1 (Hưng Mạnh)
        $hungManh = User::find(1);

        // Tạo thêm 6 user test mới nếu chưa có
        $testUsers = [
            ['name' => 'Nguyễn Văn Bố',   'email' => 'bo@test.com'],
            ['name' => 'Trần Thị Mẹ',     'email' => 'me@test.com'],
            ['name' => 'Lê Văn Con',       'email' => 'con@test.com'],
            ['name' => 'Phạm Văn An',      'email' => 'an@test.com'],
            ['name' => 'Hoàng Thị Bình',   'email' => 'binh@test.com'],
            ['name' => 'Đỗ Văn Cường',     'email' => 'cuong@test.com'],
        ];

        $users = $hungManh ? [$hungManh] : [];

        foreach ($testUsers as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('Test@1234'),
                ]
            );
        }

        return $users;
    }

    // CATEGORIES 
    private function createCategoriesForUser(User $user): void
    {
        // Danh mục cha THU
        $thu = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Thu nhập', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'money.png', 'trang_thai' => true]
        );

        // Danh mục con THU
        foreach (['Lương', 'Thưởng'] as $ten) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'ten_danh_muc' => $ten, 'danh_muc_cha_id' => $thu->id],
                ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'money.png', 'trang_thai' => true]
            );
        }

        // Danh mục cha CHI
        $chi = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Chi tiêu', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'budget.png', 'trang_thai' => true]
        );

        // Danh mục con CHI sinh hoạt
        foreach (['Ăn uống', 'Điện nước', 'Di chuyển', 'Giải trí', 'Mua sắm'] as $ten) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'ten_danh_muc' => $ten, 'danh_muc_cha_id' => $chi->id],
                ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'budget.png', 'trang_thai' => true]
            );
        }

        // Danh mục nhóm CHI (dùng cho GroupExpenseController)
        $nhomChi = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Nhóm Chi', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        foreach (['Chi nhóm', 'Trả nợ nhóm', 'Điều chỉnh nhóm (CHI)'] as $ten) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'ten_danh_muc' => $ten, 'danh_muc_cha_id' => $nhomChi->id],
                ['loai_danh_muc' => 'CHI', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
            );
        }

        // Danh mục nhóm THU (dùng cho GroupExpenseController)
        $nhomThu = Category::firstOrCreate(
            ['user_id' => $user->id, 'ten_danh_muc' => 'Nhóm Thu', 'danh_muc_cha_id' => null],
            ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
        );
        foreach (['Thu nợ nhóm', 'Điều chỉnh nhóm (THU)'] as $ten) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'ten_danh_muc' => $ten, 'danh_muc_cha_id' => $nhomThu->id],
                ['loai_danh_muc' => 'THU', 'bieu_tuong' => 'category.png', 'trang_thai' => true]
            );
        }
    }

    // TRANSACTIONS 
    private function createTransactions(array $users): void
    {
        // Số dư mỗi user: thu - chi
        $balanceData = [
            'buimanhhung3105@gmail.com' => ['thu' => 20_000_000, 'chi' => 14_720_000],
            'bo@test.com'               => ['thu' => 25_000_000, 'chi' => 18_000_000],
            'me@test.com'               => ['thu' => 20_000_000, 'chi' => 15_500_000],
            'con@test.com'              => ['thu' => 8_000_000,  'chi' => 7_500_000],
            'an@test.com'               => ['thu' => 12_000_000, 'chi' => 9_000_000],
            'binh@test.com'             => ['thu' => 10_000_000, 'chi' => 8_200_000],
            'cuong@test.com'            => ['thu' => 9_000_000,  'chi' => 7_800_000],
        ];

        foreach ($users as $user) {
            // Bỏ qua nếu đã có transaction
            if (Transaction::where('user_id', $user->id)->exists()) continue;

            $data = $balanceData[$user->email] ?? ['thu' => 10_000_000, 'chi' => 8_000_000];

            $catLuong = Category::where('user_id', $user->id)
                ->where('ten_danh_muc', 'Lương')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            $catAnUong = Category::where('user_id', $user->id)
                ->where('ten_danh_muc', 'Ăn uống')
                ->whereNotNull('danh_muc_cha_id')
                ->first();

            if (!$catLuong || !$catAnUong) continue;

            Transaction::create([
                'user_id'                => $user->id,
                'category_id'            => $catLuong->id,
                'loai_giao_dich'         => 'THU',
                'phuong_thuc_thanh_toan' => 'Chuyển khoản',
                'so_tien'                => $data['thu'],
                'ngay_giao_dich'         => now()->subDays(30)->toDateString(),
                'ghi_chu'                => 'Lương tháng ' . now()->subMonth()->format('n/Y'),
            ]);

            Transaction::create([
                'user_id'                => $user->id,
                'category_id'            => $catAnUong->id,
                'loai_giao_dich'         => 'CHI',
                'phuong_thuc_thanh_toan' => 'Tiền mặt',
                'so_tien'                => $data['chi'],
                'ngay_giao_dich'         => now()->subDays(15)->toDateString(),
                'ghi_chu'                => 'Chi tiêu tháng ' . now()->subMonth()->format('n/Y'),
            ]);
        }
    }

    // NHÓM 1: GIA ĐÌNH (balance)
    private function createFamilyGroup(array $users): void
    {
        $bo  = collect($users)->firstWhere('email', 'bo@test.com');
        $me  = collect($users)->firstWhere('email', 'me@test.com');
        $con = collect($users)->firstWhere('email', 'con@test.com');

        if (!$bo || !$me || !$con) return;

        $group = SplitGroup::firstOrCreate(
            ['ten_nhom' => 'Gia đình Nguyễn', 'created_by' => $bo->id],
            [
                'mo_ta'      => 'Quản lý tài chính gia đình hàng tháng',
                'che_do'     => 'balance',
                'hien_so_du' => true,
                'trang_thai' => 'active',
                'created_at' => now()->subDays(45),
            ]
        );

        $this->addMember($group, $bo,  'admin');
        $this->addMember($group, $me,  'member');
        $this->addMember($group, $con, 'member');

        // Đề xuất 1: đã approved
        $p1 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Phân phối tháng 1/2026'],
            [
                'proposed_by' => $bo->id,
                'tong_so_du'  => 12_000_000,
                'trang_thai'  => 'approved',
                'executed_at' => now()->subDays(30),
                'created_at'  => now()->subDays(32),
            ]
        );
        $this->createBalanceSplits($p1, [
            [$bo->id,  7_000_000, 7_000_000],
            [$me->id,  4_000_000, 3_500_000],
            [$con->id, 1_000_000, 1_500_000],
        ]);
        $this->createApprovals($p1, [$bo, $me, $con], GroupBalanceProposal::class);

        // Đề xuất 2: đang pending (chỉ Bố duyệt)
        $p2 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Phân phối tháng 3/2026'],
            [
                'proposed_by' => $bo->id,
                'tong_so_du'  => 12_000_000,
                'trang_thai'  => 'pending',
                'created_at'  => now()->subDays(1),
            ]
        );
        $this->createBalanceSplits($p2, [
            [$bo->id,  7_000_000, 6_000_000],
            [$me->id,  4_500_000, 4_500_000],
            [$con->id, 500_000,   1_500_000],
        ]);
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p2->id, 'user_id' => $bo->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subHours(20)]
        );

        // Đề xuất 3: bị rejected (Mẹ từ chối)
        $p3 = GroupBalanceProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Thử nghiệm phân phối mới'],
            [
                'proposed_by' => $bo->id,
                'tong_so_du'  => 12_000_000,
                'trang_thai'  => 'rejected',
                'created_at'  => now()->subDays(15),
            ]
        );
        $this->createBalanceSplits($p3, [
            [$bo->id,  3_000_000, 3_000_000],
            [$me->id,  4_000_000, 4_000_000],
            [$con->id, 5_000_000, 5_000_000],
        ]);
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p3->id, 'user_id' => $bo->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(15)]
        );
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupBalanceProposal::class, 'approvable_id' => $p3->id, 'user_id' => $me->id],
            ['quyet_dinh' => 'rejected', 'ghi_chu' => 'Tỷ lệ không hợp lý', 'created_at' => now()->subDays(14)]
        );
    }

    // NHÓM 2: PHÒNG TRỌ SINH VIÊN (expense) 
    private function createStudentGroup(array $users): void
    {
        $an    = collect($users)->firstWhere('email', 'an@test.com');
        $binh  = collect($users)->firstWhere('email', 'binh@test.com');
        $cuong = collect($users)->firstWhere('email', 'cuong@test.com');

        if (!$an || !$binh || !$cuong) return;

        $group = SplitGroup::firstOrCreate(
            ['ten_nhom' => 'Phòng trọ 3 người', 'created_by' => $an->id],
            [
                'mo_ta'      => 'Chia sẻ chi phí phòng trọ: điện, nước, internet',
                'che_do'     => 'expense',
                'hien_so_du' => false,
                'trang_thai' => 'active',
                'created_at' => now()->subDays(60),
            ]
        );

        $this->addMember($group, $an,    'admin');
        $this->addMember($group, $binh,  'member');
        $this->addMember($group, $cuong, 'member');

        $members = [$an, $binh, $cuong];
        $getCat  = fn($user) => Category::where('user_id', $user->id)
            ->where('ten_danh_muc', 'Chi nhóm')
            ->whereNotNull('danh_muc_cha_id')
            ->first();

        // Khoản 1: Tiền phòng tháng 2 - approved, chia đều
        $p1 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Tiền phòng tháng 2/2026'],
            [
                'proposed_by' => $an->id,
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
            [$an->id,    1_000_000],
            [$binh->id,  1_000_000],
            [$cuong->id, 1_000_000],
        ], $members, $getCat);
        $this->createApprovals($p1, $members, GroupExpenseProposal::class);

        // Khoản 2: Tiền điện tháng 2 - approved, chia custom (An dùng nhiều hơn)
        $p2 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Tiền điện tháng 2/2026'],
            [
                'proposed_by' => $binh->id,
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
            [$an->id,    200_000],
            [$binh->id,  150_000],
            [$cuong->id, 100_000],
        ], $members, $getCat);
        $this->createApprovals($p2, $members, GroupExpenseProposal::class);

        // Khoản 3: Internet tháng 3 - pending, chia theo %, chỉ An duyệt
        $p3 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Internet + điện thoại tháng 3/2026'],
            [
                'proposed_by' => $an->id,
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
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p3->id, 'user_id' => $an->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(2)]
        );

        // Khoản 4: Mua máy lọc không khí - rejected (An từ chối)
        $p4 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Mua máy lọc không khí chung'],
            [
                'proposed_by' => $cuong->id,
                'tong_tien'   => 2_000_000,
                'ngay_chi'    => now()->subDays(20)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'rejected',
                'created_at'  => now()->subDays(22),
            ]
        );
        $this->createExpenseSplitsRaw($p4, [
            [$an->id,    666_667],
            [$binh->id,  666_667],
            [$cuong->id, 666_666],
        ]);
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p4->id, 'user_id' => $cuong->id],
            ['quyet_dinh' => 'approved', 'created_at' => now()->subDays(21)]
        );
        GroupApproval::firstOrCreate(
            ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p4->id, 'user_id' => $an->id],
            ['quyet_dinh' => 'rejected', 'ghi_chu' => 'Không cần thiết', 'created_at' => now()->subDays(21)]
        );

        // Ghi nợ thẳng
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $an->id, 'nguoi_no_id' => $cuong->id, 'so_tien' => 150_000],
            ['ghi_chu' => 'Đi chợ hộ tuần trước', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(7)]
        );
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $cuong->id, 'nguoi_no_id' => $binh->id, 'so_tien' => 80_000],
            ['ghi_chu' => 'Grab đi làm hôm qua', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $binh->id, 'nguoi_no_id' => $an->id, 'so_tien' => 55_000],
            ['ghi_chu' => 'Cafe sáng thứ 6', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(2)]
        );
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $an->id, 'nguoi_no_id' => $binh->id, 'so_tien' => 200_000, 'trang_thai' => 'settled'],
            ['ghi_chu' => 'Tiền thuốc tháng trước', 'settled_at' => now()->subDays(10), 'created_at' => now()->subDays(15)]
        );
    }

    // NHÓM 3: DU LỊCH (both) 
    private function createTripGroup(array $users): void
    {
        $bo    = collect($users)->firstWhere('email', 'bo@test.com');
        $me    = collect($users)->firstWhere('email', 'me@test.com');
        $con   = collect($users)->firstWhere('email', 'con@test.com');
        $an    = collect($users)->firstWhere('email', 'an@test.com');
        $binh  = collect($users)->firstWhere('email', 'binh@test.com');
        $cuong = collect($users)->firstWhere('email', 'cuong@test.com');

        if (!$an || !$binh || !$cuong || !$bo || !$me || !$con) return;

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

        $allMembers = [$an, $binh, $cuong, $bo, $me, $con];

        $this->addMember($group, $an,    'admin');
        foreach ([$binh, $cuong, $bo, $me, $con] as $member) {
            $this->addMember($group, $member, 'member');
        }

        // Lời mời pending chưa chấp nhận
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

        // Khoản 1: Khách sạn 2 đêm - approved, chia đều 500k/người
        $p1 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Khách sạn 2 đêm'],
            [
                'proposed_by' => $an->id,
                'tong_tien'   => 3_000_000,
                'ngay_chi'    => now()->addDays(14)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'approved',
                'executed_at' => now()->subDays(2),
                'created_at'  => now()->subDays(5),
            ]
        );
        $this->createExpenseSplits(
            $p1,
            array_map(fn($m) => [$m->id, 500_000], $allMembers),
            $allMembers,
            $getCat
        );
        $this->createApprovals($p1, $allMembers, GroupExpenseProposal::class);

        // Khoản 2: Thuê xe 7 chỗ - pending, 3 người đã duyệt
        $p2 = GroupExpenseProposal::firstOrCreate(
            ['group_id' => $group->id, 'mo_ta' => 'Thuê xe 7 chỗ khứ hồi'],
            [
                'proposed_by' => $binh->id,
                'tong_tien'   => 2_400_000,
                'ngay_chi'    => now()->addDays(15)->toDateString(),
                'kieu_chia'   => 'equal',
                'trang_thai'  => 'pending',
                'created_at'  => now()->subDays(1),
            ]
        );
        $this->createExpenseSplitsRaw(
            $p2,
            array_map(fn($m) => [$m->id, 400_000], $allMembers)
        );
        foreach ([$an, $binh, $bo] as $approver) {
            GroupApproval::firstOrCreate(
                ['approvable_type' => GroupExpenseProposal::class, 'approvable_id' => $p2->id, 'user_id' => $approver->id],
                ['quyet_dinh' => 'approved', 'created_at' => now()->subHours(rand(2, 20))]
            );
        }

        // Ghi nợ: Bố ứng tiền mua đồ ăn vặt
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $bo->id, 'nguoi_no_id' => $an->id, 'so_tien' => 120_000],
            ['ghi_chu' => 'An nợ Bố tiền mua snack', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
        GroupExpenseDebt::firstOrCreate(
            ['group_id' => $group->id, 'chu_no_id' => $bo->id, 'nguoi_no_id' => $me->id, 'so_tien' => 85_000],
            ['ghi_chu' => 'Mẹ nợ Bố tiền nước suối', 'trang_thai' => 'confirmed', 'created_at' => now()->subDays(1)]
        );
    }

    private function addMember(SplitGroup $group, User $user, string $role): void
    {
        SplitGroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => $user->id],
            [
                'vai_tro'    => $role,
                'trang_thai' => 'active',
                'joined_at'  => $group->created_at,
            ]
        );
    }

    private function createBalanceSplits(GroupBalanceProposal $proposal, array $data): void
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
        foreach ($data as [$userId, $soTien]) {
            $user = collect($users)->firstWhere('id', $userId);
            $cat  = $user ? $getCat($user) : null;

            $transactionId = null;
            if ($proposal->trang_thai === 'approved' && $cat) {
                $tx = Transaction::firstOrCreate(
                    [
                        'user_id'        => $userId,
                        'category_id'    => $cat->id,
                        'so_tien'        => $soTien,
                        'ngay_giao_dich' => $proposal->ngay_chi,
                        'ghi_chu'        => 'Chi nhóm: ' . $proposal->mo_ta,
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
                    'ty_le'             => null,
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
        foreach ($data as [$userId, $soTien]) {
            GroupExpenseSplit::firstOrCreate(
                ['proposal_id' => $proposal->id, 'user_id' => $userId],
                [
                    'so_tien'           => $soTien,
                    'ty_le'             => null,
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