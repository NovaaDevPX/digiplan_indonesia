<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

/* filter */
$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND d.tanggal_kirim BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

if ($status) {
  $where .= " AND d.status_distribusi = '$status'";
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
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">

  <?php include '../include/layouts/sidebar-admin.php'; ?>

  <main class="ml-64 p-10">

    <!-- HEADER -->
    <div class="bg-white/10 backdrop-blur p-6 rounded-xl mb-6">
      <h1 class="text-3xl font-bold mb-2">Laporan Distribusi Barang</h1>
      <p class="text-white/70 text-sm">
        Menampilkan data distribusi barang berdasarkan periode dan status.
      </p>
    </div>

    <!-- FILTER -->
    <form method="GET"
      class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white/10 p-4 rounded-xl mb-6">

      <div>
        <label class="text-sm">Tanggal Awal</label>
        <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>"
          class="w-full p-2 rounded bg-gray-800">
      </div>

      <div>
        <label class="text-sm">Tanggal Akhir</label>
        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>"
          class="w-full p-2 rounded bg-gray-800">
      </div>

      <div>
        <label class="text-sm">Status</label>
        <select name="status" class="w-full p-2 rounded bg-gray-800">
          <option value="">Semua</option>
          <option value="dikirim" <?= $status == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
          <option value="diterima" <?= $status == 'diterima' ? 'selected' : '' ?>>Diterima</option>
        </select>
      </div>

      <div class="flex items-end">
        <button class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 w-full">
          Tampilkan
        </button>
      </div>
    </form>

    <div class="flex justify-end mb-4">
      <a href="report-distribution-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>"
        target="_blank"
        class="bg-red-600 px-4 py-2 rounded hover:bg-red-700 text-white text-sm">
        Export PDF
      </a>
    </div>


    <!-- TABLE -->
    <div class="bg-white/10 backdrop-blur p-6 rounded-xl overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/20">
            <th class="py-2">Kode</th>
            <th>Permintaan</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Kurir</th>
            <th>Resi</th>
            <th>Tgl Kirim</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          <?php if (mysqli_num_rows($result) == 0): ?>
            <tr>
              <td colspan="8" class="text-center py-6 text-white/60">
                Data tidak ditemukan
              </td>
            </tr>
          <?php endif; ?>

          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr class="border-b border-white/10">
              <td class="py-2"><?= $row['kode_distribusi'] ?></td>
              <td><?= $row['kode_permintaan'] ?></td>
              <td><?= $row['nama_barang'] ?></td>
              <td><?= $row['jumlah'] ?></td>
              <td><?= $row['kurir'] ?></td>
              <td><?= $row['no_resi'] ?></td>
              <td><?= $row['tanggal_kirim'] ?></td>
              <td>
                <span class="px-2 py-1 rounded text-xs
      <?= $row['status_distribusi'] == 'diterima' ? 'bg-green-600' : 'bg-yellow-600' ?>">
                  <?= $row['status_distribusi'] ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</body>

</html>