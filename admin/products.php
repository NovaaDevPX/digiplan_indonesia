<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

include '../include/base-url.php'; // pastikan ini memuat $base_url

include 'product-add.php';
include 'product-update.php';
include 'product-delete.php';
include 'product-func.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Kelola Produk | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex">

    <?php include '../include/layouts/sidebar.php'; ?>

    <!-- CONTENT -->
    <main class="ml-64 p-10 w-full">

      <h1 class="text-3xl font-bold mb-6">Kelola Produk</h1>

      <!-- Tombol Tambah -->
      <?php if (!isset($_GET['edit']) && !isset($_GET['tambah'])): ?>
        <a href="products.php?tambah=1"
          class="inline-block mb-4 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow">
          + Tambah Produk
        </a>
      <?php endif; ?>


      <!-- FORM TAMBAH -->
      <?php if (isset($_GET['tambah'])): ?>
        <div class="backdrop-blur-xl bg-white/60 p-6 rounded-xl shadow mb-6">

          <h2 class="text-xl font-semibold mb-4">Tambah Produk</h2>

          <form method="post" enctype="multipart/form-data" class="space-y-3">

            <div>
              <label class="font-medium">Nama Produk</label>
              <input type="text" name="nama_produk" class="w-full p-2 border rounded-lg" required>
            </div>

            <div>
              <label class="font-medium">Harga</label>
              <input type="number" name="harga" class="w-full p-2 border rounded-lg" required>
            </div>

            <div>
              <label class="font-medium">Deskripsi</label>
              <textarea name="deskripsi" class="w-full p-2 border rounded-lg"></textarea>
            </div>

            <div>
              <label class="font-medium">Gambar</label>
              <input type="file" name="gambar" required class="w-full p-2 border rounded-lg">
            </div>

            <div class="flex gap-3 pt-3">
              <button name="tambah"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">
                Simpan
              </button>
              <a href="products.php"
                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg shadow">
                Batal
              </a>
            </div>

          </form>

        </div>
      <?php endif; ?>



      <!-- FORM EDIT -->
      <?php if (isset($_GET['edit'])):
        $id = $_GET['edit'];
        $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'"));
      ?>
        <div class="backdrop-blur-xl bg-white/60 p-6 rounded-xl shadow mb-6">

          <h2 class="text-xl font-semibold mb-4">Edit Produk</h2>

          <form method="post" enctype="multipart/form-data" class="space-y-3">

            <input type="hidden" name="id" value="<?= $p['id']; ?>">

            <div>
              <label class="font-medium">Nama Produk</label>
              <input type="text" name="nama_produk" value="<?= $p['nama_produk']; ?>"
                class="w-full p-2 border rounded-lg" required>
            </div>

            <div>
              <label class="font-medium">Harga</label>
              <input type="number" name="harga" value="<?= $p['harga']; ?>"
                class="w-full p-2 border rounded-lg" required>
            </div>

            <div>
              <label class="font-medium">Deskripsi</label>
              <textarea name="deskripsi" class="w-full p-2 border rounded-lg"><?= $p['deskripsi']; ?></textarea>
            </div>

            <div>
              <label class="font-medium">Gambar Saat Ini</label><br>

              <!-- FIXED PATH GAMBAR -->
              <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>" width="150"
                class="rounded-lg shadow mb-3">
            </div>

            <div>
              <label class="font-medium">Ganti Gambar</label>
              <input type="file" name="gambar" class="w-full p-2 border rounded-lg">
            </div>

            <div class="flex gap-3 pt-3">
              <button name="edit"
                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow">
                Update
              </button>
              <a href="products.php"
                class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg shadow">
                Batal
              </a>
            </div>

          </form>

        </div>
      <?php endif; ?>



      <!-- LIST PRODUK -->
      <div class="backdrop-blur-xl bg-white/60 p-6 rounded-xl shadow">

        <h2 class="text-xl font-semibold mb-4">Daftar Produk</h2>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-gray-800 text-white">
                <th class="p-3">Gambar</th>
                <th class="p-3">Nama</th>
                <th class="p-3">Harga</th>
                <th class="p-3">Deskripsi</th>
                <th class="p-3">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                <tr class="border-b hover:bg-gray-100">
                  <td class="p-3">

                    <!-- FIXED PATH GAMBAR -->
                    <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>" width="70"
                      class="rounded-md shadow">

                  </td>
                  <td class="p-3"><?= $p['nama_produk']; ?></td>
                  <td class="p-3">Rp <?= number_format($p['harga']); ?></td>
                  <td class="p-3"><?= $p['deskripsi']; ?></td>
                  <td class="p-3 flex gap-2">
                    <a href="products.php?edit=<?= $p['id']; ?>"
                      class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded shadow">
                      Edit
                    </a>

                    <a onclick="return confirm('Hapus produk?')"
                      href="products.php?hapus=<?= $p['id']; ?>"
                      class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded shadow">
                      Hapus
                    </a>
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