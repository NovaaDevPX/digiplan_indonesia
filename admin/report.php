<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

/* ================= DATA GRAFIK ================= */
// Grafik Permintaan per Status
$qPermintaan = mysqli_query($conn, "
SELECT status, COUNT(*) as total 
FROM permintaan_barang 
GROUP BY status
");

$labelPermintaan = [];
$dataPermintaan  = [];

while ($r = mysqli_fetch_assoc($qPermintaan)) {
  $labelPermintaan[] = $r['status'];
  $dataPermintaan[]  = $r['total'];
}

// Grafik Pengadaan per Status
$qPengadaan = mysqli_query($conn, "
SELECT status_pengadaan, COUNT(*) as total 
FROM pengadaan_barang 
GROUP BY status_pengadaan
");

$labelPengadaan = [];
$dataPengadaan  = [];

while ($r = mysqli_fetch_assoc($qPengadaan)) {
  $labelPengadaan[] = $r['status_pengadaan'];
  $dataPengadaan[]  = $r['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">

  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <main class="ml-64 p-10 w-full">

      <!-- HEADER -->
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-10">
        <h1 class="text-4xl font-bold mb-2">Laporan</h1>
        <p class="text-white/80">
          Modul laporan digunakan untuk menampilkan dan mencetak data transaksi sistem.
        </p>
      </div>

      <!-- MENU LAPORAN -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-14">

        <a href="report-item-request.php"
          title="Klik untuk menuju laporan Permintaan Barang"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Permintaan Barang</h3>
          <p class="text-sm text-white/70">
            Laporan seluruh permintaan barang dari customer.
          </p>
        </a>

        <a href="report-procurement.php"
          title="Klik untuk menuju laporan Pengadaan Barang"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Pengadaan Barang</h3>
          <p class="text-sm text-white/70">
            Laporan proses pembelian barang dari supplier.
          </p>
        </a>

        <a href="report-distribution.php"
          title="Klik untuk menuju laporan Distribusi Barang"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Distribusi Barang</h3>
          <p class="text-sm text-white/70">
            Laporan pengiriman barang ke customer.
          </p>
        </a>

        <a href="#"
          title="Fitur laporan invoice belum tersedia"
          class="bg-white/5 border border-white/10 p-6 rounded-xl cursor-not-allowed">
          <h3 class="text-xl font-semibold mb-2">Invoice & Pembayaran</h3>
          <p class="text-sm text-white/50">
            Laporan invoice dan pembayaran (dalam pengembangan).
          </p>
        </a>

      </div>

      <!-- ================= GRAFIK ================= -->
      <h2 class="text-3xl font-bold mb-6">Grafik Ringkasan Laporan</h2>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Grafik Permintaan -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4">Grafik Permintaan Barang</h3>
          <canvas id="chartPermintaan"></canvas>
        </div>

        <!-- Grafik Pengadaan -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-4">Grafik Pengadaan Barang</h3>
          <canvas id="chartPengadaan"></canvas>
        </div>

      </div>

    </main>
  </div>

  <!-- CHART JS -->
  <script>
    const permintaanChart = new Chart(document.getElementById('chartPermintaan'), {
      type: 'pie',
      data: {
        labels: <?= json_encode($labelPermintaan) ?>,
        datasets: [{
          data: <?= json_encode($dataPermintaan) ?>
        }]
      }
    });

    const pengadaanChart = new Chart(document.getElementById('chartPengadaan'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($labelPengadaan) ?>,
        datasets: [{
          label: 'Jumlah',
          data: <?= json_encode($dataPengadaan) ?>
        }]
      }
    });
  </script>

</body>

</html>