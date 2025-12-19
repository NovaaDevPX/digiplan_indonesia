<?php
require '../include/conn.php';

if (isset($_POST['reset'])) {
  $email = $_POST['email'];
  $new = md5($_POST['new_password']);

  $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
  $stmt->bind_param("ss", $new, $email);
  $stmt->execute();

  $success = $stmt->affected_rows ? "Password berhasil direset" : "Email tidak ditemukan";
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Lupa Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-900 to-black flex items-center justify-center">

  <div class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl p-8">

    <h2 class="text-2xl font-bold text-white text-center mb-6">Reset Password</h2>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3 bg-indigo-500/20 text-indigo-300 rounded-xl"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <input type="email" name="email" placeholder="Email" required
        class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">

      <input type="password" name="new_password" placeholder="Password Baru" required
        class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">

      <button name="reset"
        class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl shadow-lg">
        Reset Password
      </button>
    </form>

    <p class="mt-6 text-center text-white/70 text-sm">
      <a href="index.php" class="text-indigo-400 hover:text-indigo-300">Kembali ke Login</a>
    </p>

  </div>
</body>

</html>