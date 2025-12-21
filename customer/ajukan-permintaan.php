<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'];

/* ==============================
   VALIDASI BARANG
============================== */
if (!isset($_GET['barang_id']) || !is_numeric($_GET['barang_id'])) {
  header("Location: dashboard.php");
  exit;
}

$barang_id = (int) $_GET['barang_id'];

$q_barang = $conn->query("
  SELECT *
  FROM barang
  WHERE id = $barang_id
    AND gambar IS NOT NULL
    AND deleted_at IS NULL
  LIMIT 1
");

if ($q_barang->num_rows === 0) {
  header("Location: dashboard.php");
  exit;
}

$barang = $q_barang->fetch_assoc();
$error  = '';

/* ==============================
   PROSES SUBMIT
============================== */
if (isset($_POST['submit'])) {

  $jumlah = (int) $_POST['jumlah'];

  if ($jumlah <= 0) {
    $error = "Jumlah tidak valid.";
  } elseif ($jumlah > $barang['stok']) {
    $error = "Stok tidak mencukupi. Sisa stok: {$barang['stok']}";
  } else {

    $conn->begin_transaction();

    try {

      /* ==============================
         GENERATE KODE PERMINTAAN
      ============================== */
      $q_last = $conn->query("
        SELECT id
        FROM permintaan_barang
        ORDER BY id DESC
        LIMIT 1
      ");

      $last_id = $q_last->num_rows
        ? (int)$q_last->fetch_assoc()['id']
        : 0;

      $kode = 'PRM-' . date('Y') . '-' . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);

      /* ==============================
         INSERT PERMINTAAN
      ============================== */
      $stmt = $conn->prepare("
        INSERT INTO permintaan_barang
          (kode_permintaan, user_id, nama_barang, merk, warna, jumlah, status)
        VALUES
          (?, ?, ?, ?, ?, ?, 'diajukan')
      ");

      $stmt->bind_param(
        "sisssi",
        $kode,
        $user_id,
        $barang['nama_barang'],
        $barang['merk'],
        $barang['warna'],
        $jumlah
      );
      $stmt->execute();

      $permintaan_id = $conn->insert_id;

      /* ==================================================
         NOTIFIKASI 1 — CUSTOMER
      ================================================== */
      $pesan_customer =
        "Permintaan Anda berhasil diajukan.\n\n" .
        "Kode Permintaan: $kode\n" .
        "Barang: {$barang['nama_barang']}\n" .
        "Jumlah: $jumlah\n\n" .
        "Status: Menunggu verifikasi admin.";

      insertNotifikasi(
        $conn,
        $user_id,          // receiver customer
        null,              // sender system
        $permintaan_id,
        '',                // pesan admin kosong
        $pesan_customer
      );

      /* ==================================================
         NOTIFIKASI 2 — INTERNAL (ADMIN & SUPER ADMIN)
      ================================================== */
      $pesan_admin =
        "Permintaan baru diajukan oleh customer.\n\n" .
        "Kode Permintaan: $kode\n" .
        "Customer ID: $user_id\n" .
        "Barang: {$barang['nama_barang']}\n" .
        "Jumlah: $jumlah\n\n" .
        "Status: DIAJUKAN";

      // Ambil SATU super admin sebagai receiver internal
      $qSuperAdmin = $conn->query("
        SELECT id
        FROM users
        WHERE role_id = 3
          AND deleted_at IS NULL
        LIMIT 1
      ");

      $superAdmin = $qSuperAdmin->fetch_assoc();

      if ($superAdmin) {
        insertNotifikasi(
          $conn,
          $superAdmin['id'], // receiver internal
          $user_id,          // sender customer
          $permintaan_id,
          $pesan_admin,      // pesan admin
          ''                 // pesan customer kosong
        );
      }

      $conn->commit();

      header("Location: history-item-request.php?success=item_request_sent");
      exit;
    } catch (Throwable $e) {
      $conn->rollback();
      $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Ajukan Permintaan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black min-h-screen">

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <main class="ml-64 p-10 w-full">
      <div class="max-w-full mx-auto">

        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl p-8 shadow-2xl">

          <h1 class="text-2xl font-bold text-white mb-6">Ajukan Permintaan</h1>

          <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-500/20 text-red-400">
              <?= htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <div class="flex gap-6 mb-6">
            <img src="../uploads/<?= htmlspecialchars($barang['gambar']); ?>" class="w-32 rounded-xl">

            <div class="space-y-1">
              <h3 class="text-white font-bold"><?= htmlspecialchars($barang['nama_barang']); ?></h3>
              <p class="text-white/70">
                <?= htmlspecialchars($barang['merk']); ?> • <?= htmlspecialchars($barang['warna']); ?>
              </p>
              <p class="text-white/70">Stok: <b><?= $barang['stok']; ?></b></p>
              <p class="text-blue-400 font-bold">
                Rp <?= number_format($barang['harga'], 0, ',', '.'); ?>
              </p>
            </div>
          </div>

          <form method="post" onsubmit="return confirm('Yakin ingin mengajukan permintaan barang ini?')">
            <label class="block text-white/80 mb-2">Jumlah Permintaan</label>
            <input
              type="number"
              name="jumlah"
              min="1"
              max="<?= $barang['stok']; ?>"
              required
              class="w-full p-3 rounded-xl bg-white/20 text-white mb-6">

            <button
              name="submit"
              class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-xl text-white font-semibold">
              Ajukan Permintaan
            </button>

            <a href="detail-barang.php?id=<?= $barang_id; ?>"
              class="block text-center mt-4 text-white/70 hover:text-white">
              Batal
            </a>
          </form>

        </div>
      </div>
    </main>
  </div>

</body>

</html>