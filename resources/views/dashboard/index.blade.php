@extends('layouts.app')
@section('title', 'Dashboard - Pantau Pangan')
@section('content')
<div class="container">
    <div class="page-header flex-between">
        <div><h1 class="page-title" id="greeting">Dashboard</h1><p class="page-subtitle">Selamat datang kembali!</p></div>
        <a href="/products" class="btn btn-primary btn-sm">🔍 Cek Harga</a>
    </div>
    <div class="grid" style="grid-template-columns:2fr 1fr;gap:32px;">
        <div>
            <h3 style="font-weight:700;margin-bottom:16px;">⭐ Produk Favorit</h3>
            <div id="favorites-list"><div class="spinner"></div></div>
            <div class="mt-3">
                <h3 style="font-weight:700;margin-bottom:16px;">📊 Harga Terkini</h3>
                <div class="grid grid-3" id="recent-prices"><div class="spinner"></div></div>
            </div>
        </div>
        <div>
            <div class="card mb-3" style="padding:24px; border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; position:relative; border: 1px solid var(--border);">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; border-bottom:1px solid var(--border); padding-bottom:12px;">
                    <span style="font-size:1.25rem;">👤</span>
                    <h3 style="font-weight:700; font-size:1.1rem; margin:0; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">Profil</h3>
                </div>
                <div id="profile-info"><div class="spinner"></div></div>
                <button class="btn btn-secondary btn-sm mt-3" style="width:100%; border-radius:8px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:6px; transition:var(--transition); padding: 8px 16px; border: 1.5px solid var(--border);" onclick="openUserProfileModal()">
                    <span>✏️</span> Edit Profil
                </button>
            </div>
            <div class="card" style="padding:20px; border-radius:16px; border: 1px solid var(--border);">
                <h3 style="font-weight:700;margin-bottom:12px; font-size:1.1rem; display:flex; align-items:center; gap:8px; color: var(--text-secondary);">🔔 Notifikasi Terbaru</h3>
                <div id="recent-notifs"><div class="spinner"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Personal Profile Modal --}}
<div class="modal-overlay" id="user-profile-modal" style="backdrop-filter: blur(8px); background: rgba(15,23,42,0.4);">
    <div class="modal" style="border-radius: 16px; box-shadow: var(--shadow-lg); padding: 32px; border: 1px solid var(--border); max-width: 520px; width: 100%;">
        <div class="flex-between mb-3" style="border-bottom: 1px solid var(--border); padding-bottom: 16px;">
            <h3 class="modal-title" style="font-weight: 800; font-size: 1.25rem; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0;">
                <span>👤</span> Edit Profil Pengguna
            </h3>
            <button class="btn btn-ghost btn-sm" style="border-radius: 50%; width: 32px; height: 32px; padding: 0;" onclick="closeUserProfileModal()">✕</button>
        </div>
        <form id="edit-user-profile-form">
            <div style="max-height: 420px; overflow-y: auto; padding-right: 6px; margin-bottom: 16px; scrollbar-width: thin;">
                
                {{-- Profile Photo Upload Section --}}
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; color: var(--text); display: block; margin-bottom: 10px;">Foto Profil</label>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div id="photo-preview-wrap" style="position: relative; width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid var(--border); background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;" onclick="document.getElementById('eup-photo-input').click()">
                            <div id="photo-preview-content" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                <span style="color: #fff; font-size: 1.5rem; font-weight: 800;" id="photo-preview-initials">??</span>
                            </div>
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.55); color: #fff; font-size: 0.6rem; text-align: center; padding: 3px 0; font-weight: 600;">📷 Ubah</div>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" id="eup-photo-input" accept="image/*" style="display: none;">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button type="button" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-size: 0.8rem; padding: 6px 14px;" onclick="document.getElementById('eup-photo-input').click()">📁 Pilih File</button>
                                <button type="button" class="btn btn-ghost btn-sm" id="btn-remove-photo" style="border-radius: 8px; font-size: 0.8rem; padding: 6px 14px; color: var(--error); display: none;" onclick="removeProfilePhoto()">🗑 Hapus Foto</button>
                            </div>
                            <p style="font-size: 0.7rem; color: var(--text-secondary); margin: 6px 0 0; line-height: 1.3;">Format: JPG, PNG, WebP · Maks 2MB<br>Di HP, tap foto untuk ambil dari kamera</p>
                        </div>
                    </div>
                </div>

                {{-- Avatar Emoji Fallback --}}
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; color: var(--text); display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        Atau Pilih Avatar
                        <span style="font-size: 0.7rem; font-weight: normal; color: var(--text-secondary); background: var(--border); padding: 2px 8px; border-radius: 12px;">jika tidak upload foto</span>
                    </label>
                    <div id="avatar-picker-list" style="display: flex; gap: 10px; overflow-x: auto; padding: 8px 4px; scrollbar-width: thin; border: 1px solid var(--border); border-radius: 8px; background: rgba(248,250,252,0.5);">
                    </div>
                    <input type="hidden" id="eup-avatar" value="">
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text);">Nama Lengkap</label>
                    <input type="text" class="form-input" id="eup-name" style="border-radius: 8px; border: 1.5px solid var(--border);" required>
                </div>

                {{-- Gender & Region Grid --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; color: var(--text);">Jenis Kelamin</label>
                        <select class="form-select" id="eup-gender" style="border-radius: 8px; border: 1.5px solid var(--border); height: 42px; padding: 0 12px;">
                            <option value="">Pilih Gender</option>
                            <option value="Laki-laki">👨 Laki-laki</option>
                            <option value="Perempuan">👩 Perempuan</option>
                            <option value="Lainnya">✨ Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; color: var(--text);">Daerah Domisili</label>
                        <select class="form-select" id="eup-region" style="border-radius: 8px; border: 1.5px solid var(--border); height: 42px; padding: 0 12px;">
                            <option value="">Pilih Daerah</option>
                        </select>
                    </div>
                </div>

                {{-- Address Field --}}
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; color: var(--text);">Alamat Lengkap</label>
                    <textarea class="form-input" id="eup-address" rows="2" placeholder="Tulis alamat rumah lengkap Anda..." style="border-radius: 8px; border: 1.5px solid var(--border); font-size: 0.9rem; padding: 10px; width: 100%; box-sizing: border-box; resize: vertical; min-height: 60px;"></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-weight: 600; color: var(--text); display: flex; align-items: center; justify-content: space-between;">
                        <span id="phone-label-text">📞 Nomor HP Warung / Toko</span>
                        <span style="font-size: 0.75rem; font-weight: normal; color: var(--text-secondary); background: var(--border); padding: 2px 8px; border-radius: 12px;">Opsional</span>
                    </label>
                    <input type="text" class="form-input" id="eup-phone" placeholder="Contoh: 0812xxxxxxxx" style="border-radius: 8px; border: 1.5px solid var(--border);">
                    <div id="phone-merchant-note" style="margin-top: 6px; display: flex; gap: 6px; align-items: flex-start; background: var(--primary-50); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--primary-100);">
                        <span style="font-size: 0.85rem;">🏪</span>
                        <p style="font-size: 0.75rem; color: var(--primary-dark); margin: 0; line-height: 1.4;">
                            <strong>Catatan:</strong> Ini adalah nomor kontak operasional warung/toko Anda (bukan nomor pribadi). Boleh dikosongkan jika belum ada.
                        </p>
                    </div>
                </div>
            
            <div style="background: rgba(226,232,240,0.25); border-radius: 12px; padding: 16px 20px; border: 1px dashed var(--border); margin: 24px 0;">
                <h4 style="font-weight: 700; font-size: 0.9rem; margin: 0 0 12px; color: var(--primary); display: flex; align-items: center; gap: 6px;">
                    <span>🔒</span> Ubah Password Baru
                </h4>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 500;">Password Saat Ini</label>
                    <input type="password" class="form-input" id="eup-current-password" placeholder="Verifikasi password saat ini" style="border-radius: 8px; font-size: 0.85rem; padding: 10px 14px;">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 500;">Password Baru</label>
                    <input type="password" class="form-input" id="eup-new-password" placeholder="Minimal 8 karakter" style="border-radius: 8px; font-size: 0.85rem; padding: 10px 14px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem; font-weight: 500;">Konfirmasi Password Baru</label>
                    <input type="password" class="form-input" id="eup-confirm-password" placeholder="Ketik ulang password baru" style="border-radius: 8px; font-size: 0.85rem; padding: 10px 14px;">
                </div>
            </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-ghost" style="flex: 1; border-radius: 8px; font-weight: 600;" onclick="closeUserProfileModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="flex: 2; border-radius: 8px; font-weight: 600; padding: 12px;" id="eup-btn">Simpan Profil</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const AVATAR_EMOJIS = ['🧑‍🌾', '👩‍🍳', '👨‍💼', '👩‍🔬', '🧑‍💻', '👩‍🎨', '👨‍🚀', '🦊', '🐼', '🦁', '🐶', '🐱', '🐱‍🚀', '👩‍🚀', '👨', '👩', '👴', '👵'];

document.addEventListener('DOMContentLoaded', async () => {
    if(!Auth.requireAuth()) return;
    renderProfile();
    
    // Load regions for profile select dropdown
    try {
        const regs = await api('/regions');
        const select = document.getElementById('eup-region');
        if (select) {
            select.innerHTML = '<option value="">Pilih Daerah</option>';
            (regs.regions || []).forEach(r => {
                select.innerHTML += `<option value="${r.id}">${r.name}</option>`;
            });
        }
    } catch(e) {}

    // Favorites
    try {
        const favData = await api('/favorites');
        const fList = document.getElementById('favorites-list');
        if (favData.favorites.length === 0) { fList.innerHTML = '<div class="card text-center" style="padding:32px;"><p class="text-muted">Belum ada produk favorit</p><a href="/products" class="btn btn-primary btn-sm mt-2">Jelajahi Produk</a></div>'; }
        else { fList.innerHTML = '<div class="grid grid-3" style="gap:12px;">' + favData.favorites.map(p => `<a href="/products/${p.slug}" class="product-card" style="text-decoration:none;color:inherit;padding:16px;"><div class="product-name">${p.name}</div><div class="product-price" style="font-size:1rem;margin-top:8px;">${formatRupiah(p.average_price)}<span class="product-unit">/${p.unit}</span></div></a>`).join('') + '</div>'; }
    } catch(e) { document.getElementById('favorites-list').innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
    // Recent products
    try {
        const prods = await api('/products?per_page=6');
        document.getElementById('recent-prices').innerHTML = (prods.data||[]).slice(0,6).map(p => `<a href="/products/${p.slug}" class="product-card" style="text-decoration:none;color:inherit;padding:14px;"><div class="product-category text-sm">${p.category?.name||''}</div><div class="product-name">${p.name}</div><div class="product-price" style="font-size:0.95rem;margin-top:6px;">${formatRupiah(p.average_price)}<span class="product-unit">/${p.unit}</span></div>${p.price_change!==null?formatChange(p.price_change):''}</a>`).join('');
    } catch(e) {}
    // Notifications
    try {
        const notifs = await api('/notifications');
        const nDiv = document.getElementById('recent-notifs');
        const items = notifs.data?.slice(0,5)||[];
        if (items.length === 0) { nDiv.innerHTML = '<p class="text-muted text-sm">Belum ada notifikasi</p>'; }
        else { nDiv.innerHTML = items.map(n => `<div class="notif-item" style="padding:8px 0;border-bottom:1px solid var(--border);"><div class="text-sm">${n.data?.message||'Notifikasi'}</div><div class="text-sm text-muted">${timeAgo(n.created_at)}</div></div>`).join(''); }
    } catch(e) {}
});

function renderProfile() {
    const user = Auth.getUser();
    document.getElementById('greeting').textContent = `Halo, ${user?.name||'User'}!`;
    
    // Avatar display: prioritize uploaded photo > emoji avatar > initials
    let avatarHtml;
    if (user?.profile_photo) {
        const photoUrl = '/storage/' + user.profile_photo;
        avatarHtml = `<img src="${photoUrl}" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">`;
    } else if (user?.avatar) {
        avatarHtml = `<div style="font-size: 2.25rem; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">${user.avatar}</div>`;
    } else {
        avatarHtml = `<span style="font-weight:800; font-size:1.25rem; color:#fff;">${(user?.name || 'User').split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()}</span>`;
    }
    
    const isPedagang = user?.role === 'pedagang';
    const phoneIcon = isPedagang ? '🏪' : '📞';
    const phonePlaceholder = isPedagang ? 'Nomor warung belum diisi' : 'Nomor HP belum diisi';
    
    let infoRows = `
        <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-secondary);">
            <span>📧</span> <span style="word-break:break-all;">${user?.email}</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-secondary);">
            <span>${phoneIcon}</span> <span>${user?.phone || `<span style="font-style:italic;color:var(--text-light)">${phonePlaceholder}</span>`}</span>
        </div>`;

    if (user?.gender) {
        infoRows += `
            <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-secondary);">
                <span>🚻</span> <span>Gender: ${user.gender}</span>
            </div>`;
    }
    if (user?.region?.name) {
        infoRows += `
            <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-secondary);">
                <span>📍</span> <span>Domisili: ${user.region.name}</span>
            </div>`;
    }
    if (user?.address) {
        infoRows += `
            <div style="display:flex; align-items:flex-start; gap:8px; font-size:0.85rem; color:var(--text-secondary); line-height: 1.4;">
                <span style="margin-top:2px;">🏠</span> <span style="word-break:break-word;">${user.address}</span>
            </div>`;
    }
    if (user?.created_at) {
        infoRows += `
            <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--text-secondary);">
                <span>📅</span> <span>Bergabung: ${formatJoinedDate(user.created_at)}</span>
            </div>`;
    }
    
    document.getElementById('profile-info').innerHTML = `
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
            <div style="width:54px; height:54px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--primary-light)); display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 10px rgba(5,150,105,0.2); overflow: hidden;">
                ${avatarHtml}
            </div>
            <div style="flex:1; min-width: 0;">
                <h4 style="font-weight:700; font-size:1.05rem; margin:0; color:var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${user?.name||'User'}</h4>
                <div class="mt-1">${roleBadge(user?.role)}</div>
            </div>
        </div>
        
        <div style="display:flex; flex-direction:column; gap:10px; border-top:1px solid var(--border); padding-top:16px; margin-top:4px;">
            ${infoRows}
        </div>`;
}

function formatJoinedDate(dateStr) {
    if (!dateStr) return '-';
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const d = new Date(dateStr);
    const day = d.getDate();
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    return `${day} ${month} ${year}`;
}

function openUserProfileModal() {
    const user = Auth.getUser();
    document.getElementById('eup-name').value = user?.name || '';
    document.getElementById('eup-phone').value = user?.phone || '';
    document.getElementById('eup-gender').value = user?.gender || '';
    document.getElementById('eup-region').value = user?.region_id || '';
    document.getElementById('eup-address').value = user?.address || '';
    
    // Setup photo preview
    updatePhotoPreview(user);
    
    // Reset file input
    const fileInput = document.getElementById('eup-photo-input');
    if (fileInput) fileInput.value = '';
    
    // Render Avatar Picker Options
    const picker = document.getElementById('avatar-picker-list');
    if (picker) {
        const currentAvatar = user?.avatar || '';
        picker.innerHTML = AVATAR_EMOJIS.map(emoji => {
            const activeStyle = currentAvatar === emoji ? 'border: 2px solid var(--primary); background: var(--primary-50);' : 'border: 2px solid transparent; background: transparent;';
            return `<div class="avatar-option-item" onclick="selectAvatarItem('${emoji}', this)" style="font-size: 1.6rem; padding: 6px; cursor: pointer; border-radius: 50%; transition: var(--transition); display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; ${activeStyle}">${emoji}</div>`;
        }).join('');
        document.getElementById('eup-avatar').value = currentAvatar;
    }
    
    // Dynamic styling & labeling based on role
    const isPedagang = user?.role === 'pedagang';
    const labelEl = document.getElementById('phone-label-text');
    const noteEl = document.getElementById('phone-merchant-note');
    if (labelEl) labelEl.textContent = isPedagang ? '📞 Nomor HP Warung / Toko' : '📞 Nomor Handphone';
    if (noteEl) noteEl.style.display = isPedagang ? 'flex' : 'none';
    
    document.getElementById('eup-current-password').value = '';
    document.getElementById('eup-new-password').value = '';
    document.getElementById('eup-confirm-password').value = '';
    document.getElementById('user-profile-modal').classList.add('show');
}

function updatePhotoPreview(user) {
    const content = document.getElementById('photo-preview-content');
    const removeBtn = document.getElementById('btn-remove-photo');
    if (!content) return;
    
    if (user?.profile_photo) {
        const photoUrl = '/storage/' + user.profile_photo;
        content.innerHTML = `<img src="${photoUrl}" alt="Foto" style="width:100%;height:100%;object-fit:cover;">`;
        if (removeBtn) removeBtn.style.display = 'inline-flex';
    } else if (user?.avatar) {
        content.innerHTML = `<div style="font-size:2.5rem; display:flex; align-items:center; justify-content:center; width:100%; height:100%;">${user.avatar}</div>`;
        if (removeBtn) removeBtn.style.display = 'none';
    } else {
        const initials = (user?.name || 'User').split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
        content.innerHTML = `<span style="color:#fff; font-size:1.5rem; font-weight:800;">${initials}</span>`;
        if (removeBtn) removeBtn.style.display = 'none';
    }
}

// File input change handler — instant upload
document.getElementById('eup-photo-input')?.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Client-side validation
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        showToast('Format foto harus JPG, PNG, atau WebP', 'error');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran foto maksimal 2MB', 'error');
        return;
    }
    
    // Show instant local preview
    const reader = new FileReader();
    reader.onload = function(ev) {
        const content = document.getElementById('photo-preview-content');
        if (content) content.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
    
    // Upload to server
    const formData = new FormData();
    formData.append('photo', file);
    
    try {
        const token = Auth.getToken();
        const res = await fetch('/api/auth/upload-photo', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        
        if (!res.ok) {
            const errMsg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal upload foto');
            showToast(errMsg, 'error');
            return;
        }
        
        Auth.setUser(data.user);
        updatePhotoPreview(data.user);
        renderProfile();
        showToast('📷 Foto profil berhasil diunggah!');
        
        const removeBtn = document.getElementById('btn-remove-photo');
        if (removeBtn) removeBtn.style.display = 'inline-flex';
    } catch(err) {
        showToast('Gagal mengunggah foto', 'error');
    }
});

async function removeProfilePhoto() {
    if (!confirm('Hapus foto profil?')) return;
    try {
        const token = Auth.getToken();
        const res = await fetch('/api/auth/remove-photo', {
            method: 'DELETE',
            headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (!res.ok) { showToast(data.message || 'Gagal menghapus foto', 'error'); return; }
        
        Auth.setUser(data.user);
        updatePhotoPreview(data.user);
        renderProfile();
        showToast('Foto profil berhasil dihapus');
    } catch(err) {
        showToast('Gagal menghapus foto', 'error');
    }
}

function selectAvatarItem(emoji, el) {
    document.querySelectorAll('.avatar-option-item').forEach(opt => {
        opt.style.border = '2px solid transparent';
        opt.style.background = 'transparent';
    });
    el.style.border = '2px solid var(--primary)';
    el.style.background = 'var(--primary-50)';
    document.getElementById('eup-avatar').value = emoji;
}

function closeUserProfileModal() {
    document.getElementById('user-profile-modal').classList.remove('show');
}

document.getElementById('edit-user-profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('eup-btn');
    btn.textContent = 'Memproses...'; btn.disabled = true;

    const name = document.getElementById('eup-name').value;
    const phone = document.getElementById('eup-phone').value;
    const avatar = document.getElementById('eup-avatar').value;
    const gender = document.getElementById('eup-gender').value;
    const region_id = document.getElementById('eup-region').value;
    const address = document.getElementById('eup-address').value;
    const currentPassword = document.getElementById('eup-current-password').value;
    const newPassword = document.getElementById('eup-new-password').value;
    const confirmPassword = document.getElementById('eup-confirm-password').value;

    try {
        // Update general profile
        const profRes = await api('/auth/profile', 'PUT', { name, phone, avatar, gender, region_id, address });
        Auth.setUser(profRes.user);

        // Update password if requested
        if (newPassword || currentPassword) {
            if (!currentPassword) {
                showToast('Masukkan password saat ini untuk mengganti password', 'error');
                btn.textContent = 'Simpan Perubahan'; btn.disabled = false;
                return;
            }
            if (newPassword !== confirmPassword) {
                showToast('Konfirmasi password baru tidak cocok', 'error');
                btn.textContent = 'Simpan Perubahan'; btn.disabled = false;
                return;
            }
            await api('/auth/change-password', 'PUT', {
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            });
            showToast('Profil & Password berhasil diperbarui');
        } else {
            showToast('Profil berhasil diperbarui');
        }

        renderProfile();
        closeUserProfileModal();
    } catch (err) {
        const msg = err.data?.errors ? Object.values(err.data.errors).flat().join(', ') : (err.data?.message || 'Gagal memperbarui profil');
        showToast(msg, 'error');
    }
    btn.textContent = 'Simpan Perubahan'; btn.disabled = false;
});
</script>
@endpush
