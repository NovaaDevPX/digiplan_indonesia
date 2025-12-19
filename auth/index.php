<?php
session_start();
require '../include/conn.php';
require '../include/base-url.php';

if (isset($_SESSION['log']) && $_SESSION['log'] === true) {
  switch ($_SESSION['role']) {
    case 'super_admin':
      header("Location: {$base_url}superadmin/dashboard.php");
      break;
    case 'admin':
      header("Location: {$base_url}admin/dashboard.php");
      break;
    default:
      header("Location: {$base_url}customer/dashboard.php");
      break;
  }
  exit;
}

if (isset($_POST['login'])) {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("
    SELECT users.*, roles.name AS role_name
    FROM users
    JOIN roles ON users.role_id = roles.id
    WHERE users.email=? AND users.deleted_at IS NULL
    LIMIT 1
  ");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows === 1) {
    $u = $res->fetch_assoc();
    if (md5($password) === $u['password']) {
      $_SESSION['log'] = true;
      $_SESSION['user_id'] = $u['id'];
      $_SESSION['name'] = $u['name'];
      $_SESSION['role'] = $u['role_name'];

      if ($u['role_name'] === 'super_admin') {
        header("Location: {$base_url}superadmin/dashboard.php");
        exit;
      } elseif ($u['role_name'] === 'admin') {
        header("Location: {$base_url}admin/dashboard.php");
        exit;
      } elseif ($u['role_name'] === 'customer') {
        header("Location: {$base_url}customer/dashboard.php");
        exit;
      }
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Email tidak ditemukan!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-b from-gray-900 to-black flex items-center justify-center">

  <div class="w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl p-8">

    <div class="text-center mb-8">
      <img src="../assets/logo.png" class="w-24 mx-auto mb-4">
      <h1 class="text-3xl font-bold text-white">Welcome Back</h1>
      <p class="text-white/70 text-sm">Login ke sistem DigiPlan</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3 bg-red-500/20 border border-red-500/30 text-red-300 rounded-xl">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <label class="text-white/80 text-sm">Email</label>
        <input type="email" name="email" required
          class="mt-1 w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/40 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="text-white/80 text-sm">Password</label>
        <input type="password" name="password" required
          class="mt-1 w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/40 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div class="flex justify-between text-sm">
        <a href="forgot-password.php" class="text-indigo-400 hover:text-indigo-300">Lupa password?</a>
      </div>

      <button name="login"
        class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl shadow-lg transform hover:scale-[1.02] transition-all">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-white/70 text-sm">
      Belum punya akun?
      <a href="register.php" class="text-indigo-400 hover:text-indigo-300 font-medium">Daftar</a>
    </p>

  </div>
</body>

</html>