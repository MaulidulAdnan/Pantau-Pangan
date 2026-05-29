@extends('layouts.app')
@section('title', 'Produk Pangan - Pantau Pangan')
@section('content')
<div class="container">
    <div class="page-header">
        <h1 class="page-title">Harga Pangan Terkini</h1>
        <p class="page-subtitle">Pantau harga dari pedagang terverifikasi di berbagai pasar</p>
    </div>

    {{-- Search & Filters --}}
    <div class="card mb-3" style="padding:20px;">
        <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
            <div class="search-bar" style="flex:1;min-width:240px;">
                <span class="search-icon">🔍</span>
                <input type="text" id="search-input" placeholder="Cari produk pangan...">
            </div>
            <select class="form-select" id="filter-category" style="width:auto;min-width:160px;">
                <option value="">Semua Kategori</option>
            </select>
            <select class="form-select" id="filter-region" style="width:auto;min-width:160px;">
                <option value="">Semua Daerah</option>
            </select>
        </div>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-4" id="products-grid">
        <div class="spinner"></div>
    </div>
    <div id="pagination-wrap"></div>
</div>
@endsection
@push('scripts')
<script>
let currentPage = 1, currentCategory = '', currentRegion = '', currentSearch = '';

document.addEventListener('DOMContentLoaded', async () => {
    // Load filters
    try {
        const cats = await api('/categories');
        const sel = document.getElementById('filter-category');
        cats.categories.forEach(c => { sel.innerHTML += `<option value="${c.slug}">${c.icon} ${c.name}</option>`; });
    } catch(e) {}
    try {
        const regs = await api('/regions');
        const sel = document.getElementById('filter-region');
        regs.regions.forEach(r => { sel.innerHTML += `<option value="${r.slug}">${r.name}</option>`; });
    } catch(e) {}
    loadProducts();
});

document.getElementById('filter-category').addEventListener('change', (e) => { currentCategory = e.target.value; currentPage = 1; loadProducts(); });
document.getElementById('filter-region').addEventListener('change', (e) => { currentRegion = e.target.value; currentPage = 1; loadProducts(); });

let searchTimer;
document.getElementById('search-input').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentSearch = e.target.value; currentPage = 1; loadProducts(); }, 400);
});

async function loadProducts() {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = '<div class="spinner"></div>';
    let params = `?page=${currentPage}`;
    if (currentCategory) params += `&category=${currentCategory}`;
    if (currentRegion) params += `&region=${currentRegion}`;
    if (currentSearch) params += `&search=${currentSearch}`;

    try {
        const data = await api('/products' + params);
        const items = data.data || [];
        if (items.length === 0) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;"><div style="font-size:3rem;margin-bottom:12px;">🔍</div><p class="text-muted">Tidak ada produk ditemukan</p></div>';
            document.getElementById('pagination-wrap').innerHTML = '';
            return;
        }
        grid.innerHTML = items.map(p => `
            <a href="/products/${p.slug}" class="product-card" style="text-decoration:none;color:inherit;">
                <div class="product-category">${p.category?.name || ''}</div>
                <div class="product-name">${p.name}</div>
                <div style="margin-top:12px;">
                    <div class="product-price">${formatRupiah(p.average_price)} <span class="product-unit">/ ${p.unit}</span></div>
                    ${p.price_change !== null ? formatChange(p.price_change) : '<span class="text-sm text-muted">Belum ada data</span>'}
                </div>
                ${p.stock_status ? stockLabel(p.stock_status) : ''}
                ${p.last_updated ? '<div class="text-sm text-muted mt-1">Update: ' + timeAgo(p.last_updated) + '</div>' : ''}
            </a>
        `).join('');

        // Pagination
        const wrap = document.getElementById('pagination-wrap');
        if (data.last_page > 1) {
            let btns = '';
            for (let i = 1; i <= data.last_page; i++) {
                btns += `<button class="${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
            }
            wrap.innerHTML = `<div class="pagination">${btns}</div>`;
        } else { wrap.innerHTML = ''; }
    } catch(e) {
        grid.innerHTML = '<p class="text-muted text-center" style="grid-column:1/-1;">Gagal memuat produk</p>';
    }
}

function goPage(p) { currentPage = p; loadProducts(); window.scrollTo({top:0,behavior:'smooth'}); }
</script>
@endpush
