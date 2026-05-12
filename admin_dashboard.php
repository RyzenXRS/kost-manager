<?php
session_start();
include 'koneksi.php'; 

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("location:login.php");
    exit();
}

// 2. DEFINISIKAN TAHUN TERLEBIH DAHULU (Menghindari error Undefined Variable)
$tahun_ini = date('Y');
$data_pemasukan = array_fill(0, 12, 0); 
$data_pengeluaran = array_fill(0, 12, 0);

// 3. AMBIL DATA STATISTIK UTAMA
$query_reminder = mysqli_query($conn, "SELECT penghuni.*, kamar.nomor_kamar FROM penghuni JOIN kamar ON penghuni.id_kamar = kamar.id_kamar WHERE penghuni.status_pembayaran = 'Belum Bayar'");
$total_kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar"))['total'];
$total_penghuni = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penghuni WHERE status_penyewa = 'Aktif'"))['total'];

// 4. AMBIL DATA PEMASUKAN UNTUK GRAFIK
$query_p = mysqli_query($conn, "SELECT MONTH(tgl_bayar) as bulan, SUM(jumlah_bayar) as total 
                                FROM pembayaran 
                                WHERE YEAR(tgl_bayar) = '$tahun_ini' 
                                GROUP BY MONTH(tgl_bayar)");
if($query_p) {
    while($row = mysqli_fetch_assoc($query_p)) {
        $data_pemasukan[$row['bulan'] - 1] = (int)$row['total'];
    }
}
$tahun_ini = date('Y'); // Tambahkan ini di bagian atas sebelum query
// 5. AMBIL DATA PENGELUARAN UNTUK GRAFIK (Dengan Error Handling)
$query_e = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(jumlah) as total 
                                FROM pengeluaran 
                                WHERE YEAR(tanggal) = '$tahun_ini' 
                                GROUP BY MONTH(tanggal)");

if ($query_e) {
    while($row = mysqli_fetch_assoc($query_e)) {
        $data_pengeluaran[$row['bulan'] - 1] = (int)$row['total'];
    }
} else {
    // Jika kolom/tabel belum ada di DB, grafik akan tetap muncul dengan angka 0 (Tanpa Fatal Error)
    $data_pengeluaran = array_fill(0, 12, 0);
}

// 6. LOGIKA SIMPAN DATA KAMAR
if (isset($_POST['simpan_kamar'])) {
    $nomor = mysqli_real_escape_string($conn, $_POST['nomor_kamar']);
    $tipe = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $harga = str_replace('.', '', $_POST['harga_per_bulan']);
    $query = "INSERT INTO kamar (nomor_kamar, tipe_kamar, harga_per_bulan, status) VALUES ('$nomor', '$tipe', '$harga', 'tersedia')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Kamar $nomor berhasil ditambah!'); window.location='admin_dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-width: 260px; --navbar-height: 70px; --primary-color: #4f46e5; }
        body { margin: 0; display: flex; background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .main-wrapper { flex: 1; margin-left: var(--sidebar-width); display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { padding: 30px; flex: 1; }
        .footer { padding: 20px 30px; background: white; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 14px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <header class="navbar" style="height: var(--navbar-height); background: white; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin:0; font-size: 20px;">Dashboard Admin</h2>
            <div style="color: #64748b;"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></div>
        </header>

        <main class="main-content">
            
            <div class="stats-container">
                <div class="card">
                    <i class="fas fa-door-open" style="background: #e0e7ff; color: #4338ca;"></i>
                    <h3>Total Kamar</h3>
                    <p><?= $total_kamar; ?></p>
                </div>
                <div class="card">
                    <i class="fas fa-user-check" style="background: #dcfce7; color: #16a34a;"></i>
                    <h3>Penyewa Aktif</h3>
                    <p><?= $total_penghuni; ?></p>
                </div>
                <div class="card">
                    <i class="fas fa-clock" style="background: #fef3c7; color: #d97706;"></i>
                    <h3>Pembayaran Pending</h3>
                    <p>0</p>
                </div>
                <div class="card">
                    <i class="fas fa-comment-dots" style="background: #fee2e2; color: #dc2626;"></i>
                    <h3>Laporan Baru</h3>
                    <p>0</p>
                </div>
            </div>

            <div class="data-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-bell" style="color: #f59e0b; font-size: 20px;"></i>
                        <h2 style="margin:0;">Pengingat Pembayaran</h2>
                    </div>
                    <span class="badge" style="background: #fef3c7; color: #d97706; font-weight: 700; padding: 5px 10px; border-radius: 5px;">
                        <?= mysqli_num_rows($query_reminder); ?> Perlu Ditagih
                    </span>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Penghuni</th>
                                <th>No. Kamar</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query_reminder) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($query_reminder)): ?>
                                <tr>
                                    <td>
                                        <strong><?= $row['nama_lengkap']; ?></strong><br>
                                        <small style="color: #94a3b8;"><?= $row['no_hp']; ?></small>
                                    </td>
                                    <td><span class="badge" style="background:#e0e7ff; color:#4338ca;">Kamar <?= $row['nomor_kamar']; ?></span></td>
                                    <td>Tiap Tgl <?= date('d', strtotime($row['tgl_masuk'])); ?></td>
                                    <td><span class="badge" style="background: #fee2e2; color: #dc2626;">Belum Bayar</span></td>
                                    <td>
                                        <a href="https://wa.me/<?= $row['no_hp']; ?>?text=Halo%20<?= urlencode($row['nama_lengkap']); ?>" target="_blank" class="btn-add" style="background: #22c55e; padding: 5px 10px; font-size: 12px; text-decoration: none; border-radius: 5px; color: white;">
                                            <i class="fab fa-whatsapp"></i> Tagih
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;">Semua lunas bulan ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="data-section" style="margin-top: 30px;">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Arus Kas (Cashflow) <?= $tahun_ini ?></h2>
                <div style="height: 300px;">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </div>

        </main>

        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
        const ctx = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: <?= json_encode($data_pemasukan); ?>,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?= json_encode($data_pengeluaran); ?>,
                        borderColor: '#fb7185',
                        backgroundColor: 'rgba(251, 113, 133, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'Rp ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>