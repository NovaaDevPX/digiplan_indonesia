<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

if (!isset($_GET['id'])) {
  header("Location: invoice.php");
  exit;
}

$distribusi_id = (int) $_GET['id'];

/* =========================
   DETAIL DISTRIBUSI
========================= */
$data = $conn->query("
  SELECT 
    d.id,
    d.kode_distribusi,
    d.tanggal_terima,
    p.kode_permintaan,
    p.nama_barang,
    p.jumlah,
    u.name AS customer,
    u.email,
    pg.harga_total
  FROM distribusi_barang d
  JOIN permintaan_barang p ON d.permintaan_id = p.id
  JOIN users u ON p.user_id = u.id
  JOIN pengadaan_barang pg ON d.pengadaan_id = pg.id
  WHERE d.id = $distribusi_id
")->fetch_assoc();

if (!$data) {
  die("Data distribusi tidak ditemukan");
}

/* =========================
   GENERATE NOMOR INVOICE
========================= */
$q = $conn->query("SELECT MAX(nomor_invoice) AS maxInv FROM invoice");
$r = $q->fetch_assoc();
$no = (int) substr($r['maxInv'], 4, 3);
$nomor_invoice = 'INV-' . str_pad($no + 1, 3, '0', STR_PAD_LEFT);

/* =========================
   SIMPAN INVOICE
========================= */
if (isset($_POST['simpan'])) {

  $tanggal_invoice = $_POST['tanggal_invoice'];
  $jatuh_tempo     = $_POST['jatuh_tempo'];
  $total           = $data['harga_total'];

  $conn->query("
  INSERT INTO invoice (
    nomor_invoice,
    distribusi_id,
    tanggal_invoice,
    jatuh_tempo,
    total,
    status
  ) VALUES (
    '$nomor_invoice',
    $distribusi_id,
    '$tanggal_invoice',
    '$jatuh_tempo',
    $total,
    'belum bayar'
  )
");

  header("Location: invoice.php?success=created");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Buat Invoice | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <main class="ml-64 p-10 w-full">
      <div class="max-w-4xl mx-auto">

        <!-- HEADER -->
        <div class="bg-white/10 p-6 rounded-2xl mb-8">
          <h1 class="text-2xl font-bold">Buat Invoice</h1>
          <p class="text-white/70">Preview dan konfirmasi invoice</p>
        </div>

        <!-- PREVIEW -->
        <div class="bg-white/10 p-6 rounded-2xl mb-8">
          <h2 class="text-lg font-semibold mb-4">Detail Transaksi</h2>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-white/60">Nomor Invoice</p>
              <p class="font-semibold"><?= $nomor_invoice ?></p>
            </div>
            <div>
              <p class="text-white/60">Customer</p>
              <p class="font-semibold"><?= $data['customer'] ?></p>
            </div>
            <div>
              <p class="text-white/60">Barang</p>
              <p class="font-semibold"><?= $data['nama_barang'] ?></p>
            </div>
            <div>
              <p class="text-white/60">Jumlah</p>
              <p class="font-semibold"><?= $data['jumlah'] ?></p>
            </div>
            <div>
              <p class="text-white/60">Total Bayar</p>
              <p class="font-semibold">
                Rp <?= number_format($data['harga_total'], 0, ',', '.') ?>
              </p>
            </div>
          </div>
        </div>

        <!-- FORM -->
        <div class="bg-white/10 p-6 rounded-2xl">
          <form method="POST">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block mb-2 text-sm">Tanggal Invoice</label>
                <input type="date" name="tanggal_invoice"
                  value="<?= date('Y-m-d') ?>"
                  required
                  class="w-full p-3 rounded-xl bg-white/20 border border-white/30">
              </div>

              <div>
                <label class="block mb-2 text-sm">Jatuh Tempo</label>
                <input type="date" name="jatuh_tempo"
                  value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                  required
                  class="w-full p-3 rounded-xl bg-white/20 border border-white/30">
              </div>
            </div>

            <button name="simpan"
              class="w-full mt-6 py-3 bg-emerald-600 hover:bg-emerald-700 rounded-xl font-semibold">
              Simpan Invoice
            </button>

          </form>
        </div>

      </div>
    </main>
  </div>
</body>

</html>