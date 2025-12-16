<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

$admin_id = $_SESSION['user_id'];

if (isset($_POST['tambah_barang'])) {

  $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
  $merk  = mysqli_real_escape_string($conn, $_POST['merk']);
  $warna = mysqli_real_escape_string($conn, $_POST['warna']);
  $stok  = (int) $_POST['stok'];
  $harga = (float) $_POST['harga'];

  $insert = mysqli_query($conn, "
    INSERT INTO barang (nama_barang, merk, warna, stok, harga)
    VALUES ('$nama_barang', '$merk', '$warna', '$stok', '$harga')
  ");

  if ($insert) {

    insertNotifikasiDB(
      $conn,
      $admin_id,
      null,
      "Admin menambahkan barang baru: $nama_barang ($merk - $warna), stok $stok."
    );

    header("Location: item.php?success=item_added");
    exit;
  }

  header("Location: item.php?error=add_failed");
  exit;
}

header('Location: item.php');
exit;
