<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

$id = $_GET['id'];

$q = mysqli_query($conn, "
SELECT * FROM distribusi_barang
WHERE id='$id'
");
$data = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Edit Distribusi</title>
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

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <!-- CONTENT -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Edit Distribusi Barang</h1>
          <p class="text-white/80">Perbarui informasi distribusi barang dengan mudah.</p>
        </div>

        <form action="distribution-update.php" method="POST"
          class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl space-y-6">

          <input type="hidden" name="id" value="<?= $data['id'] ?>">

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Kode Distribusi</label>
            <input type="text" value="<?= $data['kode_distribusi'] ?>" disabled
              class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Alamat Pengiriman</label>
            <textarea name="alamat" required rows="4"
              class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"><?= $data['alamat_pengiriman'] ?></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Kurir</label>
            <input name="kurir" value="<?= $data['kurir'] ?>" required
              class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Status Distribusi</label>
            <select name="status" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
              <option value="dikirim" class="text-black" <?= $data['status_distribusi'] == 'dikirim' ? 'selected' : '' ?>>
                Dikirim
              </option>
              <option value="diterima" class="text-black" <?= $data['status_distribusi'] == 'diterima' ? 'selected' : '' ?>>
                Diterima
              </option>
            </select>
          </div>

          <div class="pt-4">
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
              Update
            </button>
          </div>

        </form>
      </div>
    </main>
  </div>
</body>

</html>