@extends('layouts.app')
@section('title', 'Moderasi Harga - Admin')
@section('no-footer', true)
@section('content')
<div class="admin-layout">
    <aside class="sidebar">
        <a href="/admin" class="sidebar-link" id="sb-dashboard">📊 Dashboard</a>
        <a href="/admin/merchants" class="sidebar-link" id="sb-merchants">🏪 Pedagang</a>
        <a href="/admin/users" class="sidebar-link" id="sb-users">👥 Pengguna</a>
        <a href="/admin/prices" class="sidebar-link active" id="sb-prices">💰 Moderasi Harga</a>
        <a href="/admin/reports" class="sidebar-link" id="sb-reports">📋 Laporan</a>
    </aside>
    <div class="admin-content">
        <h1 class="page-title">💰 Moderasi Harga</h1>
        <p class="page-subtitle mb-3">Review harga yang terdeteksi anomali oleh sistem</p>
        <div id="suspicious-list"><div class="spinner"></div></div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if(!Auth.requireAuth()||!Auth.hasRole('admin')){window.location.href='/';return;} loadSuspicious(); });

async function loadSuspicious() {
    const el = document.getElementById('suspicious-list');
    try {
        const data = await api('/admin/prices/suspicious');
        const items = data.data || [];
        if (!items.length) { el.innerHTML = '<div class="card text-center" style="padding:48px;"><div style="font-size:3rem;margin-bottom:12px;">✅</div><p class="text-muted">Tidak ada harga suspicious saat ini</p></div>'; return; }
        el.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr><th>Produk</th><th>Pasar</th><th>Harga Input</th><th>Pedagang</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>' +
        items.map(p => `<tr style="background:rgba(239,68,68,0.04);">
            <td style="font-weight:600">${p.product?.name||'-'}</td>
            <td>${p.market?.name||'-'}<br><span class="text-sm text-muted">${p.market?.region?.name||''}</span></td>
            <td><span style="font-weight:700;color:var(--danger);font-size:1.1rem;">${formatRupiah(p.price)}</span><br><span class="badge badge-danger">⚠️ Anomali</span></td>
            <td>${p.user?.name||'-'}<br><span class="text-sm text-muted">${p.user?.email||''}</span></td>
            <td class="text-sm text-muted">${timeAgo(p.created_at)}</td>
            <td><div style="display:flex;gap:6px;"><button class="btn btn-primary btn-sm" onclick="clearPrice(${p.id})">✅ Clear</button><button class="btn btn-danger btn-sm" onclick="deletePrice(${p.id})">🗑️ Hapus</button></div></td>
        </tr>`).join('') + '</tbody></table></div>';
    } catch(e) { el.innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
}

async function clearPrice(id) {
    try { await api(`/admin/prices/${id}/clear`, 'POST'); showToast('Status suspicious dihapus'); loadSuspicious(); } catch(e) { showToast('Gagal', 'error'); }
}
async function deletePrice(id) {
    if(!confirm('Hapus harga anomali ini?')) return;
    try { await api(`/admin/prices/${id}`, 'DELETE'); showToast('Harga dihapus'); loadSuspicious(); } catch(e) { showToast('Gagal', 'error'); }
}
</script>
@endpush
