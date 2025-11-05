<?php
include 'header.php'; // Tampilkan header
require_once '../config/db.php';
require_once '../helpers/crypto_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message_id = $_GET['id'];

// Ambil pesan, pastikan pesan itu milik user yang login
$stmt = $db->prepare("SELECT * FROM messages WHERE id = ? AND receiver_id = ?");
$stmt->execute([$message_id, $user_id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    echo "<div class='bg-red-200 p-4 rounded text-red-800'>Error: Pesan tidak ditemukan atau bukan milik Anda.</div>";
    include 'footer.php';
    exit;
}

// Dekripsi pesan
$decrypted_message = super_decrypt($message['encrypted_message']);

?>

<div class_alias="w-full max-w-2xl bg-white p-8 rounded-lg shadow-xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Dekripsi Pesan</h2>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Data Terenkripsi (Base64):</label>
        <textarea rows="5" class="mt-1 block w-full bg-gray-100 rounded-md border-gray-300" readonly><?php echo htmlspecialchars($message['encrypted_message']); ?></textarea>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Pesan Asli (Plainteks):</label>
        <div class="mt-1 block w-full p-4 bg-green-50 rounded-md border border-green-300 min-h-[100px]">
            <?php echo htmlspecialchars($decrypted_message); ?>
        </div>
    </div>
    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Dasbor</a>
</div>

<?php include 'footer.php'; ?>