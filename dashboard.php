<?php
// =============================================
// dashboard.php - Halaman Utama Pegawai
// =============================================
require_once 'config.php';

// Proteksi: jika belum login, kembali ke login
if (!isset($_SESSION['pegawai_id'])) {
    header('Location: login.php');
    exit;
}

$pegawaiId   = $_SESSION['pegawai_id'];
$pegawaiNama = $_SESSION['pegawai_nama'];

// Buat object Absensi dengan dependency injection (OOP)
$absensi = new Absensi($pdo, $pegawaiId);

$pesan = '';      // pesan popup yang akan ditampilkan
$tipePesan = '';  // tipe: sukses / warning / error

// =============================================
// PROSES ABSEN MASUK
// =============================================
if (isset($_POST['absen_masuk'])) {
    $hasil = $absensi->absenMasuk(); // panggil method dari class Absensi

    if ($hasil['status'] === 'Terlambat') {
        $pesan = "⚠️ ANDA TERLAMBAT MASUK KERJA! Jam masuk: " . $hasil['jam'];
        $tipePesan = 'warning';
    } elseif ($hasil['status'] === 'Tepat Waktu') {
        $pesan = "✅ Absensi masuk berhasil! Jam masuk: " . $hasil['jam'];
        $tipePesan = 'sukses';
    } else {
        $pesan = "ℹ️ " . $hasil['pesan'];
        $tipePesan = 'info';
    }
}

// =============================================
// PROSES ABSEN PULANG
// =============================================
if (isset($_POST['absen_pulang'])) {
    $hasil = $absensi->absenPulang(); // panggil method dari class Absensi

    if ($hasil['status'] === 'belum') {
        $pesan = "🕐 Belum waktunya pulang kerja! (min. jam 17:00)";
        $tipePesan = 'warning';
    } elseif ($hasil['status'] === 'berhasil') {
        $pesan = "✅ Absensi pulang berhasil! Jam pulang: " . $hasil['jam'];
        $tipePesan = 'sukses';
    } else {
        $pesan = "⚠️ " . $hasil['pesan'];
        $tipePesan = 'error';
    }
}

// Ambil riwayat absensi menggunakan method getRiwayat()
$riwayat = $absensi->getRiwayat();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AbsenHub - Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; }

        /* Navbar */
        .navbar { background: #2c3e50; color: white; padding: 14px 25px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .navbar .user { font-size: 14px; }
        .btn-logout { background: #e74c3c; color: white; border: none; padding: 7px 14px; border-radius: 5px; cursor: pointer; font-size: 13px; text-decoration: none; }

        /* Container */
        .container { max-width: 900px; margin: 30px auto; padding: 0 15px; }

        /* Card */
        .card { background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card h2 { margin: 0 0 20px; color: #2c3e50; font-size: 17px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }

        /* Tombol absen */
        .btn-row { display: flex; gap: 15px; }
        .btn-masuk  { background: #27ae60; color: white; border: none; padding: 14px 30px; border-radius: 8px; font-size: 15px; cursor: pointer; flex: 1; }
        .btn-pulang { background: #e67e22; color: white; border: none; padding: 14px 30px; border-radius: 8px; font-size: 15px; cursor: pointer; flex: 1; }
        .btn-masuk:hover  { background: #229954; }
        .btn-pulang:hover { background: #d35400; }

        /* Popup overlay */
        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; justify-content: center; align-items: center; }
        .overlay.show { display: flex; }
        .popup { background: white; border-radius: 12px; padding: 30px; text-align: center; max-width: 380px; width: 90%; }
        .popup.sukses  .icon { font-size: 50px; }
        .popup.warning .icon { font-size: 50px; }
        .popup h3 { margin: 15px 0 10px; color: #2c3e50; }
        .popup p  { color: #555; font-size: 14px; margin-bottom: 20px; }
        .popup button { background: #2980b9; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; font-size: 14px; }

        /* Tabel riwayat */
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #2c3e50; color: white; padding: 10px 12px; text-align: left; }
        td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        tr:hover td { background: #f8f9fa; }
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge.tepat     { background: #d5f5e3; color: #1e8449; }
        .badge.terlambat { background: #fde8d8; color: #c0392b; }
        .empty { text-align: center; color: #aaa; padding: 20px; }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h1>🏢 AbsenHub</h1>
    <div class="user">
        👤 <?= htmlspecialchars($pegawaiNama) ?>
        &nbsp;|&nbsp;
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<!-- Popup Notifikasi -->
<?php if ($pesan): ?>
<div class="overlay show" id="popup">
    <div class="popup <?= $tipePesan ?>">
        <div class="icon">
            <?= $tipePesan === 'sukses' ? '✅' : ($tipePesan === 'warning' ? '⚠️' : 'ℹ️') ?>
        </div>
        <h3><?= $tipePesan === 'warning' ? 'Perhatian!' : 'Informasi' ?></h3>
        <p><?= htmlspecialchars($pesan) ?></p>
        <button onclick="document.getElementById('popup').classList.remove('show')">Tutup</button>
    </div>
</div>
<?php endif; ?>

<div class="container">

    <!-- Kartu Absensi -->
    <div class="card">
        <h2>📋 Absensi Hari Ini</h2>
        <div class="btn-row">
            <!-- Tombol ABSEN MASUK -->
            <form method="POST" style="flex:1">
                <button type="submit" name="absen_masuk" class="btn-masuk">🟢 ABSEN MASUK</button>
            </form>
            <!-- Tombol ABSEN PULANG -->
            <form method="POST" style="flex:1">
                <button type="submit" name="absen_pulang" class="btn-pulang">🔴 ABSEN PULANG</button>
            </form>
        </div>
    </div>

    <!-- Kartu Riwayat Absensi -->
    <div class="card">
        <h2>📅 Riwayat Absensi Saya</h2>
        <!-- Tabel riwayat sesuai permintaan (JTable = tabel HTML dalam konteks web) -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pegawai</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="6" class="empty">Belum ada data absensi.</td></tr>
                <?php else: ?>
                    <?php foreach ($riwayat as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= $row['tanggal'] ?></td>
                        <td><?= $row['jam_masuk']  ?? '-' ?></td>
                        <td><?= $row['jam_pulang'] ?? '-' ?></td>
                        <!-- Badge status: Tepat Waktu hijau, Terlambat merah -->
                        <td>
                            <span class="badge <?= $row['status'] === 'Tepat Waktu' ? 'tepat' : 'terlambat' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
