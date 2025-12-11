<?php

// ---- ADD PRODUK ----
if (isset($_POST['tambah'])) {
  $nama = $_POST['nama_produk'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];

  $filename = $_FILES['gambar']['name'];
  $tmp_name = $_FILES['gambar']['tmp_name'];

  $newname = time() . '_' . $filename;

  // FIX PATH
  move_uploaded_file($tmp_name, "../uploads/" . $newname);

  mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, gambar, deskripsi, created_at) 
        VALUES ('$nama', '$harga', '$newname', '$deskripsi', NOW())");

  header("Location: products.php?success=added");
  exit;
}
