<?php
include 'header.php'; // Tampilkan header
require_once '../config/db.php';
require_once '../helpers/audio_stego_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$audio_id = $_GET['id'];

// Ambil data audio, pastikan milik user (sebagai PENERIMA)
$stmt = $db->prepare("SELECT * FROM stego_audio WHERE id = ? AND receiver_id = ?");
$stmt->execute([$audio_id, $user_id]);
$audio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$audio) {
    echo "<div class='bg-red-900 p-4 rounded text-red-100'>Error: File audio tidak ditemukan atau bukan milik Anda.</div>";
    include 'footer.php';
    exit;
}

// === PERBAIKAN DI SINI ===

// Path audio di server (untuk helper file_get_contents)
// Kita butuh path file sistem absolut, cth: C:/xampp/htdocs/skap-pemerintah/uploads/audio/...
$server_path = $_SERVER['DOCUMENT_ROOT'] . $audio['audio_path'];

// Path audio untuk browser (untuk tag <audio>)
// Path dari DB sudah benar ( /skap-pemerintah/uploads/audio/... )
$browser_path = $audio['audio_path']; 

// === AKHIR PERBAIKAN ===


// Ekstrak pesan
$hidden_message = extract_message_from_wav($server_path);

?>

<div class="w-full max-w-2xl bg-gray-800 p-8 rounded-lg shadow-xl mx-auto border border-gray-700">
    <h2 class="text-2xl font-bold mb-4 text-cyan-400">Dekode Sinyal Audio</h2>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-300">Sinyal Audio:</label>
        <audio controls class="mt-1 w-full rounded">
            <source src="<?php echo $browser_path; ?>" type="audio/wav">
            Browser Anda tidak mendukung elemen audio.
        </audio>
    </div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-300">Pesan Tersembunyi:</label>
        <div class="mt-1 block w-full p-4 bg-gray-900 rounded-md border border-gray-700 min-h-[100px] text-white">
            <?php echo htmlspecialchars($hidden_message); ?>
        </div>
    </div>
    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Kembali ke Mission Control</a>
</div>

<?php include 'footer.php'; ?>