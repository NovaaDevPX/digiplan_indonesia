<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/midtrans-config.php';

$id = (int)$_GET['id'];

$invoice = $conn->query("
  SELECT * FROM invoice WHERE id_invoice = $id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
  <title>Pembayaran Invoice</title>
  <script src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="SB-Mid-client-XXXXXXXX"></script>
</head>

<body class="bg-gray-900 text-white p-10">

  <h1 class="text-2xl mb-4">Invoice <?= $invoice['nomor_invoice'] ?></h1>
  <p>Total: Rp <?= number_format($invoice['total']) ?></p>

  <button id="pay-button"
    class="mt-6 px-6 py-3 bg-blue-600 rounded-lg">
    Bayar Sekarang
  </button>

  <script>
    document.getElementById('pay-button').onclick = function() {
      fetch('payment-process.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            invoice_id: <?= $invoice['id_invoice'] ?>
          })
        })
        .then(res => res.json())
        .then(data => {
          window.snap.pay(data.snapToken);
        });
    };
  </script>

</body>

</html>