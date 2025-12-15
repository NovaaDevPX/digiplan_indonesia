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
</head>

<body class="bg-gray-900 text-white p-10">
  <h1 class="text-3xl font-bold mb-6">Invoice Saya</h1>

  <table class="w-full bg-white/10 rounded-xl overflow-hidden">
    <thead class="bg-white/20">
      <tr>
        <th class="p-4">Invoice</th>
        <th class="p-4">Total</th>
        <th class="p-4">Status</th>
        <th class="p-4">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $invoice->fetch_assoc()): ?>
        <tr class="border-t border-white/10">
          <td class="p-4"><?= $row['nomor_invoice'] ?></td>
          <td class="p-4">Rp <?= number_format($row['total']) ?></td>
          <td class="p-4"><?= ucfirst($row['status']) ?></td>
          <td class="p-4">
            <?php if ($row['status'] == 'belum bayar'): ?>
              <a href="invoice-detail.php?id=<?= $row['id_invoice'] ?>"
                class="px-4 py-2 bg-green-600 rounded-lg">
                Lihat Detail
              </a>
            <?php else: ?>
              <span class="text-green-400">Lunas</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>

</html>