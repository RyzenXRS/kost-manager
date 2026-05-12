<?php
session_start();
include 'koneksi.php';

// Fungsi format rupiah jika belum didefinisikan di koneksi.php
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka){
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// --- 1. LOGIKA SIMPAN PEMBAYARAN ---
if (isset($_POST['simpan_pembayaran'])) {
    $id_penghuni = mysqli_real_escape_string($conn, $_POST['id_penghuni']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl_bayar']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode_bayar']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    
    $no_invoice = "INV-" . date('Ym') . "-" . rand(100, 999);

    $query_ins = "INSERT INTO pembayaran (id_penghuni, tgl_bayar, no_invoice, metode_bayar, jumlah_bayar) 
                  VALUES ('$id_penghuni', '$tgl', '$no_invoice', '$metode', '$jumlah')";
    
    if (mysqli_query($conn, $query_ins)) {
        mysqli_query($conn, "UPDATE penghuni SET status_pembayaran = 'Lunas' WHERE id_penghuni = '$id_penghuni'");
        echo "<script>alert('Pembayaran Berhasil Dicatat!'); window.location='pembayaran.php';</script>";
    }
}

// --- 2. QUERY DATA UNTUK TABEL ---
$sql = "SELECT pembayaran.*, penghuni.nama_lengkap 
        FROM pembayaran 
        JOIN penghuni ON pembayaran.id_penghuni = penghuni.id_penghuni 
        ORDER BY tgl_bayar DESC";
$result = mysqli_query($conn, $sql);
$list_p = mysqli_query($conn, "SELECT id_penghuni, nama_lengkap FROM penghuni WHERE status_penyewa = 'Aktif'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Layouting Styles */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-color: #1e293b;
        }

        body {
            margin: 0;
            display: flex;
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar Tetap */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        /* Container Utama */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header / Navbar */
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

        /* Content Area */
        .main-content {
            padding: 30px;
            flex: 1;
        }

        /* Footer */
        .footer {
            background: white;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        /* Penyesuaian Tabel */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="logo"><i class="fas fa-building"></i> KostManager</div>
            <ul class="nav-links">
                <li onclick="window.location='admin_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</li>
                <li onclick="window.location='kamar.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'kamar.php' ? 'active' : '' ?>"><i class="fas fa-bed"></i> Kamar</li>
                <li onclick="window.location='penghuni.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'penghuni.php' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Penyewa</li>
                <li onclick="window.location='pembayaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pembayaran</li>
                <li onclick="window.location='tagihan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'tagihan.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Tagihan</li>
                <li onclick="window.location='pengeluaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pengeluaran</li>
                <li onclick="window.location='laporan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Laporan</li>
                <li><i class="fab fa-whatsapp"></i> WhatsApp</li>
            </ul>
        </div>
        <div class="user-profile">
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 20px 10px 20px;">
                <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                <span style="font-size: 14px;">Admin</span>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-wrapper">
        
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Manajemen Transaksi</h2>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <span style="color: #64748b; font-size: 14px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></span>
                <div class="user-info" style="display: flex; align-items: center; gap: 10px; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                    <span style="font-weight: 600; font-size: 14px;">Super Admin</span>
                    <i class="fas fa-chevron-down" style="font-size: 12px; color: #94a3b8;"></i>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0;">Pembayaran</h1>
                        <p style="color: #64748b; margin-top: 5px;">Riwayat pencatatan pembayaran penyewa</p>
                    </div>
                    <button class="btn-add" onclick="document.getElementById('modalBayar').style.display='flex'">
                        <i class="fas fa-plus"></i> Catat Pembayaran
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Penyewa</th>
                                <th>No. Invoice</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= date('j/n/Y', strtotime($row['tgl_bayar'])) ?></td>
                                <td><strong><?= $row['nama_lengkap'] ?></strong></td>
                                <td style="color: #64748b;"><?= $row['no_invoice'] ?></td>
                                <td>
                                    <?php 
                                        $m = $row['metode_bayar'];
                                        $badge_cls = ($m == 'Transfer') ? 'status-terisi' : (($m == 'Tunai') ? 'status-tersedia' : '');
                                    ?>
                                    <span class="badge <?= $badge_cls ?>" style="text-transform: capitalize;">
                                        <?= $m ?>
                                    </span>
                                </td>
                                <td><strong><?= formatRupiah($row['jumlah_bayar']) ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if(mysqli_num_rows($result) == 0): ?>
                                <tr><td colspan="5" style="text-align:center; padding:30px; color:#94a3b8;">Belum ada data pembayaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="footer">
            &copy; <?= date('Y'); ?> <strong>KostManager</strong>. Sistem Manajemen Kost.
        </footer>
    </div>

    <div id="modalBayar" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <h2 style="margin-top: 0; margin-bottom: 20px;">Catat Pembayaran Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Pilih Penghuni</label>
                    <select name="id_penghuni" required>
                        <option value="">-- Pilih Nama --</option>
                        <?php 
                        mysqli_data_seek($list_p, 0); // Reset pointer result set
                        while($p = mysqli_fetch_assoc($list_p)): ?>
                            <option value="<?= $p['id_penghuni'] ?>"><?= $p['nama_lengkap'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pembayaran</label>
                    <input type="date" name="tgl_bayar" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_bayar">
                        <option value="Transfer">Transfer</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Tunai">Tunai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah (Rp)</label>
                    <input type="number" name="jumlah" placeholder="Contoh: 1500000" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-cancel" style="flex:1;" onclick="document.getElementById('modalBayar').style.display='none'">Batal</button>
                    <button type="submit" name="simpan_pembayaran" class="btn-save" style="flex:1;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>