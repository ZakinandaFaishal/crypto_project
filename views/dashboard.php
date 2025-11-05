<?php
include 'header.php';
require_once '../config/db.php';

// Lindungi halaman
if (!isset($_SESSION['user_id'])) {
    header('Location: /skap-pemerintah/index.php?error=Anda harus login');
    exit;
}

$user_id = $_SESSION['user_id'];

// --- PERBAIKAN LOGIKA "KIRIM KE DIRI SENDIRI" ---

// 1. Ambil SEMUA user (termasuk diri sendiri)
$usersStmt = $db->prepare("SELECT id, username FROM users");
// 2. Hapus parameter, karena kueri tidak ada placeholder '?'
$usersStmt->execute();
// 3. Simpan ke variabel $all_users
$all_users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);

// --- Kueri untuk data masuk (sudah benar) ---
$messages = $db->prepare("SELECT m.*, u.username AS sender FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY m.send_at DESC");
$messages->execute([$user_id]);

$files = $db->prepare("SELECT f.*, u.username AS sender FROM secure_files f JOIN users u ON f.sender_id = u.id WHERE f.receiver_id = ? ORDER BY f.upload_at DESC");
$files->execute([$user_id]);

$images = $db->prepare("SELECT i.*, u.username AS sender FROM stego_images i JOIN users u ON i.sender_id = u.id WHERE i.receiver_id = ? ORDER BY i.upload_at DESC");
$images->execute([$user_id]);

?>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_GET['status']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>


<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-blue-700">1. Kirim Pesan (Vigenere + AES)</h2>
        <form action="../controllers/message_controller.php" method="POST">
            <div class="mb-4">
                <label for="receiver_id" class="block text-sm font-medium text-gray-700">Penerima:</label>
                <select name="receiver_id" id="receiver_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Diri Sendiri)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="pesan_teks" class="block text-sm font-medium text-gray-700">Pesan Rahasia:</label>
                <textarea name="pesan_teks" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Kirim Pesan</button>
        </form>
    </div>

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-green-700">2. Kirim File (AES-256 CBC)</h2>
        <form action="../controllers/file_controller.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="file_receiver_id" class="block text-sm font-medium text-gray-700">Penerima:</label>
                <select name="receiver_id" id="file_receiver_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Diri Sendiri)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="file_aman" class="block text-sm font-medium text-gray-700">Pilih File:</label>
                <input type="file" name="file_aman" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Kirim & Enkripsi File</button>
        </form>
    </div>

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-purple-700">3. Kirim Gambar Stego (LSB)</h2>
        <form action="../controllers/stego_controller.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="stego_receiver_id" class="block text-sm font-medium text-gray-700">Penerima:</label>
                <select name="receiver_id" id="stego_receiver_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Diri Sendiri)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="cover_image" class="block text-sm font-medium text-gray-700">Gambar Cover (.PNG):</label>
                <input type="file" name="cover_image" required accept="image/png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
            </div>
            <div class="mb-4">
                <label for="stego_message" class="block text-sm font-medium text-gray-700">Pesan Rahasia:</label>
                <textarea name="stego_message" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700">Kirim & Sembunyikan</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Pesan Masuk</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($messages->fetchAll(\PDO::FETCH_ASSOC) as $msg): ?>
                <div class="border p-3 rounded bg-gray-50">
                    <p class="text-sm text-gray-600">Dari: <b><?php echo htmlspecialchars($msg['sender']); ?></b></p>
                    <p class="text-xs text-gray-500"><?php echo $msg['send_at']; ?></p>
                    <a href="decrypt_message.php?id=<?php echo $msg['id']; ?>" class="text-blue-500 hover:underline text-sm">Lihat & Dekripsi</a>
                    <a href="../controllers/delete_item.php?type=message&id=<?php echo $msg['id']; ?>"
                        class="text-red-500 hover:underline text-sm ml-2"
                        onclick="return confirm('Anda yakin ingin menghapus pesan ini?');">Hapus</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">File Masuk</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($files->fetchAll(\PDO::FETCH_ASSOC) as $file): ?>
                <div class="border p-3 rounded bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-600">Dari: <b><?php echo htmlspecialchars($file['sender']); ?></b></p>
                            <p class="text-sm font-medium"><?php echo htmlspecialchars($file['original_file_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $file['upload_at']; ?></p>
                            <a href="../controllers/delete_item.php?type=file&id=<?php echo $file['id']; ?>"
                                class="text-red-500 hover:underline text-xs"
                                onclick="return confirm('Anda yakin ingin menghapus file ini? (File fisik juga akan dihapus)');">Hapus</a>
                        </div>
                        <a href="download_file.php?id=<?php echo $file['id']; ?>" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 flex-shrink-0">Download</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Gambar Stego Masuk</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($images->fetchAll(\PDO::FETCH_ASSOC) as $img): ?>
                <div class="border p-3 rounded bg-gray-50">
                    <p class="text-sm text-gray-600">Dari: <b><?php echo htmlspecialchars($img['sender']); ?></b></p>
                    <img src="<?php echo '.' . $img['image_path']; ?>" alt="<?php echo htmlspecialchars($img['image_name']); ?>" class="w-full h-24 object-cover rounded my-2">
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($img['image_name']); ?></p>
                    <a href="read_stego.php?id=<?php echo $img['id']; ?>" class="text-purple-500 hover:underline text-sm">Baca Pesan</a>
                    <a href="../controllers/delete_item.php?type=stego&id=<?php echo $img['id']; ?>"
                        class="text-red-500 hover:underline text-sm ml-2"
                        onclick="return confirm('Anda yakin ingin menghapus gambar ini? (File fisik juga akan dihapus)');">Hapus</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>