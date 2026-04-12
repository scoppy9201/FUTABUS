@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="profile-page" id="profilePage">

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

        <div class="profile-grid" id="profileSkeleton" aria-hidden="true">
            <aside class="profile-sidebar">
                <section class="card profile-summary skeleton-card">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="skeleton skeleton-line" style="width:60%;margin:1rem auto 0.4rem"></div>
                    <div class="skeleton skeleton-line" style="width:40%;margin:0 auto"></div>
                </section>
            </aside>
            <section class="card profile-form-card">
                <div class="skeleton skeleton-line" style="width:30%;margin-bottom:1.5rem"></div>
                @for($i = 0; $i < 5; $i++)
                    <div class="skeleton skeleton-line" style="width:100%;height:2.5rem;margin-bottom:1rem"></div>
                @endfor
            </section>
        </div>

        {{-- ── CONTENT (ẩn cho đến khi fetch xong) ────────── --}}
        <div class="profile-grid" id="profileContent" style="display:none">

            {{-- SIDEBAR --}}
            <aside class="profile-sidebar">
                <section class="card profile-summary">
                    <div class="profile-avatar-shell">
                        <div class="profile-avatar-frame" id="avatarPreviewWrap">
                            {{-- Điền bởi JS --}}
                        </div>
                        <label for="avatarInput" class="profile-avatar-edit">
                            <img src="{{ asset('images/camera.png') }}" alt="">
                            <span>Đổi ảnh</span>
                        </label>
                    </div>

                    {{-- Input file ẩn --}}
                    <input type="file" name="avatar" id="avatarInput"
                           accept="image/png,image/jpeg,image/jpg,image/gif" style="display:none">

                    <div class="profile-avatar-actions">
                        <button type="button" class="profile-btn profile-btn--secondary" id="chooseAvatarBtn">
                            Tải ảnh mới
                        </button>
                        <button type="button" class="profile-btn profile-btn--danger" id="deleteAvatarBtn" style="display:none">
                            Xóa ảnh
                        </button>
                    </div>

                    <div class="profile-identity">
                        <h2 class="profile-name" id="sidebarName"></h2>
                        <p class="profile-email" id="sidebarEmail"></p>
                        <div class="profile-pill-row" id="sidebarPills"></div>
                    </div>

                    <div class="profile-meta-grid">
                        <div class="profile-meta-card">
                            <span class="profile-meta-label">Giới tính</span>
                            <strong class="profile-meta-value" id="metaGender">—</strong>
                        </div>
                        <div class="profile-meta-card">
                            <span class="profile-meta-label">Ngày sinh</span>
                            <strong class="profile-meta-value" id="metaBirthdate">—</strong>
                        </div>
                        <div class="profile-meta-card profile-meta-card--wide">
                            <span class="profile-meta-label">Số điện thoại</span>
                            <strong class="profile-meta-value" id="metaPhone">—</strong>
                        </div>
                    </div>
                </section>
            </aside>

            {{-- FORM CARD --}}
            <section class="card profile-form-card">
                <div class="profile-section-head">
                    <div>
                        <span class="profile-section-eyebrow">Thông tin tài khoản</span>
                        <h2 class="profile-section-title">Chỉnh sửa hồ sơ</h2>
                    </div>
                    <span class="profile-section-badge">Đồng bộ toàn hệ thống</span>
                </div>

                {{-- Alert khu vực (điền bởi JS) --}}
                <div class="profile-alert-stack" id="alertStack"></div>

                <div class="profile-field-grid">
                    {{-- Tên --}}
                    <div class="profile-field">
                        <div class="profile-label-row">
                            <label for="profile_name" class="profile-label">Họ và tên</label>
                            <span class="profile-field-tag" id="nameLockTag" style="display:none">Đang khóa</span>
                        </div>
                        <input id="profile_name" type="text" name="name" class="profile-input"
                               placeholder="Nhập họ và tên">
                        <span class="profile-error" id="err_name"></span>
                    </div>

                    {{-- Email --}}
                    <div class="profile-field">
                        <div class="profile-label-row">
                            <label for="profile_email" class="profile-label">Email</label>
                            <span class="profile-field-tag" id="emailLockTag" style="display:none">Đang khóa</span>
                        </div>
                        <input id="profile_email" type="email" name="email" class="profile-input"
                               placeholder="email@example.com">
                        <span class="profile-error" id="err_email"></span>
                    </div>

                    {{-- Số điện thoại --}}
                    <div class="profile-field">
                        <label for="profile_phone" class="profile-label">Số điện thoại</label>
                        <input id="profile_phone" type="tel" name="phone" class="profile-input"
                               placeholder="0123456789" maxlength="15">
                        <span class="profile-error" id="err_phone"></span>
                    </div>

                    {{-- Ngày sinh --}}
                    <div class="profile-field">
                        <label for="profile_birthday" class="profile-label">Ngày sinh</label>
                        <input id="profile_birthday" type="date" name="ngay_sinh" class="profile-input"
                               max="{{ now()->format('Y-m-d') }}">
                        <span class="profile-error" id="err_ngay_sinh"></span>
                    </div>

                    {{-- Giới tính --}}
                    <div class="profile-field profile-field--full">
                        <label for="profile_gender" class="profile-label">Giới tính</label>
                        <select id="profile_gender" name="gioi_tinh" class="profile-input profile-select">
                            <option value="">Chọn giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                        <span class="profile-error" id="err_gioi_tinh"></span>
                    </div>
                </div>

                <div class="profile-form-footer">
                    <p class="profile-form-note">
                        Mọi thay đổi sẽ được áp dụng ngay cho tài khoản của bạn trong toàn bộ Monexa.
                    </p>
                    <button type="button" class="profile-submit" id="saveProfileBtn">
                        <span id="saveProfileBtnText">Lưu thay đổi</span>
                    </button>
                </div>
            </section>
        </div>
    </div>

    <script>
    (function () {
        'use strict';
        const API_BASE = '/api/v1';

        /**
         * Trả về headers chuẩn cho mọi request JSON.
         * X-CSRF-TOKEN bắt buộc với SPA dùng session cookie.
         */
        function jsonHeaders() {
            return {
                'Content-Type' : 'application/json',
                'Accept'       : 'application/json',
                'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        }

        /**
         * Headers cho multipart/form-data (upload file).
         * KHÔNG set Content-Type — browser tự điền boundary.
         */
        function multipartHeaders() {
            return {
                'Accept'       : 'application/json',
                'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            };
        }

        /** Hiển thị alert trong #alertStack */
        function showAlert(type, title, body) {
            const stack = document.getElementById('alertStack');
            if (!stack) return;

            const el = document.createElement('div');
            el.className = `profile-alert profile-alert--${type}`;
            el.innerHTML = `<div><strong>${title}</strong>${body ? `<p>${body}</p>` : ''}</div>`;
            stack.innerHTML = '';
            stack.appendChild(el);

            if (type === 'success') {
                window.setTimeout(function () {
                    el.classList.add('is-hiding');
                    window.setTimeout(function () { el.remove(); }, 250);
                }, 5000);
            }
        }

        /** Xoá toàn bộ lỗi field */
        function clearFieldErrors() {
            document.querySelectorAll('.profile-error').forEach(function (el) {
                el.textContent = '';
            });
            document.querySelectorAll('.profile-input').forEach(function (el) {
                el.classList.remove('is-invalid');
            });
        }

        /** Hiển thị lỗi validation trả về từ Laravel */
        function showFieldErrors(errors) {
            Object.entries(errors).forEach(function ([field, messages]) {
                const errEl    = document.getElementById('err_' + field);
                const inputEl  = document.getElementById('profile_' + field)
                              || document.getElementById('profile_birthday')  // alias ngay_sinh
                              || null;

                if (errEl) errEl.textContent = messages[0];
                if (field === 'ngay_sinh') {
                    const inp = document.getElementById('profile_birthday');
                    if (inp) inp.classList.add('is-invalid');
                } else if (inputEl) {
                    inputEl.classList.add('is-invalid');
                }
            });
        }

        /** Format ngày ISO → dd/mm/yyyy */
        function formatDate(iso) {
            if (!iso) return 'Chưa cập nhật';
            const d = new Date(iso);
            if (isNaN(d)) return 'Chưa cập nhật';
            return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        /* Render sidebar & form từ data*/

        function renderProfile(data) {
            const isGoogle = !!data.google_id;

            /* Avatar */
            const wrap = document.getElementById('avatarPreviewWrap');
            if (data.avatar) {
                wrap.innerHTML = `<img id="avatarPreview" class="profile-avatar-image" src="${data.avatar}" alt="Avatar">`;
            } else {
                const initials = (data.name ?? 'U')
                    .trim().split(/\s+/)
                    .filter(Boolean).slice(0, 2)
                    .map(function (w) { return w[0].toUpperCase(); })
                    .join('');
                wrap.innerHTML = `<div id="avatarPreview" class="profile-avatar-fallback">${initials || 'U'}</div>`;
            }

            /* Nút xoá ảnh */
            const deleteBtn = document.getElementById('deleteAvatarBtn');
            if (deleteBtn) deleteBtn.style.display = data.avatar ? '' : 'none';

            /* Sidebar identity */
            document.getElementById('sidebarName').textContent  = data.name  ?? '';
            document.getElementById('sidebarEmail').textContent = data.email ?? '';

            const pillRow = document.getElementById('sidebarPills');
            pillRow.innerHTML = isGoogle
                ? `<span class="profile-pill profile-pill--google">Đăng nhập bằng Google</span>
                   <span class="profile-pill profile-pill--muted">Tên và email đang được khóa</span>`
                : `<span class="profile-pill profile-pill--system">Tài khoản hệ thống</span>`;

            /* Sidebar meta */
            document.getElementById('metaGender').textContent    = data.gioi_tinh ?? 'Chưa cập nhật';
            document.getElementById('metaBirthdate').textContent = formatDate(data.ngay_sinh);
            document.getElementById('metaPhone').textContent     = data.phone     ?? 'Chưa cập nhật';

            /* Form fields */
            const nameInput  = document.getElementById('profile_name');
            const emailInput = document.getElementById('profile_email');

            nameInput.value  = data.name  ?? '';
            emailInput.value = data.email ?? '';
            document.getElementById('profile_phone').value    = data.phone     ?? '';
            document.getElementById('profile_birthday').value = data.ngay_sinh
                ? data.ngay_sinh.substring(0, 10)  // ISO → yyyy-mm-dd
                : '';

            const genderSel = document.getElementById('profile_gender');
            genderSel.value = data.gioi_tinh ?? '';

            /* Khoá / mở khoá field Google */
            if (isGoogle) {
                nameInput.disabled  = true;
                emailInput.disabled = true;
                document.getElementById('nameLockTag').style.display  = '';
                document.getElementById('emailLockTag').style.display = '';
                /* Cảnh báo Google */
                showAlert('warning', 'Lưu ý cho tài khoản Google',
                    'Tên và email đang được đồng bộ từ Google nên chỉ có thể cập nhật các trường bổ sung khác.');
            } else {
                nameInput.disabled  = false;
                emailInput.disabled = false;
            }
        }

        /* 1. Load profile khi trang mở */

        async function loadProfile() {
            try {
                const res  = await fetch(`${API_BASE}/profile`, {
                    method  : 'GET',
                    headers : jsonHeaders(),
                });
                const json = await res.json();

                if (!res.ok || !json.success) {
                    throw new Error(json.message ?? 'Không thể tải thông tin hồ sơ.');
                }

                renderProfile(json.data);

                document.getElementById('profileSkeleton').style.display = 'none';
                document.getElementById('profileContent').style.display  = '';

            } catch (err) {
                document.getElementById('profileSkeleton').style.display = 'none';
                document.getElementById('profileContent').style.display  = '';
                showAlert('error', 'Lỗi tải dữ liệu', err.message);
            }
        }

        /*2. Lưu thông tin cá nhân */

        document.getElementById('saveProfileBtn')?.addEventListener('click', async function () {
            clearFieldErrors();

            const btn     = this;
            const btnText = document.getElementById('saveProfileBtnText');

            btn.disabled    = true;
            btnText.textContent = 'Đang lưu…';

            const payload = {
                name      : document.getElementById('profile_name').value,
                email     : document.getElementById('profile_email').value,
                phone     : document.getElementById('profile_phone').value,
                ngay_sinh : document.getElementById('profile_birthday').value,
                gioi_tinh : document.getElementById('profile_gender').value,
            };

            try {
                const res  = await fetch(`${API_BASE}/profile`, {
                    method  : 'PATCH',
                    headers : jsonHeaders(),
                    body    : JSON.stringify(payload),
                });
                const json = await res.json();

                if (res.status === 422 && json.errors) {
                    showFieldErrors(json.errors);
                    showAlert('error', 'Vui lòng kiểm tra lại thông tin', '');
                    return;
                }

                if (!res.ok || !json.success) {
                    throw new Error(json.message ?? 'Cập nhật thất bại.');
                }

                /* Cập nhật lại sidebar với data mới */
                renderProfile(json.data);
                showAlert('success', 'Cập nhật thành công', json.message);

            } catch (err) {
                showAlert('error', 'Đã xảy ra lỗi', err.message);
            } finally {
                btn.disabled        = false;
                btnText.textContent = 'Lưu thay đổi';
            }
        });

        /* 3. Upload avatar */

        document.getElementById('chooseAvatarBtn')?.addEventListener('click', function () {
            document.getElementById('avatarInput')?.click();
        });

        document.getElementById('avatarInput')?.addEventListener('change', async function (e) {
            const file = e.target.files?.[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('Ảnh không được vượt quá 2MB.');
                e.target.value = '';
                return;
            }
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn đúng tệp hình ảnh.');
                e.target.value = '';
                return;
            }

            /* Preview tức thì */
            const reader = new FileReader();
            reader.onload = function (ev) {
                const wrap = document.getElementById('avatarPreviewWrap');
                wrap.innerHTML = `<img id="avatarPreview" class="profile-avatar-image" src="${ev.target.result}" alt="Avatar preview">`;
            };
            reader.readAsDataURL(file);

            /* Gửi lên server */
            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const res  = await fetch(`${API_BASE}/profile/avatar`, {
                    method  : 'POST',
                    headers : multipartHeaders(),
                    body    : formData,
                });
                const json = await res.json();

                if (!res.ok || !json.success) {
                    throw new Error(json.message ?? 'Upload thất bại.');
                }

                /* Cập nhật preview với URL thật từ server */
                const wrap = document.getElementById('avatarPreviewWrap');
                wrap.innerHTML = `<img id="avatarPreview" class="profile-avatar-image" src="${json.avatar_url}" alt="Avatar">`;
                document.getElementById('deleteAvatarBtn').style.display = '';
                showAlert('success', 'Thành công', json.message);

            } catch (err) {
                showAlert('error', 'Upload thất bại', err.message);
            } finally {
                e.target.value = '';
            }
        });

        /* 4. Xoá avatar */
        document.getElementById('deleteAvatarBtn')?.addEventListener('click', async function () {
            if (!confirm('Bạn có chắc muốn xóa ảnh đại diện?')) return;

            const btn = this;
            btn.disabled = true;

            try {
                const res  = await fetch(`${API_BASE}/profile/avatar`, {
                    method  : 'DELETE',
                    headers : jsonHeaders(),
                });
                const json = await res.json();

                if (!res.ok || !json.success) {
                    throw new Error(json.message ?? 'Xoá thất bại.');
                }

                /* Reset về initials */
                await loadProfile();
                showAlert('success', 'Đã xoá ảnh', json.message);

            } catch (err) {
                showAlert('error', 'Xoá thất bại', err.message);
            } finally {
                btn.disabled = false;
            }
        });

        loadProfile();
    })();
    </script>
@endsection