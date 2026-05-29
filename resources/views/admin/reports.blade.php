@extends('layouts.app')
@section('title', 'Laporan - Admin')
@section('no-footer', true)
@section('content')
<div class="admin-layout">
    <aside class="sidebar">
        <a href="/admin" class="sidebar-link" id="sb-dashboard">📊 Dashboard</a>
        <a href="/admin/merchants" class="sidebar-link" id="sb-merchants">🏪 Pedagang</a>
        <a href="/admin/users" class="sidebar-link" id="sb-users">👥 Pengguna</a>
        <a href="/admin/prices" class="sidebar-link" id="sb-prices">💰 Moderasi Harga</a>
        <a href="/admin/reports" class="sidebar-link active" id="sb-reports">📋 Laporan</a>
    </aside>
    <div class="admin-content">
        <h1 class="page-title">📋 Laporan</h1>
        <p class="page-subtitle mb-3">Monitor laporan dari pengguna</p>
        <div class="card mb-3" style="padding:16px;">
            <div style="display:flex;gap:12px;">
                <select class="form-select" id="filter-type" style="width:auto;" onchange="loadReports()">
                    <option value="">Semua Tipe</option>
                    <option value="inappropriate_comment">Komentar</option>
                    <option value="price_anomaly">Harga Anomali</option>
                    <option value="bug">Bug</option>
                </select>
                <select class="form-select" id="filter-status" style="width:auto;" onchange="loadReports()">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>
        <div id="reports-list"><div class="spinner"></div></div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if(!Auth.requireAuth()||!Auth.hasRole('admin')){window.location.href='/';return;} loadReports(); });

async function loadReports() {
    const el = document.getElementById('reports-list');
    el.innerHTML = '<div class="spinner"></div>';
    let params = '?';
    const t = document.getElementById('filter-type').value; if(t) params += `type=${t}&`;
    const s = document.getElementById('filter-status').value; if(s) params += `status=${s}&`;
    try {
        const data = await api('/admin/reports' + params);
        const items = data.data || [];
        if (!items.length) { el.innerHTML = '<div class="card text-center" style="padding:48px;"><p class="text-muted">Tidak ada laporan</p></div>'; return; }
        const typeMap = { inappropriate_comment: '💬 Komentar', price_anomaly: '💰 Harga', bug: '🐛 Bug' };
        const statusMap = { pending: 'badge-warning', reviewed: 'badge-info', resolved: 'badge-success' };
        el.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr><th>Pelapor</th><th>Tipe</th><th>Deskripsi</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>' +
        items.map(r => `<tr>
            <td style="font-weight:600">${r.user?.name||'-'}</td>
            <td><span class="badge badge-info">${typeMap[r.type]||r.type}</span></td>
            <td class="text-sm" style="max-width:300px;">${r.description?.substring(0,100)||'-'}</td>
            <td><span class="badge ${statusMap[r.status]||''}">${r.status}</span></td>
            <td class="text-sm text-muted">${timeAgo(r.created_at)}</td>
            <td>${r.status!=='resolved'?`<div style="display:flex;gap:4px;">
                ${r.status==='pending'?`<button class="btn btn-secondary btn-sm" onclick="reviewReport(${r.id})">Review</button>`:''}
                <button class="btn btn-primary btn-sm" onclick="resolveReport(${r.id})">Resolve</button>
            </div>`:''}</td>
        </tr>`).join('') + '</tbody></table></div>';
    } catch(e) { el.innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
}

async function reviewReport(id) { try { await api(`/admin/reports/${id}/review`, 'POST'); showToast('Laporan ditinjau'); loadReports(); } catch(e) { showToast('Gagal', 'error'); } }
async function resolveReport(id) { try { await api(`/admin/reports/${id}/resolve`, 'POST'); showToast('Laporan diselesaikan'); loadReports(); } catch(e) { showToast('Gagal', 'error'); } }
</script>
@endpush
