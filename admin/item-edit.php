<?php
require '../include/conn.php';

if (isset($_POST['edit_barang'])) {

  $id = (int) $_POST['id'];
  $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
  $merk = mysqli_real_escape_string($conn, $_POST['merk']);
  $warna = mysqli_real_escape_string($conn, $_POST['warna']);
  $stok = (int) $_POST['stok'];
  $harga = (float) $_POST['harga'];

  $update = mysqli_query($conn, "UPDATE barang SET 
        nama_barang='$nama_barang', merk='$merk', warna='$warna', stok='$stok', harga='$harga'
        WHERE id='$id'");

  if ($update) {
    header("Location: item.php?success=item_updated");
    exit;
  } else {
    header("Location: item.php?error=update_failed");
    exit;
  }
} else {
  header('Location: item.php');
  exit;
}
