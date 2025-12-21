<?php
require '../include/conn.php';
require '../include/auth.php';

cek_role(['customer']);

$user_id = $_SESSION['user_id'];

// ==============================
// VALIDASI BARANG
// ==============================
if (!isset($_GET['barang_id']) || !is_numeric($_GET['barang_id'])) {
  header("Location: dashboard.php");
  exit;
}

$barang_id = (int) $_GET['barang_id'];

$q_barang = $conn->query("
  SELECT * FROM barang
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
$error = '';

// ==============================
// PROSES SUBMIT
// ==============================
if (isset($_POST['submit'])) {
  $jumlah = (int) $_POST['jumlah'];

  if ($jumlah <= 0) {
    $error = "Jumlah tidak valid.";
  } elseif ($jumlah > $barang['stok']) {
    $error = "Stok tidak mencukupi. Sisa stok: {$barang['stok']}";
  } else {

    // generate kode permintaan
    $kode = 'PRM-' . str_pad($next, 3, '0', STR_PAD_LEFT);

    // INSERT PERMINTAAN
    $conn->query("
      INSERT INTO permintaan_barang
      (kode_permintaan, user_id, nama_barang, merk, warna, jumlah, status)
      VALUES
      (
        '$kode',
        $user_id,
        '{$barang['nama_barang']}',
        '{$barang['merk']}',
        '{$barang['warna']}',
        $jumlah,
        'diajukan'
      )
    ");

    // UPDATE STOK BARANG
    $conn->query("
      UPDATE barang
      SET stok = stok - $jumlah
      WHERE id = $barang_id
    ");

    header("Location: history-item-request.php?success=item_request_sent");
    exit;
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
      <div class="max-w-xl mx-auto">

        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl p-8 shadow-2xl">

          <h1 class="text-2xl font-bold text-white mb-6">Ajukan Permintaan</h1>

          <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-xl bg-red-500/20 text-red-400">
              <?= $error; ?>
            </div>
          <?php endif; ?>

          <div class="flex gap-6 mb-6">
            <img src="../uploads/<?= $barang['gambar']; ?>" class="w-32 rounded-xl">

            <div class="space-y-1">
              <h3 class="text-white font-bold"><?= $barang['nama_barang']; ?></h3>
              <p class="text-white/70"><?= $barang['merk']; ?> • <?= $barang['warna']; ?></p>
              <p class="text-white/70">Stok: <b><?= $barang['stok']; ?></b></p>
              <p class="text-blue-400 font-bold">
                Rp <?= number_format($barang['harga'], 0, ',', '.'); ?>
              </p>
            </div>
          </div>

          <form method="post" onsubmit="return confirm('Yakin ingin mengajukan permintaan barang ini?')">
            <label class="block text-white/80 mb-2">Jumlah Permintaan</label>
            <input type="number"
              name="jumlah"
              min="1"
              max="<?= $barang['stok']; ?>"
              required
              class="w-full p-3 rounded-xl bg-white/20 text-white mb-6">

            <button name="submit"
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