<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$data = mysqli_query($conn, "
  SELECT 
    i.*,
    p.nama_barang,
    p.jumlah,
    u.name AS customer
  FROM invoice i
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE i.id_invoice = $id
  AND p.user_id = $user_id
")->fetch_assoc();

if (!$data) {
  die('Invoice tidak ditemukan');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Detail Invoice</title>
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
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white min-h-screen">
  <?php include '../include/layouts/sidebar-customer.php'; ?>

  <main class="ml-64 p-10 flex-1">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Detail Invoice</h1>
        <p class="text-white/80">Lihat detail lengkap invoice Anda dan lakukan pembayaran jika diperlukan.</p>
      </div>

      <!-- INVOICE DETAILS -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
        <h2 class="text-2xl font-semibold text-white mb-6">Invoice <?= $data['nomor_invoice'] ?></h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="space-y-4">
            <div>
              <p class="text-white/70 text-sm">Customer</p>
              <p class="text-white font-semibold text-lg"><?= $data['customer'] ?></p>
            </div>
            <div>
              <p class="text-white/70 text-sm">Tanggal</p>
              <p class="text-white text-lg"><?= $data['tanggal_invoice'] ?></p>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-white/70 text-sm">Barang</p>
              <p class="text-white font-semibold text-lg"><?= $data['nama_barang'] ?></p>
            </div>
            <div>
              <p class="text-white/70 text-sm">Jumlah</p>
              <p class="text-white text-lg"><?= $data['jumlah'] ?></p>
            </div>
          </div>
        </div>

        <hr class="border-white/20 my-6">

        <div class="flex justify-between items-center mb-8">
          <span class="text-white/90 text-lg">Total Bayar</span>
          <span class="text-2xl font-bold text-green-400">
            Rp <?= number_format($data['total'], 0, ',', '.') ?>
          </span>
        </div>

        <div class="flex gap-4">
          <a href="invoice-pdf.php?id=<?= $data['id_invoice'] ?>"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Unduh Invoice
          </a>

          <?php if ($data['status'] == 'belum bayar'): ?>
            <a href="payment.php?id=<?= $data['id_invoice'] ?>"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
              </svg>
              Bayar Sekarang
            </a>
          <?php endif; ?>
        </div>

      </div>

    </div>

  </main>
</body>

</html>