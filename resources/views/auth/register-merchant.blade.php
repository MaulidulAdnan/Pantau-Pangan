@extends('layouts.app')
@section('title', 'Daftar Pedagang - Pantau Pangan')
@section('content')
<div class="container" style="max-width:520px;padding-top:40px;">
    <div class="card" style="padding:40px;">
        <div class="text-center mb-3">
            <div style="font-size:2.5rem;margin-bottom:8px;">🏪</div>
            <h1 style="font-size:1.5rem;font-weight:800;">Daftar Pedagang</h1>
            <p class="text-muted text-sm">Bergabung sebagai pedagang terverifikasi dan kontribusi data harga pangan</p>
        </div>
        <form id="merchant-form">
            <h3 style="font-weight:700;font-size:0.95rem;margin-bottom:16px;color:var(--primary);">📋 Data Pribadi</h3>
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-input" id="name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="email" required>
            </div>
            <div class="form-group">
                <label class="form-label">No. Telepon</label>
                <input type="text" class="form-input" id="phone">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" id="password" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-input" id="password_confirmation" required>
            </div>
            <h3 style="font-weight:700;font-size:0.95rem;margin:24px 0 16px;color:var(--primary);">🏪 Data Toko</h3>
            <div class="form-group">
                <label class="form-label">Nama Toko</label>
                <input type="text" class="form-input" id="store_name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Toko</label>
                <input type="text" class="form-input" id="store_address">
            </div>
            <div class="form-group">
                <label class="form-label">Daerah Operasional</label>
                <select class="form-select" id="region_id" required>
                    <option value="">Pilih Daerah</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Pasar Utama</label>
                <select class="form-select" id="market_id" required disabled>
                    <option value="">Pilih Daerah terlebih dahulu</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Foto Toko / Kios <span class="text-error">*</span></label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div id="shop-photo-preview-container" style="display: none; width: 100%; height: 200px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border); background: var(--bg-color);">
                        <img id="shop-photo-preview" src="" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div>
                        <input type="file" class="form-input" id="shop_photo" accept="image/*" required onchange="previewShopPhoto(this)">
                        <p class="text-sm text-muted mt-1" style="font-size: 0.75rem;">Maksimal 2MB (JPG, PNG, WebP). Bukti fisik toko untuk mempermudah verifikasi.</p>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi (opsional)</label>
                <textarea class="form-textarea" id="description" rows="3" placeholder="Ceritakan tentang toko Anda..."></textarea>
            </div>
            <div class="card" style="background:var(--primary-50);border-color:var(--primary-light);padding:12px 16px;margin-bottom:20px;">
                <p class="text-sm" style="color:var(--primary-dark);">⚠️ Setelah mendaftar, akun pedagang Anda perlu diverifikasi oleh admin sebelum dapat menginput harga.</p>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;" id="reg-btn">Daftar sebagai Pedagang</button>
        </form>
        <div class="text-center mt-3 text-sm">
            Sudah punya akun? <a href="/login">Masuk</a> · <a href="/register">Daftar User</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const data = await api('/regions');
        const regSel = document.getElementById('region_id');
        (data.regions||[]).forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            regSel.appendChild(opt);
        });
        
        regSel.addEventListener('change', async function() {
            const marketSel = document.getElementById('market_id');
            const regionId = this.value;
            marketSel.innerHTML = '<option value="">Pilih Pasar</option>';
            if (!regionId) {
                marketSel.disabled = true;
                marketSel.innerHTML = '<option value="">Pilih Daerah terlebih dahulu</option>';
                return;
            }
            marketSel.disabled = true;
            try {
                const mData = await api(`/regions/${regionId}/markets`);
                (mData.markets||[]).forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name;
                    marketSel.appendChild(opt);
                });
                marketSel.disabled = false;
            } catch(e) {
                showToast('Gagal memuat pasar di daerah ini', 'error');
            }
        });
    } catch(e) {}
});
document.getElementById('merchant-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('reg-btn');
    btn.textContent = 'Memproses...'; btn.disabled = true;
    
    const fileInput = document.getElementById('shop_photo');
    if (!fileInput.files[0]) {
        showToast('Pilih foto toko terlebih dahulu', 'error');
        btn.textContent = 'Daftar sebagai Pedagang'; btn.disabled = false;
        return;
    }
    if (fileInput.files[0].size > 2 * 1024 * 1024) {
        showToast('Ukuran foto toko maksimal 2MB', 'error');
        btn.textContent = 'Daftar sebagai Pedagang'; btn.disabled = false;
        return;
    }

    try {
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('password_confirmation', document.getElementById('password_confirmation').value);
        formData.append('store_name', document.getElementById('store_name').value);
        formData.append('market_id', document.getElementById('market_id').value);
        formData.append('shop_photo', fileInput.files[0]);
        
        const phone = document.getElementById('phone').value;
        if (phone) formData.append('phone', phone);
        const storeAddress = document.getElementById('store_address').value;
        if (storeAddress) formData.append('store_address', storeAddress);
        const description = document.getElementById('description').value;
        if (description) formData.append('description', description);

        const res = await fetch('/api/auth/register-merchant', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });
        
        const data = await res.json();
        if (!res.ok) {
            throw { status: res.status, data };
        }

        Auth.setToken(data.token);
        Auth.setUser(data.user);
        showToast('Registrasi pedagang berhasil! Menunggu verifikasi admin.');
        setTimeout(() => window.location.href = '/merchant', 1000);
    } catch(err) {
        const msgs = err.data?.errors ? Object.values(err.data.errors).flat().join(', ') : (err.data?.message || 'Registrasi gagal');
        showToast(msgs, 'error');
        btn.textContent = 'Daftar sebagai Pedagang'; btn.disabled = false;
    }
});

function previewShopPhoto(input) {
    const previewContainer = document.getElementById('shop-photo-preview-container');
    const previewImg = document.getElementById('shop-photo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = '';
        previewContainer.style.display = 'none';
    }
}
</script>
@endpush
