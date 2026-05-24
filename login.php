<?php
// =============================================
// login.php - Halaman Login AbsenHub
// =============================================
require_once 'config.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['pegawai_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Proses form login saat tombol Login ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database($pdo);                        // Buat object Database
    $pegawai = new Pegawai($db->getConnection());    // Buat object Pegawai

    // Panggil method login dari class Pegawai
    if ($pegawai->login($_POST['username'], $_POST['password'])) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AbsenHub - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 5px; }
        p.sub { text-align: center; color: #7f8c8d; font-size: 13px; margin-bottom: 25px; }
        label { display: block; font-size: 14px; color: #555; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
        button { width: 100%; padding: 11px; background: #2980b9; color: white; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #2471a3; }
        .error { background: #fde8e8; color: #c0392b; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .hint { background: #eaf4fb; padding: 12px; border-radius: 6px; font-size: 12px; color: #555; margin-top: 15px; }
    </style>
</head>
<body>
<div class="card">
    <h2>🏢 AbsenHub</h2>
    <p class="sub">Sistem Absensi Pegawai</p>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Form login -->
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
        <button type="submit">Login</button>
    </form>

    <!-- Hint akun untuk demo/presentasi -->
    <div class="hint">
        <strong>Akun Demo:</strong><br>
        👤 gatot / gatot123 (Tepat Waktu)<br>
        👤 iwan / iwan123 (Terlambat)
    </div>
</div>
</body>
</html>
