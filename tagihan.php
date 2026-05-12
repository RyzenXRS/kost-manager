<?php
session_start();
require 'koneksi.php'; // Mengambil koneksi database

// 1. PROTEKSI HALAMAN: Hanya admin yang boleh mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// 2. QUERY AMBIL DATA TAGIHAN
// Menggabungkan tabel tagihan, penghuni, dan kamar untuk mendapatkan informasi lengkap
$sql = "SELECT tagihan.*, penghuni.nama_lengkap, kamar.nomor_kamar 
        FROM tagihan 
        JOIN penghuni ON tagihan.id_penghuni = penghuni.id_penghuni
        LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar
        ORDER BY tagihan.jatuh_tempo ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tagihan - KostManager</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Penyesuaian layout agar konsisten dengan dashboard admin */
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

        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            padding: 30px;
            flex: 1;
        }

        /* Styling Badge Status */
        .status-lunas { background: #dcfce7; color: #16a34a; }
        .status-pending { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <header class="navbar" style="height: var(--navbar-height); background: white; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin:0; font-size: 20px; color: #1e293b;">Manajemen Tagihan</h2>
            <div style="color: #64748b;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></div>
        </header>

        <main class="main-content">
            <div class="data-section" style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px;">Daftar Tagihan</h1>
                        <p style="color: #64748b; margin-top: 5px;">Total tagihan aktif: <?= mysqli_num_rows($result); ?></p>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 15px; color: #64748b;">Penghuni</th>
                                <th style="padding: 15px; color: #64748b;">Kamar</th>
                                <th style="padding: 15px; color: #64748b;">Bulan</th>
                                <th style="padding: 15px; color: #64748b;">Jumlah</th>
                                <th style="padding: 15px; color: #64748b;">Jatuh Tempo</th>
                                <th style="padding: 15px; color: #64748b;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 15px;"><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                    <td style="padding: 15px;"><span class="badge status-terisi">Kamar <?= htmlspecialchars($row['nomor_kamar']) ?></span></td>
                                    <td style="padding: 15px;"><?= htmlspecialchars($row['bulan_tagihan']) ?></td>
                                    <td style="padding: 15px;"><strong>Rp <?= number_format($row['jumlah_tagihan'], 0, ',', '.') ?></strong></td>
                                    <td style="padding: 15px;"><?= date('d M Y', strtotime($row['jatuh_tempo'])) ?></td>
                                    <td style="padding: 15px;">
                                        <?php if($row['status_tagihan'] == 'Lunas'): ?>
                                            <span class="badge status-tersedia" style="padding: 5px 10px; border-radius: 6px; font-size: 12px;">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge status-jatuh-tempo" style="padding: 5px 10px; border-radius: 6px; font-size: 12px;">Belum Bayar</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <i class="fas fa-file-invoice" style="font-size: 30px; margin-bottom: 10px; display: block;"></i>
                                        Belum ada data tagihan yang tercatat.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php include 'includes/footer.php'; ?>
    </div>

</body>
</html>