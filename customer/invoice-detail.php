<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$id = (int) ($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$data = mysqli_query($conn, "
  SELECT 
    i.*,
    p.nama_barang,
    p.jumlah,
    u.name AS customer,
    d.status_distribusi
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
        <p class="text-white/80">
          Lihat detail lengkap invoice Anda dan lakukan pembayaran jika diperlukan.
        </p>
      </div>

      <!-- DETAIL INVOICE -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">

        <h2 class="text-2xl font-semibold text-white mb-4">
          Invoice <?= htmlspecialchars($data['nomor_invoice']) ?>
        </h2>

        <!-- ================= STATUS ================= -->
        <div class="flex flex-wrap gap-3 mb-6">
          <?php
          // STATUS INVOICE
          switch ($data['status']) {
            case 'belum bayar':
              $invoiceClass = 'bg-yellow-500/20 text-yellow-300';
              $invoiceText  = 'Belum Dibayar';
              break;
            case 'lunas':
              $invoiceClass = 'bg-emerald-500/20 text-emerald-300';
              $invoiceText  = 'Lunas';
              break;
            case 'dibatalkan':
              $invoiceClass = 'bg-red-500/20 text-red-300';
              $invoiceText  = 'Dibatalkan';
              break;
          }
          ?>

          <span class="px-4 py-1 rounded-full text-sm font-semibold <?= $invoiceClass ?>">
            <?= $invoiceText ?>
          </span>
        </div>

        <!-- ================= INFORMASI ================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="space-y-4">
            <div>
              <p class="text-white/70 text-sm">Customer</p>
              <p class="text-white font-semibold text-lg">
                <?= htmlspecialchars($data['customer']) ?>
              </p>
            </div>
            <div>
              <p class="text-white/70 text-sm">Tanggal Invoice</p>
              <p class="text-white text-lg"><?= $data['tanggal_invoice'] ?></p>
            </div>
            <div>
              <p class="text-white/70 text-sm">Jatuh Tempo</p>
              <p class="text-white text-lg"><?= $data['jatuh_tempo'] ?></p>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <p class="text-white/70 text-sm">Barang</p>
              <p class="text-white font-semibold text-lg">
                <?= htmlspecialchars($data['nama_barang']) ?>
              </p>
            </div>
            <div>
              <p class="text-white/70 text-sm">Jumlah</p>
              <p class="text-white text-lg"><?= $data['jumlah'] ?></p>
            </div>
          </div>
        </div>

        <hr class="border-white/20 my-6">

        <!-- TOTAL -->
        <div class="flex justify-between items-center mb-8">
          <span class="text-white/90 text-lg">Total Bayar</span>
          <span class="text-2xl font-bold text-green-400">
            Rp <?= number_format($data['total'], 0, ',', '.') ?>
          </span>
        </div>

        <!-- AKSI -->
        <div class="flex flex-wrap gap-4">

          <a href="invoice-pdf.php?id=<?= $data['id_invoice'] ?>"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
            Unduh Invoice
          </a>

          <?php if ($data['status'] === 'belum bayar'): ?>
            <a href="payment.php?id=<?= $data['id_invoice'] ?>"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
              Bayar Sekarang
            </a>
          <?php endif; ?>

        </div>

      </div>

    </div>

  </main>
</body>

</html>