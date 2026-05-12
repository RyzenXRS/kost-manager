<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penyewa') {
    header("location:login.php");
    exit();
}

// Ambil ID secara otomatis dari session yang dikirim login.php
$id_penghuni = isset($_SESSION['id_penghuni']) ? $_SESSION['id_penghuni'] : 0;
// JIKA MAU TES MANUAL, hapus tanda komentar di bawah:
// $id_penghuni = 7; 

// Hapus 3 baris $sql yang lama, ganti jadi ini:
$sql = "SELECT p.nama_lengkap, p.tgl_masuk, p.id_kamar, 
               k.nomor_kamar, k.tipe_kamar, k.harga_per_bulan
        FROM penghuni p
        LEFT JOIN kamar k ON p.id_kamar = k.id_kamar 
        WHERE p.id_penghuni = '$id_penghuni'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

// Jika ID Session masih 0, tampilkan peringatan jelas
if ($id_penghuni == 0) {
    die("Error: Session ID tidak ditemukan. Silakan Logout lalu Login kembali agar ID terdaftar.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kamar Saya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f9; padding: 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: auto; overflow: hidden; }
        .card-header { background: #1e293b; color: white; padding: 15px; }
        .card-body { padding: 20px; }
        .info-item { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .label { font-size: 12px; color: #64748b; font-weight: bold; text-transform: uppercase; }
        .value { font-size: 16px; color: #1e293b; }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <h3 style="margin:0;">Detail Kamar: <?= htmlspecialchars($data['nama_lengkap'] ?? 'User') ?></h3>
    </div>
    <div class="card-body">
        <?php if($data && !empty($data['id_kamar'])): ?>
            <div class="info-item">
                <div class="label">Nomor Kamar</div>
                <div class="value">Kamar <?= htmlspecialchars($data['nomor_kamar']) ?> (<?= htmlspecialchars($data['tipe_kamar']) ?>)</div>
            </div>
            <div class="info-item">
                <div class="label">Tanggal Masuk</div>
                <div class="value"><?= date('d F Y', strtotime($data['tgl_masuk'])) ?></div>
            </div>
            <div class="info-item">
                <div class="label">Harga Sewa</div>
                <div class="value">Rp <?= number_format($data['harga_per_bulan'], 0, ',', '.') ?> / bulan</div>
            </div>
        <?php else: ?>
            <p style="text-align:center; color: #64748b;">
                Halo <strong><?= htmlspecialchars($data['nama_lengkap'] ?? 'Penghuni') ?></strong>,<br>
                Data kamar tidak ditemukan. Pastikan Admin sudah mengisi nomor kamar Anda.
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>