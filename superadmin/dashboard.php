<?php require 'dashboard-func.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Dashboard | DigiPlan Indonesia</title>

  <!-- Tailwind CSS -->
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
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Dashboard Hari Ini</h1>
          <p class="text-white/80">Pantau statistik dan performa sistem Anda secara real-time.</p>
        </div>

        <!-- STATISTIC CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">

          <!-- Permintaan Hari Ini -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl hover:bg-white/5 transition-colors duration-200">
            <p class="text-white/70 font-medium">Permintaan Hari Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-indigo-400"><?= $permintaan_hari_ini; ?></h2>
          </div>

          <!-- Permintaan Diterima -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl hover:bg-white/5 transition-colors duration-200">
            <p class="text-white/70 font-medium">Permintaan Diterima</p>
            <h2 class="text-4xl font-bold mt-2 text-green-400"><?= $permintaan_diterima; ?></h2>
          </div>

          <!-- Barang Masuk Bulan Ini -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl hover:bg-white/5 transition-colors duration-200">
            <p class="text-white/70 font-medium">Barang Masuk Bulan Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-yellow-400"><?= $barang_masuk; ?></h2>
          </div>

          <!-- Barang Keluar Bulan Ini -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl hover:bg-white/5 transition-colors duration-200">
            <p class="text-white/70 font-medium">Barang Keluar Bulan Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-red-400"><?= $barang_keluar; ?></h2>
          </div>

        </div>

        <!-- GRAPH -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
          <h2 class="text-2xl font-bold text-white mb-6">Grafik Permintaan vs Distribusi</h2>
          <canvas id="chartPermintaanDistribusi" height="120"></canvas>
        </div>

      </div>

    </main>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>