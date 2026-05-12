<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    // Ambil data dan bersihkan
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role     = 'penyewa'; // Nilai ini yang menyebabkan error jika ENUM di DB tidak sesuai

    // 1. Cek apakah username atau email sudah terdaftar
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' OR email = '$email'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username atau Email sudah terdaftar!'); window.history.back();</script>";
    } else {
        // 2. Query Insert - Gunakan backtick (`) untuk nama tabel/kolom agar lebih aman
        $query = "INSERT INTO `users` (`nama_lengkap`, `email`, `username`, `password`, `role`) 
                  VALUES ('$nama', '$email', '$username', '$password', '$role')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            // Jika masih error, ini akan menampilkan pesan error yang jelas
            echo "<div style='color:red; background:white; padding:10px;'>
                    <b>Gagal Registrasi:</b> " . mysqli_error($conn) . "
                  </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - KostManager</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reg-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); width: 100%; max-width: 400px; border: 1px solid #e2e8f0; }
        h2 { margin-top: 0; color: #1e293b; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #64748b; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; }
        .btn-reg { width: 100%; padding: 12px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-reg:hover { background: #4338ca; }
    </style>
</head>
<body>

<div class="reg-card">
    <h2>Buat Akun Baru</h2>
    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="register" class="btn-reg">Daftar Sekarang</button>
        <p style="text-align:center; font-size:14px; color:#64748b; margin-top:20px;">
            Sudah punya akun? <a href="login.php" style="color:#4f46e5; text-decoration:none;">Login</a>
        </p>
    </form>
</div>

</body>
</html>