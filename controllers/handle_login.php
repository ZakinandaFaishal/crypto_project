<?php
session_start();
require_once '../config/db.php';

if (empty($_POST['username']) || empty($_POST['password'])) {
    header('Location: ../index.php?error=Username dan password tidak boleh kosong');
    exit;
}

$username = $_POST['username'];
$password_plain = $_POST['password'];

// 1. Ambil data user dari DB berdasarkan username
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../index.php?error=Username atau password salah');
    exit;
}

// 2. Verifikasi password (KRITERIA SHA-256)
// Hash password yang diinput user DENGAN SALT yang tersimpan di DB
$check_hash = hash('sha256', $user['salt'] . $password_plain);

// 3. Bandingkan hash
if ($check_hash === $user['password_hash']) {
    // Login berhasil
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header('Location: ../views/dashboard.php');
} else {
    // Login gagal
    header('Location: ../index.php?error=Username atau password salah');
}
?>