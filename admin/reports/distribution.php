<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

/* filter */
$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';
$customers = mysqli_query($conn, "SELECT id, name FROM users ORDER BY name ASC");

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND d.tanggal_kirim BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND d.status_distribusi = '$status'";
}

$customer = $_GET['customer'] ?? '';
if ($customer) {
  $where .= " AND pm.user_id = '$customer'";
}


$query = "
SELECT d.*, 
       pm.kode_permintaan,
       pm.nama_barang,
       pm.jumlah
FROM distribusi_barang d
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
$where
ORDER BY d.tanggal_kirim DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Distribusi Barang</title>
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

  <?php include '../../include/layouts/sidebar-admin.php'; ?>

  <main class="ml-64 p-10 flex-1">

    <div class="max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Laporan Distribusi Barang</h1>
        <p class="text-white/80">Menampilkan data distribusi barang berdasarkan periode dan status.</p>
      </div>

      <!-- FILTER -->
      <form method="GET"
        class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8 grid grid-cols-1 md:grid-cols-5 gap-6">

        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Tanggal Awal</label>
          <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Tanggal Akhir</label>
          <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
        </div>

        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Status</label>
          <select name="status" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
            <option value="" class="text-black">Semua</option>
            <option value="dikirim" class="text-black" <?= $status == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
            <option value="diterima" class="text-black" <?= $status == 'diterima' ? 'selected' : '' ?>>Diterima</option>
            <option value="dibatalkan" class="text-black" <?= $status == 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-white/90 mb-2">Customer</label>
          <select name="customer"
            class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">

            <option value="" class="text-black">Semua Customer</option>

            <?php while ($cus = mysqli_fetch_assoc($customers)): ?>
              <option value="<?= $cus['id'] ?>" class="text-black"
                <?= $customer == $cus['id'] ? 'selected' : '' ?>>
                <?= $cus['name'] ?>
              </option>
            <?php endwhile; ?>

          </select>
        </div>


        <div class="flex items-end">
          <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
            Tampilkan
          </button>
        </div>
      </form>

      <!-- EXPORT BUTTON -->
      <div class="flex justify-end mb-6">
        <a href="distribution-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>&customer=<?= $customer ?>"
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
              <th class="p-4 text-left">Kurir</th>
              <th class="p-4 text-left">Resi</th>
              <th class="p-4 text-left">Tgl Kirim</th>
              <th class="p-4 text-left">Status</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-white/10">
            <?php if (mysqli_num_rows($result) == 0): ?>
              <tr>
                <td colspan="9" class="text-center py-6 text-white/60">
                  Data tidak ditemukan
                </td>
              </tr>
            <?php endif; ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="hover:bg-white/5 transition-colors duration-200">
                <td class="p-4 text-white/90 font-medium"><?= $row['kode_distribusi'] ?></td>
                <td class="p-4 text-white/90"><?= $row['kode_permintaan'] ?></td>
                <td class="p-4 text-white/90"><?= $row['nama_barang'] ?></td>
                <td class="p-4 text-white/90"><?= $row['jumlah'] ?></td>
                <td class="p-4 text-white/90"><?= $row['kurir'] ?></td>
                <td class="p-4 text-white/90"><?= $row['no_resi'] ?></td>
                <td class="p-4 text-white/90"><?= $row['tanggal_kirim'] ?></td>
                <td class="p-4">
                  <span class="px-3 py-1 rounded-lg text-xs font-semibold
<?= $row['status_distribusi'] == 'diterima'
                ? 'bg-green-500/20 text-green-300 border border-green-500/30'
                : ($row['status_distribusi'] == 'dibatalkan'
                  ? 'bg-red-500/20 text-red-300 border border-red-500/30'
                  : 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30')
?>">
                    <?= ucfirst($row['status_distribusi']) ?>
                  </span>
                </td>
                <td class="p-4 text-center">
                  <a href="../single-report-pdf/distribution.php?kode=<?= $row['kode_distribusi'] ?>"
                    target="_blank"
                    class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-indigo-500 to-blue-600
            hover:from-indigo-600 hover:to-blue-700 text-white text-xs font-semibold
            rounded-lg shadow-md transform hover:scale-105 transition-all duration-200">
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