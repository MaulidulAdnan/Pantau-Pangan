<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pantau Pangan - Platform monitoring harga pangan berbasis komunitas dan pedagang terverifikasi">
    <title>@yield('title', 'Pantau Pangan - Monitoring Harga Pangan')</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Floating Bug FAB styles */
        .bug-report-fab {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--bg-card);
            border: 1.5px solid var(--border);
            box-shadow: var(--shadow-lg);
            border-radius: 30px;
            padding: 10px 18px;
            display: none; /* Shown dynamically if logged in */
            align-items: center;
            gap: 8px;
            cursor: pointer;
            z-index: 400;
            transition: var(--transition);
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text);
            user-select: none;
        }
        .bug-report-fab:hover {
            transform: translateY(-4px) scale(1.05);
            border-color: #f59e0b;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2);
        }
        .bug-report-fab span:first-child {
            font-size: 1.25rem;
            animation: bugWiggle 3s ease infinite;
        }
        @keyframes bugWiggle {
            0%, 100% { transform: rotate(0deg); }
            10%, 20% { transform: rotate(-12deg); }
            15%, 25% { transform: rotate(12deg); }
            30% { transform: rotate(0deg); }
        }
        [data-theme="dark"] .bug-report-fab {
            background: var(--bg-card-dark);
            border-color: var(--border);
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- NAVBAR --}}
    <nav class="navbar" id="main-navbar">
        <div class="navbar-inner">
            <a href="/" class="navbar-brand">
                <span>🌾</span> PantauPangan
            </a>
            <div class="navbar-links" id="nav-links">
                <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Beranda</a>
                <a href="/products" class="{{ request()->is('products*') ? 'active' : '' }}">Produk</a>
                <a href="/news" class="{{ request()->is('news*') ? 'active' : '' }}">Berita</a>
                <a href="/dashboard" class="{{ request()->is('dashboard*') ? 'active' : '' }}">Dashboard</a>
            </div>
            <div class="navbar-actions">
                <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()">🌙</button>

                {{-- Notification Bell --}}
                <div class="notif-bell" onclick="showNotifDropdown()" id="notif-bell-wrap" style="display:none">
                    <button class="btn-icon btn-ghost">🔔</button>
                    <span class="notif-count" id="notif-count" style="display:none">0</span>
                    <div class="notif-dropdown" id="notif-dropdown"></div>
                </div>

                {{-- Auth Links --}}
                <div id="auth-links" style="display:flex;gap:8px;">
                    <a href="/login" class="btn btn-ghost btn-sm">Masuk</a>
                    <a href="/register" class="btn btn-primary btn-sm">Daftar</a>
                </div>

                {{-- User Menu --}}
                <div id="user-menu" style="display:none;align-items:center;gap:12px;">
                    <div id="nav-avatar" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;"></div>
                    <span id="user-name" style="font-weight:600;font-size:0.9rem;"></span>
                    <button class="btn btn-ghost btn-sm" onclick="Auth.logout()">Keluar</button>
                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="page-wrapper">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @hasSection('no-footer')
    @else
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">🌾 PantauPangan</div>
                    <p class="footer-desc">Platform monitoring harga pangan berbasis komunitas dan pedagang terverifikasi. Membantu masyarakat memantau harga bahan pangan secara transparan.</p>
                </div>
                <div>
                    <div class="footer-title">Navigasi</div>
                    <div class="footer-links">
                        <a href="/">Beranda</a>
                        <a href="/products">Produk</a>
                        <a href="/dashboard">Dashboard</a>
                    </div>
                </div>
                <div>
                    <div class="footer-title">Fitur</div>
                    <div class="footer-links">
                        <a href="/products">Harga Pangan</a>
                        <a href="/register/merchant">Daftar Pedagang</a>
                        <a href="/products">Diskusi Komunitas</a>
                    </div>
                </div>
                <div>
                    <div class="footer-title">Informasi</div>
                    <div class="footer-links">
                        <a href="/about">Tentang Kami</a>
                        <a href="/privacy">Kebijakan Privasi</a>
                        <a href="/terms">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; 2026 PantauPangan. Platform komunitas monitoring harga pangan.
            </div>
        </div>
    </footer>
    @endif

    {{-- GLOBAL FLOATING BUG FAB --}}
    <div id="bug-report-fab" class="bug-report-fab" onclick="openBugReportModal()">
        <span>🐛</span>
        <span>Lapor Bug</span>
    </div>

    {{-- GLOBAL BUG REPORT MODAL --}}
    <div class="modal-overlay" id="bug-report-modal" style="backdrop-filter: blur(8px); background: rgba(15,23,42,0.4);">
        <div class="modal" style="border-radius: 20px; box-shadow: var(--shadow-lg); padding: 32px; border: 1px solid var(--border); max-width: 480px; width: 100%;">
            <div class="flex-between mb-3" style="border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <h3 class="modal-title" style="font-weight: 800; font-size: 1.25rem; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🐛</span> Laporkan Bug Aplikasi
                </h3>
                <button class="btn btn-ghost btn-sm" style="border-radius: 50%; width: 32px; height: 32px; padding: 0;" onclick="closeBugReportModal()">✕</button>
            </div>
            
            <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; gap: 10px; align-items: flex-start;">
                <span style="font-size: 1.2rem;">💡</span>
                <p style="font-size: 0.75rem; color: var(--accent-dark); margin: 0; line-height: 1.45; font-weight: 500;">
                    Bantu kami menyempurnakan PantauPangan! Deskripsikan masalah atau bug yang Anda temui secara detail agar admin dapat segera memperbaikinya.
                </p>
            </div>

            <form id="global-bug-report-form">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text); margin-bottom: 8px;">Deskripsi Bug / Masalah</label>
                    <textarea class="form-textarea" id="br-description" placeholder="Contoh: Saat saya menekan tombol edit profil, halaman macet dan tidak memuat data..." style="border-radius: 12px; border: 1.5px solid var(--border); font-size: 0.875rem; min-height: 140px; padding: 14px;" required></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="button" class="btn btn-ghost" style="flex: 1; border-radius: 10px; font-weight: 600;" onclick="closeBugReportModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2; border-radius: 10px; font-weight: 700; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;" id="br-btn">Kirim Laporan 🚀</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/js/app.js"></script>
    <script>
        // Show notif bell & Bug FAB if logged in
        document.addEventListener('DOMContentLoaded', () => {
            if (Auth.isLoggedIn()) {
                const nb = document.getElementById('notif-bell-wrap');
                if (nb) nb.style.display = 'block';
                
                const bugFab = document.getElementById('bug-report-fab');
                if (bugFab) bugFab.style.display = 'flex';

                // Show admin/merchant nav
                const user = Auth.getUser();
                const navLinks = document.getElementById('nav-links');
                if (user && user.role === 'pedagang' && navLinks) {
                    navLinks.innerHTML += '<a href="/merchant" class="{{ request()->is("merchant*") ? "active" : "" }}">Pedagang</a>';
                }
                if (user && user.role === 'admin' && navLinks) {
                    navLinks.innerHTML += '<a href="/admin" class="{{ request()->is("admin*") ? "active" : "" }}">Admin</a>';
                }
            }
        });

        // Bug Report Handlers
        function openBugReportModal() {
            document.getElementById('br-description').value = '';
            document.getElementById('bug-report-modal').classList.add('show');
        }
        
        function closeBugReportModal() {
            document.getElementById('bug-report-modal').classList.remove('show');
        }

        document.getElementById('global-bug-report-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('br-btn');
            const desc = document.getElementById('br-description').value.trim();
            
            btn.textContent = 'Mengirim...';
            btn.disabled = true;
            
            try {
                await api('/reports', 'POST', {
                    reportable_type: 'bug',
                    reportable_id: null,
                    type: 'bug',
                    description: desc
                });
                showToast('Laporan bug berhasil dikirim. Terima kasih!');
                closeBugReportModal();
            } catch (err) {
                const msg = err.data?.message || 'Gagal mengirim laporan bug';
                showToast(msg, 'error');
            } finally {
                btn.textContent = 'Kirim Laporan 🚀';
                btn.disabled = false;
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
