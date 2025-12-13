<?php
session_start();
require '../include/conn.php';
require '../include/auth.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Form Permintaan Barang | DigiPlan Indonesia</title>
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
  <script src="//unpkg.com/alpinejs" defer></script>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden">

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <!-- Main Content -->
    <main class="ml-64 p-10 w-full flex-1">
      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Form Permintaan Barang</h1>
          <p class="text-white/80">Ajukan permintaan barang baru dengan mudah dan cepat.</p>
        </div>

        <div class="space-y-8">

          <!-- Form Permintaan -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Detail Permintaan</h2>

            <form method="POST" action="form-item-request-func.php" class="space-y-6">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Barang</label>
                  <input type="text" name="nama_barang" required
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Merk</label>
                  <input type="text" name="merk"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Warna</label>
                  <input type="text" name="warna"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Jumlah</label>
                  <input type="number" name="jumlah" min="1" required
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
                </div>

              </div>

              <div>
                <label class="block text-sm font-medium text-white/90 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="4"
                  class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"></textarea>
              </div>

              <div class="mt-6">
                <button type="submit" name="submit"
                  class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
                  Kirim Permintaan
                </button>
              </div>

            </form>

          </div>

        </div>

      </div>
    </main>
  </div>

</body>

</html>