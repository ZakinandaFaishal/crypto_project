<?php
require_once '../config/db.php';

// Validasi input dasar
if (empty($_POST['username']) || empty($_POST['password'])) {
    header('Location: ../register.php?error=Username dan password tidak boleh kosong');
    exit;
}

$username = $_POST['username'];
$password_plain = $_POST['password'];

// Cek jika username sudah ada
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    header('Location: ../register.php?error=Username sudah digunakan');
    exit;
}

// KRITERIA LOGIN: SHA-256
// Peringatan: Ini BUKAN standar industri. Standar industri adalah password_hash() (Bcrypt).
// Tapi sesuai permintaan tugas, kita gunakan SHA-256 dengan salt manual.

// 1. Buat salt acak
$salt = bin2hex(random_bytes(16)); // 16 bytes = 32 karakter hex

// 2. Hash password dengan salt (salt + password)
$password_hash = hash('sha256', $salt . $password_plain);

// 3. Simpan user, hash, dan salt ke DB
try {
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, salt) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password_hash, $salt]);
    
    header('Location: ../index.php?success=Registrasi berhasil. Silakan login.');
} catch (PDOException $e) {
    header('Location: ../register.php?error=Terjadi kesalahan database: ' . $e->getMessage());
}
?>