<?php
require '../include/conn.php';

if (isset($_POST['tambah_barang'])) {
  $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
  $merk = mysqli_real_escape_string($conn, $_POST['merk']);
  $warna = mysqli_real_escape_string($conn, $_POST['warna']);
  $stok = (int) $_POST['stok'];
  $harga = (float) $_POST['harga'];

  $insert = mysqli_query($conn, "INSERT INTO barang (nama_barang, merk, warna, stok, harga) 
        VALUES ('$nama_barang', '$merk', '$warna', '$stok', '$harga')");

  if ($insert) {
    header("Location: item.php?success=item_added");
    exit;
  } else {
    header("Location: item.php?error=add_failed");
    exit;
  }
} else {
  header('Location: item.php');
  exit;
}
