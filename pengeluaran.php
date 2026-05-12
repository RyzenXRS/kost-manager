<?php
session_start();
require 'koneksi.php'; // Memastikan koneksi ke db_kost

// 1. PROTEKSI HALAMAN: Hanya admin yang boleh mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// 2. LOGIKA TAMBAH PENGELUARAN
if (isset($_POST['tambah_pengeluaran'])) {
    $nama_p = mysqli_real_escape_string($conn, $_POST['nama_pengeluaran']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $ket = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query_ins = "INSERT INTO pengeluaran (nama_pengeluaran, kategori, jumlah, tanggal, keterangan) 
                  VALUES ('$nama_p', '$kategori', '$jumlah', '$tanggal', '$ket')";
    
    if (mysqli_query($conn, $query_ins)) {
        echo "<script>alert('Pengeluaran berhasil dicatat!'); window.location='pengeluaran.php';</script>";
    } else {
        echo "<script>alert('Gagal mencatat: " . mysqli_error($conn) . "');</script>";
    }
}

// 3. LOGIKA HAPUS PENGELUARAN
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    if (mysqli_query($conn, "DELETE FROM pengeluaran WHERE id_pengeluaran = '$id'")) {
        echo "<script>alert('Data dihapus!'); window.location='pengeluaran.php';</script>";
    }
}

// 4. QUERY AMBIL DATA UNTUK TABEL
$sql = "SELECT * FROM pengeluaran ORDER BY tanggal DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - KostManager</title>
    <link rel="stylesheet" href="style.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { padding: 30px; flex: 1; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        
        <header class="navbar" style="height: var(--navbar-height); background: white; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin:0; font-size: 20px; color: #1e293b;">Manajemen Pengeluaran</h2>
            <div style="color: #64748b;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></div>
        </header>

        <main class="main-content">
            <div class="data-section" style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0; font-size: 24px;">Daftar Pengeluaran</h1>
                        <p style="color: #64748b; margin-top: 5px;">Catatan biaya operasional kost</p>
                    </div>
                    <button class="btn-add" style="background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;" onclick="document.getElementById('modalPengeluaran').style.display='flex'">
                        <i class="fas fa-plus"></i> Catat Pengeluaran
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                <th style="padding: 15px; color: #64748b;">Tanggal</th>
                                <th style="padding: 15px; color: #64748b;">Nama Pengeluaran</th>
                                <th style="padding: 15px; color: #64748b;">Kategori</th>
                                <th style="padding: 15px; color: #64748b;">Jumlah</th>
                                <th style="padding: 15px; color: #64748b;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 15px;"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                    <td style="padding: 15px;"><strong><?= htmlspecialchars($row['nama_pengeluaran']) ?></strong></td>
                                    <td style="padding: 15px;"><span class="badge status-terisi"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                    <td style="padding: 15px;"><strong><?= formatRupiah($row['jumlah']) ?></strong></td>
                                    <td style="padding: 15px;">
                                        <a href="pengeluaran.php?hapus=<?= $row['id_pengeluaran'] ?>" onclick="return confirm('Hapus catatan ini?')" style="color: #fb7185; text-decoration: none;">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada data pengeluaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>

    <div id="modalPengeluaran" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div class="modal-content" style="background:white; padding:25px; border-radius:12px; width:450px;">
            <h2 style="margin-top:0;">Catat Pengeluaran Baru</h2>
            <form action="pengeluaran.php" method="POST">
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Nama Pengeluaran</label>
                    <input type="text" name="nama_pengeluaran" style="width:100%; padding:8px;" required>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Kategori</label>
                    <select name="kategori" style="width:100%; padding:8px;">
                        <option value="Utilitas">Utilitas (Listrik/Air)</option>
                        <option value="Pemeliharaan">Pemeliharaan (AC/Bangunan)</option>
                        <option value="Kebersihan">Kebersihan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" style="width:100%; padding:8px;" required>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" style="width:100%; padding:8px;" required>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Keterangan</label>
                    <textarea name="keterangan" style="width:100%; padding:8px;"></textarea>
                </div>
                <div style="text-align:right;">
                    <button type="button" onclick="document.getElementById('modalPengeluaran').style.display='none'" style="padding:8px 15px;">Batal</button>
                    <button type="submit" name="tambah_pengeluaran" style="padding:8px 15px; background:#4f46e5; color:white; border:none; border-radius:5px; cursor:pointer;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>