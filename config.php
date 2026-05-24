<?php
// =============================================
// config.php - Koneksi database & session
// =============================================

session_start();

// Koneksi ke database MySQL
$host = 'localhost';
$db   = 'absenhub';
$user = 'root';
$pass = 'root';

try {
    // PDO digunakan agar lebih aman (mencegah SQL Injection)
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// =============================================
// CLASS: Database (Encapsulation koneksi)
// =============================================
// Jawaban soal: Class Database membungkus (encapsulate)
// koneksi PDO agar tidak diakses langsung dari luar.
class Database {
    private $pdo; // property private = encapsulation

    public function __construct($pdo) {
        $this->pdo = $pdo; // constructor: inisialisasi koneksi
    }

    // Getter untuk mengambil koneksi (getter method)
    public function getConnection() {
        return $this->pdo;
    }
}

// =============================================
// CLASS: Pegawai
// =============================================
// Merepresentasikan data pegawai dalam sistem
class Pegawai {
    // Property (encapsulation: private)
    private $id;
    private $nama;
    private $username;
    private $pdo;

    // Constructor: dipanggil saat object Pegawai dibuat
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Method login: cek username & password ke database
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM pegawai WHERE username=? AND password=?");
        $stmt->execute([$username, $password]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Simpan data ke session jika login berhasil
            $_SESSION['pegawai_id']   = $data['id'];
            $_SESSION['pegawai_nama'] = $data['nama'];
            return true;
        }
        return false;
    }

    // Getter untuk id (method getter)
    public function getId()   { return $this->id; }

    // Getter untuk nama (method getter)
    public function getNama() { return $this->nama; }
}

// =============================================
// CLASS: Absensi
// =============================================
// Merepresentasikan data absensi pegawai
class Absensi {
    private $pdo;           // property private = encapsulation
    private $pegawaiId;     // id pegawai yang sedang login
    private $jamMasukBatas  = '07:00:00'; // batas jam masuk
    private $jamPulangBatas = '17:00:00'; // batas jam pulang

    // Constructor: inisialisasi saat object Absensi dibuat
    public function __construct($pdo, $pegawaiId) {
        $this->pdo       = $pdo;
        $this->pegawaiId = $pegawaiId;
    }

    // Method absen masuk
    public function absenMasuk() {
        $today = date('Y-m-d');

        // Cek apakah sudah absen masuk hari ini
        $cek = $this->pdo->prepare("SELECT id FROM absensi WHERE pegawai_id=? AND tanggal=?");
        $cek->execute([$this->pegawaiId, $today]);
        if ($cek->fetch()) return ['status' => 'sudah', 'pesan' => 'Anda sudah absen masuk hari ini.'];

        // Waktu simulasi: jika Gatot (id=1) pakai 06:55, Iwan (id=2) pakai 08:30
        $jamSekarang = ($this->pegawaiId == 1) ? '06:55:00' : '08:30:00';

        // Tentukan status: terlambat jika lewat jam 07:00
        $status = ($jamSekarang > $this->jamMasukBatas) ? 'Terlambat' : 'Tepat Waktu';

        // Simpan absen masuk ke database
        $stmt = $this->pdo->prepare("INSERT INTO absensi (pegawai_id, tanggal, jam_masuk, status) VALUES (?,?,?,?)");
        $stmt->execute([$this->pegawaiId, $today, $jamSekarang, $status]);

        return ['status' => $status, 'jam' => $jamSekarang];
    }

    // Method absen pulang
    public function absenPulang() {
        $today = date('Y-m-d');

        // Waktu simulasi: Gatot pulang 17:05, Iwan pulang 15:30
        $jamSekarang = ($this->pegawaiId == 1) ? '17:05:00' : '15:30:00';

        // Cek apakah belum waktunya pulang (sebelum 17:00)
        if ($jamSekarang < $this->jamPulangBatas) {
            return ['status' => 'belum', 'pesan' => 'Belum waktunya pulang kerja.'];
        }

        // Cek apakah sudah absen masuk hari ini
        $cek = $this->pdo->prepare("SELECT id FROM absensi WHERE pegawai_id=? AND tanggal=? AND jam_masuk IS NOT NULL");
        $cek->execute([$this->pegawaiId, $today]);
        $baris = $cek->fetch();
        if (!$baris) return ['status' => 'error', 'pesan' => 'Anda belum absen masuk hari ini.'];

        // Simpan jam pulang ke database
        $update = $this->pdo->prepare("UPDATE absensi SET jam_pulang=? WHERE pegawai_id=? AND tanggal=?");
        $update->execute([$jamSekarang, $this->pegawaiId, $today]);

        return ['status' => 'berhasil', 'jam' => $jamSekarang];
    }

    // Method ambil riwayat absensi pegawai yang login
    public function getRiwayat() {
        $stmt = $this->pdo->prepare(
            "SELECT a.id, p.nama, a.tanggal, a.jam_masuk, a.jam_pulang, a.status
             FROM absensi a JOIN pegawai p ON a.pegawai_id = p.id
             WHERE a.pegawai_id = ?
             ORDER BY a.tanggal DESC"
        );
        $stmt->execute([$this->pegawaiId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Getter jam masuk batas (getter method)
    public function getJamMasukBatas() { return $this->jamMasukBatas; }
}
