@php
    $avatarPath = Auth::user()->avatar;
    $headerAvatarUrl = is_string($avatarPath) && str_starts_with($avatarPath, 'avatars/')
        && ! str_contains($avatarPath, '..') && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath)
        ? (Auth::user()->isStaff() ? route('api.v1.staff.me.avatar')
            : \Illuminate\Support\Facades\Storage::disk('public')->url($avatarPath)) : null;
@endphp
<span x-data="{ avatarUrl: @js($headerAvatarUrl), avatarName: @js(Auth::user()->name), failed: false, init() {}, destroy() {} }"
    @staff-profile-saved.window="avatarUrl = $event.detail.avatar_url; avatarName = $event.detail.name; failed = false"
    class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full border-2 border-futa-green/20 bg-futa-green/5 font-bold text-futa-green">
    <img x-show="avatarUrl && !failed" x-cloak :src="avatarUrl || null" x-on:error="failed = true"
        alt="" class="size-full object-cover">
    <span x-show="!avatarUrl || failed" x-text="Array.from(avatarName.trim())[0]?.toLocaleUpperCase() || ''">
        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
    </span>
</span>
