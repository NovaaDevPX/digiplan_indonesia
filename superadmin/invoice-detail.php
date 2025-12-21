<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

$id = $_GET['id'] ?? null;
if (!$id) {
  header("Location: invoice.php");
  exit;
}

/* =========================
   AMBIL DETAIL INVOICE
========================= */
$stmt = $conn->prepare("
  SELECT
    i.id_invoice,
    i.nomor_invoice,
    i.tanggal_invoice,
    i.jatuh_tempo,
    i.total,
    i.status AS status_invoice,

    d.kode_distribusi,
    d.tanggal_terima,
    d.alamat_pengiriman,
    d.kurir,
    d.no_resi,
    d.harga_satuan,
    d.harga_total,
    d.sumber_harga,

    p.kode_permintaan,
    p.nama_barang,
    p.merk,
    p.warna,
    p.jumlah,

    u.name AS customer

  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  LEFT JOIN invoice i 
    ON i.distribusi_id = d.id 
    AND i.deleted_at IS NULL
  WHERE d.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
  echo "<p class='text-white p-10'>Data invoice tidak ditemukan.</p>";
  exit;
}

/* =========================
   DATA PEMBAYARAN
========================= */
$pembayaran = null;
if (!empty($data['id_invoice'])) {
  $q = $conn->prepare("
    SELECT *
    FROM pembayaran
    WHERE id_invoice = ?
    ORDER BY tanggal_bayar DESC
    LIMIT 1
  ");
  $q->bind_param("i", $data['id_invoice']);
  $q->execute();
  $pembayaran = $q->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Detail Invoice | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gradient-to-br from-gray-950 via-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <main class="ml-64 p-10 w-full max-w-7xl mx-auto">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
          <h1 class="text-4xl font-extrabold tracking-tight">
            Invoice Detail
          </h1>
          <p class="text-white/50 mt-1">
            Nomor Invoice: <span class="font-semibold text-white"><?= $data['nomor_invoice'] ?? '-' ?></span>
          </p>
        </div>

        <div class="flex gap-3">
          <a href="single-report-pdf/invoice.php?id=<?= $id ?>"
            target="_blank"
            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 font-semibold shadow-lg shadow-emerald-600/20 transition">
            Export PDF
          </a>

          <a href="invoice.php"
            class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition">
            Kembali
          </a>
        </div>
      </div>

      <!-- STATUS BADGE -->
      <?php
      $statusClass = match ($data['status_invoice']) {
        'lunas' => 'bg-emerald-500/20 text-emerald-400 ring-emerald-500/30',
        'belum bayar' => 'bg-yellow-500/20 text-yellow-300 ring-yellow-500/30',
        'dibatalkan' => 'bg-red-500/20 text-red-400 ring-red-500/30',
        default => 'bg-gray-500/20 text-gray-300 ring-gray-500/30'
      };
      ?>
      <div class="mb-8">
        <span class="inline-flex items-center px-5 py-2 rounded-full text-sm font-bold ring-1 <?= $statusClass ?>">
          <?= strtoupper($data['status_invoice'] ?? 'BELUM ADA INVOICE') ?>
        </span>
      </div>

      <!-- MAIN CARD -->
      <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-10 space-y-12">

        <!-- INFO GRID -->
        <div class="grid md:grid-cols-2 gap-8">
          <div class="bg-black/30 rounded-2xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-emerald-400">Customer</h3>
            <p class="font-medium"><?= $data['customer'] ?></p>
            <p class="text-sm text-white/50 mt-2">Kode Permintaan: <?= $data['kode_permintaan'] ?></p>
            <p class="text-sm text-white/50">Kode Distribusi: <?= $data['kode_distribusi'] ?></p>
          </div>

          <div class="bg-black/30 rounded-2xl p-6">
            <h3 class="text-lg font-semibold mb-4 text-sky-400">Pengiriman</h3>
            <p><?= $data['alamat_pengiriman'] ?></p>
            <div class="text-sm text-white/50 mt-2 space-y-1">
              <p>Kurir: <?= $data['kurir'] ?></p>
              <p>No Resi: <?= $data['no_resi'] ?></p>
              <p>Tanggal Terima: <?= $data['tanggal_terima'] ?></p>
            </div>
          </div>
        </div>

        <!-- BARANG TABLE -->
        <div>
          <h3 class="text-xl font-semibold mb-4">Detail Barang</h3>

          <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="w-full text-sm">
              <thead class="bg-white/10 text-white/70">
                <tr>
                  <th class="p-4 text-left">Barang</th>
                  <th class="p-4 text-center">Qty</th>
                  <th class="p-4 text-right">Harga</th>
                  <th class="p-4 text-right">Subtotal</th>
                  <th class="p-4 text-right">Sumber Harga
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-t border-white/10 hover:bg-white/5 transition">
                  <td class="p-4">
                    <p class="font-medium"><?= $data['nama_barang'] ?></p>
                    <p class="text-xs text-white/40">
                      <?= $data['merk'] ?> • <?= $data['warna'] ?>
                    </p>
                  </td>
                  <td class="p-4 text-center"><?= $data['jumlah'] ?></td>
                  <td class="p-4 text-right">
                    Rp <?= number_format($data['harga_satuan'], 0, ',', '.') ?>
                  </td>
                  <td class="p-4 text-right font-semibold">
                    Rp <?= number_format($data['harga_total'], 0, ',', '.') ?>
                  </td>
                  <td class="p-4 text-right font-semibold">
                    <?= strtoupper($data['sumber_harga']) ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TOTAL -->
        <div class="flex justify-end">
          <div class="bg-gradient-to-br from-emerald-600/20 to-emerald-500/10 border border-emerald-500/30 p-6 rounded-2xl w-96 shadow-lg">
            <div class="flex justify-between items-center text-xl font-bold">
              <span>Total Invoice</span>
              <span class="text-emerald-400">
                Rp <?= number_format($data['harga_total'], 0, ',', '.') ?>
              </span>
            </div>
          </div>
        </div>

        <!-- PEMBAYARAN -->
        <div class="bg-black/30 rounded-2xl p-6">
          <h3 class="text-lg font-semibold mb-4">Pembayaran</h3>

          <?php if ($pembayaran): ?>
            <div class="grid md:grid-cols-2 gap-4 text-sm">
              <p>Metode: <strong><?= $pembayaran['metode'] ?></strong></p>
              <p>Tanggal: <?= $pembayaran['tanggal_bayar'] ?></p>
              <p>Jumlah: Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.') ?></p>
              <p class="font-bold text-emerald-400">
                <?= strtoupper($pembayaran['status']) ?>
              </p>
            </div>
          <?php else: ?>
            <p class="text-white/50">Belum ada pembayaran</p>
          <?php endif; ?>
        </div>

      </div>
    </main>
  </div>
</body>


</html>