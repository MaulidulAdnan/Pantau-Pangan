/* ========================================
   PANTAU PANGAN - App JavaScript
   ======================================== */

const API_BASE = '/api';

// ===== AUTH & API HELPERS =====
const Auth = {
    getToken() { return localStorage.getItem('pp_token'); },
    setToken(token) { localStorage.setItem('pp_token', token); },
    setUser(user) { localStorage.setItem('pp_user', JSON.stringify(user)); },
    getUser() {
        const u = localStorage.getItem('pp_user');
        return u ? JSON.parse(u) : null;
    },
    isLoggedIn() { return !!this.getToken(); },
    logout() {
        api('/auth/logout', 'POST').catch(() => {});
        localStorage.removeItem('pp_token');
        localStorage.removeItem('pp_user');
        window.location.href = '/login';
    },
    requireAuth() {
        if (!this.isLoggedIn()) { window.location.href = '/login'; return false; }
        return true;
    },
    hasRole(role) {
        const user = this.getUser();
        return user && user.role === role;
    }
};

async function api(endpoint, method = 'GET', body = null) {
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
    const token = Auth.getToken();
    if (token) headers['Authorization'] = `Bearer ${token}`;

    const opts = { method, headers };
    if (body && method !== 'GET') opts.body = JSON.stringify(body);

    const res = await fetch(API_BASE + endpoint, opts);
    const data = await res.json();

    if (res.status === 401) {
        Auth.logout();
        return;
    }
    if (!res.ok) {
        throw { status: res.status, data };
    }
    return data;
}

// ===== TOAST SYSTEM =====
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icons = { success: '✅', error: '❌', warning: '⚠️' };
    toast.innerHTML = `<span>${icons[type] || '📌'}</span><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(40px)'; setTimeout(() => toast.remove(), 300); }, 4000);
}

// ===== THEME TOGGLE =====
function initTheme() {
    const saved = localStorage.getItem('pp_theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('pp_theme', next);
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    const btn = document.getElementById('theme-toggle');
    if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}

// ===== NOTIFICATION SYSTEM =====
async function loadNotifications() {
    if (!Auth.isLoggedIn()) return;
    try {
        const data = await api('/notifications/unread-count');
        const badge = document.getElementById('notif-count');
        if (badge) {
            badge.textContent = data.unread_count;
            badge.style.display = data.unread_count > 0 ? 'flex' : 'none';
        }
    } catch (e) {}
}

async function updateAdminSidebarBadges() {
    if (!Auth.hasRole('admin')) return;
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    try {
        const stats = await api('/admin/dashboard');
        
        // Update Merchants Badge (Pending Approvals + Pending Stores)
        const merchantsBadge = document.getElementById('sb-merchants');
        if (merchantsBadge) {
            let existing = merchantsBadge.querySelector('.sidebar-badge');
            let pendingMerchantsTotal = (stats.pending_merchants || 0) + (stats.pending_stores || 0);
            if (pendingMerchantsTotal > 0) {
                if (!existing) merchantsBadge.insertAdjacentHTML('beforeend', `<span class="sidebar-badge">${pendingMerchantsTotal}</span>`);
                else existing.textContent = pendingMerchantsTotal;
            } else if (existing) {
                existing.remove();
            }
        }

        // Update Prices Badge (Suspicious Prices)
        const pricesBadge = document.getElementById('sb-prices');
        if (pricesBadge) {
            let existing = pricesBadge.querySelector('.sidebar-badge');
            if (stats.suspicious_prices > 0) {
                if (!existing) pricesBadge.insertAdjacentHTML('beforeend', `<span class="sidebar-badge">${stats.suspicious_prices}</span>`);
                else existing.textContent = stats.suspicious_prices;
            } else if (existing) {
                existing.remove();
            }
        }

        // Update Reports Badge (Pending Reports)
        const reportsBadge = document.getElementById('sb-reports');
        if (reportsBadge) {
            let existing = reportsBadge.querySelector('.sidebar-badge');
            if (stats.pending_reports > 0) {
                if (!existing) reportsBadge.insertAdjacentHTML('beforeend', `<span class="sidebar-badge">${stats.pending_reports}</span>`);
                else existing.textContent = stats.pending_reports;
            } else if (existing) {
                existing.remove();
            }
        }
    } catch (e) {}
}

async function showNotifDropdown() {
    const dropdown = document.getElementById('notif-dropdown');
    if (!dropdown) return;
    dropdown.classList.toggle('show');
    if (dropdown.classList.contains('show')) {
        try {
            const data = await api('/notifications');
            const items = data.data || [];
            let html = '';

            // Header with action buttons
            html += `<div class="notif-header">
                <span class="notif-header-title">🔔 Notifikasi</span>
                <div class="notif-actions">
                    <button class="notif-action-btn mark-all" onclick="event.stopPropagation(); markAllRead();">✓ Baca semua</button>
                    <button class="notif-action-btn clear-read" onclick="event.stopPropagation(); clearReadNotifs();">🗑 Hapus terbaca</button>
                </div>
            </div>`;

            if (items.length > 0) {
                // Sort: unread first, then read
                const unread = items.filter(n => !n.read_at);
                const read = items.filter(n => n.read_at);

                if (unread.length > 0) {
                    html += `<div class="notif-separator">Belum Dibaca (${unread.length})</div>`;
                    unread.forEach(n => {
                        html += renderNotifItem(n, true);
                    });
                }
                if (read.length > 0) {
                    html += `<div class="notif-separator">Sudah Dibaca</div>`;
                    read.forEach(n => {
                        html += renderNotifItem(n, false);
                    });
                }
            } else {
                html += `<div class="notif-empty">
                    <div class="notif-empty-icon">🔕</div>
                    <div>Belum ada notifikasi</div>
                </div>`;
            }
            dropdown.innerHTML = html;
        } catch (e) {
            dropdown.innerHTML = `<div class="notif-header"><span class="notif-header-title">🔔 Notifikasi</span></div>
                <div class="notif-empty"><div class="notif-empty-icon">⚠️</div><div>Gagal memuat notifikasi</div></div>`;
        }
    }
}

function renderNotifItem(n, isUnread) {
    const cls = isUnread ? 'unread' : 'read';
    const dot = isUnread ? '<div class="notif-dot"></div>' : '';
    const msg = n.data?.message || n.data?.title || 'Notifikasi baru';
    const link = getNotifLink(n);
    return `<div class="notif-item ${cls}" onclick="handleNotifClick('${n.id}', ${isUnread}, '${link}')">
        ${dot}
        <div class="notif-msg">${msg}</div>
        <div class="notif-time">${timeAgo(n.created_at)}</div>
    </div>`;
}

function getNotifLink(n) {
    const d = n.data || {};
    const type = d.type || '';
    const user = Auth.getUser();
    const role = user?.role || '';

    // Comment-related: redirect to product page (comments section)
    if (type === 'comment_like' || type === 'comment_reply') {
        if (d.product_slug) return '/products/' + d.product_slug;
        return '/products';
    }

    // Price anomaly: admin goes to moderation, merchant stays on their dashboard
    if (type === 'price_anomaly') {
        if (role === 'admin') return '/admin/prices';
        return '/merchant';
    }

    // Price change: go to product page
    if (type === 'price_change') {
        if (d.product_slug) return '/products/' + d.product_slug;
        return '/products';
    }

    // Warning: go to terms page
    if (type === 'warning') return '/terms';

    // System broadcast / pengumuman
    const msg = (d.message || '').toLowerCase();
    if (msg.includes('pengumuman')) return '/dashboard';
    if (msg.includes('anomali') || msg.includes('suspicious')) {
        return role === 'admin' ? '/admin/prices' : '/merchant';
    }
    if (msg.includes('harga')) return '/products';
    if (msg.includes('verifikasi')) return role === 'admin' ? '/admin/merchants' : '/merchant';

    return '';
}

async function handleNotifClick(id, isUnread, link) {
    if (isUnread) {
        try {
            await api(`/notifications/${id}/read`, 'POST');
            loadNotifications();
        } catch(e) {}
    }
    // Refresh the dropdown to show updated state
    const dropdown = document.getElementById('notif-dropdown');
    if (dropdown) dropdown.classList.remove('show');
    // Navigate if there's a relevant link
    if (link) {
        window.location.href = link;
    } else {
        // Just refresh dropdown to show read state
        setTimeout(() => { showNotifDropdown(); }, 200);
    }
}

async function markAllRead() {
    try {
        await api('/notifications/read-all', 'POST');
        showToast('Semua notifikasi ditandai sudah dibaca');
        loadNotifications();
        // Refresh dropdown
        const dropdown = document.getElementById('notif-dropdown');
        if (dropdown) { dropdown.classList.remove('show'); setTimeout(showNotifDropdown, 200); }
    } catch(e) { showToast('Gagal menandai notifikasi', 'error'); }
}

async function clearReadNotifs() {
    try {
        await api('/notifications/read', 'DELETE');
        showToast('Notifikasi terbaca berhasil dihapus');
        loadNotifications();
        // Refresh dropdown
        const dropdown = document.getElementById('notif-dropdown');
        if (dropdown) { dropdown.classList.remove('show'); setTimeout(showNotifDropdown, 200); }
    } catch(e) { showToast('Gagal menghapus notifikasi', 'error'); }
}

// Keep old function name for backward compat
async function markNotifRead(id) {
    handleNotifClick(id, true, '');
}

// ===== FORMAT HELPERS =====
function formatRupiah(num) {
    if (!num && num !== 0) return '-';
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function formatChange(pct) {
    if (pct === null || pct === undefined) return '';
    const icon = pct > 0 ? '↑' : pct < 0 ? '↓' : '→';
    const cls = pct > 0 ? 'up' : pct < 0 ? 'down' : '';
    return `<span class="stat-change ${cls}">${icon} ${Math.abs(pct)}%</span>`;
}

function timeAgo(dateStr) {
    const d = new Date(dateStr);
    const now = new Date();
    const diff = (now - d) / 1000;
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff/60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff/3600) + ' jam lalu';
    if (diff < 604800) return Math.floor(diff/86400) + ' hari lalu';
    return d.toLocaleDateString('id-ID');
}

function stockLabel(status) {
    const map = { available: 'Tersedia', scarce: 'Langka', out_of_stock: 'Habis' };
    return `<span class="product-stock"><span class="stock-dot ${status}"></span>${map[status] || status}</span>`;
}

function roleBadge(role) {
    const map = { user: 'User', pedagang: 'Pedagang', admin: 'Admin' };
    return `<span class="badge badge-${role}">${map[role] || role}</span>`;
}

// ===== NAVBAR STATE =====
function updateNavbar() {
    const authLinks = document.getElementById('auth-links');
    const userMenu = document.getElementById('user-menu');
    if (!authLinks || !userMenu) return;

    if (Auth.isLoggedIn()) {
        const user = Auth.getUser();
        authLinks.style.display = 'none';
        userMenu.style.display = 'flex';
        const nameEl = document.getElementById('user-name');
        if (nameEl) nameEl.textContent = user ? user.name : 'User';
        
        const navAvatar = document.getElementById('nav-avatar');
        if (navAvatar && user) {
            if (user.profile_photo) {
                navAvatar.innerHTML = `<img src="/storage/${user.profile_photo}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">`;
            } else if (user.avatar) {
                navAvatar.innerHTML = `<span style="font-size:1.2rem;">${user.avatar}</span>`;
            } else {
                const initials = (user.name || 'U').split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase();
                navAvatar.innerHTML = `<span style="color:#fff;font-size:0.85rem;font-weight:700;">${initials}</span>`;
            }
        }
    } else {
        authLinks.style.display = 'flex';
        userMenu.style.display = 'none';
    }
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    updateNavbar();
    loadNotifications();
    updateAdminSidebarBadges();
    
    // Poll notifications and badges every 60s
    if (Auth.isLoggedIn()) {
        setInterval(() => {
            loadNotifications();
            updateAdminSidebarBadges();
        }, 60000);
    }
});

// Close dropdowns on outside click
document.addEventListener('click', (e) => {
    if (!e.target.closest('.notif-bell')) {
        const d = document.getElementById('notif-dropdown');
        if (d) d.classList.remove('show');
    }
});
