<?php include 'views/header.php'; ?>

<div class="flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg shadow-2xl border border-gray-700">
        <h2 class="text-3xl font-bold text-center text-gray-200 mb-6">Akses Agen ENIGMA</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <form action="controllers/handle_login.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-400">Username Agen:</label>
                <input type="text" name="username" id="username" required
                       class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-400">Password:</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full rounded-md bg-gray-700 border-gray-600 text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
                Login
            </button>
        </form>
    </div>
</div>

<?php include 'views/footer.php'; ?>