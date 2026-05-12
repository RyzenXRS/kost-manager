<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// Cek jika tombol Simpan Perubahan diklik
if (isset($_POST['simpan_edit'])) {
    $id    = $_POST['id_penghuni'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nik   = mysqli_real_escape_string($conn, $_POST['nik']);
    $kamar = mysqli_real_escape_string($conn, $_POST['id_kamar']);

    // Query Update
    $sql = "UPDATE PENGHUNI SET 
            nama_lengkap = '$nama', 
            no_hp = '$hp', 
            email = '$email', 
            nik = '$nik', 
            id_kamar = '$kamar' 
            WHERE id_penghuni = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Perubahan Berhasil Disimpan!'); window.location='penghuni.php';</script>";
    } else {
        echo "<script>alert('Gagal Update: " . mysqli_error($koneksi) . "');</script>";
    }
}

// --- 1. LOGIKA UBAH STATUS JADI SELESAI ---
if (isset($_GET['set_selesai'])) {
    $id_p = mysqli_real_escape_string($conn, $_GET['set_selesai']);

    // Ambil ID Kamar yang dipakai penghuni ini sebelum statusnya diubah
    $query_kamar = mysqli_query($conn, "SELECT id_kamar FROM penghuni WHERE id_penghuni = '$id_p'");
    $data_p = mysqli_fetch_assoc($query_kamar);
    $id_kamar = $data_p['id_kamar'];

    // Update status penghuni jadi 'Selesai'
    mysqli_query($conn, "UPDATE penghuni SET status_penyewa = 'Selesai' WHERE id_penghuni = '$id_p'");
    
    // OTOMATIS: Update status kamar tersebut jadi 'tersedia' kembali
    mysqli_query($conn, "UPDATE kamar SET status = 'tersedia' WHERE id_kamar = '$id_kamar'");

    echo "<script>alert('Penyewa telah selesai. Kamar kini tersedia kembali!'); window.location='penghuni.php';</script>";
}

// --- 2. FILTER STATUS (Default: Aktif) ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'Aktif';

// Query Ambil Data
$sql = "SELECT penghuni.*, kamar.nomor_kamar 
        FROM penghuni 
        LEFT JOIN kamar ON penghuni.id_kamar = kamar.id_kamar 
        WHERE penghuni.status_penyewa = '$filter'";
$result = mysqli_query($conn, $sql);

// --- 3. TAMBAH PENYEWA 
// Bungkus dengan IF ISSSET agar tidak error "Undefined array key" saat halaman dibuka
if (isset($_POST['tambah_penghuni'])) {
    
    // Gunakan variabel conn yang ada di file conn.php kamu
    // Jika di conn.php kamu pakai $conn, ganti $conn jadi $conn
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nik   = mysqli_real_escape_string($conn, $_POST['nik']);
    $hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);

    $query = "INSERT INTO PENGHUNI (nama_lengkap, nik, no_hp, email, tgl_masuk) 
              VALUES ('$nama', '$nik', '$hp', '$email', '$tanggal')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Berhasil Disimpan!'); window.location='penghuni.php';</script>";
        exit; // Hentikan script setelah redirect
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
// Query untuk menggabungkan tabel PENGHUNI dan KAMAR
$query_penghuni = "SELECT penghuni.*, KAMAR.nomor_kamar 
                   FROM penghuni 
                   LEFT JOIN KAMAR ON PENGHUNI.id_kamar = KAMAR.id_kamar 
                   ORDER BY PENGHUNI.nama_lengkap ASC";

$tampil = mysqli_query($conn, $query_penghuni);

if (isset($_POST['update_penghuni'])) {
    $id    = $_POST['id_penghuni'];
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $kamar = mysqli_real_escape_string($koneksi, $_POST['id_kamar']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);

    // Update data di database
    $sql = "UPDATE PENGHUNI SET 
            nama_lengkap = '$nama', 
            no_hp = '$hp', 
            id_kamar = '$kamar' 
            WHERE id_penghuni = '$id'";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Perubahan berhasil disimpan!'); window.location='penghuni.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penghuni - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Layout CSS */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --primary-bg: #1e293b;
        }

        body {
            margin: 0;
            display: flex;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Tetap */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-bg);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
        }

        /* Wrapper Konten */
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

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; }
        
        .badge-aktif { background: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
        .badge-selesai { background: #f1f5f9; color: #64748b; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
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
            <div style="display: flex; align-items: center; gap: 10px; padding: 0 20px 10px;">
                <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                <span style="font-size: 14px;">Admin</span>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-wrapper">
        
        <header class="header">
            <div class="header-left">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">Manajemen Penyewa</h2>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <div class="date" style="color: #64748b; font-size: 14px;">
                    <i class="far fa-calendar-alt"></i> <?= date('d M Y') ?>
                </div>
                <div style="width: 1px; height: 20px; background: #e2e8f0;"></div>
                <div style="font-weight: 600; font-size: 14px; color: #1e293b;">Super Admin</div>
            </div>
        </header>

        <main class="main-content">
            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h1 style="margin: 0;">Daftar Penghuni</h1>
                        <p style="color: #64748b; margin-top: 5px;">Total: <?= mysqli_num_rows($result); ?> orang (Status: <?= $filter ?>)</p>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <select onchange="location.href='penghuni.php?filter=' + this.value" style="padding: 10px; border-radius: 10px; border: 1px solid #ddd; cursor:pointer; background: white;">
                            <option value="Aktif" <?= $filter == 'Aktif' ? 'selected' : '' ?>>Penyewa Aktif</option>
                            <option value="Selesai" <?= $filter == 'Selesai' ? 'selected' : '' ?>>Sudah Keluar</option>
                        </select>
                        <button type="button" 
        class="btn btn-primary" 
        onclick="document.getElementById('modalTambahPenyewa').style.display='flex'">
    <i class="fas fa-plus"></i> Tambah Penyewa
</button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kamar</th>
                                <th>No. HP</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong><?= $row['nama_lengkap'] ?></strong></td>
                                    <td>
                                        <?php if($row['nomor_kamar']): ?>
                                            <span class="badge status-terisi">Kamar <?= $row['nomor_kamar'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light-danger text-danger">
            <?= $row['nomor_kamar'] ?? 'Belum Pilih Kamar'; ?>
        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['no_hp'] ?></td>
                                    <td>
    <?= ($row['tgl_masuk'] && $row['tgl_masuk'] != '0000-00-00') 
        ? date('d M Y', strtotime($row['tgl_masuk'])) 
        : '<span style="color:red;">Belum diisi</span>'; 
    ?>
</td>
                                    <td>
                                        <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                            <span class="badge-aktif">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge-selesai">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 12px; align-items: center;">
                                            <?php if($row['status_penyewa'] == 'Aktif'): ?>
                                                <a href="penghuni.php?set_selesai=<?= $row['id_penghuni'] ?>" 
                                                   onclick="return confirm('Yakin penyewa ini sudah selesai? Kamar akan otomatis kosong.')"
                                                   style="color: #4f46e5; font-size: 11px; font-weight: 700; text-decoration:none; border: 1px solid #4f46e5; padding: 5px 10px; border-radius: 6px; text-transform: uppercase;">
                                                   Checkout
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn-edit" 
    onclick="bukaModalEdit('<?= $row['id_penghuni'] ?>', '<?= $row['nama_lengkap'] ?>', '<?= $row['no_hp'] ?>', '<?= $row['email'] ?>', '<?= $row['nik'] ?>', '<?= $row['id_kamar'] ?>')">
    <i class="fas fa-pencil-alt" style="color: #7191fb; cursor:pointer;" title="Edit"></i>
</button>
                                            <i class="fas fa-trash" style="color: #fb7185; cursor:pointer;" title="Hapus"></i>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center; padding:40px; color:#94a3b8;">Tidak ada data penyewa dengan status <strong><?= $filter ?></strong>.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <div id="modalTambahPenyewa" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:500px; max-width:90%; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
        <h4 style="margin-top:0;">Tambah Data Penyewa</h4>
        <hr>
        <form action="penghuni.php" method="POST">
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nama Lengkap:</label>
        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" style="width: 100%; padding: 8px;" required>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">NIK (No. KTP):</label>
        <input type="text" name="nik" placeholder="Masukkan 16 digit NIK" style="width: 100%; padding: 8px;" required>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">No. HP / WhatsApp:</label>
        <input type="text" name="no_hp" placeholder="Contoh: 08123456789" style="width: 100%; padding: 8px;" required>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Email:</label>
        <input type="email" name="email" placeholder="alamat@email.com" style="width: 100%; padding: 8px;" required>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: bold; margin-bottom: 5px;">Tanggal Masuk:</label>
        <input type="date" name="tgl_masuk" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
    </div>
    
    <button type="submit" name="tambah_penghuni" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer;">
        Simpan Penghuni
    </button>
</form>
    </div>
</div>

<div id="modalEditPenghuni" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:450px; max-width:95%; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0;">Edit Data Penghuni</h3>
        <hr>
        <form action="penghuni.php" method="POST">
            <input type="hidden" id="edit_id" name="id_penghuni">

            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Nama Lengkap</label>
                <input type="text" id="edit_nama" name="nama_lengkap" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Nomor Telepon</label>
                <input type="text" id="edit_hp" name="no_hp" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Alamat Email</label>
                <input type="email" id="edit_email" name="email" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">NIK</label>
                <input type="text" id="edit_nik" name="nik" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>

            <div style="margin-bottom:15px;">
    <label style="display:block; font-weight:bold; margin-bottom:5px;">Pilih Kamar</label>
    <select id="edit_kamar" name="id_kamar" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" required>
        <option value="">-- Pilih Kamar --</option>
        <?php
        // Pastikan variabel $koneksi sudah didefinisikan di atas
        $kamar_query = mysqli_query($conn, "SELECT id_kamar, nomor_kamar FROM kamar ORDER BY nomor_kamar ASC");
        
        if (!$kamar_query) {
            // Jika query error, akan muncul pesan di dalam dropdown
            echo "<option value=''>Error: " . mysqli_error($koneksi) . "</option>";
        } else {
            if (mysqli_num_rows($kamar_query) > 0) {
                while($k = mysqli_fetch_assoc($kamar_query)) {
                    echo "<option value='".$k['id_kamar']."'>".$k['nomor_kamar']."</option>";
                }
            } else {
                echo "<option value=''>Tidak ada data kamar di database</option>";
            }
        }
        ?>
    </select>
</div>

            <hr>
            <div style="text-align:right;">
                <button type="button" onclick="document.getElementById('modalEditPenghuni').style.display='none'" style="padding:8px 15px; border:none; background:#eee; cursor:pointer; border-radius:4px;">Batal</button>
                <button type="submit" name="simpan_edit" style="padding:8px 15px; border:none; background:#007bff; color:white; cursor:pointer; border-radius:4px; font-weight:bold;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalEdit(id, nama, hp, email, nik, kamar) {
    // 1. Masukkan data ke masing-masing input berdasarkan ID-nya
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_hp').value = hp;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_nik').value = nik;
    document.getElementById('edit_kamar').value = kamar;

    // 2. Baru tampilkan modalnya
    document.getElementById('modalEditPenghuni').style.display = 'flex';
}
</script>

        <footer class="footer">
            &copy; <?= date('Y'); ?> <strong>KostManager</strong>. Sistem Manajemen Kost.
        </footer>
    </div>

</body>
</html