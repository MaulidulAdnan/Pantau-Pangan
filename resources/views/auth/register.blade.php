@extends('layouts.app')
@section('title', 'Daftar - Pantau Pangan')
@section('content')
<div class="container" style="max-width:440px;padding-top:40px;">
    <div class="card" style="padding:40px;">
        <div class="text-center mb-3">
            <div style="font-size:2.5rem;margin-bottom:8px;">🌾</div>
            <h1 style="font-size:1.5rem;font-weight:800;">Daftar Akun</h1>
            <p class="text-muted text-sm">Bergabung dengan komunitas PantauPangan</p>
        </div>
        <form id="register-form">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-input" id="name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-input" id="email" required>
            </div>
            <div class="form-group">
                <label class="form-label">No. Telepon (opsional)</label>
                <input type="text" class="form-input" id="phone">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-input" id="password" minlength="8" required>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-input" id="password_confirmation" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;" id="reg-btn">Daftar</button>
        </form>
        <div class="text-center mt-3 text-sm">
            Sudah punya akun? <a href="/login">Masuk</a>
        </div>
        <div class="text-center mt-1 text-sm">
            <a href="/register/merchant">Daftar sebagai Pedagang →</a>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('reg-btn');
    btn.textContent = 'Memproses...'; btn.disabled = true;
    try {
        const data = await api('/auth/register', 'POST', {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value || null,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value,
        });
        Auth.setToken(data.token);
        Auth.setUser(data.user);
        showToast('Registrasi berhasil!');
        setTimeout(() => window.location.href = '/dashboard', 500);
    } catch(err) {
        const msgs = err.data?.errors ? Object.values(err.data.errors).flat().join(', ') : (err.data?.message || 'Registrasi gagal');
        showToast(msgs, 'error');
        btn.textContent = 'Daftar'; btn.disabled = false;
    }
});
</script>
@endpush
