<?php
// midtrans/finish.php

$order_id = $_GET['order_id'] ?? null;
$status   = $_GET['transaction_status'] ?? null;

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Pembayaran Selesai</title>
</head>

<body>
  <h2>Pembayaran Berhasil Diproses</h2>

  <p>Order ID: <b><?= htmlspecialchars($order_id) ?></b></p>
  <p>Status: <b><?= htmlspecialchars($status) ?></b></p>

  <p>Terima kasih telah melakukan pembayaran.</p>

  <a href="../customer/dashboard.php">Kembali ke Dashboard</a>
</body>

</html>