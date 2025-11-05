<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/stego_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=Anda harus login');
    exit;
}

// --- PERUBAHAN LOGIKA START ---
if (empty($_POST['receiver_id'])) {
    header('Location: ../views/dashboard.php?error=Penerima gambar tidak boleh kosong');
    exit;
}
if (empty($_POST['stego_message'])) {
     header('Location: ../views/dashboard.php?error=Pesan rahasia tidak boleh kosong');
    exit;
}
if (!isset($_FILES['cover_image']) || empty($_FILES['cover_image']['name'])) {
    header('Location: ../views/dashboard.php?error=Tidak ada gambar PNG yang dipilih');
    exit;
}
$file = $_FILES['cover_image'];

// Cek error upload (pakai angka 0)
if ($file['error'] !== 0) {
    header('Location: ../views/dashboard.php?error=Terjadi error saat upload gambar. Kode error: ' . $file['error']);
    exit;
}

$upload_dir = '../uploads/images/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
         header('Location: ../views/dashboard.php?error=Folder tujuan (uploads/images) tidak ada dan tidak bisa dibuat.');
         exit;
    }
}
if (!is_writable($upload_dir)) {
    header('Location: ../views/dashboard.php?error=Folder tujuan (uploads/images) tidak bisa ditulis. Cek permissions!');
    exit;
}

$file_tmp_path = $file['tmp_name'];
$image_type = @exif_imagetype($file_tmp_path);
if ($image_type !== IMAGETYPE_PNG) {
    header('Location: ../views/dashboard.php?error=File cover HARUS berformat .PNG. File yang diupload bukan PNG.');
    exit;
}
// --- PERUBAHAN LOGIKA END ---

$message = $_POST['stego_message'];
$original_name = $file['name'];

// --- UBAH DARI user_id MENJADI sender_id dan receiver_id ---
$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

$output_name = 'stego_' . time() . '_' . $original_name;
$output_path = $upload_dir . $output_name;

try {
     $success = hide_message_in_image($file_tmp_path, $message, $output_path);
} catch (Exception $e) {
    header('Location: ../views/dashboard.php?error=Error saat proses stego: ' . $e->getMessage());
    exit;
}

if ($success) {
    $db_path = '/uploads/images/' . $output_name; 
    
    // --- PERBARUI QUERY INSERT ---
    $stmt = $db->prepare("INSERT INTO stego_images (sender_id, receiver_id, image_name, image_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $original_name, $db_path]);
    
    header('Location: ../views/dashboard.php?status=Pesan berhasil disembunyikan dan dikirim');
} else {
    header('Location: ../views/dashboard.php?error=Gagal menyembunyikan pesan. Mungkin pesan terlalu panjang.');
}
?>