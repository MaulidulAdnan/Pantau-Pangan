@extends('layouts.app')
@section('title', 'Masuk - Pantau Pangan')
@section('content')
<div class="container" style="max-width:440px;padding-top:40px;">
    <div class="card" style="padding:40px;">
        <div class="text-center mb-3">
            <div style="font-size:2.5rem;margin-bottom:8px;">🌾</div>
            <h1 style="font-size:1.5rem;font-weight:800;">Masuk ke PantauPangan</h1>
            <p class="text-muted text-sm">Pantau harga pangan secara transparan</p>
        </div>
        <form id="login-form">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="email" placeholder="nama@email.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" id="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;" id="login-btn">Masuk</button>
        </form>
        <div class="text-center mt-3 text-sm">
            Belum punya akun? <a href="/register">Daftar sekarang</a>
        </div>
        <div class="text-center mt-1 text-sm">
            <a href="/register/merchant">Daftar sebagai Pedagang →</a>
        </div>
    </div>
    <div class="card mt-2" style="padding:16px;">
        <p class="text-sm text-muted text-center" style="margin-bottom:8px;">Demo Accounts:</p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;">
            <button class="btn btn-ghost btn-sm" onclick="fillDemo('admin@pantaupangan.id')">Admin</button>
            <button class="btn btn-ghost btn-sm" onclick="fillDemo('budi@pedagang.id')">Pedagang</button>
            <button class="btn btn-ghost btn-sm" onclick="fillDemo('dewi@user.id')">User</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function fillDemo(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password123';
}
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    btn.textContent = 'Memproses...'; btn.disabled = true;
    try {
        const data = await api('/auth/login', 'POST', {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
        });
        Auth.setToken(data.token);
        Auth.setUser(data.user);
        showToast('Login berhasil!');
        setTimeout(() => {
            if (data.user.role === 'admin') window.location.href = '/admin';
            else if (data.user.role === 'pedagang') window.location.href = '/merchant';
            else window.location.href = '/dashboard';
        }, 500);
    } catch(err) {
        showToast(err.data?.message || 'Login gagal', 'error');
        btn.textContent = 'Masuk'; btn.disabled = false;
    }
});
</script>
@endpush
