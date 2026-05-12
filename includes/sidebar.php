<div class="sidebar">
    <div>
        <div class="logo"><i class="fas fa-building"></i> KostManager</div>
        <ul class="nav-links">
            <li onclick="window.location='admin_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</li>
            <li onclick="window.location='kamar.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'kamar.php' ? 'active' : '' ?>"><i class="fas fa-bed"></i> Kamar</li>
            <li onclick="window.location='penghuni.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'penghuni.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Penyewa</li>
            <li onclick="window.location='pembayaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pembayaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pembayaran</li>
            <li onclick="window.location='tagihan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'tagihan.php' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Tagihan</li>
            <li onclick="window.location='pengeluaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>"><i class="fas fa-wallet"></i> Pengeluaran</li>
            <li onclick="window.location='laporan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Laporan</li>
            <li><i class="fab fa-whatsapp"></i> WhatsApp</li>
        </ul>   
    </div>
    <div class="user-profile">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-user-circle" style="font-size: 30px;"></i>
            <div>
                <div style="font-size: 14px; font-weight: 600;">Super Admin</div>
                <div style="font-size: 12px; color: #cbd5e1;">Admin</div>
            </div>
        </div>
        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')" class="logout-btn" style="color: #ef4444; text-decoration: none; font-weight: 600; margin-top: 15px; display: block;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>