<?php
require_once 'config/session.php';
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bengkel Racing Cihuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#0a0a0f] min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 mb-4">
                <img src="assets/img/logo.png" alt="Bengkel Racing Cihuy" class="w-full h-full">
            </div>
            <h1 class="text-3xl font-bold text-white">Bengkel Racing Cihuy</h1>
            <p class="text-gray-400 mt-1">POS Bengkel Motor Racing</p>
        </div>

        <div class="bg-[#161622] rounded-2xl p-8 shadow-xl border border-[#2a2a3a]">
            <h2 class="text-xl font-semibold text-white mb-6">Login</h2>

            <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-300 text-sm font-medium mb-2">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00] transition-colors"
                        placeholder="Masukkan username">
                </div>
                <div>
                    <label class="block text-gray-300 text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-[#0a0a0f] border border-[#2a2a3a] rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ccff00] transition-colors"
                        placeholder="Masukkan password">
                </div>
                <button type="submit"
                    class="w-full py-3 bg-[#ccff00] hover:bg-[#ff0066] text-white font-semibold rounded-xl transition-colors duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-6">
            &copy; 2025 Bengkel Racing Cihuy. UAS Praktikum Pemrograman Web
        </p>
    </div>
</body>
</html>
