<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['super_admin']);

$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pg.tanggal_pengadaan BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND pg.status_pengadaan = '$status'";
}

$query = "
SELECT pg.*, 
       pm.kode_permintaan,
       u.name AS admin
FROM pengadaan_barang pg
JOIN permintaan_barang pm ON pg.permintaan_id = pm.id
JOIN users u ON pg.admin_id = u.id
$where
ORDER BY pg.tanggal_pengadaan DESC
";

$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Pengadaan Barang</title>
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

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <?php include '../../include/layouts/sidebar-superadmin.php'; ?>

  <main class="ml-64 p-10 flex-1">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Laporan Pengadaan Barang</h1>
        <p class="text-white/80">Menampilkan data pengadaan barang berdasarkan periode dan status.</p>
      </div>

      <!-- FILTER -->
      <form method="GET" class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Tanggal Awal</label>
          <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Tanggal Akhir</label>
          <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Status</label>
          <select name="status" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
            <option value="" class="text-black">Semua Status</option>
            <option value="diproses" class="text-black" <?= ($status == 'diproses') ? 'selected' : '' ?>>Diproses</option>
            <option value="selesai" class="text-black" <?= ($status == 'selesai') ? 'selected' : '' ?>>Selesai</option>
            <option value="dibatalkan" class="text-black" <?= ($status == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
          </select>
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
            Filter
          </button>
        </div>
      </form>

      <!-- EXPORT PDF -->
      <div class="flex justify-end mb-6">
        <a href="procurement-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>"
          target="_blank"
          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export PDF
        </a>
      </div>

      <!-- TABLE -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl overflow-x-auto">
        <table class="w-full border-collapse rounded-xl overflow-hidden">
          <thead>
            <tr class="bg-white/20 text-white">
              <th class="p-4 text-left">Kode</th>
              <th class="p-4 text-left">Permintaan</th>
              <th class="p-4 text-left">Barang</th>
              <th class="p-4 text-left">Jumlah</th>
              <th class="p-4 text-left">Supplier</th>
              <th class="p-4 text-left">Harga Satuan</th>
              <th class="p-4 text-left">Total</th>
              <th class="p-4 text-left">Status</th>
              <th class="p-4 text-left">Tanggal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            <?php while ($row = mysqli_fetch_assoc($data)): ?>
              <tr class="hover:bg-white/5 transition-colors duration-200">
                <td class="p-4 text-white/90 font-medium"><?= $row['kode_pengadaan'] ?></td>
                <td class="p-4 text-white/90"><?= $row['kode_permintaan'] ?></td>
                <td class="p-4 text-white/90"><?= $row['nama_barang'] ?></td>
                <td class="p-4 text-white/90"><?= $row['jumlah'] ?></td>
                <td class="p-4 text-white/90"><?= $row['supplier'] ?></td>
                <td class="p-4 text-white/90">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                <td class="p-4 text-white/90">Rp <?= number_format($row['harga_total'], 0, ',', '.') ?></td>
                <td class="p-4 capitalize">
                  <?php
                  switch ($row['status_pengadaan']) {
                    case 'diproses':
                      echo "<span class='px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs font-semibold border border-yellow-500/30 whitespace-nowrap'>
              Diproses
            </span>";
                      break;

                    case 'selesai':
                      echo "<span class='px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-500/30 whitespace-nowrap'>
              Selesai
            </span>";
                      break;

                    case 'dibatalkan':
                      echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs font-semibold border border-red-500/30 whitespace-nowrap'>
              Dibatalkan
            </span>";
                      break;

                    default:
                      echo "<span class='px-3 py-1 bg-gray-500/20 text-gray-300 rounded-lg text-xs font-semibold border border-gray-500/30 whitespace-nowrap'>
              Tidak diketahui
            </span>";
                  }
                  ?>
                </td>

                <td class="p-4 text-white/90"><?= $row['tanggal_pengadaan'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>

  </main>
</body>

</html>