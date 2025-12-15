<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$data = mysqli_query($conn, "
  SELECT 
    i.*,
    p.nama_barang,
    p.jumlah,
    u.name AS customer
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
</head>

<body class="bg-gray-100 text-gray-800">

  <div class="max-w-3xl mx-auto my-10 bg-white p-8 rounded-xl shadow">

    <h1 class="text-2xl font-bold mb-4">Invoice <?= $data['nomor_invoice'] ?></h1>

    <div class="grid grid-cols-2 gap-4 text-sm mb-6">
      <div>
        <p class="text-gray-500">Customer</p>
        <p class="font-semibold"><?= $data['customer'] ?></p>
      </div>
      <div>
        <p class="text-gray-500">Tanggal</p>
        <p><?= $data['tanggal_invoice'] ?></p>
      </div>
      <div>
        <p class="text-gray-500">Barang</p>
        <p><?= $data['nama_barang'] ?></p>
      </div>
      <div>
        <p class="text-gray-500">Jumlah</p>
        <p><?= $data['jumlah'] ?></p>
      </div>
    </div>

    <hr class="my-4">

    <div class="flex justify-between font-semibold">
      <span>Total Bayar</span>
      <span>
        Rp <?= number_format($data['total'], 0, ',', '.') ?>
      </span>
    </div>

    <div class="mt-6 flex gap-3">
      <a href="invoice-pdf.php?id=<?= $data['id_invoice'] ?>"
        class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">
        Unduh Invoice
      </a>

      <?php if ($data['status'] == 'belum bayar'): ?>
        <a href="payment.php?id=<?= $data['id_invoice'] ?>"
          class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">
          Bayar Sekarang
        </a>
      <?php endif; ?>
    </div>

  </div>
</body>

</html>