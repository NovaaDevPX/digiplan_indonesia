<?php
require '../../include/conn.php';
require '../../include/auth.php';
cek_role(['admin']);

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
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <?php include '../../include/layouts/sidebar-admin.php'; ?>

  <main class="ml-64 p-10">

    <h1 class="text-3xl font-bold mb-6">Laporan Pengadaan Barang</h1>

    <!-- FILTER -->
    <form method="GET" class="bg-white/10 p-4 rounded-xl mb-6 grid grid-cols-4 gap-4">
      <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="p-2 rounded bg-gray-800">
      <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="p-2 rounded bg-gray-800">

      <select name="status" class="p-2 rounded bg-gray-800">
        <option value="">Semua Status</option>
        <option value="diproses" <?= ($status == 'diproses') ? 'selected' : '' ?>>Diproses</option>
        <option value="selesai" <?= ($status == 'selesai') ? 'selected' : '' ?>>Selesai</option>
        <option value="dibatalkan" <?= ($status == 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
      </select>

      <button class="bg-blue-600 rounded px-4 hover:bg-blue-700">
        Filter
      </button>
    </form>

    <!-- EXPORT PDF -->
    <div class="flex justify-end mb-4">
      <a href="procurement-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>"
        target="_blank"
        class="bg-red-600 px-4 py-2 rounded hover:bg-red-700 text-sm">
        Export PDF
      </a>
    </div>

    <!-- TABLE -->
    <div class="bg-white/10 rounded-xl p-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/20">
            <th>Kode</th>
            <th>Permintaan</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Supplier</th>
            <th>Harga Satuan</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($data)): ?>
            <tr class="border-b border-white/10">
              <td><?= $row['kode_pengadaan'] ?></td>
              <td><?= $row['kode_permintaan'] ?></td>
              <td><?= $row['nama_barang'] ?></td>
              <td><?= $row['jumlah'] ?></td>
              <td><?= $row['supplier'] ?></td>
              <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
              <td>Rp <?= number_format($row['harga_total'], 0, ',', '.') ?></td>
              <td><?= $row['status_pengadaan'] ?></td>
              <td><?= $row['tanggal_pengadaan'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</body>

</html>