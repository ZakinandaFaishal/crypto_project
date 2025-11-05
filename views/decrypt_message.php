<?php
include 'header.php';
require_once '../config/db.php';
require_once '../helpers/crypto_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message_id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM messages WHERE id = ? AND receiver_id = ?");
$stmt->execute([$message_id, $user_id]);
$message = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$message) {
    echo "<div class='bg-red-900 p-4 rounded text-red-200'>Error: Intel tidak ditemukan atau bukan milik Anda.</div>";
    include 'footer.php';
    exit;
}

$decrypted_message = super_decrypt($message['encrypted_message']);
?>

<div class="w-full max-w-2xl bg-gray-800 p-8 rounded-lg shadow-2xl border border-gray-700 mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-blue-400">Deklasifikasi Intel</h2>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-400">Data Terenkripsi (Base64):</label>
        <textarea rows="5" class="mt-1 block w-full bg-gray-700 rounded-md border-gray-600 text-gray-300" readonly><?php echo htmlspecialchars($message['encrypted_message']); ?></textarea>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-400">Intel Asli (Plainteks):</label>
        <div class="mt-1 block w-full p-4 bg-gray-900 rounded-md border border-gray-600 min-h-[100px] text-green-300">
            <?php echo htmlspecialchars($decrypted_message); ?>
        </div>
    </div>
    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Mission Control</a>
</div>

<?php include 'footer.php'; ?>