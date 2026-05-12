<?php
session_start();
require 'koneksi.php';

// Proteksi Halaman: Hanya penyewa yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penyewa') {
    header("location:login.php");
    exit();
}

// Ambil Nama dari Session
$nama_login = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'penyewa';

// Ambil data kamar & status
$query = "SELECT penghuni.*, kamar.nomor_kamar, kamar.harga_per_bulan 
          FROM penghuni 
          LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar 
          WHERE penghuni.nama_lengkap = '$nama_login'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

$status_bayar = isset($data['status_pembayaran']) ? $data['status_pembayaran'] : 'Menunggu';
// Warna badge disesuaikan dengan standar admin
$status_class = ($status_bayar == 'Lunas') ? 'status-tersedia' : 'status-terisi'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Layouting agar identik dengan Admin */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-dark: #1e293b;
        }

        body { 
            margin: 0; 
            display: flex; 
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar: Disamakan dengan Admin (Dark) */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar: Disamakan dengan Admin */
        .header {
            height: var(--navbar-height);
            background: white;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .main-content {
            padding: 30px;
            flex: 1;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .footer {
            background: white;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        /* Utility Badge */
        .badge-user {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <?php include 'includes/user_sidebar.php'; ?>

    <div class="main-wrapper">
        
        <?php include 'includes/user_header.php'; ?>

        <main class="main-content">
            <div class="section-header" style="margin-bottom: 25px;">
                <h1 style="margin: 0; font-size: 24px;">Selamat Datang, <?= $nama_login; ?>!</h1>
                <p style="color: #64748b; margin-top: 5px;">Pantau status sewa dan tagihan Anda di sini.</p>
            </div>

            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                
                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0 0 10px 0;">Kamar Saya</p>
                            <h3 style="font-size: 28px; margin: 0; color: #1e293b;">No. <?= $data['nomor_kamar'] ?? '-'; ?></h3>
                        </div>
                        <i class="fas fa-door-open" style="font-size: 24px; color: #e2e8f0;"></i>
                    </div>
                    <span class="badge status-terisi" style="margin-top: 15px; display: inline-block;">Penyewa Aktif</span>
                </div>

                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0 0 10px 0;">Biaya Sewa</p>
                            <h3 style="font-size: 28px; margin: 0; color: #1e293b;">Rp <?= number_format($data['harga_per_bulan'] ?? 0, 0, ',', '.'); ?></h3>
                        </div>
                        <i class="fas fa-coins" style="font-size: 24px; color: #e2e8f0;"></i>
                    </div>
                    <p style="color: #94a3b8; font-size: 12px; margin: 15px 0 0 0;">Tagihan rutin setiap bulan</p>
                </div>

                <div class="stat-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <p style="color: #94a3b8; font-size: 14px; margin: 0 0 10px 0;">Status Pembayaran</p>
                            <h3 style="font-size: 28px; margin: 0; color: <?= ($status_bayar == 'Lunas') ? '#10b981' : '#f59e0b'; ?>;">
                                <?= $status_bayar; ?>
                            </h3>
                        </div>
                        <i class="fas fa-receipt" style="font-size: 24px; color: #e2e8f0;"></i>
                    </div>
                    <p style="color: #94a3b8; font-size: 12px; margin: 15px 0 0 0;">Periode: <strong><?= date('F Y'); ?></strong></p>
                </div>

            </div>
        </main>

        <?php include 'includes/user_footer.php'; ?>
    </div> </body>
</html>