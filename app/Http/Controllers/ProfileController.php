<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * GET /api/v1/profile
     * Trả về thông tin hồ sơ người dùng hiện tại.
     */
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'ngay_sinh'  => $user->ngay_sinh,
                'gioi_tinh'  => $user->gioi_tinh,
                'avatar'     => $user->avatar
                    ? (str_starts_with($user->avatar, 'http')
                        ? $user->avatar
                        : asset('storage/' . $user->avatar))
                    : null,
                'google_id'  => $user->google_id ? true : false, // chỉ expose boolean
            ],
        ]);
    }

    /**
     * PATCH /api/v1/profile
     * Cập nhật thông tin cá nhân.
     */
    public function update(Request $request)
    {
        $user   = Auth::user();
        $userId = $user->id;

        $rules = [
            'phone'      => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'ngay_sinh'  => 'nullable|date|before:today',
            'gioi_tinh'  => 'nullable|in:Nam,Nữ,Khác',
        ];

        if (!$user->google_id) {
            $rules['name']  = 'required|string|max:255';
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users')->ignore($userId),
            ];
        }

        $validated = $request->validate($rules);

        $updateData = [
            'phone'     => $validated['phone']     ?? null,
            'ngay_sinh' => $validated['ngay_sinh'] ?? null,
            'gioi_tinh' => $validated['gioi_tinh'] ?? null,
        ];

        if (!$user->google_id) {
            $updateData['name']  = $validated['name'];
            $updateData['email'] = $validated['email'];
        }

        User::where('id', $userId)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!',
            'data'    => User::find($userId),
        ]);
    }

    /**
     * POST /api/v1/profile/avatar
     * Upload ảnh đại diện mới.
     */
    public function updateAvatar(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find($userId);

        // Xoá ảnh cũ nếu tồn tại
        if (
            $user->avatar &&
            !str_starts_with($user->avatar, 'http') &&
            !str_contains($user->avatar, 'default') &&
            Storage::disk('public')->exists($user->avatar)
        ) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        User::where('id', $userId)->update(['avatar' => $path]);

        return response()->json([
            'success'    => true,
            'message'    => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => asset('storage/' . $path),
        ]);
    }

    /**
     * DELETE /api/v1/profile/avatar
     * Xoá ảnh đại diện.
     */
    public function deleteAvatar()
    {
        $userId = Auth::id();
        $user   = User::find($userId);

        if (
            $user->avatar &&
            !str_starts_with($user->avatar, 'http') &&
            !str_contains($user->avatar, 'default') &&
            Storage::disk('public')->exists($user->avatar)
        ) {
            Storage::disk('public')->delete($user->avatar);
        }

        User::where('id', $userId)->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Đã xoá ảnh đại diện!',
        ]);
    }
}