<?php
session_start();
require 'koneksi.php'; // Menggunakan file koneksi yang sudah Anda buat

// 1. PROTEKSI HALAMAN: Hanya admin yang boleh mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// 2. QUERY RINGKASAN DATA UNTUK LAPORAN

// A. Statistik Kamar
$total_kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar"))['total'];
$kamar_tersedia = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'Tersedia'"))['total'];
$kamar_terisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'Terisi'"))['total'];

// B. Statistik Penghuni
$penghuni_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penghuni WHERE status_penyewa = 'Aktif'"))['total'];
$belum_bayar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penghuni WHERE status_pembayaran = 'Belum Bayar' AND status_penyewa = 'Aktif'"))['total'];

// C. Ringkasan Keuangan (Bulan Ini)
$bulan_ini = date('m');
$tahun_ini = date('Y');

$pemasukan_bln_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_bayar) as total FROM pembayaran WHERE MONTH(tgl_bayar) = '$bulan_ini' AND YEAR(tgl_bayar) = '$tahun_ini'"))['total'] ?? 0;
$pengeluaran_bln_ini = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'"))['total'] ?? 0;
$laba_bersih = $pemasukan_bln_ini - $pengeluaran_bln_ini;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem - KostManager</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { padding: 30px; flex: 1; }
        
        .report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .report-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .report-card h3 { margin: 0 0 15px 0; font-size: 16px; color: #64748b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        .report-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .report-item span.label { color: #64748b; }
        .report-item span.value { font-weight: 600; color: #1e293b; }
        
        .btn-print { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        @media print { .sidebar, .btn-print, .navbar { display: none; } .main-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <header class="navbar" style="height: var(--navbar-height); background: white; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin:0; font-size: 20px; color: #1e293b;">Laporan Operasional</h2>
            <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Laporan</button>
        </header>

        <main class="main-content">
            <div class="section-header" style="margin-bottom: 25px;">
                <h1 style="margin: 0; font-size: 24px;">Ringkasan Kost - <?= date('F Y') ?></h1>
                <p style="color: #64748b;">Laporan otomatis berdasarkan data sistem saat ini.</p>
            </div>

            <div class="report-grid">
                <div class="report-card">
                    <h3><i class="fas fa-bed"></i> Status Hunian Kamar</h3>
                    <div class="report-item">
                        <span class="label">Total Kapasitas</span>
                        <span class="value"><?= $total_kamar ?> Kamar</span>
                    </div>
                    <div class="report-item">
                        <span class="label">Kamar Terisi</span>
                        <span class="value" style="color: #4f46e5;"><?= $kamar_terisi ?> Kamar</span>
                    </div>
                    <div class="report-item">
                        <span class="label">Kamar Kosong</span>
                        <span class="value" style="color: #10b981;"><?= $kamar_tersedia ?> Kamar</span>
                    </div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-users"></i> Status Penyewa</h3>
                    <div class="report-item">
                        <span class="label">Penyewa Aktif</span>
                        <span class="value"><?= $penghuni_aktif ?> Orang</span>
                    </div>
                    <div class="report-item">
                        <span class="label">Menunggu Pembayaran</span>
                        <span class="value" style="color: #ef4444;"><?= $belum_bayar ?> Orang</span>
                    </div>
                </div>

                <div class="report-card">
                    <h3><i class="fas fa-wallet"></i> Keuangan Bulan Ini</h3>
                    <div class="report-item">
                        <span class="label">Total Pemasukan</span>
                        <span class="value" style="color: #10b981;"><?= formatRupiah($pemasukan_bln_ini) ?></span>
                    </div>
                    <div class="report-item">
                        <span class="label">Total Pengeluaran</span>
                        <span class="value" style="color: #ef4444;"><?= formatRupiah($pengeluaran_bln_ini) ?></span>
                    </div>
                    <div style="margin-top: 15px; padding-top: 10px; border-top: 2px dashed #f1f5f9; display: flex; justify-content: space-between;">
                        <span style="font-weight: bold;">Laba Bersih</span>
                        <span style="font-weight: bold; color: #4f46e5;"><?= formatRupiah($laba_bersih) ?></span>
                    </div>
                </div>
            </div>

            <div style="background: #e0e7ff; color: #4338ca; padding: 15px; border-radius: 8px; font-size: 14px;">
                <i class="fas fa-info-circle"></i> Data di atas adalah data *real-time*. Untuk detail transaksi lengkap, silakan cek menu <strong>Pembayaran</strong> atau <strong>Pengeluaran</strong>.
            </div>
        </main>

        <?php include 'includes/footer.php'; ?>
    </div>

</body>
</html>