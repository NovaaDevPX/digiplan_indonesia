<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$user_id = $_SESSION['user_id'];

$invoice = $conn->query("
  SELECT i.*, d.kode_distribusi
  FROM invoice i
  JOIN distribusi_barang d ON i.distribusi_id = d.id
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  WHERE p.user_id = $user_id
  ORDER BY i.id_invoice DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
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
              <th class="p-4 text-left">Total</th>
              <th class="p-4 text-left">Status</th>
              <th class="p-4 text-left">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            <?php while ($row = $invoice->fetch_assoc()): ?>
              <tr class="hover:bg-white/5 transition-colors duration-200">
                <td class="p-4 text-white/90 font-medium"><?= $row['nomor_invoice'] ?></td>
                <td class="p-4 text-white/90">Rp <?= number_format($row['total']) ?></td>
                <td class="p-4">
                  <span class="px-3 py-1 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-500/30">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td class="p-4">
                  <?php if ($row['status'] == 'belum bayar'): ?>
                    <a href="invoice-detail.php?id=<?= $row['id_invoice'] ?>"
                      class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg shadow-md transform hover:scale-105 transition-all duration-200">
                      Lihat Detail
                    </a>
                  <?php else: ?>
                    <span class="text-green-400 font-medium">Lunas</span>
                  <?php endif; ?>
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