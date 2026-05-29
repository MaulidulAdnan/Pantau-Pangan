@extends('layouts.app')
@section('title', 'Detail Produk - Pantau Pangan')
@section('content')
<div class="container">
    <div id="product-detail"><div class="spinner"></div></div>
</div>
@endsection
@push('scripts')
<script>
const SLUG = '{{ $slug }}';
let productId = null, isFav = false, chartInstance = null, selectedRegion = '';

document.addEventListener('DOMContentLoaded', () => loadProduct());

async function loadProduct() {
    try {
        const data = await api(`/products/${SLUG}`);
        const p = data.product;
        productId = p.id;
        // Check favorite
        if (Auth.isLoggedIn()) {
            try { const favs = await api('/favorites'); isFav = favs.favorites.some(f => f.id === p.id); } catch(e) {}
        }
        document.title = `${p.name} - Pantau Pangan`;
        document.getElementById('product-detail').innerHTML = `
        <div style="margin-bottom:8px;"><a href="/products" class="text-sm text-muted">← Kembali ke Produk</a></div>
        <div class="grid" style="grid-template-columns:1fr 1fr;gap:32px;">
            <div>
                <div class="flex-between"><div>
                    <span class="badge badge-info">${p.category?.name||''}</span>
                    <h1 class="page-title mt-1">${p.name}</h1>
                    <p class="text-muted text-sm">Satuan: ${p.unit}</p>
                </div>
                <button class="btn ${isFav?'btn-accent':'btn-secondary'}" id="fav-btn" onclick="toggleFav()">${isFav?'⭐ Favorit':'☆ Favorit'}</button>
                </div>
                <div class="grid grid-3 mt-3" style="gap:12px;">
                    <div class="stat-card"><div class="stat-value">${formatRupiah(data.average_price)}</div><div class="stat-label">Rata-rata / ${p.unit}</div></div>
                    <div class="stat-card"><div class="stat-value">${data.price_change!==null?data.price_change+'%':'-'}</div><div class="stat-label">Perubahan Mingguan</div>${data.price_change!==null?formatChange(data.price_change):''}</div>
                    <div class="stat-card"><div class="stat-value">${data.prices_by_market?.length||0}</div><div class="stat-label">Pasar Tersedia</div></div>
                </div>
                <div class="mt-3">
                    <div class="flex-between" style="margin-bottom:12px; gap: 12px; flex-wrap: wrap;">
                        <h3 style="font-weight:700;margin:0;">📊 Grafik Harga</h3>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <select id="filter-region-detail" class="form-select text-sm" style="padding:4px 8px; width:auto; border-radius:4px;" onchange="changeRegionFilter(this.value)">
                                <option value="">Semua Daerah</option>
                            </select>
                            <div id="unit-selector-container"></div>
                        </div>
                    </div>
                    <div class="tabs"><button class="tab active" onclick="loadChart('daily',this)">Harian</button><button class="tab" onclick="loadChart('weekly',this)">Mingguan</button><button class="tab" onclick="loadChart('monthly',this)">Bulanan</button></div>
                    <div class="chart-container"><canvas id="price-chart"></canvas></div>
                </div>
            </div>
            <div>
                <h3 style="font-weight:700;margin-bottom:12px;">🏪 Harga Per Pasar</h3>
                <div class="table-wrap"><table class="table"><thead><tr><th>Pasar</th><th>Daerah</th><th>Harga</th><th>Stok</th></tr></thead><tbody id="market-prices">
                ${(data.prices_by_market||[]).map(pr=>`<tr data-region="${pr.market?.region?.slug||''}"><td style="font-weight:600">${pr.market?.name||'-'}</td><td>${pr.market?.region?.name||'-'}</td><td class="product-price" style="font-size:0.9rem">${formatRupiah(pr.price)}</td><td>${stockLabel(pr.stock_status)}</td></tr>`).join('')||'<tr id="market-prices-empty"><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>'}
                </tbody></table></div>
                <div class="mt-3"><h3 style="font-weight:700;margin-bottom:12px;">💬 Diskusi Komunitas</h3><div id="comments-section"></div>
                ${Auth.isLoggedIn()?`<form id="comment-form" class="mt-2"><textarea class="form-textarea" id="comment-body" rows="3" placeholder="Tulis komentar atau pertanyaan..."></textarea><button type="submit" class="btn btn-primary btn-sm mt-1">Kirim Komentar</button></form>`:'<p class="text-sm text-muted mt-2"><a href="/login">Masuk</a> untuk berkomentar</p>'}
                </div>
            </div>
        </div>`;
        
        setupUnitSelector(p.unit);
        loadRegions();
        loadChart('daily');
        loadComments();
        if (Auth.isLoggedIn()) {
            document.getElementById('comment-form')?.addEventListener('submit', submitComment);
        }
    } catch(e) {
        document.getElementById('product-detail').innerHTML = '<div class="text-center mt-4"><p class="text-muted">Produk tidak ditemukan</p><a href="/products" class="btn btn-primary mt-2">Kembali</a></div>';
    }
}

const unitConversions = {
    'kg': { '1 Kg': 1, '1/2 Kg (500g)': 0.5, '1/4 Kg (250g)': 0.25, '1 Ons (100g)': 0.1 },
    'liter': { '1 Liter': 1, '1/2 Liter (500ml)': 0.5, '1/4 Liter (250ml)': 0.25 }
};
let currentRawChartData = [];
let currentProductUnit = '';
let currentPeriod = 'daily';

function setupUnitSelector(baseUnit) {
    currentProductUnit = baseUnit.toLowerCase();
    const container = document.getElementById('unit-selector-container');
    if (unitConversions[currentProductUnit]) {
        let options = '';
        for (const [label, factor] of Object.entries(unitConversions[currentProductUnit])) {
            options += `<option value="${factor}">${label}</option>`;
        }
        container.innerHTML = `<select id="chart-unit-select" class="form-select text-sm" style="padding:4px 8px; width:auto; border-radius:4px;" onchange="redrawChart()">${options}</select>`;
    } else {
        container.innerHTML = `<span class="badge badge-secondary">Satuan: ${baseUnit}</span>`;
    }
}

async function loadChart(period, tabEl) {
    if(period) currentPeriod = period;
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    if(tabEl) tabEl.classList.add('active');
    else document.querySelector(`.tab[onclick="loadChart('${currentPeriod}',this)"]`)?.classList.add('active');
    
    try {
        let url = `/prices/${productId}/chart?period=${currentPeriod}`;
        if (selectedRegion) url += `&region=${selectedRegion}`;
        const data = await api(url);
        currentRawChartData = data.chart_data;
        redrawChart();
    } catch(e) {}
}

async function loadRegions() {
    try {
        const data = await api('/regions');
        const sel = document.getElementById('filter-region-detail');
        if (sel) {
            sel.innerHTML = '<option value="">Semua Daerah</option>';
            (data.regions||[]).forEach(r => {
                sel.innerHTML += `<option value="${r.slug}">${r.name}</option>`;
            });
        }
    } catch(e) {}
}

function changeRegionFilter(regionSlug) {
    selectedRegion = regionSlug;
    loadChart(currentPeriod);
    loadComments();
    
    // Filter table rows
    const tbody = document.getElementById('market-prices');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('tr:not(#market-prices-empty)');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const rowRegion = row.getAttribute('data-region');
        if (!regionSlug || rowRegion === regionSlug) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Handle empty row
    let emptyRow = document.getElementById('market-prices-empty');
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'market-prices-empty';
            emptyRow.innerHTML = `<td colspan="4" class="text-center text-muted">Belum ada data untuk daerah ini</td>`;
            tbody.appendChild(emptyRow);
        } else {
            emptyRow.style.display = '';
        }
    } else {
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }
}

function redrawChart() {
    if (!currentRawChartData || currentRawChartData.length === 0) return;
    
    let factor = 1;
    let unitLabel = currentProductUnit;
    const selectEl = document.getElementById('chart-unit-select');
    if (selectEl) {
        factor = parseFloat(selectEl.value);
        unitLabel = selectEl.options[selectEl.selectedIndex].text;
    }

    const labels = currentRawChartData.map(d => d.date||d.start_date||d.month||'');
    const avgPrices = currentRawChartData.map(d => Math.round(d.avg_price * factor));
    
    if (chartInstance) chartInstance.destroy();
    const ctx = document.getElementById('price-chart');
    chartInstance = new Chart(ctx, {
        type:'line', data:{labels, datasets:[
            {label:`Harga per ${unitLabel}`,data:avgPrices,borderColor:'#059669',backgroundColor:'rgba(5,150,105,0.1)',fill:true,tension:0.4,pointRadius:3},
        ]},
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                legend:{display:false},
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${formatRupiah(context.raw)} / ${unitLabel}`;
                        }
                    }
                }
            },
            scales:{y:{beginAtZero:false,ticks:{callback:v=>formatRupiah(v)}}}
        }
    });
}

async function loadComments() {
    const section = document.getElementById('comments-section');
    try {
        let url = `/comments/${productId}`;
        if (selectedRegion) url += `?region=${selectedRegion}`;
        const data = await api(url);
        const items = data.data||[];
        if (items.length===0) { section.innerHTML='<p class="text-muted text-sm">Belum ada diskusi. Mulai diskusi pertama!</p>'; return; }
        section.innerHTML = items.map(c => commentHTML(c)).join('');
    } catch(e) { section.innerHTML='<p class="text-muted text-sm">Gagal memuat komentar</p>'; }
}

function commentHTML(c) {
    const initials = c.user?.name?.split(' ').map(w=>w[0]).join('').substring(0,2)||'?';
    const replies = (c.replies||[]).map(r=>commentHTML(r)).join('');
    const regionBadge = c.region ? `<span class="badge badge-secondary" style="font-size:0.75rem;margin-left:6px;background:rgba(5,150,105,0.1);color:#059669;">📍 ${c.region.name}</span>` : '';
    return `<div class="comment-item"><div class="comment-header">
        <div class="comment-avatar">${initials}</div>
        <span class="comment-author">${c.user?.name||'Anonim'}</span>
        ${c.user?.role?roleBadge(c.user.role):''}
        ${regionBadge}
        <span class="comment-time">${timeAgo(c.created_at)}</span>
    </div><div class="comment-body">${c.body}</div>
    <div class="comment-actions">
        <button onclick="likeComment(${c.id},this)">❤️ ${c.likes_count||0}</button>
        ${Auth.isLoggedIn()?`<button onclick="showReply(${c.id})">💬 Reply</button>`:''}
    </div>
    <div id="reply-form-${c.id}" style="display:none;margin:8px 0 0 46px;">
        <textarea class="form-textarea" id="reply-body-${c.id}" rows="2" placeholder="Tulis balasan..."></textarea>
        <button class="btn btn-primary btn-sm mt-1" onclick="submitReply(${c.id})">Balas</button>
    </div>
    ${replies?`<div class="comment-replies">${replies}</div>`:''}</div>`;
}

function showReply(id) { const el=document.getElementById('reply-form-'+id); el.style.display=el.style.display==='none'?'block':'none'; }

async function likeComment(id,btn) {
    if(!Auth.isLoggedIn()){window.location.href='/login';return;}
    try{const d=await api(`/comments/${id}/like`,'POST');btn.textContent=`❤️ ${d.likes_count}`;}catch(e){showToast('Gagal','error');}
}

async function submitComment(e) {
    e.preventDefault();
    const body=document.getElementById('comment-body').value;
    if(!body.trim())return;
    const payload = { body };
    if (selectedRegion) payload.region_slug = selectedRegion;
    try{await api(`/comments/${productId}`,'POST',payload);document.getElementById('comment-body').value='';loadComments();showToast('Komentar ditambahkan');}catch(e){showToast('Gagal','error');}
}

async function submitReply(parentId) {
    const body=document.getElementById('reply-body-'+parentId).value;
    if(!body.trim())return;
    try{await api(`/comments/${parentId}/reply`,'POST',{body});loadComments();showToast('Balasan ditambahkan');}catch(e){showToast('Gagal','error');}
}

async function toggleFav() {
    if(!Auth.isLoggedIn()){window.location.href='/login';return;}
    try{const d=await api(`/favorites/${productId}/toggle`,'POST');isFav=d.is_favorite;const btn=document.getElementById('fav-btn');btn.className=`btn ${isFav?'btn-accent':'btn-secondary'}`;btn.textContent=isFav?'⭐ Favorit':'☆ Favorit';showToast(d.message);}catch(e){showToast('Gagal','error');}
}
</script>
@endpush
