<?php
require '../include/conn.php'; // koneksi database
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Pengadaan Barang | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <?php include '../include/base-url.php'; ?>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-100 overflow-x-hidden" x-data="{ openModal: false, editModalId: null }">
  <div class="flex">
    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <!-- Content -->
    <main class="ml-64 p-10 w-full">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">List Barang</h2>
        <button @click="openModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
          <i class="fas fa-plus"></i> Tambah Barang
        </button>
      </div>

      <!-- Table Barang -->
      <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">No</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Nama Barang</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Merk</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Warna</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Stok</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Harga</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php
            $no = 1;
            $query = mysqli_query($conn, "SELECT * FROM barang ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($query)):
            ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $no ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $row['nama_barang'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $row['merk'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $row['warna'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $row['stok'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <button @click="editModalId = <?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</button> |
                  <a href="item-delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus barang ini?')" class="text-red-600 hover:underline">Hapus</a>
                </td>
              </tr>

              <!-- Modal Edit Barang -->
              <div x-show="editModalId === <?= $row['id'] ?>" x-cloak class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg w-1/3 p-6">
                  <h3 class="text-lg font-semibold mb-4">Edit Barang</h3>
                  <form action="item-edit.php" method="POST">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                      <input type="text" name="nama_barang" value="<?= $row['nama_barang'] ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700">Merk</label>
                      <input type="text" name="merk" value="<?= $row['merk'] ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700">Warna</label>
                      <input type="text" name="warna" value="<?= $row['warna'] ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700">Stok</label>
                      <input type="number" name="stok" value="<?= $row['stok'] ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0">
                    </div>
                    <div class="mb-3">
                      <label class="block text-sm font-medium text-gray-700">Harga</label>
                      <input type="number" name="harga" value="<?= $row['harga'] ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" step="1000">
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                      <button type="button" @click="editModalId = null" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                      <button type="submit" name="edit_barang" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php $no++;
            endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Modal Tambah Barang -->
      <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-1/3 p-6">
          <h3 class="text-lg font-semibold mb-4">Tambah Barang</h3>
          <form action="item-add.php" method="POST">
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
              <input type="text" name="nama_barang" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700">Merk</label>
              <input type="text" name="merk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700">Warna</label>
              <input type="text" name="warna" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700">Stok</label>
              <input type="number" name="stok" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" value="0">
            </div>
            <div class="mb-3">
              <label class="block text-sm font-medium text-gray-700">Harga</label>
              <input type="number" name="harga" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" step="1000" value="0">
            </div>
            <div class="flex justify-end gap-2 mt-4">
              <button type="button" @click="openModal = false" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
              <button type="submit" name="tambah_barang" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>
</body>

</html>