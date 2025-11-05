<?php
session_start();
require_once '../config/db.php';
require_once '../config/crypto_keys.php'; 

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = $_GET['id'];

// --- PERUBAHAN QUERY KEAMANAN ---
// Cek apakah user ini adalah PENERIMA file
$stmt = $db->prepare("SELECT * FROM secure_files WHERE id = ? AND receiver_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$file) {
    die("Error: File tidak ditemukan atau Anda tidak punya akses.");
}

// Path di DB adalah '/uploads/files/nama.enc'
// Path relatif dari file ini adalah '../uploads/files/nama.enc'
$server_file_path = '..' . $file['encrypted_file_path'];

if (!file_exists($server_file_path)) {
    die("Error: File fisik tidak ditemukan di server. Path: " . htmlspecialchars($server_file_path));
}

$ciphertext = file_get_contents($server_file_path);

if ($ciphertext === false) {
    die("Error: Gagal membaca file terenkripsi dari path.");
}

// Ambil IV
$iv = hex2bin($file['iv_hex']);

// DEKRIPSI FILE
$plaintext = openssl_decrypt(
    $ciphertext, 
    AES_METHOD, 
    AES_KEY, 
    OPENSSL_RAW_DATA, 
    $iv
);

if ($plaintext === false) {
    die("DEKRIPSI GAGAL. Kunci atau file korup.");
}

// Kirim file ke browser
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream'); 
header('Content-Disposition: attachment; filename="' . basename($file['original_file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($plaintext));
echo $plaintext;
exit;
?>