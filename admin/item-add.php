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
    echo "<script>alert('Barang berhasil ditambahkan!'); window.location='procurement.php';</script>";
  } else {
    echo "<script>alert('Gagal menambahkan barang!'); window.location='procurement.php';</script>";
  }
} else {
  header('Location: procurement.php');
}
