@extends('layouts.app')
@section('title', 'Syarat & Ketentuan - Pantau Pangan')

@section('content')
<div class="container py-4">
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 40px;">
        <h1 class="page-title text-center" style="margin-bottom: 8px;">Syarat & Ketentuan</h1>
        <p class="text-center text-muted" style="margin-bottom: 32px;">Berlaku efektif sejak: {{ now()->format('d F Y') }}</p>
        
        <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text);">
            <p>Selamat datang di <strong>Pantau Pangan</strong>. Dengan mengakses dan mendaftar pada platform ini, Anda setuju untuk terikat dan patuh terhadap Syarat & Ketentuan berikut. Harap membacanya dengan seksama.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">1. Penggunaan Layanan</h3>
            <p>Layanan Pantau Pangan (website dan aplikasi) disediakan semata-mata untuk tujuan informasi dan pemantauan harga pangan. Anda dilarang menggunakan platform ini untuk:</p>
            <ul style="padding-left: 20px;">
                <li style="margin-bottom: 8px;">Melakukan manipulasi data harga (memasukkan harga fiktif) untuk tujuan menjatuhkan harga pasar atau penimbunan.</li>
                <li style="margin-bottom: 8px;">Mengirim spam, ujaran kebencian, atau promosi tidak relevan di kolom diskusi komunitas.</li>
                <li style="margin-bottom: 8px;">Melakukan upaya peretasan, ekstraksi data masif (<i>scraping</i>) tanpa izin tertulis dari Admin.</li>
            </ul>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">2. Tanggung Jawab Pedagang</h3>
            <p>Bagi Anda yang mendaftar sebagai <strong>Pedagang</strong>:</p>
            <ul style="padding-left: 20px;">
                <li style="margin-bottom: 8px;">Anda wajib memberikan informasi verifikasi (KTP/Lokasi Toko) yang sah dan dapat dipertanggungjawabkan.</li>
                <li style="margin-bottom: 8px;">Anda harus menjamin bahwa harga yang Anda input adalah harga aktual (eceran) yang sedang berlaku di toko Anda.</li>
                <li style="margin-bottom: 8px;">Sistem kami memiliki algoritma pendeteksi anomali (<i>Suspicious Price Detection</i>). Apabila harga yang Anda masukkan menyimpang terlalu jauh dari harga pasar (lebih dari 50%), sistem berhak menandai atau menyembunyikan data tersebut hingga ditinjau oleh Admin.</li>
            </ul>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">3. Sanksi dan Pemblokiran (Banned)</h3>
            <p>Admin berhak penuh untuk menonaktifkan, membekukan sementara, atau menghapus akun secara permanen (<i>Banned</i>) apabila Anda ditemukan melanggar Syarat & Ketentuan ini berulang kali, terutama dalam kasus manipulasi data atau laporan negatif dari komunitas.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">4. Penolakan Tanggung Jawab (Disclaimer)</h3>
            <p>Data harga yang ditampilkan merupakan hasil <i>crowdsourcing</i> dan perhitungan rata-rata algoritma kami. Walaupun kami berupaya keras memfilter anomali, <strong>Pantau Pangan tidak menjamin harga tersebut 100% akurat</strong> di lapangan karena fluktuasi menit ke menit. Pantau Pangan tidak dapat dituntut atas kerugian materil akibat perbedaan harga saat transaksi langsung di pasar.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">5. Perubahan Syarat</h3>
            <p>Kami berhak memperbarui Syarat & Ketentuan ini kapan saja. Perubahan yang signifikan akan kami sampaikan melalui fitur <i>Broadcast Pengumuman</i> kepada seluruh pengguna aktif.</p>
        </div>
    </div>
</div>
@endsection
