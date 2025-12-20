<?php
require '../include/conn.php';

if (isset($_POST['register'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = md5($_POST['password']);
  $role_id = 1;

  $cek = $conn->prepare("SELECT id FROM users WHERE email=?");
  $cek->bind_param("s", $email);
  $cek->execute();
  $cek->store_result();

  if ($cek->num_rows > 0) {
    $error = "Email sudah terdaftar!";
  } else {
    $stmt = $conn->prepare("
      INSERT INTO users (name,email,password,role_id)
      VALUES (?,?,?,?)
    ");
    $stmt->bind_param("sssi", $name, $email, $password, $role_id);
    $stmt->execute();
    $success = "Registrasi berhasil, silakan login.";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Register | DigiPlan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-900 to-black flex items-center justify-center">

  <div class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl p-8">

    <div class="text-center mb-8">
      <img src="../assets/logo.png" class="w-24 mx-auto mb-4 rounded-full p-2 backdrop-blur-xl bg-white border border-white/20 rounded-2xl shadow-2xl">
      <h1 class="text-3xl font-bold text-white">Welcome</h1>
      <p class="text-white/70 text-sm">Regist ke sistem DigiPlan</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-500/20 text-red-300 rounded-xl"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3 bg-green-500/20 text-green-300 rounded-xl"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <input name="name" placeholder="Nama Perusahaan" required
        class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">

      <input type="email" name="email" placeholder="Email" required
        class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">

      <input type="password" name="password" placeholder="Password" required
        class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">

      <button name="register"
        class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl shadow-lg transition-all">
        Daftar
      </button>
    </form>

    <p class="mt-6 text-center text-white/70 text-sm">
      Sudah punya akun?
      <a href="index.php" class="text-indigo-400 hover:text-indigo-300">Login</a>
    </p>
  </div>
</body>

</html>