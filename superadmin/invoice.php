<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

/* =========================
   DATA DISTRIBUSI SIAP INVOICE
========================= */
$distribusi = $conn->query("
  SELECT 
    d.id,
    d.kode_distribusi,
    d.tanggal_terima,
    p.kode_permintaan,
    p.nama_barang,
    p.jumlah,
    u.name AS customer,
    pg.harga_total,
    i.id_invoice,
    i.status AS status_invoice
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  JOIN pengadaan_barang pg ON d.pengadaan_id = pg.id
  LEFT JOIN invoice i 
    ON i.distribusi_id = d.id
    AND i.deleted_at IS NULL
  WHERE d.status_distribusi = 'diterima'
  ORDER BY d.tanggal_terima DESC
");


/* =========================
   GENERATE NOMOR INVOICE
========================= */
$q = $conn->query("SELECT MAX(nomor_invoice) AS maxInv FROM invoice");
$data = $q->fetch_assoc();
$no = (int) substr($data['maxInv'], 4, 3);
$nomor_invoice = 'INV-' . str_pad($no + 1, 3, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Invoice | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <main class="ml-64 p-10 w-full">

      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="backdrop-blur-xl bg-white/10 p-6 rounded-2xl mb-8">
          <h1 class="text-3xl font-bold">Pembuatan Invoice</h1>
          <p class="text-white/70">Buat invoice untuk distribusi yang telah selesai</p>
        </div>

        <!-- TABEL DISTRIBUSI -->
        <div class="bg-white/10 p-6 rounded-2xl mb-8">
          <h2 class="text-xl font-semibold mb-4">Distribusi Siap Invoice</h2>

          <table class="w-full text-sm">
            <thead class="bg-white/20">
              <tr>
                <th class="p-3 text-left">Kode Distribusi</th>
                <th class="p-3 text-left">Customer</th>
                <th class="p-3 text-left">Barang</th>
                <th class="p-3 text-left">Total</th>
                <th class="p-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/10">

              <?php while ($row = $distribusi->fetch_assoc()): ?>
                <tr>
                  <td class="p-3"><?= $row['kode_distribusi'] ?></td>
                  <td class="p-3"><?= $row['customer'] ?></td>
                  <td class="p-3"><?= $row['nama_barang'] ?> (<?= $row['jumlah'] ?>)</td>
                  <td class="p-3">Rp <?= number_format($row['harga_total'], 0, ',', '.') ?></td>
                  <td class="p-3 text-center">

                    <?php if (!$row['id_invoice']): ?>
                      <!-- BELUM ADA INVOICE -->
                      <a href="invoice-create.php?id=<?= $row['id'] ?>"
                        class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-700">
                        Buat Invoice
                      </a>

                    <?php elseif ($row['status_invoice'] === 'dibatalkan'): ?>
                      <!-- INVOICE DIBATALKAN -->
                      <a href="invoice-create.php?id=<?= $row['id'] ?>&retry=1"
                        class="px-4 py-2 bg-yellow-500 rounded-lg hover:bg-yellow-600">
                        Buat Invoice Baru
                      </a>

                    <?php elseif ($row['status_invoice'] === 'belum bayar'): ?>
                      <!-- MASIH MENUNGGU BAYAR -->
                      <span class="px-4 py-2 bg-gray-500/30 rounded-lg text-sm text-white/60">
                        Menunggu Pembayaran
                      </span>

                    <?php else: ?>
                      <!-- LUNAS -->
                      <span class="px-4 py-2 bg-emerald-500/20 text-emerald-300 rounded-lg text-sm">
                        Lunas
                      </span>
                    <?php endif; ?>

                  </td>

                </tr>
              <?php endwhile; ?>

            </tbody>
          </table>
        </div>

      </div>
    </main>
  </div>
</body>

</html>