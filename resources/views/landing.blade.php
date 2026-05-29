@extends('layouts.app')
@section('title', 'Pantau Pangan - Monitoring Harga Pangan Berbasis Komunitas')

@section('content')
{{-- HERO --}}
<section class="hero">
    <!-- Placeholder video. The user will replace this with their own video file at public/videos/hero-bg.mp4 -->
    <video autoplay loop muted playsinline class="hero-video">
        <source src="/videos/hero-bg.mp4" type="video/mp4">
    </video>
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;width:100%;position:relative;z-index:2;">
        <div class="hero-content">
            <h1>Pantau Harga Pangan<br>Secara Transparan</h1>
            <p>Platform monitoring harga pangan berbasis komunitas dan pedagang terverifikasi. Dapatkan informasi harga bahan pangan terkini dari berbagai pasar di seluruh Indonesia.</p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="/products" class="btn btn-lg" style="background:#fff;color:var(--primary);font-weight:700;">🔍 Cek Harga Pangan</a>
                <a href="/register/merchant" class="btn btn-lg" style="border:2px solid rgba(255,255,255,0.5);color:#fff;background:transparent;">🏪 Daftar Pedagang</a>
            </div>
            <div class="hero-stats" id="hero-stats">
                <div><div class="hero-stat-value" id="stat-products">-</div><div class="hero-stat-label">Produk Pangan</div></div>
                <div><div class="hero-stat-value" id="stat-markets">-</div><div class="hero-stat-label">Pasar</div></div>
                <div><div class="hero-stat-value" id="stat-merchants">-</div><div class="hero-stat-label">Pedagang</div></div>
                <div><div class="hero-stat-value" id="stat-regions">-</div><div class="hero-stat-label">Daerah</div></div>
            </div>
        </div>
    </div>
</section>

{{-- CATEGORIES --}}
<section class="container mt-4">
    <div class="page-header text-center">
        <h2 class="page-title">Kategori Pangan</h2>
        <p class="page-subtitle">Jelajahi berbagai kategori bahan pangan yang tersedia</p>
    </div>
    <div class="grid grid-4" id="categories-grid" style="margin-top:24px;">
        <div class="spinner"></div>
    </div>
</section>

{{-- FEATURED PRODUCTS --}}
<section class="container mt-4">
    <div class="flex-between mb-3">
        <div>
            <h2 class="page-title">Harga Pangan Terkini</h2>
            <p class="page-subtitle">Update harga terbaru dari pedagang terverifikasi</p>
        </div>
        <a href="/products" class="btn btn-secondary btn-sm">Lihat Semua →</a>
    </div>
    <div class="grid grid-4" id="featured-products">
        <div class="spinner"></div>
    </div>
</section>

{{-- HOT NEWS --}}
<section class="container mt-4">
    <div class="flex-between mb-3">
        <div>
            <h2 class="page-title">Berita Pangan Terkini</h2>
            <p class="page-subtitle">Informasi terbaru seputar harga dan pasar</p>
        </div>
        <a href="/news" class="btn btn-secondary btn-sm">Baca Semua Berita →</a>
    </div>
    <div class="grid grid-3" id="hot-news">
        <div class="spinner"></div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="container mt-4" style="padding-bottom:48px;">
    <div class="page-header text-center">
        <h2 class="page-title">Bagaimana Cara Kerjanya?</h2>
        <p class="page-subtitle">Sistem crowdsourcing yang transparan dan terverifikasi</p>
    </div>
    <div class="grid grid-3" style="margin-top:32px;">
        <div class="card text-center" style="padding:32px;">
            <div style="font-size:3rem;margin-bottom:16px;">🏪</div>
            <h3 style="font-weight:700;margin-bottom:8px;">Pedagang Input Harga</h3>
            <p class="text-muted text-sm">Pedagang terverifikasi menginput harga pangan dari pasar mereka secara berkala</p>
        </div>
        <div class="card text-center" style="padding:32px;">
            <div style="font-size:3rem;margin-bottom:16px;">📊</div>
            <h3 style="font-weight:700;margin-bottom:8px;">Sistem Hitung Rata-rata</h3>
            <p class="text-muted text-sm">Sistem menghitung rata-rata harga dari banyak pedagang dan mendeteksi anomali otomatis</p>
        </div>
        <div class="card text-center" style="padding:32px;">
            <div style="font-size:3rem;margin-bottom:16px;">👥</div>
            <h3 style="font-weight:700;margin-bottom:8px;">Masyarakat Pantau</h3>
            <p class="text-muted text-sm">Masyarakat dapat memantau, berdiskusi, dan mendapat notifikasi perubahan harga</p>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Load categories
    try {
        const catData = await api('/categories');
        const grid = document.getElementById('categories-grid');
        grid.innerHTML = catData.categories.map(c => `
            <a href="/products?category=${c.slug}" class="card product-card text-center" style="text-decoration:none;color:inherit;">
                <div style="font-size:2.5rem;margin-bottom:12px;">${c.icon || '📦'}</div>
                <div class="product-name">${c.name}</div>
                <div class="text-muted text-sm">${c.products_count} produk</div>
            </a>
        `).join('');
    } catch(e) {
        document.getElementById('categories-grid').innerHTML = '<p class="text-muted">Gagal memuat kategori</p>';
    }

    // Load featured products
    try {
        const prodData = await api('/products?per_page=8');
        const prodGrid = document.getElementById('featured-products');
        const items = prodData.data || [];
        prodGrid.innerHTML = items.slice(0,8).map(p => `
            <a href="/products/${p.slug}" class="product-card" style="text-decoration:none;color:inherit;">
                <div class="product-category">${p.category ? p.category.name : ''}</div>
                <div class="product-name">${p.name}</div>
                <div style="margin-top:12px;">
                    <div class="product-price">${formatRupiah(p.average_price)} <span class="product-unit">/ ${p.unit}</span></div>
                    ${p.price_change !== null ? formatChange(p.price_change) : ''}
                </div>
                ${p.stock_status ? stockLabel(p.stock_status) : ''}
                ${p.last_updated ? '<div class="text-sm text-muted mt-1">' + timeAgo(p.last_updated) + '</div>' : ''}
            </a>
        `).join('');
    } catch(e) {
        document.getElementById('featured-products').innerHTML = '<p class="text-muted">Gagal memuat produk</p>';
    }

    // Stats
    try {
        const cats = await api('/categories');
        const regions = await api('/regions');
        document.getElementById('stat-products').textContent = cats.categories.reduce((s,c) => s + c.products_count, 0);
        document.getElementById('stat-regions').textContent = regions.regions.length;
        document.getElementById('stat-markets').textContent = regions.regions.reduce((s,r) => s + (r.markets_count || 0), 0);
        document.getElementById('stat-merchants').textContent = '3+';
    } catch(e) {}

    // Hot News
    try {
        const newsData = await fetch('/api/news?per_page=3').then(r => r.json());
        const hotNewsGrid = document.getElementById('hot-news');
        const articles = newsData.articles || [];
        
        if (articles.length === 0) {
            hotNewsGrid.innerHTML = '<p class="text-muted">Tidak ada berita saat ini.</p>';
        } else {
            hotNewsGrid.innerHTML = articles.slice(0,3).map(article => {
                const imgSrc = article.urlToImage || '';
                const hasImage = imgSrc && !imgSrc.includes('null');
                const date = article.publishedAt ? new Date(article.publishedAt) : null;
                const formattedDate = date ? date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
                const desc = article.description ? (article.description.length > 100 ? article.description.substring(0, 100) + '...' : article.description) : '';
                
                return `
                    <a href="${article.url}" target="_blank" rel="noopener noreferrer" class="news-card" style="text-decoration:none;color:inherit;">
                        <div class="news-card-inner">
                            ${hasImage ? `
                                <div class="news-card-img" style="height:140px;">
                                    <img src="${imgSrc}" alt="" loading="lazy" onerror="this.parentElement.style.display='none'">
                                </div>
                            ` : `
                                <div class="news-card-img news-card-img-placeholder" style="height:140px;">
                                    <span>📰</span>
                                </div>
                            `}
                            <div class="news-card-body" style="padding:16px;">
                                <div class="news-card-source">
                                    <span class="news-source-badge">${article.source || 'Berita'}</span>
                                </div>
                                <h3 class="news-card-title" style="font-size:0.95rem;">${article.title || 'Tanpa Judul'}</h3>
                                <p class="news-card-desc" style="font-size:0.8rem; margin-bottom: 8px;">${desc}</p>
                                <div class="news-card-footer" style="padding-top:8px;">
                                    <span class="news-card-date" style="font-size:0.7rem;">${formattedDate}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                `;
            }).join('');
        }
    } catch(e) {
        document.getElementById('hot-news').innerHTML = '<p class="text-muted">Gagal memuat berita terkini.</p>';
    }
});
</script>
@endpush
