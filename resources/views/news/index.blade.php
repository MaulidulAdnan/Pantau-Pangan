@extends('layouts.app')
@section('title', 'Berita Pangan - Pantau Pangan')
@section('content')
<div class="container">
    <div class="page-header">
        <h1 class="page-title">📰 Berita Pangan</h1>
        <p class="page-subtitle">Informasi terkini seputar harga pangan, pasar, dan ketahanan pangan Indonesia</p>
    </div>

    {{-- Search & Filter --}}
    <div class="card mb-3" style="padding:16px 20px;">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <input type="text" class="form-input" id="news-search" placeholder="🔍 Cari berita... (misal: harga beras, cabai, pasar)" style="margin:0;">
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn btn-primary btn-sm news-topic active" data-query="">Semua</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="harga beras">🍚 Beras</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="harga cabai">🌶️ Cabai</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="harga daging">🥩 Daging</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="harga sayur">🥬 Sayur</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="pasar tradisional">🏪 Pasar</button>
                <button class="btn btn-secondary btn-sm news-topic" data-query="sembako">📦 Sembako</button>
            </div>
        </div>
    </div>

    {{-- News Grid --}}
    <div id="news-grid" class="grid grid-3" style="gap:24px;">
        <div class="spinner" style="grid-column:1/-1;"></div>
    </div>

    {{-- Load More --}}
    <div id="news-load-more" style="text-align:center;margin-top:32px;display:none;">
        <button class="btn btn-secondary" onclick="loadMoreNews()">📰 Muat Lebih Banyak</button>
    </div>

    {{-- Empty State --}}
    <div id="news-empty" style="display:none;text-align:center;padding:64px 24px;">
        <div style="font-size:4rem;margin-bottom:16px;">📭</div>
        <h3 style="font-weight:700;margin-bottom:8px;">Tidak Ada Berita Ditemukan</h3>
        <p class="text-muted">Coba ubah kata kunci pencarian atau pilih topik lain</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let newsPage = 1;
let currentQuery = '';
let isLoading = false;

document.addEventListener('DOMContentLoaded', () => {
    loadNews(true);

    // Topic filter buttons
    document.querySelectorAll('.news-topic').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.news-topic').forEach(b => {
                b.classList.remove('active');
                b.classList.remove('btn-primary');
                b.classList.add('btn-secondary');
            });
            this.classList.add('active');
            this.classList.remove('btn-secondary');
            this.classList.add('btn-primary');
            currentQuery = this.dataset.query;
            document.getElementById('news-search').value = currentQuery;
            newsPage = 1;
            loadNews(true);
        });
    });

    // Search input with debounce
    let searchTimer;
    document.getElementById('news-search').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentQuery = this.value.trim();
            // Deactivate topic buttons
            document.querySelectorAll('.news-topic').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-secondary');
            });
            if (!currentQuery) {
                document.querySelector('.news-topic[data-query=""]').classList.add('active', 'btn-primary');
                document.querySelector('.news-topic[data-query=""]').classList.remove('btn-secondary');
            }
            newsPage = 1;
            loadNews(true);
        }, 500);
    });
});

async function loadNews(reset = false) {
    if (isLoading) return;
    isLoading = true;

    const grid = document.getElementById('news-grid');
    const loadMoreBtn = document.getElementById('news-load-more');
    const emptyState = document.getElementById('news-empty');

    if (reset) {
        grid.innerHTML = '<div class="spinner" style="grid-column:1/-1;"></div>';
        loadMoreBtn.style.display = 'none';
        emptyState.style.display = 'none';
    }

    try {
        let url = `/api/news?page=${newsPage}&per_page=12`;
        if (currentQuery) url += `&q=${encodeURIComponent(currentQuery)}`;

        const res = await fetch(url);
        const data = await res.json();

        if (reset) grid.innerHTML = '';

        const articles = data.articles || [];

        if (articles.length === 0 && newsPage === 1) {
            emptyState.style.display = 'block';
            loadMoreBtn.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            articles.forEach(article => {
                grid.insertAdjacentHTML('beforeend', renderNewsCard(article));
            });
            // Show load more if there might be more results
            if (articles.length >= 12) {
                loadMoreBtn.style.display = 'block';
            } else {
                loadMoreBtn.style.display = 'none';
            }
        }
    } catch (e) {
        if (reset) grid.innerHTML = '';
        grid.insertAdjacentHTML('beforeend', `
            <div style="grid-column:1/-1;text-align:center;padding:48px;">
                <div style="font-size:3rem;margin-bottom:12px;">⚠️</div>
                <p class="text-muted">Gagal memuat berita. Coba lagi nanti.</p>
            </div>
        `);
    }

    isLoading = false;
}

function loadMoreNews() {
    newsPage++;
    loadNews(false);
}

function renderNewsCard(article) {
    const imgSrc = article.urlToImage || '';
    const hasImage = imgSrc && !imgSrc.includes('null');
    const date = article.publishedAt ? new Date(article.publishedAt) : null;
    const formattedDate = date ? date.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    }) : '';
    const timeStr = date ? timeAgo(article.publishedAt) : '';
    const desc = article.description
        ? (article.description.length > 120 ? article.description.substring(0, 120) + '...' : article.description)
        : 'Klik untuk membaca selengkapnya...';

    return `
        <a href="${article.url}" target="_blank" rel="noopener noreferrer" class="news-card" style="text-decoration:none;color:inherit;">
            <div class="news-card-inner">
                ${hasImage ? `
                    <div class="news-card-img">
                        <img src="${imgSrc}" alt="" loading="lazy" onerror="this.parentElement.style.display='none'">
                    </div>
                ` : `
                    <div class="news-card-img news-card-img-placeholder">
                        <span>📰</span>
                    </div>
                `}
                <div class="news-card-body">
                    <div class="news-card-source">
                        <span class="news-source-badge">${article.source || 'Sumber Berita'}</span>
                        <span class="news-card-time">${timeStr}</span>
                    </div>
                    <h3 class="news-card-title">${article.title || 'Tanpa Judul'}</h3>
                    <p class="news-card-desc">${desc}</p>
                    <div class="news-card-footer">
                        <span class="news-card-date">${formattedDate}</span>
                        <span class="news-card-read">Baca →</span>
                    </div>
                </div>
            </div>
        </a>
    `;
}
</script>
@endpush
