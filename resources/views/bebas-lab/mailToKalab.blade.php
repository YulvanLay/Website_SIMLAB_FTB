<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Notifikasi Persetujuan Bebas Laboratorium</title>
</head>

<body>

    <p>Yth. Kepala Laboratorium,</p>

    <p>
        Terdapat pengajuan Bebas Laboratorium yang telah diverifikasi dan
        disetujui oleh Laboran, serta saat ini menunggu persetujuan
        Bapak/Ibu selaku Kepala Laboratorium.
    </p>

    <p><strong>Detail Pengajuan:</strong></p>

    <ul>
        <li><strong>Nomor Pengajuan :</strong> {{ $bebasLaboratorium->id }}</li>
        <li><strong>Nama Mahasiswa :</strong> {{ $pelanggan->nama_pelanggan }}</li>
        <li><strong>NRP/NIM :</strong> {{ $pelanggan->kode_pelanggan }}</li>
        <li><strong>Laboratorium :</strong> {{ $laboratorium->nama_laboratorium }}</li>
        <li><strong>Tanggal Verifikasi Laboran :</strong> {{ $tanggal }}</li>
        <li><strong>Status :</strong> Menunggu Persetujuan Kepala Laboratorium</li>
    </ul>

    <p>
        Mohon untuk melakukan peninjauan dan memberikan persetujuan melalui
        Sistem Informasi Manajemen Laboratorium (SIMLAB).
    </p>

    <p>
        Terima kasih atas perhatian dan kerja samanya.
    </p>

    <br>

    <p>
        Hormat kami,
    </p>

    <p>
        Sistem Informasi Manajemen Laboratorium (SIMLAB)<br>
        Fakultas Teknologi dan Bisnis
    </p>

</body>

</html>