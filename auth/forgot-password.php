<?php
require '../include/conn.php';

$success = '';
$error = '';

if (isset($_POST['reset'])) {
  $email = $_POST['email'];
  $new = md5($_POST['new_password']);

  $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
  $stmt->bind_param("ss", $new, $email);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    $success = "Password berhasil direset";
  } else {
    $error = "Email tidak ditemukan";
  }
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

    <div class="text-center mb-8">
      <img src="../assets/logo.png" class="w-24 mx-auto mb-4 rounded-full p-2 backdrop-blur-xl bg-white border border-white/20 rounded-2xl shadow-2xl">
      <h1 class="text-3xl font-bold text-white">Welcome Back</h1>
      <p class="text-white/70 text-sm">Buat password Baru di sistem DigiPlan</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3 bg-green-500/20 border border-green-500/30 text-green-300 rounded-xl">
        <?= $success ?>
      </div>
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