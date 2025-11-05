<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKAP - Sistem Komunikasi Aman</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-700">SKAP</h1>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700 mr-4">Halo, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
                    <a href="/skap-pemerintah/controllers/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                <?php else: ?>
                    <a href="/skap-pemerintah/index.php" class="text-gray-700 hover:text-blue-600 mr-4">Login</a>
                    <a href="/skap-pemerintah/register.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Registrasi</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container mx-auto p-6">