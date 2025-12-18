<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$user_id = $_SESSION['user_id'];

/**
 * Ambil daftar invoice milik customer
 */
$invoice = $conn->query("
  SELECT 
    i.*,
    d.kode_distribusi,
    p.nama_barang,
    p.merk,
    p.warna,
    p.jumlah
  FROM invoice i
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  WHERE p.user_id = $user_id
  ORDER BY i.id_invoice DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Invoice Saya</title>

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
        <h1 class="text-4xl font-bold text-white mb-2">Invoice Saya</h1>
        <p class="text-white/80">Kelola dan lihat status pembayaran invoice Anda.</p>
      </div>

      <!-- TABLE -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl overflow-x-auto">
        <table class="w-full border-collapse rounded-xl overflow-hidden">

          <thead>
            <tr class="bg-white/20 text-white">
              <th class="p-4 text-left">Invoice</th>
              <th class="p-4 text-left">Barang</th>
              <th class="p-4 text-left">Jumlah</th>
              <th class="p-4 text-left">Total</th>
              <th class="p-4 text-left">Status</th>
              <th class="p-4 text-left">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-white/10">
            <?php while ($row = $invoice->fetch_assoc()): ?>

              <tr class="hover:bg-white/5 transition-colors duration-200">

                <!-- INVOICE -->
                <td class="p-4 text-white/90 font-medium">
                  <?= $row['nomor_invoice'] ?>
                  <div class="text-xs text-white/50">
                    <?= $row['kode_distribusi'] ?>
                  </div>
                </td>

                <!-- BARANG -->
                <td class="p-4 text-white/90">
                  <?= $row['nama_barang'] ?>
                  <div class="text-xs text-white/50">
                    <?= $row['merk'] ?> • <?= $row['warna'] ?>
                  </div>
                </td>

                <!-- JUMLAH -->
                <td class="p-4 text-white/90">
                  <?= $row['jumlah'] ?>
                </td>

                <!-- TOTAL -->
                <td class="p-4 text-white/90">
                  Rp <?= number_format($row['total'], 0, ',', '.') ?>
                </td>

                <!-- STATUS -->
                <td class="p-4">
                  <?php
                  if ($row['status'] === 'lunas') {
                    $bg = 'bg-green-500/20';
                    $text = 'text-green-300';
                    $border = 'border-green-500/30';
                    $label = 'Lunas';
                  } elseif ($row['status'] === 'belum bayar') {
                    $bg = 'bg-yellow-500/20';
                    $text = 'text-yellow-300';
                    $border = 'border-yellow-500/30';
                    $label = 'Belum Dibayar';
                  } else {
                    $bg = 'bg-red-500/20';
                    $text = 'text-red-300';
                    $border = 'border-red-500/30';
                    $label = ucfirst($row['status']);
                  }
                  ?>

                  <span class="px-3 py-1 rounded-lg <?= $bg ?> <?= $text ?> text-xs font-semibold border <?= $border ?>">
                    <?= $label ?>
                  </span>
                </td>

                <!-- AKSI -->
                <td class="p-4">
                  <a href="invoice-detail.php?id=<?= $row['id_invoice'] ?>"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-lg shadow-md transform hover:scale-105 transition-all duration-200">
                    Lihat Detail
                  </a>
                </td>

              </tr>

            <?php endwhile; ?>
          </tbody>

        </table>
      </div>

    </div>

  </main>

</body>

</html>