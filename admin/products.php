<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

include '../include/base-url.php';

/* ===============================
   DATA
================================ */

// Produk (barang yang sudah punya gambar)
$produk = mysqli_query($conn, "
  SELECT * FROM barang
  WHERE gambar IS NOT NULL
  AND deleted_at IS NULL
  ORDER BY id DESC
");

// Barang tanpa gambar
$barang_tanpa_gambar = mysqli_query($conn, "
  SELECT * FROM barang
  WHERE gambar IS NULL
  AND deleted_at IS NULL
");

/* ===============================
   TAMBAH (UPLOAD GAMBAR)
================================ */
if (isset($_POST['tambah'])) {
  $barang_id = $_POST['barang_id'];

  if (!empty($_FILES['gambar']['name'])) {
    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    $ext    = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

    $nama_file = 'barang_' . time() . '.' . $ext;
    move_uploaded_file($tmp, "../uploads/$nama_file");

    mysqli_query($conn, "
      UPDATE barang SET gambar='$nama_file'
      WHERE id='$barang_id'
    ");
  }

  header("Location: products.php?success=add_photo");
  exit;
}

/* ===============================
   EDIT (GANTI GAMBAR)
================================ */
if (isset($_POST['edit'])) {
  $id = $_POST['id'];

  $old = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT gambar FROM barang WHERE id='$id'
  "));

  if (!empty($_FILES['gambar']['name'])) {
    if ($old['gambar'] && file_exists("../uploads/" . $old['gambar'])) {
      unlink("../uploads/" . $old['gambar']);
    }

    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    $ext    = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

    $nama_file = 'barang_' . time() . '.' . $ext;
    move_uploaded_file($tmp, "../uploads/$nama_file");

    mysqli_query($conn, "
      UPDATE barang SET gambar='$nama_file'
      WHERE id='$id'
    ");
  }

  header("Location: products.php?success=update_photo");
  exit;
}

/* ===============================
   HAPUS GAMBAR
================================ */
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];

  $old = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT gambar FROM barang WHERE id='$id'
  "));

  if ($old['gambar'] && file_exists("../uploads/" . $old['gambar'])) {
    unlink("../uploads/" . $old['gambar']);
  }

  mysqli_query($conn, "
    UPDATE barang SET gambar=NULL
    WHERE id='$id'
  ");

  header("Location: products.php?success=deleted_photo");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kelola Produk</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/notifications.php'; ?>
    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <main class="ml-64 p-10 w-full">

      <!-- Header -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
        <h1 class="text-4xl font-bold text-white mb-2">Kelola Produk</h1>
        <p class="text-white/80">Kelola produk Anda dengan mudah dan efisien.</p>
      </div>

      <?php if (!isset($_GET['tambah']) && !isset($_GET['edit'])): ?>
        <a href="products.php?tambah=1"
          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 mb-6">
          + Tambah Produk
        </a>
      <?php endif; ?>

      <!-- ===============================
         FORM TAMBAH
    ================================ -->
      <?php if (isset($_GET['tambah'])): ?>
        <div class="bg-white/10 p-6 rounded-xl mb-8">
          <h2 class="text-2xl mb-4">Tambah Produk (Upload Gambar)</h2>

          <form method="post" enctype="multipart/form-data" class="space-y-4">

            <select name="barang_id" id="barangSelect" required
              onchange="onBarangSelect(this)"
              class="w-full p-3 rounded-xl bg-white/20">
              <option value="" class="text-black">-- Pilih Barang --</option>
              <?php while ($b = mysqli_fetch_assoc($barang_tanpa_gambar)): ?>
                <option class="text-black" value="<?= $b['id']; ?>">
                  <?= $b['nama_barang']; ?> - <?= $b['merk']; ?>
                  (Belum Ada Gambar)
                </option>
              <?php endwhile; ?>
            </select>

            <!-- Upload Gambar (Hidden awal) -->
            <div id="uploadSection" class="hidden space-y-3">
              <input type="file" name="gambar" id="gambarTambah"
                accept="image/*"
                onchange="previewImage(event,'previewTambah'); enableSubmit()"
                class="w-full p-3 bg-white/20 rounded-xl">

              <img id="previewTambah" class="hidden w-40 rounded-xl">
            </div>

            <button name="tambah" id="btnSimpan"
              disabled
              class="px-6 py-3 bg-green-600 rounded-xl opacity-50 cursor-not-allowed">
              Simpan
            </button>

            <a href="products.php" class="ml-3 text-white/70">Batal</a>
          </form>
        </div>
      <?php endif; ?>

      <!-- ===============================
         FORM EDIT
    ================================ -->
      <?php if (isset($_GET['edit'])):
        $id = $_GET['edit'];
        $p = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM barang WHERE id='$id'
      "));
      ?>
        <div class="bg-white/10 p-6 rounded-xl mb-8">
          <h2 class="text-2xl mb-2">Ganti Gambar Produk</h2>

          <!-- WARNING -->
          <div class="mb-4 p-4 rounded-xl bg-yellow-500/20 text-yellow-300 text-sm">
            ⚠ Jika ingin mengubah data lainnya, silakan masuk ke menu
            <b>Barang</b> pada sidebar.
          </div>

          <form method="post" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" value="<?= $p['id']; ?>">

            <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>"
              class="w-40 rounded-xl">

            <input type="file" name="gambar"
              accept="image/*"
              onchange="previewImage(event,'previewEdit')"
              class="w-full p-3 bg-white/20 rounded-xl">

            <img id="previewEdit" class="hidden w-40 rounded-xl">

            <button name="edit"
              class="px-6 py-3 bg-yellow-600 rounded-xl">
              Update Gambar
            </button>

            <a href="products.php" class="ml-3 text-white/70">Batal</a>
          </form>
        </div>
      <?php endif; ?>

      <!-- ===============================
         LIST PRODUK
    ================================ -->
      <!-- ===============================
     LIST PRODUK (LEBIH LENGKAP)
================================ -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
        <h2 class="text-2xl font-semibold text-white mb-6">Daftar Produk</h2>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse rounded-xl overflow-hidden">
            <thead>
              <tr class="bg-white/20 text-white">
                <th class="p-4 text-left">Gambar</th>
                <th class="p-4 text-left">Barang</th>
                <th class="p-4 text-left">Detail</th>
                <th class="p-4 text-left">Stok</th>
                <th class="p-4 text-left">Harga</th>
                <th class="p-4 text-left">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                <tr class="border-b border-white/10 hover:bg-white/5 transition-colors duration-200">

                  <!-- Gambar -->
                  <td class="p-4">
                    <img src="<?= $base_url ?>uploads/<?= $p['gambar']; ?>"
                      class="w-20 rounded-lg shadow-md">
                  </td>

                  <!-- Nama -->
                  <td class="p-4">
                    <div class="text-white font-semibold">
                      <?= $p['nama_barang']; ?>
                    </div>
                    <div class="text-white/60 text-sm">
                      <?= $p['merk']; ?>
                    </div>
                  </td>

                  <!-- Detail -->
                  <td class="p-4 text-white/70 text-sm max-w-xs">
                    <div>Warna: <span class="text-white/90"><?= $p['warna']; ?></span></div>
                    <div class="truncate" title="<?= $p['deskripsi']; ?>">
                      <?= $p['deskripsi']; ?>
                    </div>
                  </td>

                  <!-- Stok -->
                  <td class="p-4">
                    <?php if ($p['stok'] > 20): ?>
                      <span class="px-3 py-1 rounded-full text-xs bg-green-500/20 text-green-400">
                        <?= $p['stok']; ?> tersedia
                      </span>
                    <?php elseif ($p['stok'] > 0): ?>
                      <span class="px-3 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-400">
                        <?= $p['stok']; ?> terbatas
                      </span>
                    <?php else: ?>
                      <span class="px-3 py-1 rounded-full text-xs bg-red-500/20 text-red-400">
                        Habis
                      </span>
                    <?php endif; ?>
                  </td>

                  <!-- Harga -->
                  <td class="p-4 text-white font-medium">
                    Rp <?= number_format($p['harga']); ?>
                  </td>

                  <!-- Aksi -->
                  <td class="p-4 relative">
                    <button onclick="toggleDropdown(<?= $p['id']; ?>, event)"
                      class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                      </svg>
                    </button>

                    <div id="dropdown-<?= $p['id']; ?>"
                      class="hidden fixed right-[60px] z-[99999] w-48
                       bg-slate-900/50 backdrop-blur-xl
                       border border-white/30
                       rounded-xl shadow-2xl">

                      <a href="products.php?edit=<?= $p['id']; ?>"
                        class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                        Edit Gambar
                      </a>

                      <a href="products.php?hapus=<?= $p['id']; ?>"
                        onclick="return confirm('Hapus gambar produk?')"
                        class="block px-4 py-3 text-white hover:bg-white/10 rounded-b-xl transition-colors duration-200">
                        Hapus Gambar
                      </a>
                    </div>
                  </td>

                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- ===============================
     SCRIPT
================================ -->
  <script>
    function onBarangSelect(select) {
      const upload = document.getElementById('uploadSection');
      const btn = document.getElementById('btnSimpan');

      if (select.value) {
        upload.classList.remove('hidden');
      } else {
        upload.classList.add('hidden');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
      }
    }

    function previewImage(event, target) {
      const img = document.getElementById(target);
      img.src = URL.createObjectURL(event.target.files[0]);
      img.classList.remove('hidden');
    }

    function enableSubmit() {
      const btn = document.getElementById('btnSimpan');
      btn.disabled = false;
      btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  </script>

  <script>
    function toggleDropdown(id, event) {
      event.stopPropagation();

      const current = document.getElementById("dropdown-" + id);

      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        if (d !== current) d.classList.add("hidden");
      });

      current.classList.toggle("hidden");
    }

    document.addEventListener("click", function() {
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        d.classList.add("hidden");
      });
    });
  </script>

</body>

</html>