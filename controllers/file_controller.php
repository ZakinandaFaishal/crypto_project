<?php
session_start();
require_once '../config/db.php';
require_once '../config/crypto_keys.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=Anda harus login');
    exit;
}

// --- PERUBAHAN LOGIKA START ---

// 1. Cek Penerima
if (empty($_POST['receiver_id'])) {
    header('Location: ../views/dashboard.php?error=Penerima file tidak boleh kosong');
    exit;
}

// 2. Cek File
if (!isset($_FILES['file_aman']) || empty($_FILES['file_aman']['name'])) {
    header('Location: ../views/dashboard.php?error=Tidak ada file yang dipilih');
    exit;
}
$file = $_FILES['file_aman'];

// 3. Cek error upload (pakai angka 0, bukan UPLOAD_OK)
if ($file['error'] !== 0) { 
    $errors = [
        1 => 'File terlalu besar (melebihi php.ini: upload_max_filesize).', // UPLOAD_ERR_INI_SIZE
        2 => 'File terlalu besar (melebihi form: MAX_FILE_SIZE).', // UPLOAD_ERR_FORM_SIZE
        3 => 'File hanya ter-upload sebagian.', // UPLOAD_ERR_PARTIAL
        4 => 'Tidak ada file yang ter-upload.', // UPLOAD_ERR_NO_FILE
        6 => 'Folder temporary (tmp) tidak ditemukan.', // UPLOAD_ERR_NO_TMP_DIR
        7 => 'Gagal menulis file ke disk.', // UPLOAD_ERR_CANT_WRITE
        8 => 'Ekstensi PHP menghentikan upload.', // UPLOAD_ERR_EXTENSION
    ];
    $error_message = $errors[$file['error']] ?? 'Error upload tidak diketahui.';
    header('Location: ../views/dashboard.php?error=' . urlencode($error_message));
    exit;
}

// 4. Cek folder (logika tetap sama)
$upload_dir = '../uploads/files/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
         header('Location: ../views/dashboard.php?error=Folder tujuan (uploads/files) tidak ada dan tidak bisa dibuat.');
         exit;
    }
}
if (!is_writable($upload_dir)) {
    header('Location: ../views/dashboard.php?error=Folder tujuan (uploads/files) tidak bisa ditulis. Cek permissions!');
    exit;
}
// --- PERUBAHAN LOGIKA END ---

$original_name = $file['name'];
$file_tmp_path = $file['tmp_name'];

// --- UBAH DARI user_id MENJADI sender_id dan receiver_id ---
$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];

$plaintext = file_get_contents($file_tmp_path);
if ($plaintext === false) {
    header('Location: ../views/dashboard.php?error=Gagal membaca file temporary.');
    exit;
}

$iv_length = openssl_cipher_iv_length(AES_METHOD);
$iv = openssl_random_pseudo_bytes($iv_length);
$ciphertext = openssl_encrypt($plaintext, AES_METHOD, AES_KEY, OPENSSL_RAW_DATA, $iv);

$encrypted_file_name = hash('sha256', $original_name . time()) . '.enc';
$encrypted_file_path = $upload_dir . $encrypted_file_name;

if (file_put_contents($encrypted_file_path, $ciphertext)) {
    $iv_hex = bin2hex($iv);
    $db_path = '/uploads/files/' . $encrypted_file_name; 
    
    // --- PERBARUI QUERY INSERT ---
    $stmt = $db->prepare("INSERT INTO secure_files (sender_id, receiver_id, original_file_name, encrypted_file_path, iv_hex) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $original_name, $db_path, $iv_hex]);
    
    header('Location: ../views/dashboard.php?status=File berhasil dienkripsi dan dikirim');
} else {
    header('Location: ../views/dashboard.php?error=Gagal menyimpan file terenkripsi setelah enkripsi.');
}
?>