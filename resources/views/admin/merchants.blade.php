@extends('layouts.app')
@section('title', 'Verifikasi Pedagang - Admin')
@section('no-footer', true)
@section('content')
<div class="admin-layout">
    <aside class="sidebar">
        <a href="/admin" class="sidebar-link" id="sb-dashboard">📊 Dashboard</a>
        <a href="/admin/merchants" class="sidebar-link active" id="sb-merchants">🏪 Pedagang</a>
        <a href="/admin/users" class="sidebar-link" id="sb-users">👥 Pengguna</a>
        <a href="/admin/prices" class="sidebar-link" id="sb-prices">💰 Moderasi Harga</a>
        <a href="/admin/reports" class="sidebar-link" id="sb-reports">📋 Laporan</a>
    </aside>
    <div class="admin-content">
        <h1 class="page-title">🏪 Manajemen Pedagang</h1>
        <div class="tabs mt-3">
            <button class="tab active" onclick="loadMerchants('pending',this)">Verifikasi Pedagang</button>
            <button class="tab" onclick="loadMerchants('all',this)">Semua Pedagang</button>
            <button class="tab" onclick="loadStoreRequests(this)">Pengajuan Toko</button>
        </div>
        <div id="merchants-list"><div class="spinner"></div></div>
    </div>
</div>

{{-- Photo Viewer Modal --}}
<div class="modal-overlay" id="photo-modal" style="backdrop-filter: blur(8px); background: rgba(15,23,42,0.6);" onclick="if(event.target===this) this.classList.remove('show')">
    <div class="modal" style="max-width: 600px; width: 90%; padding: 0; background: transparent; box-shadow: none; border: none; text-align: center;">
        <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
            <button class="btn btn-ghost" style="color: #fff; background: rgba(0,0,0,0.5); border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;" onclick="document.getElementById('photo-modal').classList.remove('show')">✕</button>
        </div>
        <img id="modal-photo-img" src="" alt="Foto Toko Full" style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); object-fit: contain;">
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if(!Auth.requireAuth()||!Auth.hasRole('admin')){window.location.href='/';return;} loadMerchants('pending'); });

async function loadMerchants(type, tabEl) {
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    if(tabEl) tabEl.classList.add('active'); else document.querySelector('.tab').classList.add('active');
    const el = document.getElementById('merchants-list');
    el.innerHTML = '<div class="spinner"></div>';
    try {
        const url = type==='pending' ? '/admin/merchants/pending' : '/admin/merchants';
        const data = await api(url);
        const items = data.data || [];
        if (!items.length) { el.innerHTML = '<div class="card text-center" style="padding:40px;"><div style="font-size:2.5rem;margin-bottom:8px;">✅</div><p class="text-muted">Tidak ada data</p></div>'; return; }
        el.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr><th>Pedagang</th><th>Toko</th><th>Foto Toko</th><th>Pasar</th><th>Status</th><th>Aksi</th></tr></thead><tbody>' +
        items.map(m => `<tr>
            <td><strong>${m.user?.name||'-'}</strong><br><span class="text-sm text-muted">${m.user?.email||''}</span></td>
            <td>${m.store_name}<br><span class="text-sm text-muted">${m.store_address||''}</span></td>
            <td>${m.shop_photo ? `<img src="/storage/${m.shop_photo}" alt="Foto Toko" style="width:60px;height:60px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid var(--border);" onclick="viewPhoto('/storage/${m.shop_photo}')">` : '<span class="text-muted text-sm">Tidak ada</span>'}</td>
            <td>${m.market?.name||'-'}<br><span class="text-sm text-muted">${m.market?.region?.name||''}</span></td>
            <td>${m.status==='approved'?'<span class="badge badge-success">Verified</span>':m.status==='pending'?'<span class="badge badge-warning">Pending</span>':'<span class="badge badge-danger">Rejected</span>'}</td>
            <td>${m.status==='pending'?`<div style="display:flex;gap:6px;"><button class="btn btn-primary btn-sm" onclick="approveMerchant(${m.user_id})">✅ Approve</button><button class="btn btn-danger btn-sm" onclick="rejectMerchant(${m.user_id})">❌ Reject</button></div>`:'-'}</td>
        </tr>`).join('') + '</tbody></table></div>';
    } catch(e) { el.innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
}

async function loadStoreRequests(tabEl) {
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    if(tabEl) tabEl.classList.add('active');
    const el = document.getElementById('merchants-list');
    el.innerHTML = '<div class="spinner"></div>';
    try {
        const data = await api('/admin/stores/pending');
        const items = data.data || [];
        if (!items.length) { el.innerHTML = '<div class="card text-center" style="padding:40px;"><div style="font-size:2.5rem;margin-bottom:8px;">✅</div><p class="text-muted">Tidak ada pengajuan toko baru</p></div>'; return; }
        el.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr><th>Pedagang</th><th>Nama Toko</th><th>Foto Toko</th><th>Pasar</th><th>Daerah</th><th>Aksi</th></tr></thead><tbody>' +
        items.map(s => `<tr>
            <td><strong>${s.user?.name||'-'}</strong><br><span class="text-sm text-muted">${s.user?.email||''}</span></td>
            <td>${s.store_name}<br><span class="text-sm text-muted">${s.store_address||''}</span></td>
            <td>${s.shop_photo ? `<img src="/storage/${s.shop_photo}" alt="Foto Toko" style="width:60px;height:60px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid var(--border);" onclick="viewPhoto('/storage/${s.shop_photo}')">` : '<span class="text-muted text-sm">Tidak ada</span>'}</td>
            <td>${s.market?.name||'-'}</td>
            <td>${s.market?.region?.name||s.region?.name||'-'}</td>
            <td><div style="display:flex;gap:6px;">
                <button class="btn btn-primary btn-sm" onclick="approveStore(${s.id})">✅ Setujui</button>
                <button class="btn btn-danger btn-sm" onclick="rejectStore(${s.id})">❌ Tolak</button>
            </div></td>
        </tr>`).join('') + '</tbody></table></div>';
    } catch(e) { el.innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
}

async function approveMerchant(uid) {
    try { await api(`/admin/merchants/${uid}/approve`, 'POST'); showToast('Pedagang diverifikasi!'); loadMerchants('pending'); } catch(e) { showToast('Gagal', 'error'); }
}
async function rejectMerchant(uid) {
    if(!confirm('Tolak pedagang ini?')) return;
    try { await api(`/admin/merchants/${uid}/reject`, 'POST'); showToast('Pedagang ditolak'); loadMerchants('pending'); } catch(e) { showToast('Gagal', 'error'); }
}
async function approveStore(id) {
    try { await api(`/admin/stores/${id}/approve`, 'POST'); showToast('Toko disetujui!'); loadStoreRequests(); } catch(e) { showToast('Gagal', 'error'); }
}
async function rejectStore(id) {
    if(!confirm('Tolak pengajuan toko ini?')) return;
    try { await api(`/admin/stores/${id}/reject`, 'POST'); showToast('Pengajuan ditolak'); loadStoreRequests(); } catch(e) { showToast('Gagal', 'error'); }
}

function viewPhoto(url) {
    document.getElementById('modal-photo-img').src = url;
    document.getElementById('photo-modal').classList.add('show');
}
</script>
@endpush
