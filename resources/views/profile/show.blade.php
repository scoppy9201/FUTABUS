@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
    @php
        $nameParts = preg_split('/\s+/u', trim($user->name ?? ''));
        $initials = collect($nameParts)
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $initials = $initials !== '' ? $initials : 'U';

        $avatarUrl = null;
        if ($user->avatar) {
            $avatarUrl = str_starts_with($user->avatar, 'http')
                ? $user->avatar
                : asset('storage/' . $user->avatar);
        }

        $birthDate = $user->ngay_sinh ? \Carbon\Carbon::parse($user->ngay_sinh) : null;
        $gender = $user->gioi_tinh ?: 'Chưa cập nhật';
        $phone = $user->phone ?: 'Chưa cập nhật';
    @endphp

    <div class="profile-page">
        <section class="card profile-header">
            <div class="profile-header-copy">
                <span class="profile-kicker">Hồ sơ cá nhân</span>
                <h1 class="profile-title">Quản lý thông tin tài khoản theo cùng giao diện Monexa</h1>
                <p class="profile-subtitle">
                    Cập nhật ảnh đại diện, thông tin liên hệ và các dữ liệu cá nhân quan trọng để trải nghiệm trong hệ thống luôn đồng bộ.
                </p>
            </div>

            <a href="{{ route('dashboard') }}" class="profile-back-link">
                <img src="{{ asset('images/arrow.png') }}" alt="">
                <span>Quay lại Dashboard</span>
            </a>
        </section>

        <div class="profile-grid">
            <aside class="profile-sidebar">
                <section class="card profile-summary">
                    <div class="profile-avatar-shell">
                        <div class="profile-avatar-frame" id="avatarPreviewWrap">
                            @if($avatarUrl)
                                <img id="avatarPreview" class="profile-avatar-image" src="{{ $avatarUrl }}" alt="Avatar của {{ $user->name }}">
                            @else
                                <div id="avatarPreview" class="profile-avatar-fallback">{{ $initials }}</div>
                            @endif
                        </div>

                        <label for="avatarInput" class="profile-avatar-edit">
                            <img src="{{ asset('images/camera.png') }}" alt="">
                            <span>Đổi ảnh</span>
                        </label>
                    </div>

                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm" class="profile-avatar-form">
                        @csrf
                        <input type="file" name="avatar" id="avatarInput" accept="image/png,image/jpeg,image/jpg,image/gif">
                    </form>

                    <div class="profile-avatar-actions">
                        <button type="button" class="profile-btn profile-btn--secondary" id="chooseAvatarBtn">
                            Tải ảnh mới
                        </button>

                        @if($user->avatar)
                            <form action="{{ route('profile.avatar.delete') }}" method="POST" class="profile-inline-form">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="profile-btn profile-btn--danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa ảnh đại diện?')"
                                >
                                    Xóa ảnh
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="profile-identity">
                        <h2 class="profile-name">{{ $user->name }}</h2>
                        <p class="profile-email">{{ $user->email }}</p>

                        <div class="profile-pill-row">
                            @if($user->google_id)
                                <span class="profile-pill profile-pill--google">Đăng nhập bằng Google</span>
                                <span class="profile-pill profile-pill--muted">Tên và email đang được khóa</span>
                            @else
                                <span class="profile-pill profile-pill--system">Tài khoản hệ thống</span>
                            @endif
                        </div>
                    </div>

                    <div class="profile-meta-grid">
                        <div class="profile-meta-card">
                            <span class="profile-meta-label">Giới tính</span>
                            <strong class="profile-meta-value">{{ $gender }}</strong>
                        </div>

                        <div class="profile-meta-card">
                            <span class="profile-meta-label">Ngày sinh</span>
                            <strong class="profile-meta-value">{{ $birthDate ? $birthDate->format('d/m/Y') : 'Chưa cập nhật' }}</strong>
                        </div>

                        <div class="profile-meta-card profile-meta-card--wide">
                            <span class="profile-meta-label">Số điện thoại</span>
                            <strong class="profile-meta-value">{{ $phone }}</strong>
                        </div>
                    </div>
                </section>

                <section class="card profile-side-note">
                    <span class="profile-side-note__eyebrow">Gợi ý từ hệ thống</span>
                    <h3 class="profile-side-note__title">Giữ hồ sơ của bạn luôn rõ ràng và đáng tin cậy</h3>
                    <p class="profile-side-note__text">
                        Ảnh đại diện rõ nét, số điện thoại chính xác và ngày sinh đầy đủ sẽ giúp thao tác khôi phục, xác thực và đồng bộ tài khoản thuận tiện hơn.
                    </p>
                </section>
            </aside>

            <section class="card profile-form-card">
                <div class="profile-section-head">
                    <div>
                        <span class="profile-section-eyebrow">Thông tin tài khoản</span>
                        <h2 class="profile-section-title">Chỉnh sửa hồ sơ</h2>
                    </div>
                    <span class="profile-section-badge">Đồng bộ toàn hệ thống</span>
                </div>

                <div class="profile-alert-stack">
                    @if(session('success'))
                        <div class="profile-alert profile-alert--success" data-auto-dismiss>
                            <div>
                                <strong>Cập nhật thành công</strong>
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="profile-alert profile-alert--error">
                            <div>
                                <strong>Vui lòng kiểm tra lại thông tin</strong>
                                <ul class="profile-alert-list">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($user->google_id)
                        <div class="profile-alert profile-alert--warning">
                            <div>
                                <strong>Lưu ý cho tài khoản Google</strong>
                                <p>Tên và email đang được đồng bộ từ Google nên chỉ có thể cập nhật các trường bổ sung khác.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="profile-field-grid">
                        <div class="profile-field">
                            <div class="profile-label-row">
                                <label for="profile_name" class="profile-label">Họ và tên</label>
                                @if($user->google_id)
                                    <span class="profile-field-tag">Đang khóa</span>
                                @endif
                            </div>
                            <input
                                id="profile_name"
                                type="text"
                                name="name"
                                class="profile-input"
                                value="{{ old('name', $user->name) }}"
                                placeholder="Nhập họ và tên"
                                {{ $user->google_id ? 'disabled' : 'required' }}
                            >
                            @error('name')
                                <span class="profile-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-field">
                            <div class="profile-label-row">
                                <label for="profile_email" class="profile-label">Email</label>
                                @if($user->google_id)
                                    <span class="profile-field-tag">Đang khóa</span>
                                @endif
                            </div>
                            <input
                                id="profile_email"
                                type="email"
                                name="email"
                                class="profile-input"
                                value="{{ old('email', $user->email) }}"
                                placeholder="email@example.com"
                                {{ $user->google_id ? 'disabled' : 'required' }}
                            >
                            @error('email')
                                <span class="profile-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-field">
                            <label for="profile_phone" class="profile-label">Số điện thoại</label>
                            <input
                                id="profile_phone"
                                type="tel"
                                name="phone"
                                class="profile-input"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="0123456789"
                                maxlength="15"
                            >
                            @error('phone')
                                <span class="profile-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-field">
                            <label for="profile_birthday" class="profile-label">Ngày sinh</label>
                            <input
                                id="profile_birthday"
                                type="date"
                                name="ngay_sinh"
                                class="profile-input"
                                value="{{ old('ngay_sinh', $birthDate ? $birthDate->format('Y-m-d') : '') }}"
                                max="{{ now()->format('Y-m-d') }}"
                            >
                            @error('ngay_sinh')
                                <span class="profile-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="profile-field profile-field--full">
                            <label for="profile_gender" class="profile-label">Giới tính</label>
                            <select id="profile_gender" name="gioi_tinh" class="profile-input profile-select">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" {{ old('gioi_tinh', $user->gioi_tinh) === 'Nam' ? 'selected' : '' }}>Nam</option>
                                <option value="Nữ" {{ old('gioi_tinh', $user->gioi_tinh) === 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                <option value="Khác" {{ old('gioi_tinh', $user->gioi_tinh) === 'Khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gioi_tinh')
                                <span class="profile-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="profile-form-footer">
                        <p class="profile-form-note">Mọi thay đổi sẽ được áp dụng ngay cho tài khoản của bạn trong toàn bộ Monexa.</p>
                        <button type="submit" class="profile-submit">
                            <span>Lưu thay đổi</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        const avatarInput = document.getElementById('avatarInput');
        const avatarForm = document.getElementById('avatarForm');
        const avatarPreviewWrap = document.getElementById('avatarPreviewWrap');
        const chooseAvatarBtn = document.getElementById('chooseAvatarBtn');

        chooseAvatarBtn?.addEventListener('click', function () {
            avatarInput?.click();
        });

        avatarInput?.addEventListener('change', function (event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('Ảnh không được vượt quá 2MB.');
                event.target.value = '';
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn đúng tệp hình ảnh.');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (loadEvent) {
                let preview = document.getElementById('avatarPreview');

                if (!preview || preview.tagName !== 'IMG') {
                    avatarPreviewWrap.innerHTML = '<img id="avatarPreview" class="profile-avatar-image" alt="Avatar preview">';
                    preview = document.getElementById('avatarPreview');
                }

                preview.src = loadEvent.target.result;
            };

            reader.readAsDataURL(file);

            window.setTimeout(function () {
                avatarForm?.submit();
            }, 350);
        });

        window.setTimeout(function () {
            document.querySelectorAll('[data-auto-dismiss]').forEach(function (alert) {
                alert.classList.add('is-hiding');
                window.setTimeout(function () {
                    alert.remove();
                }, 250);
            });
        }, 5000);
    </script>
@endsection
