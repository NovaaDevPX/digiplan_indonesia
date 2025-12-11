<?php
require 'function.php';
cek_role(['customer']);
$user_id = $_SESSION['user_id'];

$cart = mysqli_query($conn, 
    "SELECT k.*, p.nama_produk, p.harga 
     FROM keranjang k 
     JOIN produk p ON k.produk_id = p.id
     WHERE k.user_id='$user_id'"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | DigiPlan</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed">
<?php include 'navbar.php'; ?>
<div id="layoutSidenav">
<?php include 'sidebar_customer.php'; ?>

<div id="layoutSidenav_content">
<main class="container-fluid px-4 mt-4">

<h2 class="fw-bold">Konfirmasi Permintaan Barang</h2>

<div class="card shadow-sm mt-4">
    <div class="card-body">

        <h5 class="fw-bold">Daftar Barang:</h5>
        <ul class="list-group mb-3">

        <?php
        $total = 0;
        while ($c = mysqli_fetch_assoc($cart)) { 
            $sub = $c['jumlah'] * $c['harga'];
            $total += $sub;
        ?>
            <li class="list-group-item d-flex justify-content-between">
                <?= $c['nama_produk']; ?> (x<?= $c['jumlah']; ?>)
                <strong>Rp <?= number_format($sub); ?></strong>
            </li>
        <?php } ?>

        </ul>

        <div class="fs-4 fw-bold mb-3 text-end">
            Total: Rp <?= number_format($total); ?>
        </div>

        <form action="checkout_process.php" method="POST">
            <button class="btn btn-lg btn-success w-100">
                Kirim Permintaan Barang
            </button>
        </form>

    </div>
</div>

</main>
</div>
</div>

</body>
</html>
