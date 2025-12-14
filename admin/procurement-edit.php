<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);

$data = $conn->prepare("
  SELECT id, kode_pengadaan, supplier, kontak_supplier, alamat_supplier, status_pengadaan
  FROM pengadaan_barang
  WHERE id = ?
");
$data->bind_param("i", $id);
$data->execute();
$pengadaan = $data->get_result()->fetch_assoc();

if (!$pengadaan) {
  die('Data pengadaan tidak ditemukan');
}

if ($pengadaan['status_pengadaan'] === 'dibatalkan') {
  die('Pengadaan dibatalkan tidak dapat diedit');
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

        <div class="w-full max-w-full backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">

          <h1 class="text-2xl font-bold mb-2">Edit Pengadaan</h1>
          <p class="text-white/70 mb-6">
            Kode Pengadaan: <strong><?= $pengadaan['kode_pengadaan']; ?></strong>
          </p>

          <form action="procurement-update.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?= $pengadaan['id']; ?>">

            <div>
              <label class="block mb-1">Nama Supplier</label>
              <input type="text" name="supplier"
                value="<?= htmlspecialchars($pengadaan['supplier']); ?>"
                required
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl">
            </div>

            <div>
              <label class="block mb-1">Kontak Supplier</label>
              <input type="text" name="kontak_supplier"
                value="<?= htmlspecialchars($pengadaan['kontak_supplier']); ?>"
                required
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl">
            </div>

            <div>
              <label class="block mb-1">Alamat Supplier</label>
              <input type="text" name="alamat_supplier"
                value="<?= htmlspecialchars($pengadaan['alamat_supplier']); ?>"
                required
                class="w-full p-3 bg-white/20 border border-white/30 rounded-xl">
            </div>

            <div class="flex gap-3 pt-4">
              <button type="submit"
                class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 rounded-xl font-semibold">
                Simpan Perubahan
              </button>
              <a href="procurement.php"
                class="flex-1 py-3 bg-gray-600 hover:bg-gray-700 rounded-xl text-center">
                Batal
              </a>
            </div>
          </form>

        </div>
      </div>
    </main>
  </div>

</body>

</html>