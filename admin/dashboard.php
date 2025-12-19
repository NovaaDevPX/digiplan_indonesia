<?php require 'dashboard-func.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Dashboard | DigiPlan Indonesia</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">
  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <main class="ml-64 p-10 w-full">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Dashboard Hari Ini</h1>
          <p class="text-white/80">Pantau statistik dan performa sistem Anda secara real-time.</p>
        </div>

        <!-- STATISTIC CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <p class="text-white/70">Permintaan Hari Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-indigo-400"><?= $permintaan_hari_ini; ?></h2>
          </div>

          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <p class="text-white/70">Permintaan Diterima</p>
            <h2 class="text-4xl font-bold mt-2 text-green-400"><?= $permintaan_diterima; ?></h2>
          </div>

          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <p class="text-white/70">Barang Masuk Bulan Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-yellow-400"><?= $barang_masuk; ?></h2>
          </div>

          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <p class="text-white/70">Barang Keluar Bulan Ini</p>
            <h2 class="text-4xl font-bold mt-2 text-red-400"><?= $barang_keluar; ?></h2>
          </div>
        </div>

        <!-- NOTIFIKASI TERBARU -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-10">
          <h2 class="text-2xl font-bold text-white mb-6">Notifikasi Terbaru</h2>

          <?php if (empty($notifikasi)): ?>
            <p class="text-white/60">Belum ada notifikasi.</p>
          <?php else: ?>
            <ul class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
              <?php foreach ($notifikasi as $n): ?>
                <li class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/10">
                  <div class="mt-2 w-3 h-3 rounded-full bg-indigo-400"></div>
                  <div class="flex-1">
                    <p class="text-white text-sm"><?= htmlspecialchars($n['pesan']); ?></p>

                    <?php if (!empty($n['kode_permintaan'])): ?>
                      <p class="text-indigo-300 text-xs mt-1">
                        Kode Permintaan: <?= $n['kode_permintaan']; ?>
                      </p>
                    <?php endif; ?>

                    <p class="text-white/50 text-xs mt-2">
                      <?= date('d M Y H:i', strtotime($n['created_at'])); ?>
                    </p>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
        <!-- ================= GRAFIK 2 x 2 ================= -->
        <h2 class="text-2xl font-bold mb-6">Analitik Sistem</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

          <div class="bg-white/10 p-6 rounded-xl">
            <h3 class="mb-3">Permintaan per Status</h3>
            <canvas id="chartPermintaanStatus"></canvas>
          </div>

          <div class="bg-white/10 p-6 rounded-xl">
            <h3 class="mb-3">Pengadaan per Status</h3>
            <canvas id="chartPengadaanStatus"></canvas>
          </div>

          <div class="bg-white/10 p-6 rounded-xl lg:col-span-2">
            <h3 class="mb-3">Invoice vs Pembayaran (Rp)</h3>
            <canvas id="chartInvoicePembayaran"></canvas>
          </div>

        </div>

      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      const baseOpt = {
        responsive: true,
        scales: {
          x: {
            ticks: {
              color: '#fff'
            },
            grid: {
              color: 'rgba(255,255,255,.1)'
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: '#fff'
            },
            grid: {
              color: 'rgba(255,255,255,.1)'
            }
          }
        },
        plugins: {
          legend: {
            labels: {
              color: '#fff'
            }
          }
        }
      };

      /* ================= PERMINTAAN ================= */
      new Chart(
        document.getElementById('chartPermintaanStatus'), {
          type: 'bar',
          data: {
            labels: <?= json_encode($permintaan_status_label) ?>,
            datasets: [{
              label: 'Jumlah Permintaan',
              data: <?= json_encode($permintaan_status_data) ?>,
              backgroundColor: '#6366f1',
              borderRadius: 8
            }]
          },
          options: baseOpt
        }
      );

      /* ================= PENGADAAN ================= */
      new Chart(
        document.getElementById('chartPengadaanStatus'), {
          type: 'bar',
          data: {
            labels: <?= json_encode($pengadaan_status_label) ?>,
            datasets: [{
              label: 'Jumlah Pengadaan',
              data: <?= json_encode($pengadaan_status_data) ?>,
              backgroundColor: '#22c55e',
              borderRadius: 8
            }]
          },
          options: baseOpt
        }
      );

      /* ================= INVOICE vs PEMBAYARAN ================= */
      new Chart(
        document.getElementById('chartInvoicePembayaran'), {
          type: 'bar',
          data: {
            labels: ['Invoice', 'Pembayaran'],
            datasets: [{
              label: 'Total (Rp)',
              data: [
                <?= (int)$invoice_total ?>,
                <?= (int)$pembayaran_total ?>
              ],
              backgroundColor: [
                '#facc15', // invoice
                '#10b981' // pembayaran
              ],
              borderRadius: 10
            }]
          },
          options: {
            ...baseOpt,
            plugins: {
              tooltip: {
                callbacks: {
                  label: function(ctx) {
                    return 'Rp ' + ctx.raw.toLocaleString('id-ID');
                  }
                }
              }
            }
          }
        }
      );

    });
  </script>


</body>

</html>