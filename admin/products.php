<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

include '../include/base-url.php';

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
  <script>
    tailwind.config = {
      theme: {
        extend: {
          backdropBlur: {
            'xs': '2px',
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black">

  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <!-- CONTENT -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Kelola Produk</h1>
          <p class="text-white/80">Kelola produk Anda dengan mudah dan efisien.</p>
        </div>

        <!-- Tombol Tambah -->
        <?php if (!isset($_GET['edit']) && !isset($_GET['tambah'])): ?>
          <div class="mb-6">
            <a href="products.php?tambah=1"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Tambah Produk
            </a>
          </div>
        <?php endif; ?>

        <!-- FORM TAMBAH -->
        <?php if (isset($_GET['tambah'])): ?>
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl mb-8">
            <h2 class="text-2xl font-semibold text-white mb-6">Tambah Produk Baru</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Produk</label>
                  <input type="text" name="nama_produk" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Harga</label>
                  <input type="number" name="harga" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" required>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Gambar</label>
                <input type="file" name="gambar" required class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
              </div>
              <div class="flex gap-4 pt-4">
                <button name="tambah" class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
                  Simpan
                </button>
                <a href="products.php" class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
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
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl mb-8">
            <h2 class="text-2xl font-semibold text-white mb-6">Edit Produk</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-6">
              <input type="hidden" name="id" value="<?= $p['id']; ?>">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Produk</label>
                  <input type="text" name="nama_produk" value="<?= $p['nama_produk']; ?>" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Harga</label>
                  <input type="number" name="harga" value="<?= $p['harga']; ?>" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" required>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"><?= $p['deskripsi']; ?></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Gambar Saat Ini</label>
                <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>" width="150" class="rounded-xl shadow-lg mb-4">
              </div>
              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Ganti Gambar</label>
                <input type="file" name="gambar" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
              </div>
              <div class="flex gap-4 pt-4">
                <button name="edit" class="px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
                  Update
                </button>
                <a href="products.php" class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
                  Batal
                </a>
              </div>
            </form>
          </div>
        <?php endif; ?>

        <!-- LIST PRODUK -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
          <h2 class="text-2xl font-semibold text-white mb-6">Daftar Produk</h2>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-xl overflow-hidden">
              <thead>
                <tr class="bg-white/20 text-white">
                  <th class="p-4 text-left">Gambar</th>
                  <th class="p-4 text-left">Nama</th>
                  <th class="p-4 text-left">Harga</th>
                  <th class="p-4 text-left">Deskripsi</th>
                  <th class="p-4 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                  <tr class="border-b border-white/10 hover:bg-white/5 transition-colors duration-200">
                    <td class="p-4">
                      <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>" width="70" class="rounded-lg shadow-md">
                    </td>
                    <td class="p-4 text-white/90 font-medium"><?= $p['nama_produk']; ?></td>
                    <td class="p-4 text-white/90">Rp <?= number_format($p['harga']); ?></td>
                    <td class="p-4 text-white/70 max-w-xs truncate"><?= $p['deskripsi']; ?></td>
                    <td class="p-4 relative">
                      <button onclick="toggleDropdown(<?= $p['id']; ?>, event)"
                        class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                      </button>

                      <div id="dropdown-<?= $p['id']; ?>"
                        class="hidden fixed right-[60px] z-[99999] w-48 bg-white/20 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl">
                        <a href="products.php?edit=<?= $p['id']; ?>"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                          Edit
                        </a>
                        <a onclick="return confirm('Hapus produk?')"
                          href="products.php?hapus=<?= $p['id']; ?>"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-b-xl transition-colors duration-200">
                          Hapus
                        </a>
                      </div>
                    </td>

                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script>
    function toggleDropdown(id, event) {
      event.stopPropagation(); // cegah event dari bubbling ke document

      const current = document.getElementById("dropdown-" + id);

      // Tutup semua dropdown lain
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        if (d !== current) d.classList.add("hidden");
      });

      // Toggle dropdown sekarang
      current.classList.toggle("hidden");
    }

    // Tutup jika klik di luar
    document.addEventListener("click", function() {
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        d.classList.add("hidden");
      });
    });
  </script>

</body>

</html>