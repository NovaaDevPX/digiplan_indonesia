<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['super_admin']);

$admin_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {

  $id = (int) $_GET['id'];

  // Ambil nama barang sebelum delete
  $qBarang = mysqli_query($conn, "SELECT nama_barang FROM barang WHERE id='$id'");
  $barang = mysqli_fetch_assoc($qBarang);
  $nama_barang = $barang['nama_barang'] ?? 'Barang tidak diketahui';

  $delete = mysqli_query($conn, "DELETE FROM barang WHERE id='$id'");

  if ($delete) {

    insertNotifikasiDB(
      $conn,
      $admin_id,
      null,
      "Super Admin menghapus barang : $nama_barang."
    );

    header("Location: item.php?success=item_deleted");
    exit;
  }

  header("Location: item.php?error=delete_failed");
  exit;
}

header("Location: item.php");
exit;
