<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/audio_stego_helper.php'; // Helper stego .WAV Anda

if (!isset($_SESSION['user_id'])) {
    die('Akses ditolak'); // Hanya untuk AJAX
}

// 1. Validasi Input
if (empty($_POST['stego_message_audio']) || empty($_POST['receiver_id']) || !isset($_FILES['cover_audio_record'])) {
    // 'die' akan mengirim pesan error kembali ke JavaScript
    die('Error: Pesan rahasia, penerima, dan rekaman audio tidak boleh kosong.');
}

if ($_FILES['cover_audio_record']['error'] != 0) {
    die('Error: Gagal menerima rekaman audio dari browser.');
}

// 2. Tentukan Path
$upload_dir = '../uploads/audio/';
$temp_dir = '../uploads/temp/'; // Folder untuk konversi

if (!is_dir($temp_dir)) mkdir($temp_dir, 0755, true);
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// Path file .webm yang diupload
$webm_file_path = $_FILES['cover_audio_record']['tmp_name'];
$original_name = $_FILES['cover_audio_record']['name'];

// Path untuk file .WAV hasil konversi
$wav_file_name = 'converted_' . time() . '.wav';
$wav_file_path = $temp_dir . $wav_file_name;

// 3. Konversi .WEBM ke .WAV (Butuh FFmpeg)
// Pastikan path ke 'ffmpeg' benar di server Anda.
// 'ffmpeg' mungkin perlu path penuh seperti 'C:\ffmpeg\bin\ffmpeg.exe'
// atau jika ada di PATH, 'ffmpeg' saja cukup.
$command = "C:\\ffmpeg\\bin\\ffmpeg.exe -i " . escapeshellarg($webm_file_path) . " " . escapeshellarg($wav_file_path);
exec($command, $output, $return_var);

if ($return_var != 0 || !file_exists($wav_file_path)) {
    // Jika FFmpeg gagal
    die('Error: Gagal mengkonversi rekaman audio ke .WAV. Pastikan FFmpeg terinstal di server dan path-nya benar.');
}

// 4. Jalankan Steganografi
$message = $_POST['stego_message_audio'];
$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

// Path untuk file .WAV final yang sudah ada stego
$output_name = 'stego_rec_' . time() . '.wav';
$output_path = $upload_dir . $output_name;

// Panggil helper stego (dari file WAV hasil konversi)
$success = hide_message_in_wav($wav_file_path, $message, $output_path);

// 5. Simpan ke DB jika berhasil
if ($success) {
    // Gunakan path web yang benar (sesuai perbaikan kita sebelumnya)
    $db_path = '/skap-pemerintah/uploads/audio/' . $output_name; 
    
    $stmt = $db->prepare("INSERT INTO stego_audio (sender_id, receiver_id, audio_name, audio_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, 'Rekaman Sinyal (' . $original_name . ')', $db_path]);
    
    // Hapus file sementara
    unlink($wav_file_path);
    
    die('status=ok'); // Kirim sinyal sukses ke JavaScript

} else {
    // Hapus file sementara
    unlink($wav_file_path);
    die('Gagal menyembunyikan pesan di file .WAV. Pesan mungkin terlalu panjang.');
}
?>