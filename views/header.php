<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ENIGMA Agency</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200 font-mono">
    <nav class="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-400 tracking-wider">ENIGMA</h1>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-300 mr-4">Agen: <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
                    <a href="/skap-pemerintah/controllers/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">Logout</a>
                <?php else: ?>
                    <a href="/skap-pemerintah/index.php" class="text-gray-300 hover:text-blue-400 mr-4">Login</a>
                    <a href="/skap-pemerintah/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">Registrasi</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-6">