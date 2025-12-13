<?php

// ---- EDIT PRODUK ----
if (isset($_POST['edit'])) {

  $id = $_POST['id'];
  $nama = $_POST['nama_produk'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];

  if (!empty($_FILES['gambar']['name'])) {

    $filename = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    $newname = time() . '_' . $filename;

    // simpan file baru
    $target = "../uploads/" . $newname;
    move_uploaded_file($tmp_name, $target);

    // hapus gambar lama
    $cek = mysqli_query($conn, "SELECT gambar FROM produk WHERE id='$id'");
    $g = mysqli_fetch_assoc($cek)['gambar'];

    $old_path = "../uploads/" . $g;
    if (file_exists($old_path)) unlink($old_path);

    $update_gambar = ", gambar='$newname'";
  } else {
    $update_gambar = "";
  }

  mysqli_query($conn, "UPDATE produk SET 
      nama_produk='$nama', 
      harga='$harga',
      deskripsi='$deskripsi'
      $update_gambar
    WHERE id='$id'");

  header("Location: products.php?success=updated");
  exit;
}
