<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/audio_stego_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=Anda harus login');
    exit;
}

// Validasi input
if (empty($_POST['stego_message_audio']) || empty($_POST['receiver_id']) || !isset($_FILES['cover_audio']) || $_FILES['cover_audio']['error'] != 0) {
    header('Location: ../views/dashboard.php?error=File Audio .WAV, pesan rahasia, dan penerima tidak boleh kosong');
    exit;
}

// Pastikan file adalah .WAV
$file = $_FILES['cover_audio'];
$file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_type !== 'wav') {
    header('Location: ../views/dashboard.php?error=File cover HARUS berformat .WAV');
    exit;
}

$message = $_POST['stego_message_audio'];
$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

$original_name = $file['name'];
$file_tmp_path = $file['tmp_name'];

// Buat folder uploads/audio jika belum ada
$upload_dir = '../uploads/audio/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Tentukan path output
$output_name = 'stego_' . time() . '_' . $original_name;
$output_path = $upload_dir . $output_name;

// Panggil helper
$success = hide_message_in_wav($file_tmp_path, $message, $output_path);

if ($success) {
    // Hapus '..' dari path untuk disimpan di DB
    $db_path = '/skap-pemerintah/uploads/audio/' . $output_name; 
    
    $stmt = $db->prepare("INSERT INTO stego_audio (sender_id, receiver_id, audio_name, audio_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $original_name, $db_path]);
    
    header('Location: ../views/dashboard.php?status=Pesan berhasil disembunyikan di file audio');
} else {
    header('Location: ../views/dashboard.php?error=Gagal menyembunyikan pesan. Mungkin pesan terlalu panjang untuk file audio ini.');
}
?>