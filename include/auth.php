<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['log']) || $_SESSION['log'] !== true) {
  // Belum login, arahkan ke login
  header('Location: login.php');
  exit;
}

// Kalau halaman ini punya batasan role, kita bisa validasi di sini
function cek_role($allowed_roles = [])
{
  if (!in_array($_SESSION['role'], $allowed_roles)) {
    // Jika role tidak diperbolehkan, arahkan sesuai rolenya
    switch ($_SESSION['role']) {
      case 'super_admin':
        header('Location: superadmin_dashboard.php');
        break;
      case 'admin':
        header('Location: admin_dashboard.php');
        break;
      default:
        header('Location: customer_dashboard.php');
        break;
    }
    exit;
  }
}
