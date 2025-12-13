<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$autoOpen   = isset($_GET['openModal']);
$namaBarang = $_GET['nama_barang'] ?? '';
$merk       = $_GET['merk'] ?? '';
$warna      = $_GET['warna'] ?? '';



$admin_id = $_SESSION['user_id'];

// Ambil seluruh barang
$barang_list = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Pengadaan Barang | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <?php include '../include/base-url.php'; ?>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body
  x-data="{
    openModal: <?= $autoOpen ? 'true' : 'false' ?>,
    editModal: false,
    editData: {},
    activeRowId: null,

    namaBarang: '<?= htmlspecialchars($namaBarang, ENT_QUOTES) ?>',
    merkBarang: '<?= htmlspecialchars($merk, ENT_QUOTES) ?>',
    warnaBarang: '<?= htmlspecialchars($warna, ENT_QUOTES) ?>'
  }"
  class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden">


  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-admin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>

    <!-- Content -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">List Barang</h1>
          <p class="text-white/80">Kelola barang dan lakukan pengadaan barang dengan mudah.</p>
        </div>

        <!-- Header Barang -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-white">List Barang</h2>
          <button
            @click="openModal = true"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Barang
          </button>
        </div>

        <!-- Table Barang -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl mb-8">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead>
                <tr class="bg-white/20 text-white">
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">No</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Nama Barang</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Merk</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Warna</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Stok</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Harga</th>
                  <th class="px-6 py-4 text-left text-sm font-medium uppercase">Aksi</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-white/10">
                <?php
                $no = 1;
                $query = mysqli_query($conn, "SELECT * FROM barang ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($query)):
                ?>
                  <tr class="hover:bg-white/5 transition-colors duration-200">
                    <td class="px-6 py-4 text-white/90"><?= $no ?></td>
                    <td class="px-6 py-4 text-white/90 font-medium"><?= $row['nama_barang'] ?></td>
                    <td class="px-6 py-4 text-white/90"><?= $row['merk'] ?></td>
                    <td class="px-6 py-4 text-white/90"><?= $row['warna'] ?></td>
                    <td class="px-6 py-4 text-white/90"><?= $row['stok'] ?></td>
                    <td class="px-6 py-4 text-white/90">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td class="px-6 py-4 text-white/90 relative">
                      <!-- Three Dots Button -->
                      <button
                        @click.stop="activeRowId = activeRowId === '<?= $row['id'] ?>' ? null : '<?= $row['id'] ?>'"
                        class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                      </button>

                      <!-- Dropdown Menu (Floating) -->
                      <div
                        x-show="activeRowId === '<?= $row['id'] ?>'"
                        x-cloak
                        x-transition
                        class="fixed right-[60px] z-[99999] w-48 bg-slate-900/50 backdrop-blur-xl 
           border border-white/30 rounded-xl shadow-2xl"
                        @click.away="activeRowId = null">
                        <button
                          @click="
                            editModal = true;
                            activeRowId = null;
                            editData = {
                              id: '<?= $row['id'] ?>',
                              nama_barang: '<?= $row['nama_barang'] ?>',
                              merk: '<?= $row['merk'] ?>',
                              warna: '<?= $row['warna'] ?>',
                              stok: '<?= $row['stok'] ?>',
                              harga: '<?= $row['harga'] ?>'
                            }
                          "
                          class="block w-full text-left px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                          Edit
                        </button>
                        <a href="item-delete.php?id=<?= $row['id'] ?>"
                          onclick="return confirm('Hapus barang ini?')"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-b-xl transition-colors duration-200">
                          Hapus
                        </a>
                      </div>
                    </td>
                  </tr>

                <?php $no++;
                endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Edit Barang -->
        <div
          x-show="editModal"
          x-cloak
          class="fixed inset-0 flex items-center justify-center z-50 bg-black/50 backdrop-blur-sm">

          <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl 
              w-[26rem] p-6">

            <h3 class="text-lg font-semibold text-white mb-4">Edit Barang</h3>

            <form action="item-edit.php" method="POST">
              <input type="hidden" name="id" :value="editData.id">

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Nama Barang</label>
                <input type="text" name="nama_barang"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  :value="editData.nama_barang" required>
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Merk</label>
                <input type="text" name="merk"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  :value="editData.merk">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Warna</label>
                <input type="text" name="warna"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  :value="editData.warna">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Stok</label>
                <input type="number" name="stok"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  :value="editData.stok" min="0">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Harga</label>
                <input type="number" name="harga"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  :value="editData.harga" min="0" step="1000">
              </div>

              <div class="flex justify-end gap-3 mt-5">
                <button type="button" @click="editModal = false"
                  class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl">
                  Batal
                </button>

                <button type="submit" name="edit_barang"
                  class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Tambah Barang -->
        <div
          x-show="openModal"
          x-cloak
          class="fixed inset-0 flex items-center justify-center z-50 bg-black/50 backdrop-blur-sm">

          <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-2xl shadow-2xl 
              w-[26rem] p-6">

            <h3 class="text-lg font-semibold text-white mb-4">Tambah Barang</h3>

            <form action="item-add.php" method="POST">

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Nama Barang</label>
                <input type="text" name="nama_barang"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  x-model="namaBarang"
                  required>
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Merk</label>
                <input type="text" name="merk"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  x-model="merkBarang">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Warna</label>
                <input type="text" name="warna"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  x-model="warnaBarang">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Stok</label>
                <input type="number" name="stok"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  min="0" value="0">
              </div>

              <div class="mb-3">
                <label class="block text-sm font-medium text-white/90 mb-1">Harga</label>
                <input type="number" name="harga"
                  class="w-full p-2.5 bg-white/20 border border-white/30 rounded-xl text-white"
                  min="0" step="1000" value="0">
              </div>

              <div class="flex justify-end gap-3 mt-5">
                <button type="button" @click="openModal = false"
                  class="px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl">
                  Batal
                </button>

                <button type="submit" name="tambah_barang"
                  class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl">
                  Simpan
                </button>
              </div>

            </form>

          </div>
        </div>

    </main>

  </div>

</body>

</html>