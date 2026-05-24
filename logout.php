<?php
// =============================================
// logout.php - Menghapus session dan kembali ke login
// =============================================
session_start();
session_destroy(); // hapus semua data session
header('Location: login.php');
exit;
