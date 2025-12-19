<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

/* ================= DATA GRAFIK ================= */

// ===== Permintaan Barang =====
$qPermintaan = mysqli_query($conn, "
  SELECT status, COUNT(*) total
  FROM permintaan_barang
  GROUP BY status
");
$labelPermintaan = $dataPermintaan = [];
while ($r = mysqli_fetch_assoc($qPermintaan)) {
  $labelPermintaan[] = ucfirst($r['status']);
  $dataPermintaan[]  = (int)$r['total'];
}

// ===== Pengadaan Barang =====
$qPengadaan = mysqli_query($conn, "
  SELECT status_pengadaan status, COUNT(*) total
  FROM pengadaan_barang
  GROUP BY status_pengadaan
");
$labelPengadaan = $dataPengadaan = [];
while ($r = mysqli_fetch_assoc($qPengadaan)) {
  $labelPengadaan[] = ucfirst($r['status']);
  $dataPengadaan[]  = (int)$r['total'];
}

// ===== Invoice per Status =====
$qInvoice = mysqli_query($conn, "
  SELECT status, COUNT(*) total
  FROM invoice
  GROUP BY status
");
$labelInvoice = $dataInvoice = [];
while ($r = mysqli_fetch_assoc($qInvoice)) {
  $labelInvoice[] = ucfirst($r['status']);
  $dataInvoice[]  = (int)$r['total'];
}

// ===== Invoice vs Pembayaran (TOTAL NILAI) =====
$qInvoiceTotal = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT IFNULL(SUM(total),0) total FROM invoice WHERE status != 'dibatalkan'
"));

$qPembayaranTotal = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT IFNULL(SUM(jumlah),0) total FROM pembayaran WHERE status = 'berhasil'
"));

$invoiceVsBayar = [
  (float)$qInvoiceTotal['total'],
  (float)$qPembayaranTotal['total']
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan | DigiPlan Indonesia</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white min-h-screen">

  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex">
    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <main class="ml-64 p-10 w-full">

      <!-- HEADER -->
      <div class="bg-white/10 border border-white/20 backdrop-blur-xl rounded-2xl p-6 mb-10">
        <h1 class="text-4xl font-bold mb-2">Dashboard Laporan</h1>
        <p class="text-white/70">Ringkasan transaksi & keuangan sistem</p>
      </div>
      <!-- MENU LAPORAN -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-14">

        <a href="reports/item-request.php"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Permintaan Barang</h3>
          <p class="text-sm text-white/60">Tekan untuk masuk ke laporan permintaan barang</p>
        </a>

        <a href="reports/procurement.php"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Pengadaan Barang</h3>
          <p class="text-sm text-white/60">Tekan untuk masuk ke laporan pengadaan barang</p>
        </a>

        <a href="reports/distribution.php"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Distribusi Barang</h3>
          <p class="text-sm text-white/60">Tekan untuk masuk ke laporan distribusi</p>
        </a>

        <a href="reports/invoice.php"
          class="bg-white/10 hover:bg-white/20 border border-white/20 p-6 rounded-xl transition">
          <h3 class="text-xl font-semibold mb-2">Invoice & Pembayaran</h3>
          <p class="text-sm text-white/60">Tekan untuk masuk ke laporan invoice</p>
        </a>

      </div>

      <!-- ================= GRAFIK ================= -->
      <h2 class="text-3xl font-bold mb-6">Grafik Ringkasan</h2>

      <!-- GRID 2 x 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- PERMINTAAN -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="font-semibold mb-4">Permintaan Barang</h3>
          <canvas id="chartPermintaan"></canvas>
        </div>

        <!-- PENGADAAN -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="font-semibold mb-4">Pengadaan Barang</h3>
          <canvas id="chartPengadaan"></canvas>
        </div>

        <!-- INVOICE -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="font-semibold mb-4">Invoice</h3>
          <canvas id="chartInvoice"></canvas>
        </div>

        <!-- INVOICE VS PEMBAYARAN -->
        <div class="bg-white/10 border border-white/20 rounded-xl p-6">
          <h3 class="font-semibold mb-4">Invoice vs Pembayaran</h3>
          <canvas id="chartInvoiceVsBayar"></canvas>
        </div>

      </div>

    </main>
  </div>

  <script>
    const textColor = '#ffffffcc';
    const gridColor = 'rgba(255,255,255,0.1)';

    // ===== CONFIG COMMON =====
    const commonOptions = {
      responsive: true,
      scales: {
        x: {
          ticks: {
            color: textColor
          },
          grid: {
            color: gridColor
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            color: textColor
          },
          grid: {
            color: gridColor
          }
        }
      },
      plugins: {
        legend: {
          labels: {
            color: textColor
          }
        }
      }
    };

    // PERMINTAAN
    new Chart(chartPermintaan, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labelPermintaan) ?>,
        datasets: [{
          label: 'Permintaan',
          data: <?= json_encode($dataPermintaan) ?>,
          backgroundColor: '#22c55e',
          borderRadius: 8
        }]
      },
      options: commonOptions
    });

    // PENGADAAN
    new Chart(chartPengadaan, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labelPengadaan) ?>,
        datasets: [{
          label: 'Pengadaan',
          data: <?= json_encode($dataPengadaan) ?>,
          backgroundColor: '#38bdf8',
          borderRadius: 8
        }]
      },
      options: commonOptions
    });

    // INVOICE
    new Chart(chartInvoice, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labelInvoice) ?>,
        datasets: [{
          label: 'Invoice',
          data: <?= json_encode($dataInvoice) ?>,
          backgroundColor: ['#facc15', '#22c55e', '#ef4444'],
          borderRadius: 8
        }]
      },
      options: commonOptions
    });

    // INVOICE VS PEMBAYARAN
    new Chart(chartInvoiceVsBayar, {
      type: 'bar',
      data: {
        labels: ['Total Invoice', 'Total Pembayaran'],
        datasets: [{
          label: 'Nilai (Rp)',
          data: <?= json_encode($invoiceVsBayar) ?>,
          backgroundColor: ['#6366f1', '#10b981'],
          borderRadius: 10
        }]
      },
      options: commonOptions
    });
  </script>

</body>

</html>