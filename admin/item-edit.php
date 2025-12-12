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
    echo "<script>alert('Barang berhasil diupdate!'); window.location='procurement.php';</script>";
  } else {
    echo "<script>alert('Gagal update barang!'); window.location='procurement.php';</script>";
  }
} else {
  header('Location: procurement.php');
}
