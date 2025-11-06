<?php
include 'header.php';
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /skap-pemerintah/index.php?error=Akses ditolak. Silakan login.');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil SEMUA user (termasuk diri sendiri)
$usersStmt = $db->prepare("SELECT id, username FROM users");
$usersStmt->execute();
$all_users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC); 

// Ambil data masuk
$messages = $db->prepare("SELECT m.*, u.username AS sender FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY m.send_at DESC");
$messages->execute([$user_id]);

// Perhatikan: f.sender_id
$files = $db->prepare("SELECT f.*, u.username AS sender FROM secure_files f JOIN users u ON f.sender_id = u.id WHERE f.receiver_id = ? ORDER BY f.upload_at DESC");
$files->execute([$user_id]);

// Perhatikan: i.sender_id
$images = $db->prepare("SELECT i.*, u.username AS sender FROM stego_images i JOIN users u ON i.sender_id = u.id WHERE i.receiver_id = ? ORDER BY i.upload_at DESC");
$images->execute([$user_id]);

// --- TAMBAHKAN KUERI INI ---
$audios = $db->prepare("SELECT a.*, u.username AS sender FROM stego_audio a JOIN users u ON a.sender_id = u.id WHERE a.receiver_id = ? ORDER BY a.upload_at DESC");
$audios->execute([$user_id]);
// --- AKHIR TAMBAHAN KUERI ---

?>

<?php if (isset($_GET['status'])): ?>
    <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_GET['status']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<h1 class="text-3xl font-bold text-gray-200 mb-6 tracking-wide">Mission Control</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h2 class="text-xl font-semibold mb-4 text-blue-400">1. Kirim Intel (Teks)</h2>
        <form action="../controllers/message_controller.php" method="POST">
            <div class="mb-4">
                <label for="receiver_id" class="block text-sm font-medium text-gray-400">Agen Penerima:</label>
                <select name="receiver_id" id="receiver_id" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Agen --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Safe House)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="pesan_teks" class="block text-sm font-medium text-gray-400">Intel Rahasia:</label>
                <textarea name="pesan_teks" rows="4" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Kirim Intel</button>
        </form>
    </div>

    <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h2 class="text-xl font-semibold mb-4 text-green-400">2. Amankan Aset (File)</h2>
        <form action="../controllers/file_controller.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="file_receiver_id" class="block text-sm font-medium text-gray-400">Agen Penerima:</label>
                <select name="receiver_id" id="file_receiver_id" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Agen --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Safe House)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="file_aman" class="block text-sm font-medium text-gray-400">Pilih Aset:</label>
                <input type="file" name="file_aman" required class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-green-300 hover:file:bg-gray-600">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Amankan & Kirim Aset</button>
        </form>
    </div>

    <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h2 class="text-xl font-semibold mb-4 text-purple-400">3. Mode Siluman (Gambar)</h2>
        <form action="../controllers/stego_controller.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="stego_receiver_id" class="block text-sm font-medium text-gray-400">Agen Penerima:</label>
                <select name="receiver_id" id="stego_receiver_id" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Agen --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Safe House)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="cover_image" class="block text-sm font-medium text-gray-400">Gambar Cover (.PNG):</label>
                <input type="file" name="cover_image" required accept="image/png" class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-purple-300 hover:file:bg-gray-600">
            </div>
            <div class="mb-4">
                <label for="stego_message" class="block text-sm font-medium text-gray-400">Pesan Rahasia:</label>
                <textarea name="stego_message" rows="2" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700">Aktifkan Siluman & Kirim</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h2 class="text-xl font-semibold mb-4 text-cyan-400">4. Mode Siluman (Audio)</h2>
        <form action="../controllers/audio_stego_controller.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="receiver_id_audio" class="block text-sm font-medium text-gray-400">Agen Penerima:</label>
                <select name="receiver_id" id="receiver_id_audio" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Agen --</option>
                    <?php foreach ($all_users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == $user_id) echo ' (Safe House)'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="cover_audio" class="block text-sm font-medium text-gray-400">File Audio Cover (HANYA .WAV):</label>
                <input type="file" name="cover_audio" id="cover_audio" required accept="audio/wav,audio/x-wav" class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-cyan-300 hover:file:bg-gray-600">
            </div>
            <div class="mb-4">
                <label for="stego_message_audio" class="block text-sm font-medium text-gray-400">Pesan Rahasia:</label>
                <textarea name="stego_message_audio" id="stego_message_audio" rows="2" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-cyan-600 text-white py-2 px-4 rounded-md hover:bg-cyan-700">Aktifkan Siluman Audio</button>
        </form>
    </div>
    </div>


<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
    
    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2 text-blue-400">Intel Diterima</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($messages->fetchAll(\PDO::FETCH_ASSOC) as $msg): ?>
                <div class="border border-gray-700 p-3 rounded bg-gray-700">
                    <p class="text-sm text-gray-400">Dari: <b><?php echo htmlspecialchars($msg['sender']); ?></b></p>
                    <p class="text-xs text-gray-500"><?php echo $msg['send_at']; ?></p>
                    <a href="decrypt_message.php?id=<?php echo $msg['id']; ?>" class="text-blue-400 hover:underline text-sm">Buka & Deklasifikasi</a>
                    <a href="../controllers/delete_item.php?type=message&id=<?php echo $msg['id']; ?>" 
                       class="text-red-500 hover:underline text-sm ml-2" 
                       onclick="return confirm('Hapus intel ini?');">Hapus</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2 text-green-400">Aset Diterima</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            <?php foreach ($files->fetchAll(\PDO::FETCH_ASSOC) as $file): ?>
                <div class="border border-gray-700 p-3 rounded bg-gray-700">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-400">Dari: <b><?php echo htmlspecialchars($file['sender']); ?></b></p>
                            <p class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($file['original_file_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $file['upload_at']; ?></p>
                            <a href="../controllers/delete_item.php?type=file&id=<?php echo $file['id']; ?>" 
                               class="text-red-500 hover:underline text-xs" 
                               onclick="return confirm('Hapus aset ini? (File fisik juga akan dihapus)');">Hapus</a>
                        </div>
                        <a href="download_file.php?id=<?php echo $file['id']; ?>" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 flex-shrink-0">Download</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2 text-purple-400">Sinyal Siluman Diterima</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
             <?php foreach ($images->fetchAll(\PDO::FETCH_ASSOC) as $img): ?>
                <div class="border border-gray-700 p-3 rounded bg-gray-700">
                    <p class="text-sm text-gray-400">Dari: <b><?php echo htmlspecialchars($img['sender']); ?></b></p>
                    <img src="<?php echo $img['image_path']; ?>" alt="Sinyal Gambar" class="w-full h-24 object-cover rounded my-2 border border-gray-600">
                    <p class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($img['image_name']); ?></p>
                    <a href="read_stego.php?id=<?php echo $img['id']; ?>" class="text-purple-400 hover:underline text-sm">Dekode Sinyal</a>
                    <a href="../controllers/delete_item.php?type=stego&id=<?php echo $img['id']; ?>" 
                       class="text-red-500 hover:underline text-sm ml-2" 
                       onclick="return confirm('Hapus sinyal ini? (File fisik juga akan dihapus)');">Hapus</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2 text-cyan-400">Sinyal Audio Diterima</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
             <?php foreach ($audios->fetchAll(\PDO::FETCH_ASSOC) as $audio): ?>
                <div class="border border-gray-700 p-3 rounded bg-gray-700">
                    <p class="text-sm text-gray-400">Dari: <b><?php echo htmlspecialchars($audio['sender']); ?></b></p>
                    <p class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($audio['audio_name']); ?></p>
                    <audio controls class="w-full mt-2">
                        <source src="<?php echo $audio['audio_path']; ?>" type="audio/wav">
                    </audio>
                    <a href="read_audio.php?id=<?php echo $audio['id']; ?>" class="text-cyan-400 hover:underline text-sm">Dekode Sinyal Audio</a>
                    <a href="../controllers/delete_item.php?type=audio&id=<?php echo $audio['id']; ?>" 
                       class="text-red-500 hover:underline text-sm ml-2" 
                       onclick="return confirm('Hapus sinyal audio ini?');">Hapus</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="md:col-span-1 bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
    <h2 class="text-xl font-semibold mb-4 text-cyan-400">4. Mode Siluman (Audio)</h2>
    
    <form id="form-audio-record" action="../controllers/audio_record_controller.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="receiver_id_audio" class="block text-sm font-medium text-gray-400">Agen Penerima:</label>
            <select name="receiver_id" id="receiver_id_audio" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Pilih Agen --</option>
                <?php foreach ($all_users as $user): ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['username']); ?>
                        <?php if ($user['id'] == $user_id) echo ' (Safe House)'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="stego_message_audio" class="block text-sm font-medium text-gray-400">Pesan Rahasia:</label>
            <textarea name="stego_message_audio" id="stego_message_audio" rows="2" required class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="mb-4">
            <button type="button" id="btn-start-record" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">Mulai Merekam</button>
            <button type="button" id="btn-stop-record" class="w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 hidden">Berhenti Merekam</button>
            <p id="record-status" class="text-yellow-400 text-sm mt-2 text-center hidden">Merekam...</p>
            <audio id="audio-playback" controls class="mt-2 w-full hidden"></audio>
        </div>
        
        <button type="submit" id="btn-send-record" class="w-full bg-cyan-600 text-white py-2 px-4 rounded-md hover:bg-cyan-700 hidden">Kirim Sinyal Audio</button>
    </form>
</div>

</div>

<?php include 'footer.php'; ?>