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

// --- PERUBAHAN QUERY KEAMANAN ---
// Cek apakah user ini adalah PENERIMA gambar
$stmt = $db->prepare("SELECT * FROM stego_images WHERE id = ? AND receiver_id = ?");
$stmt->execute([$image_id, $user_id]);
$image = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$image) {
    echo "<div class='bg-red-200 p-4 rounded text-red-800'>Error: Gambar tidak ditemukan atau bukan milik Anda.</div>";
    include 'footer.php';
    exit;
}

$server_path = '..' . $image['image_path'];
$hidden_message = "Error: Gambar tidak ditemukan di path server."; // Default

if (file_exists($server_path)) {
    $hidden_message = extract_message_from_image($server_path);
} else {
    $hidden_message = "Error: File gambar fisik tidak ditemukan di server.";
}

?>

<div class="w-full max-w-2xl bg-white p-8 rounded-lg shadow-xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Ekstrak Pesan dari Gambar</h2>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Gambar Stego:</label>
        <img src="<?php echo $server_path; ?>" alt="Stego Image" class="mt-1 w-full rounded border">
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Pesan Tersembunyi:</label>
        <div class="mt-1 block w-full p-4 bg-purple-50 rounded-md border border-purple-300 min-h-[100px]">
            <?php echo htmlspecialchars($hidden_message); ?>
        </div>
    </div>
    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Dasbor</a>
</div>

<?php include 'footer.php'; ?>