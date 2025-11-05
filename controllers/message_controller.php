<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/crypto_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=Anda harus login');
    exit;
}

if (empty($_POST['receiver_id']) || empty($_POST['pesan_teks'])) {
    header('Location: ../views/dashboard.php?error=Penerima dan pesan tidak boleh kosong');
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$plaintext = $_POST['pesan_teks'];

// KRITERIA SUPER ENKRIPSI
$encrypted_message = super_encrypt($plaintext);

// Simpan ke DB
try {
    $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, encrypted_message) VALUES (?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $encrypted_message]);
    header('Location: ../views/dashboard.php?status=Pesan terenkripsi berhasil dikirim');
} catch (PDOException $e) {
    header('Location: ../views/dashboard.php?error=Gagal mengirim pesan: ' . $e->getMessage());
}
?>