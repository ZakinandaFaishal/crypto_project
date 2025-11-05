<?php
include 'header.php';
require_once '../config/db.php';
require_once '../helpers/stego_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$image_id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM stego_images WHERE id = ? AND receiver_id = ?");
$stmt->execute([$image_id, $user_id]);
$image = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$image) {
    echo "<div class='bg-red-900 p-4 rounded text-red-200'>Error: Sinyal tidak ditemukan atau bukan milik Anda.</div>";
    include 'footer.php';
    exit;
}

$server_path = '..' . $image['image_path'];
$hidden_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($server_path)) {
        $hidden_message = extract_message_from_image($server_path);
    } else {
        $hidden_message = "Error: File sinyal fisik tidak ditemukan di server.";
    }
}
?>

<div class="w-full max-w-2xl bg-gray-800 p-8 rounded-lg shadow-2xl border border-gray-700 mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-purple-400">Dekode Sinyal Siluman</h2>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-400">Sinyal Gambar:</label>
        <img src="<?php echo $server_path; ?>" alt="Sinyal Gambar" class="mt-1 w-full rounded border border-gray-700">
    </div>

    <div class="mb-4">
        <?php if ($hidden_message === null): ?>
            <form method="POST" action="">
                <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition duration-300">
                    Deklasifikasi Sinyal
                </button>
            </form>

        <?php else: ?>
            <label class="block text-sm font-medium text-gray-400">Pesan Tersembunyi:</label>
            <div class="mt-1 block w-full p-4 bg-gray-900 rounded-md border border-gray-600 min-h-[100px] text-green-300">
                <?php echo htmlspecialchars($hidden_message); ?>
            </div>
        <?php endif; ?>
    </div>

    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Mission Control</a>
</div>

<?php include 'footer.php'; ?>