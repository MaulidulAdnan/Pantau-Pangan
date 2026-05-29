@extends('layouts.app')
@section('title', 'Manajemen User - Admin')
@section('no-footer', true)
@section('content')
<div class="admin-layout">
    <aside class="sidebar">
        <a href="/admin" class="sidebar-link" id="sb-dashboard">📊 Dashboard</a>
        <a href="/admin/merchants" class="sidebar-link" id="sb-merchants">🏪 Pedagang</a>
        <a href="/admin/users" class="sidebar-link active" id="sb-users">👥 Pengguna</a>
        <a href="/admin/prices" class="sidebar-link" id="sb-prices">💰 Moderasi Harga</a>
        <a href="/admin/reports" class="sidebar-link" id="sb-reports">📋 Laporan</a>
    </aside>
    <div class="admin-content">
        <h1 class="page-title">👥 Manajemen Pengguna</h1>
        <div class="card mt-3" style="padding:16px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <input type="text" class="form-input" id="user-search" placeholder="Cari nama atau email..." style="max-width:300px;">
                <select class="form-select" id="user-role" style="width:auto;"><option value="">Semua Role</option><option value="user">User</option><option value="pedagang">Pedagang</option><option value="admin">Admin</option></select>
                <select class="form-select" id="user-status" style="width:auto;"><option value="">Semua Status</option><option value="active">Active</option><option value="suspended">Suspended</option><option value="muted">Muted</option></select>
            </div>
        </div>
        <div id="users-list" class="mt-3"><div class="spinner"></div></div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => { if(!Auth.requireAuth()||!Auth.hasRole('admin')){window.location.href='/';return;} loadUsers(); });
document.getElementById('user-search').addEventListener('input', debounce(loadUsers, 400));
document.getElementById('user-role').addEventListener('change', loadUsers);
document.getElementById('user-status').addEventListener('change', loadUsers);

function debounce(fn, ms) { let t; return function() { clearTimeout(t); t = setTimeout(fn, ms); }; }

async function loadUsers() {
    const el = document.getElementById('users-list');
    el.innerHTML = '<div class="spinner"></div>';
    let params = '?';
    const s = document.getElementById('user-search').value; if(s) params += `search=${s}&`;
    const r = document.getElementById('user-role').value; if(r) params += `role=${r}&`;
    const st = document.getElementById('user-status').value; if(st) params += `status=${st}&`;
    try {
        const data = await api('/admin/users' + params);
        const items = data.data || [];
        el.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead><tbody>' +
        items.map(u => `<tr>
            <td style="font-weight:600">${u.name}</td><td class="text-sm">${u.email}</td>
            <td>${roleBadge(u.role)}</td>
            <td>${u.status==='active'?'<span class="badge badge-success">Active</span>':u.status==='suspended'?'<span class="badge badge-danger">Suspended</span>':'<span class="badge badge-warning">Muted</span>'}</td>
            <td>${u.role!=='admin'?`<div style="display:flex;gap:4px;flex-wrap:wrap;">
                ${u.status!=='suspended'?`<button class="btn btn-danger btn-sm" onclick="userAction(${u.id},'suspend')">Suspend</button>`:''}
                ${u.status!=='muted'?`<button class="btn btn-accent btn-sm" onclick="userAction(${u.id},'mute')">Mute</button>`:''}
                ${u.status!=='active'?`<button class="btn btn-primary btn-sm" onclick="userAction(${u.id},'activate')">Activate</button>`:''}
            </div>`:''}</td>
        </tr>`).join('') + '</tbody></table></div>';
    } catch(e) { el.innerHTML = '<p class="text-muted">Gagal memuat</p>'; }
}

async function userAction(uid, action) {
    try { await api(`/admin/users/${uid}/${action}`, 'POST'); showToast('Berhasil!'); loadUsers(); } catch(e) { showToast('Gagal', 'error'); }
}
</script>
@endpush
