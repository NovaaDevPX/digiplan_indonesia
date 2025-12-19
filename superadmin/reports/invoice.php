<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin']);

/* FILTER */
$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';
$customer  = $_GET['customer']  ?? '';

$where = "WHERE i.deleted_at IS NULL";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND i.tanggal_invoice BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND i.status = '$status'";
}

if ($customer) {
  $where .= " AND u.id = '$customer'";
}

/* LIST CUSTOMER */
$customerList = mysqli_query($conn, "SELECT id, name FROM users WHERE role_id = 1 AND deleted_at IS NULL");

/* QUERY */
$query = "
SELECT 
  i.*,
  d.kode_distribusi,
  pm.kode_permintaan,
  u.name AS customer,
  p.metode,
  p.tanggal_bayar,
  p.status AS status_bayar
FROM invoice i
JOIN distribusi_barang d ON i.distribusi_id = d.id
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
JOIN users u ON pm.user_id = u.id
LEFT JOIN pembayaran p ON p.id_invoice = i.id_invoice
$where
ORDER BY i.tanggal_invoice DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Invoice & Pembayaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">

  <?php include '../../include/layouts/sidebar-superadmin.php'; ?>

  <main class="ml-64 p-10">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold mb-2">Laporan Invoice & Pembayaran</h1>
        <p class="text-white/70">
          Menampilkan data invoice serta status pembayaran customer.
        </p>
      </div>

      <!-- FILTER -->
      <form method="GET"
        class="grid grid-cols-1 md:grid-cols-5 gap-6 backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">

        <div>
          <label class="text-sm text-white/80">Tanggal Awal</label>
          <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
        </div>

        <div>
          <label class="text-sm text-white/80">Tanggal Akhir</label>
          <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
        </div>

        <div>
          <label class="text-sm text-white/80">Status Invoice</label>
          <select name="status"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white whitespace-nowrap">
            <option value="" class="text-black">Semua</option>
            <option value="lunas" class="text-black" <?= $status == 'lunas' ? 'selected' : '' ?>>Lunas</option>
            <option value="belum bayar" class="text-black" <?= $status == 'belum bayar' ? 'selected' : '' ?>>Belum Bayar</option>
            <option value="dibatalkan" class="text-black" <?= $status == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
          </select>
        </div>

        <!-- FILTER CUSTOMER -->
        <div>
          <label class="text-sm text-white/80">Customer</label>
          <select name="customer" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
            <option value="" class="text-black">Semua Customer</option>
            <?php while ($c = mysqli_fetch_assoc($customerList)): ?>
              <option value="<?= $c['id'] ?>" class="text-black"
                <?= ($customer == $c['id']) ? 'selected' : '' ?>>
                <?= $c['name'] ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="flex items-end">
          <button class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg hover:scale-105 transition">
            Tampilkan
          </button>
        </div>
      </form>

      <!-- EXPORT -->
      <div class="flex justify-end mb-6">
        <a target="_blank"
          href="invoice-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>&customer=<?= $customer ?>"
          class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl shadow-lg hover:scale-105 transition">
          Export PDF
        </a>
      </div>

      <!-- TABLE -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-white/20">
              <th class="p-4 text-left">Invoice</th>
              <th class="p-4 text-left">Customer</th>
              <th class="p-4 text-left">Permintaan</th>
              <th class="p-4 text-left">Distribusi</th>
              <th class="p-4 text-left">Total</th>
              <th class="p-4 text-left">Metode</th>
              <th class="p-4 text-left">Status</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">

            <?php if (mysqli_num_rows($result) == 0): ?>
              <tr>
                <td colspan="8" class="text-center py-6 text-white/60">
                  Data tidak ditemukan
                </td>
              </tr>
            <?php endif; ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-white/5">
                <td class="p-4 font-medium"><?= $row['nomor_invoice'] ?></td>
                <td class="p-4"><?= $row['customer'] ?></td>
                <td class="p-4"><?= $row['kode_permintaan'] ?></td>
                <td class="p-4"><?= $row['kode_distribusi'] ?></td>
                <td class="p-4">Rp <?= number_format($row['total']) ?></td>
                <td class="p-4"><?= $row['metode'] ?? '-' ?></td>
                <td class="p-4">
                  <span class="px-3 py-1 whitespace-nowrap rounded-lg text-xs
            <?= $row['status'] == 'lunas'
                ? 'bg-green-500/20 text-green-300'
                : ($row['status'] == 'belum bayar'
                  ? 'bg-yellow-500/20 text-yellow-300'
                  : 'bg-red-500/20 text-red-300') ?>">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td class="p-4 text-center">
                  <a href="../single-report-pdf/invoice.php?id=<?= $row['distribusi_id'] ?>"
                    target="_blank"
                    class="inline-flex items-center px-3 py-2
            bg-gradient-to-r from-indigo-500 to-blue-600
            hover:from-indigo-600 hover:to-blue-700
            text-white text-xs font-semibold rounded-lg
            shadow-md transform hover:scale-105 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v12m0 0l-3-3m3 3l3-3m5 7H4" />
                    </svg>
                    Import
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