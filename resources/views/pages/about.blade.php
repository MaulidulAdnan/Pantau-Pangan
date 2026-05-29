@extends('layouts.app')
@section('title', 'Tentang Kami - Pantau Pangan')

@section('content')
<div class="container py-4">
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 40px;">
        <h1 class="page-title text-center" style="margin-bottom: 24px;">Tentang Pantau Pangan</h1>
        
        <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text);">
            <p><strong>Pantau Pangan</strong> adalah platform inovatif berbasis komunitas yang didedikasikan untuk memantau fluktuasi harga bahan pangan pokok secara <i>real-time</i> dan transparan. Kami lahir dari kebutuhan mendesak akan keterbukaan informasi harga di pasar tradisional, di mana masyarakat sering kali kesulitan membandingkan harga atau menyadari adanya lonjakan harga yang tidak wajar.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">Visi Kami</h3>
            <p>Menciptakan ekosistem pasar tradisional yang transparan, adil, dan stabil bagi seluruh masyarakat Indonesia, serta membantu pemerintah dalam mendeteksi dan mencegah penimbunan atau manipulasi harga pangan pokok.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">Misi Kami</h3>
            <ul style="padding-left: 20px;">
                <li style="margin-bottom: 8px;">Menyediakan akses informasi harga pangan harian yang terpercaya dari para pedagang yang telah diverifikasi.</li>
                <li style="margin-bottom: 8px;">Memanfaatkan teknologi <i>crowdsourcing</i> dan deteksi anomali otomatis untuk mencegah lonjakan harga yang merugikan.</li>
                <li style="margin-bottom: 8px;">Membangun ruang diskusi komunitas (forum) agar masyarakat dan pedagang dapat saling berbagi informasi ketersediaan stok secara interaktif.</li>
                <li style="margin-bottom: 8px;">Menyajikan data yang komprehensif bagi pemerintah dan pengambil kebijakan (Admin) untuk mengambil tindakan cepat melalui fitur pelaporan CSV dan Broadcast.</li>
            </ul>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">Bagaimana Kami Bekerja?</h3>
            <p>Platform kami menghubungkan dua pihak utama: <strong>Masyarakat (Pembeli)</strong> dan <strong>Pedagang Terverifikasi</strong>. Pedagang yang mendaftarkan tokonya dapat secara berkala memasukkan harga terbaru dari komoditas yang mereka jual. Sistem pintar kami akan mengakumulasi data tersebut, menghitung rata-rata secara <i>real-time</i>, dan langsung menampilkannya dalam bentuk grafik yang mudah dipahami oleh masyarakat umum.</p>

            <div style="margin-top: 48px; text-align: center; padding: 24px; background: rgba(5,150,105,0.05); border-radius: 12px;">
                <h4 style="font-weight: 700; margin-bottom: 8px;">Mari Bergabung Memantau Harga!</h4>
                <p class="text-muted" style="margin-bottom: 16px;">Satu laporan harga dari Anda dapat menyelamatkan dompet ribuan pembeli lainnya.</p>
                <a href="/register" class="btn btn-primary">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>
@endsection
