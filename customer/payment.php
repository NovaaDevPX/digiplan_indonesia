<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/midtrans-config.php';
require '../include/env.php';


$id = (int)$_GET['id'];

$invoice = $conn->query("
  SELECT * FROM invoice WHERE id_invoice = $id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
  <title>Pembayaran Invoice</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          backdropBlur: {
            'xs': '2px',
          }
        }
      }
    }
  </script>
  <script
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="<?= getenv('MIDTRANS_CLIENT_KEY') ?>">
  </script>
  <meta http-equiv="Content-Security-Policy"
    content="
  script-src
  'self'
  'unsafe-inline'
  'unsafe-eval'
  https://app.sandbox.midtrans.com
  https://api.sandbox.midtrans.com
">

</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white min-h-screen">
  <?php include '../include/layouts/sidebar-customer.php'; ?>

  <main class="ml-64 p-10 flex-1">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Pembayaran Invoice</h1>
        <p class="text-white/80">Lakukan pembayaran untuk invoice dengan mudah dan aman.</p>
      </div>

      <!-- INVOICE DETAILS -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl mb-8">
        <h2 class="text-2xl font-semibold text-white mb-4">Detail Invoice</h2>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-white/90">Nomor Invoice:</span>
            <span class="text-white font-medium"><?= $invoice['nomor_invoice'] ?></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-white/90">Total Pembayaran:</span>
            <span class="text-2xl font-bold text-green-400">Rp <?= number_format($invoice['total']) ?></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-white/90">Status:</span>
            <span class="px-3 py-1 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-500/30">
              <?= ucfirst($invoice['status']) ?>
            </span>
          </div>
        </div>
      </div>

      <?php if ($invoice['status'] === 'belum bayar'): ?>
        <!-- PAYMENT BUTTON -->
        <div class="text-center">
          <button id="pay-button"
            class="px-8 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold text-lg">
            <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            Bayar Sekarang
          </button>
        </div>
      <?php endif; ?>
    </div>

  </main>

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
          console.log('SNAP TOKEN:', data.snapToken);

          if (!data.snapToken) {
            alert('Snap token gagal dibuat');
            return;
          }

          console.log('SNAP:', window.snap);

          window.snap.pay(data.snapToken, {
            onSuccess: function(result) {
              console.log('SUCCESS', result);
              location.reload();
            },
            onPending: function(result) {
              console.log('PENDING', result);
            },
            onError: function(result) {
              console.log('ERROR', result);
              alert('Pembayaran gagal');
            }
          });
        })
        .catch(err => {
          console.error(err);
          alert('Error saat memproses pembayaran');
        });
    };
  </script>
</body>

</html>