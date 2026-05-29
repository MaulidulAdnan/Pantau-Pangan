@extends('layouts.app')
@section('title', 'Kebijakan Privasi - Pantau Pangan')

@section('content')
<div class="container py-4">
    <div class="card" style="max-width: 800px; margin: 0 auto; padding: 40px;">
        <h1 class="page-title text-center" style="margin-bottom: 8px;">Kebijakan Privasi</h1>
        <p class="text-center text-muted" style="margin-bottom: 32px;">Pembaruan Terakhir: {{ now()->format('d F Y') }}</p>
        
        <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text);">
            <p>Privasi Anda sangat penting bagi <strong>Pantau Pangan</strong>. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat Anda menggunakan platform kami.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">1. Informasi yang Kami Kumpulkan</h3>
            <p>Kami hanya mengumpulkan informasi yang esensial untuk beroperasinya platform ini. Informasi tersebut meliputi:</p>
            <ul style="padding-left: 20px;">
                <li style="margin-bottom: 8px;"><strong>Data Akun:</strong> Nama, email, dan kata sandi yang Anda berikan saat mendaftar.</li>
                <li style="margin-bottom: 8px;"><strong>Data Pedagang:</strong> Khusus untuk pedagang terverifikasi, kami mengumpulkan data tambahan berupa nama toko, foto KTP, dan lokasi toko/pasar (daerah) untuk keperluan validasi kontribusi harga.</li>
                <li style="margin-bottom: 8px;"><strong>Data Interaksi:</strong> Komentar, pelaporan anomali harga (report), dan status favorit yang Anda pilih di dalam platform.</li>
            </ul>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">2. Penggunaan Informasi</h3>
            <p>Informasi yang kami kumpulkan digunakan untuk:</p>
            <ul style="padding-left: 20px;">
                <li style="margin-bottom: 8px;">Memfasilitasi layanan utama (login, pembuatan profil, dan pencatatan harga).</li>
                <li style="margin-bottom: 8px;">Menjaga akurasi dan kredibilitas data (mendeteksi input harga anonim atau manipulatif).</li>
                <li style="margin-bottom: 8px;">Mengirimkan pengumuman sistem (broadcast) atau notifikasi terkait interaksi komunitas.</li>
            </ul>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">3. Transparansi Data Harga</h3>
            <p>Perlu dipahami bahwa <strong>data harga dan nama toko</strong> yang diinput oleh pedagang bersifat publik (<i>crowdsourced public data</i>) dan akan ditampilkan secara terbuka bagi seluruh pengguna untuk memfasilitasi transparansi pasar.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">4. Keamanan Data</h3>
            <p>Kami menerapkan prosedur keamanan teknis (seperti enkripsi kata sandi dan keamanan token JWT) untuk melindungi data pribadi Anda dari akses yang tidak sah. KTP pedagang hanya disimpan untuk verifikasi admin dan tidak disebar luaskan.</p>

            <h3 style="margin-top: 32px; font-weight: 700; color: var(--primary);">5. Hubungi Kami</h3>
            <p>Jika Anda memiliki pertanyaan mengenai perlindungan data pribadi atau ingin menghapus akun beserta seluruh data terkait, silakan hubungi tim Admin kami melalui email: <strong>privacy@pantaupangan.com</strong>.</p>
        </div>
    </div>
</div>
@endsection
