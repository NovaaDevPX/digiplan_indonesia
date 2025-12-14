<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$tgl_awal  = $_GET['tgl_awal']  ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status    = $_GET['status']    ?? '';

$where = "WHERE 1=1";

if ($tgl_awal && $tgl_akhir) {
  $where .= " AND pb.created_at BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";
}

if ($status) {
  $where .= " AND pb.status = '$status'";
}

$query = "
SELECT pb.*, u.name AS customer
FROM permintaan_barang pb
JOIN users u ON pb.user_id = u.id
$where
ORDER BY pb.created_at DESC
";

$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Permintaan Barang</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <?php include '../include/layouts/sidebar-admin.php'; ?>

  <main class="ml-64 p-10">

    <h1 class="text-3xl font-bold mb-6">Laporan Permintaan Barang</h1>

    <!-- FILTER -->
    <form method="GET" class="bg-white/10 p-4 rounded-xl mb-6 grid grid-cols-4 gap-4">
      <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="p-2 rounded bg-gray-800">
      <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="p-2 rounded bg-gray-800">

      <select name="status" class="p-2 rounded bg-gray-800">
        <option value="">Semua Status</option>
        <?php
        $statusList = ['diajukan', 'disetujui', 'ditolak', 'dalam_pengadaan', 'siap_distribusi', 'selesai'];
        foreach ($statusList as $s):
        ?>
          <option value="<?= $s ?>" <?= ($status == $s) ? 'selected' : '' ?>>
            <?= ucfirst(str_replace('_', ' ', $s)) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button class="bg-blue-600 rounded px-4 hover:bg-blue-700">
        Filter
      </button>
    </form>

    <!-- EXPORT PDF -->
    <div class="flex justify-end mb-4">
      <a href="report-item-request-pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>&status=<?= $status ?>"
        target="_blank"
        class="bg-red-600 px-4 py-2 rounded hover:bg-red-700 text-sm">
        Export PDF
      </a>
    </div>

    <!-- TABLE -->
    <div class="bg-white/10 rounded-xl p-4">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/20">
            <th>Kode</th>
            <th>Customer</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($data)): ?>
            <tr class="border-b border-white/10">
              <td><?= $row['kode_permintaan'] ?></td>
              <td><?= $row['customer'] ?></td>
              <td><?= $row['nama_barang'] ?></td>
              <td><?= $row['jumlah'] ?></td>
              <td><?= $row['status'] ?></td>
              <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</body>

</html>