<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['admin']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

$admin_id = (int) $_SESSION['user_id']; // SENDER NOTIFIKASI

if (!isset($_GET['id'])) {
  die('❌ ID pengadaan tidak ditemukan');
}

$pengadaan_id = (int) $_GET['id'];

/* =========================
   AMBIL DATA PENGADAAN + CUSTOMER
========================= */
$q = $conn->prepare("
  SELECT 
    pg.*,
    pb.id      AS permintaan_id,
    pb.user_id AS customer_id,
    u.name     AS customer_name
  FROM pengadaan_barang pg
  JOIN permintaan_barang pb ON pg.permintaan_id = pb.id
  JOIN users u ON pb.user_id = u.id
  WHERE pg.id = ?
    AND pg.status_pengadaan = 'diproses'
");
$q->bind_param("i", $pengadaan_id);
$q->execute();
$data = $q->get_result()->fetch_assoc();

if (!$data) {
  header("Location: procurement.php?error=must_proccess");
  exit();
}

/* =========================
   DATA LAMA
========================= */
$old_supplier     = $data['supplier'];
$old_kontak       = $data['kontak_supplier'];
$old_alamat       = $data['alamat_supplier'];
$old_harga_satuan = (float) $data['harga_satuan'];
$old_harga_total  = (float) $data['harga_total'];

/* =========================
   UPDATE DATA
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $supplier = trim($_POST['supplier']);
  $kontak   = trim($_POST['kontak_supplier']);
  $alamat   = trim($_POST['alamat_supplier']);
  $harga_satuan = (float) $_POST['harga_satuan'];
  $harga_total  = $harga_satuan * $data['jumlah'];

  if ($supplier === '' || $kontak === '' || $alamat === '' || $harga_satuan <= 0) {
    die('❌ Data tidak valid');
  }

  /* =========================
     DETEKSI PERUBAHAN
  ========================= */
  $perubahan = [];

  if ($supplier !== $old_supplier)
    $perubahan[] = "Supplier: $old_supplier → $supplier";

  if ($kontak !== $old_kontak)
    $perubahan[] = "Kontak Supplier: $old_kontak → $kontak";

  if ($alamat !== $old_alamat)
    $perubahan[] = "Alamat Supplier: $old_alamat → $alamat";

  if ($harga_satuan !== $old_harga_satuan)
    $perubahan[] =
      "Harga Satuan: Rp " . number_format($old_harga_satuan, 0, ',', '.') .
      " → Rp " . number_format($harga_satuan, 0, ',', '.');

  if ($harga_total !== $old_harga_total)
    $perubahan[] =
      "Total Harga: Rp " . number_format($old_harga_total, 0, ',', '.') .
      " → Rp " . number_format($harga_total, 0, ',', '.');

  $detail_perubahan = $perubahan
    ? implode("\n", $perubahan)
    : "Tidak ada perubahan data.";

  $conn->begin_transaction();

  try {

    /* =========================
       UPDATE PENGADAAN
    ========================= */
    $up = $conn->prepare("
      UPDATE pengadaan_barang
      SET supplier = ?, kontak_supplier = ?, alamat_supplier = ?, 
          harga_satuan = ?, harga_total = ?
      WHERE id = ?
    ");
    $up->bind_param(
      "sssddi",
      $supplier,
      $kontak,
      $alamat,
      $harga_satuan,
      $harga_total,
      $pengadaan_id
    );
    $up->execute();

    /* =========================
       PESAN ADMIN
    ========================= */
    $pesan_admin =
      "Pengadaan diperbarui oleh Admin\n\n" .
      "Kode Pengadaan: {$data['kode_pengadaan']}\n" .
      "Barang: {$data['nama_barang']}\n\n" .
      "Perubahan:\n" . $detail_perubahan;

    /* =========================
       PESAN CUSTOMER
    ========================= */
    $pesan_customer =
      "Halo {$data['customer_name']},\n\n" .
      "Data pengadaan untuk permintaan Anda telah diperbarui.\n\n" .
      "Barang: {$data['nama_barang']}\n\n" .
      "Perubahan:\n" . $detail_perubahan;

    /* =========================
       NOTIFIKASI (FINAL)
    ========================= */
    insertNotifikasi(
      $conn,
      $data['customer_id'],   // RECEIVER
      $admin_id,         // SENDER
      $data['permintaan_id'],
      $pesan_admin,
      $pesan_customer
    );

    $conn->commit();
    header('Location: procurement.php?success=procurement_updated');
    exit;
  } catch (Throwable $e) {
    $conn->rollback();
    die('❌ ERROR DATABASE: ' . $e->getMessage());
  }
}

?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Edit Pengadaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="min-h-screen flex">
    <?php include '../include/layouts/sidebar-admin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- ================= HEADER ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Pengadaan Barang</h1>
          <p class="text-white/80">Kelola proses pengadaan barang dengan mudah dan efisien.</p>
        </div>

        <div class="max-w-full mx-auto backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
          <h2 class="text-2xl font-semibold text-white mb-6">
            Edit Pengadaan
          </h2>

          <div class="grid grid-cols-2 gap-4 text-white/80 mb-6">
            <div><b>Kode:</b> <?= htmlspecialchars($data['kode_pengadaan']) ?></div>
            <div><b>Barang:</b> <?= htmlspecialchars($data['nama_barang']) ?></div>
            <div><b>Merk:</b> <?= htmlspecialchars($data['merk']) ?></div>
            <div><b>Warna:</b> <?= htmlspecialchars($data['warna']) ?></div>
            <div><b>Jumlah:</b> <?= $data['jumlah'] ?></div>
            <div><b>Status:</b> <?= $data['status_pengadaan'] ?></div>
          </div>

          <form method="POST" class="space-y-6">
            <div>
              <label class="text-white/70 text-sm">Supplier</label>
              <input name="supplier"
                value="<?= htmlspecialchars($data['supplier']) ?>"
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
            </div>

            <div>
              <label class="text-white/70 text-sm">Kontak Supplier</label>
              <input name="kontak_supplier"
                value="<?= htmlspecialchars($data['kontak_supplier']) ?>"
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
            </div>

            <div>
              <label class="text-white/70 text-sm">Alamat Supplier</label>
              <textarea name="alamat_supplier"
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white"><?= htmlspecialchars($data['alamat_supplier']) ?></textarea>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <label class="text-white/70 text-sm">Harga Satuan</label>
                <input type="number" step="0.01" name="harga_satuan" id="harga_satuan"
                  value="<?= $data['harga_satuan'] ?>"
                  class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white"
                  oninput="hitungTotal()">
              </div>

              <div>
                <label class="text-white/70 text-sm">Harga Total</label>
                <input readonly id="harga_total"
                  value="<?= $data['harga_total'] ?>"
                  class="w-full p-3 bg-white/10 border border-white/30 rounded-xl text-white">
              </div>
            </div>

            <button
              class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-xl shadow-lg transform hover:scale-105 transition">
              Simpan Perubahan
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <script>
    function hitungTotal() {
      const harga = parseFloat(document.getElementById('harga_satuan').value || 0);
      const jumlah = <?= (int) $data['jumlah'] ?>;
      document.getElementById('harga_total').value = harga * jumlah;
    }
  </script>

</body>

</html>