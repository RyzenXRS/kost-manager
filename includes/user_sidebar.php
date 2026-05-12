<div class="sidebar">
    <div>
        <div class="logo"><i class="fas fa-building"></i> KostManager</div>
        <ul class="nav-links">
            <li onclick="window.location='user_dashboard.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </li>
            <li onclick="window.location='kamar_saya.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'kamar_saya.php' ? 'active' : '' ?>">
                <i class="fas fa-bed"></i> Kamar Saya
            </li>
            <li onclick="window.location='teman_kost.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'teman_kost.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Teman Kost
            </li>
            <li onclick="window.location='user_pembayaran.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'user_pembayaran.php' ? 'active' : '' ?>">
                <i class="fas fa-wallet"></i> Pembayaran
            </li>
            <li onclick="window.location='user_tagihan.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'user_tagihan.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Tagihan
            </li>
            <li onclick="window.location='user_riwayat.php'" class="<?= basename($_SERVER['PHP_SELF']) == 'user_riwayat.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Riwayat
            </li>
        </ul>
    </div>
    <div class="user-profile">
        <div style="display: flex; align-items: center; gap: 10px; padding: 0 20px 10px 20px;">
            <i class="fas fa-user-circle" style="font-size: 24px;"></i>
            <span style="font-size: 14px; color: #cbd5e1;">Penyewa</span>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>