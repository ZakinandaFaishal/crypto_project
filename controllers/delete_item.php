<?php
session_start();
require_once '../config/db.php';

// Cek jika login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
// Cek jika parameter lengkap
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header('Location: ../views/dashboard.php?error=Permintaan tidak valid');
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = $_GET['id'];
$type = $_GET['type'];

$table = '';
$file_path_column = null;

// Tentukan tabel mana yang akan dihapus
switch ($type) {
    case 'message':
        $table = 'messages';
        break;
    case 'file':
        $table = 'secure_files';
        $file_path_column = 'encrypted_file_path';
        break;
    case 'stego':
        $table = 'stego_images';
        $file_path_column = 'image_path';
        break;
    // --- TAMBAHAN UNTUK AUDIO ---
    case 'audio':
        $table = 'stego_audio';
        $file_path_column = 'audio_path'; // Kolom path dari stego_audio
        break;
    default:
        header('Location: ../views/dashboard.php?error=Tipe item tidak valid');
        exit;
}

// --- Logika Hapus File Fisik (Jika ada) ---
if ($file_path_column) {
    // Ambil path file dari DB SEBELUM menghapus record
    $stmt = $db->prepare("SELECT $file_path_column FROM $table WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$item_id, $user_id]);
    $item = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($item) {
        
        // --- LOGIKA PATH BARU (Untuk menangani inkonsistensi) ---
        $db_path = $item[$file_path_column]; // e.g., /skap-pemerintah/uploads/... ATAU /uploads/...
        $server_file_path = '';

        // Cek apakah path berisi awalan /skap-pemerintah
        if (strpos($db_path, '/skap-pemerintah') === 0) {
            // Jika ya, hapus awalan itu
            $relative_path = str_replace('/skap-pemerintah', '', $db_path);
            // Buat path relatif dari folder /controllers/
            $server_file_path = '..' . $relative_path; // Menjadi ../uploads/audio/file.wav
        } else {
            // Jika tidak, gunakan logika lama
            $server_file_path = '..' . $db_path; // Menjadi ../uploads/files/file.enc
        }
        // --- AKHIR LOGIKA PATH BARU ---

        if (file_exists($server_file_path)) {
            @unlink($server_file_path); // Hapus file fisik dari server
        }
    }
}

// --- Logika Hapus Record Database ---
// Hapus item HANYA jika user adalah penerimanya (receiver_id)
$stmt = $db->prepare("DELETE FROM $table WHERE id = ? AND receiver_id = ?");
$stmt->execute([$item_id, $user_id]);

$rows_deleted = $stmt->rowCount();

if ($rows_deleted > 0) {
    header('Location: ../views/dashboard.php?status=Item berhasil dihapus');
} else {
    header('Location: ../views/dashboard.php?error=Gagal menghapus item atau Anda tidak punya akses');
}
exit;
?>