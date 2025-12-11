<?php require 'dashboard-func.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Dashboard | DigiPlan Indonesia</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <?php include '../include/base-url.php'; ?>
</head>

<body class="bg-gray-100">
  <div class="flex">

    <?php include '../include/layouts/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="ml-64 p-10 w-full">

      <h1 class="text-3xl font-bold mb-6">Dashboard Hari Ini</h1>

      <!-- STATISTIC CARDS -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <!-- Permintaan Hari Ini -->
        <div class="p-5 rounded-2xl shadow-xl
        bg-indigo-500/20 border border-indigo-400/50
        backdrop-blur-md">
          <p class="text-gray-700 font-medium">Permintaan Hari Ini</p>
          <h2 class="text-3xl font-bold mt-2 text-indigo-600"><?= $permintaan_hari_ini; ?></h2>
        </div>

        <!-- Permintaan Diterima -->
        <div class="p-5 rounded-2xl shadow-xl
        bg-green-500/20 border border-green-400/50
        backdrop-blur-md">
          <p class="text-gray-700 font-medium">Permintaan Diterima</p>
          <h2 class="text-3xl font-bold mt-2 text-green-600"><?= $permintaan_diterima; ?></h2>
        </div>

        <!-- Barang Masuk Bulan Ini -->
        <div class="p-5 rounded-2xl shadow-xl
        bg-yellow-500/20 border border-yellow-400/50
        backdrop-blur-md">
          <p class="text-gray-700 font-medium">Barang Masuk Bulan Ini</p>
          <h2 class="text-3xl font-bold mt-2 text-yellow-600"><?= $barang_masuk; ?></h2>
        </div>

        <!-- Barang Keluar Bulan Ini -->
        <div class="p-5 rounded-2xl shadow-xl
        bg-red-500/20 border border-red-400/50
        backdrop-blur-md">
          <p class="text-gray-700 font-medium">Barang Keluar Bulan Ini</p>
          <h2 class="text-3xl font-bold mt-2 text-red-600"><?= $barang_keluar; ?></h2>
        </div>

      </div>

      <!-- GRAPH -->
      <div class="mt-10 bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Grafik Permintaan vs Distribusi</h2>
        <canvas id="chartPermintaanDistribusi" height="120"></canvas>
      </div>

    </main>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>