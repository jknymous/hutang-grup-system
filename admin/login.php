<?php
session_start();
require '../database/koneksi.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex items-center justify-center bg-blue-100 px-4">
    <form method="POST" class="bg-white w-full max-w-xs p-6 rounded-2xl shadow-md text-center">
        <img src="../assets/foto/agus.png" alt="Agus" class="w-20 h-20 object-cover rounded-lg mx-auto mb-4">
        <h2 class="text-xl font-bold text-blue-900 mb-4">Login Admin</h2>

        <?php if (!empty($error)): ?>
            <div class="text-red-500 text-sm mb-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
            autofocus
            class="w-full border-2 border-blue-900 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-900 mb-3"
        >

        <div class="relative mb-4">
            <input
                type="password"
                name="password"
                id="passwordInput"
                placeholder="Password"
                required
                class="w-full border-2 border-blue-900 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-900 pr-10"
            >
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 focus:outline-none">
                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.957 9.957 0 012.145-3.568M6.213 6.213A9.964 9.964 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.955 9.955 0 01-4.042 5.163M6.213 6.213L3 3m0 0l3.213 3.213m0 0L21 21" />
                </svg>
            </button>
        </div>

        <button
            type="submit"
            class="w-full bg-blue-900 hover:bg-blue-900 text-white font-bold py-2 rounded-xl transition duration-200"
        >
            Masuk
        </button>
    </form>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
