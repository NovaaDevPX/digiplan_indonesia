<?php
require 'function.php';
require 'cek.php';
cek_role(['customer']);

$id = $_GET['id'] ?? 0;
$q = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'");
$p = mysqli_fetch_assoc($q);

if (!$p) die("Produk tidak ditemukan");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $p['nama_produk']; ?></title>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="sb-nav-fixed">

<div class="container mt-4">

    <div class="card shadow">
        <div class="row g-0">

            <div class="col-md-5">
                <img src="uploads/<?= $p['gambar']; ?>" 
                     class="img-fluid rounded-start" 
                     style="object-fit:cover; width:100%; height:100%;">
            </div>

            <div class="col-md-7 p-4">
                <h2><?= $p['nama_produk']; ?></h2>

                <h4 class="text-primary fw-bold mt-3">
                    Rp <?= number_format($p['harga']); ?>
                </h4>

                <p class="mt-3"><?= nl2br($p['deskripsi']); ?></p>

                <a href="proses_ajukan.php?id=<?= $p['id']; ?>"
                   class="btn btn-success btn-lg mt-3">
                   Ajukan Permintaan Barang
                </a>
                <form action="add_cart.php" method="POST">
    <input type="hidden" name="produk_id" value="<?= $p['id']; ?>">
    <button class="btn btn-sm btn-success mt-2">
        <i class="fas fa-cart-plus"></i> Keranjang
    </button>
</form>
            </div>

        </div>
    </div>

</div>

</body>
</html>
