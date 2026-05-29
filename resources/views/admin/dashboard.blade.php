@extends('layouts.app')
@section('title', 'Admin Dashboard - Pantau Pangan')
@section('no-footer', true)
@section('content')
<div class="admin-layout">
    <aside class="sidebar">
        <a href="/admin" class="sidebar-link active" id="sb-dashboard">📊 Dashboard</a>
        <a href="/admin/merchants" class="sidebar-link" id="sb-merchants">🏪 Pedagang</a>
        <a href="/admin/users" class="sidebar-link" id="sb-users">👥 Pengguna</a>
        <a href="/admin/prices" class="sidebar-link" id="sb-prices">💰 Moderasi Harga</a>
        <a href="/admin/reports" class="sidebar-link" id="sb-reports">📋 Laporan</a>
    </aside>
    <div class="admin-content">
        <div class="flex-between mb-3">
            <div>
                <h1 class="page-title">📊 Admin Dashboard</h1>
                <p class="page-subtitle mt-1">Ringkasan platform PantauPangan</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="exportPrices()">📥 Ekspor CSV Laporan</button>
            </div>
        </div>

        <div class="grid grid-4" id="stats-grid" style="margin-bottom:32px;">
            <div class="stat-card"><div class="stat-value" id="s-users">-</div><div class="stat-label">Total User</div></div>
            <div class="stat-card"><div class="stat-value" id="s-merchants">-</div><div class="stat-label">Pedagang Terverifikasi</div></div>
            <div class="stat-card"><div class="stat-value" id="s-pending">-</div><div class="stat-label">Menunggu Verifikasi</div></div>
            <div class="stat-card"><div class="stat-value" id="s-suspicious">-</div><div class="stat-label">Harga Suspicious</div></div>
        </div>

        <div class="grid grid-2">
            <div class="card"><h3 style="font-weight:700;margin-bottom:16px;">📈 Tren Harga (30 Hari)</h3><div style="height:280px;"><canvas id="trend-chart"></canvas></div></div>
            <div class="card"><h3 style="font-weight:700;margin-bottom:16px;">🏆 Produk Populer</h3><div id="popular-list"><div class="spinner"></div></div></div>
        </div>

        <div class="grid grid-2 mt-3">
            <div class="card"><h3 style="font-weight:700;margin-bottom:16px;">👤 User Baru (30 Hari)</h3><div style="height:250px;"><canvas id="users-chart"></canvas></div></div>
            <div class="card"><h3 style="font-weight:700;margin-bottom:16px;">🗺️ Harga Rata-rata per Daerah</h3><div id="region-prices"><div class="spinner"></div></div></div>
        </div>

        <div class="card mt-3">
            <h3 style="font-weight:700;margin-bottom:16px;">📢 Broadcast Pengumuman Global</h3>
            <p class="text-sm text-muted mb-3">Kirim notifikasi ke seluruh pengguna atau pedagang secara real-time.</p>
            <form id="broadcast-form" style="display:flex; flex-direction:column; gap:16px;">
                <textarea id="broadcast-message" class="form-textarea" rows="3" placeholder="Tulis pesan pengumuman di sini..." required></textarea>
                <div class="flex-between">
                    <select id="broadcast-target" class="form-select" style="width:200px;">
                        <option value="all">Semua Pengguna</option>
                        <option value="merchants">Hanya Pedagang</option>
                    </select>
                    <button type="submit" id="broadcast-btn" class="btn btn-accent">Kirim Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    if(!Auth.requireAuth()) return;
    if(!Auth.hasRole('admin')){window.location.href='/dashboard';return;}

    try {
        const stats = await api('/admin/dashboard');
        document.getElementById('s-users').textContent = stats.total_users;
        document.getElementById('s-merchants').textContent = stats.approved_merchants;
        document.getElementById('s-pending').textContent = stats.pending_merchants;
        document.getElementById('s-suspicious').textContent = stats.suspicious_prices;
    } catch(e) {}

    try {
        const analytics = await api('/admin/analytics');
        // Price trend chart
        if (analytics.price_trend?.length) {
            new Chart(document.getElementById('trend-chart'), {
                type:'line',data:{labels:analytics.price_trend.map(d=>d.date),datasets:[{label:'Rata-rata Harga',data:analytics.price_trend.map(d=>Math.round(d.avg_price)),borderColor:'#059669',backgroundColor:'rgba(5,150,105,0.1)',fill:true,tension:0.4}]},
                options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{ticks:{callback:v=>formatRupiah(v)}}}}
            });
        }
        // Users chart
        if (analytics.new_users?.length) {
            new Chart(document.getElementById('users-chart'), {
                type:'bar',data:{labels:analytics.new_users.map(d=>d.date),datasets:[{label:'User Baru',data:analytics.new_users.map(d=>d.count),backgroundColor:'#0ea5e9',borderRadius:6}]},
                options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}
            });
        }
        // Popular products
        if (analytics.popular_products?.length) {
            document.getElementById('popular-list').innerHTML = analytics.popular_products.slice(0,8).map((p,i) => `<div class="flex-between" style="padding:8px 0;border-bottom:1px solid var(--border);"><div class="flex gap-sm" style="align-items:center"><span class="text-muted text-sm" style="width:24px">#${i+1}</span><span style="font-weight:600;font-size:0.9rem">${p.name}</span></div><span class="badge badge-info">${p.prices_count} data</span></div>`).join('');
        }
        // Region prices
        if (analytics.region_prices?.length) {
            document.getElementById('region-prices').innerHTML = analytics.region_prices.map(r => `<div class="flex-between" style="padding:8px 0;border-bottom:1px solid var(--border);"><span style="font-weight:600;font-size:0.9rem">${r.name}</span><span style="font-weight:700;color:var(--primary)">${formatRupiah(r.avg_price)}</span></div>`).join('');
        }
    } catch(e) {}

    // Broadcast form logic
    document.getElementById('broadcast-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('broadcast-btn');
        const message = document.getElementById('broadcast-message').value.trim();
        const target = document.getElementById('broadcast-target').value;
        
        if (!message) return;
        
        btn.textContent = 'Mengirim...';
        btn.disabled = true;
        
        try {
            const res = await api('/admin/broadcast', 'POST', { message, target });
            showToast(res.message || 'Broadcast terkirim!');
            document.getElementById('broadcast-form').reset();
        } catch (err) {
            showToast('Gagal mengirim broadcast', 'error');
        }
        
        btn.textContent = 'Kirim Broadcast';
        btn.disabled = false;
    });
});

function exportPrices() {
    // Generate an export by directing browser to download route with JWT in query if possible, or using fetch to blob.
    // Easiest is to use fetch, get blob, and trigger download.
    const token = localStorage.getItem('token');
    showToast('Menyiapkan laporan...', 'info');
    fetch('/api/admin/export-prices', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
    .then(res => {
        if (!res.ok) throw new Error('Export failed');
        return res.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'laporan_harga_terbaru.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        showToast('Laporan berhasil diunduh!');
    })
    .catch(() => showToast('Gagal mengunduh laporan', 'error'));
}
</script>
@endpush
