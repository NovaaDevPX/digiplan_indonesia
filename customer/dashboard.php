<?php
require '../include/conn.php';
require '../include/auth.php';
require '../include/notification-func-db.php';
include 'dashboard-func.php';

cek_role(['customer']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Dashboard Customer | DigiPlan Indonesia</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          backdropBlur: {
            xs: '2px'
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black min-h-screen text-gray-100">

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <main class="ml-64 p-10 w-full">
      <div class="max-w-7xl mx-auto space-y-8">

        <!-- ================= HEADER ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h1 class="text-4xl font-bold mb-2">
            Selamat Datang, <?= htmlspecialchars($_SESSION['name']); ?> 👋
          </h1>
          <p class="text-white/70">
            Kelola permintaan, pembayaran, dan pantau notifikasi Anda.
          </p>
        </div>

        <!-- ================= NOTIFIKASI ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">🔔 Notifikasi</h2>

            <?php if ($total_notif_belum_dibaca > 0) { ?>
              <span class="bg-red-500 text-white text-xs px-3 py-1 rounded-full">
                <?= $total_notif_belum_dibaca; ?> baru
              </span>
            <?php } ?>
          </div>

          <?php if ($notifikasi_q->num_rows > 0) { ?>
            <div class="space-y-4">
              <?php while ($n = $notifikasi_q->fetch_assoc()) { ?>
                <div class="p-4 rounded-xl border
                <?= $n['status_baca'] ? 'bg-white/5 border-white/10' : 'bg-blue-500/10 border-blue-400/30'; ?>">

                  <p class="text-sm">
                    <?= htmlspecialchars(tampilkanPesanNotifikasi($n, 1)); ?>
                  </p>

                  <?php if ($n['kode_permintaan']) { ?>
                    <p class="text-xs text-white/50 mt-1">
                      Kode Permintaan:
                      <span class="font-semibold"><?= $n['kode_permintaan']; ?></span>
                    </p>
                  <?php } ?>

                  <p class="text-xs text-white/40 mt-2">
                    <?= date('d M Y H:i', strtotime($n['created_at'])); ?>
                  </p>
                </div>
              <?php } ?>
            </div>
          <?php } else { ?>
            <div class="bg-white/5 p-4 rounded-xl text-white/60">
              Belum ada notifikasi.
            </div>
          <?php } ?>
        </div>

        <!-- ================= RINGKASAN ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold mb-4">Ringkasan Permintaan</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/10 p-4 rounded-xl text-center">
              <p class="text-white/70">Total Permintaan</p>
              <h2 class="text-3xl font-bold"><?= $total_permintaan; ?></h2>
            </div>

            <div class="bg-white/10 p-4 rounded-xl text-center">
              <p class="text-white/70">Dalam Proses</p>
              <h2 class="text-3xl font-bold text-yellow-400"><?= $proses; ?></h2>
            </div>

            <div class="bg-white/10 p-4 rounded-xl text-center">
              <p class="text-white/70">Selesai</p>
              <h2 class="text-3xl font-bold text-green-400"><?= $diterima; ?></h2>
            </div>
          </div>
        </div>

        <!-- ================= PEMBAYARAN ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold mb-4">Informasi Pembayaran</h2>

          <?php if ($pembayaran) { ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <p class="text-white/70">Status</p>
                <p class="font-bold <?= $pembayaran['status'] == 'lunas' ? 'text-green-400' : 'text-yellow-400'; ?>">
                  <?= strtoupper($pembayaran['status']); ?>
                </p>
              </div>

              <div>
                <p class="text-white/70">Jumlah</p>
                <p class="font-bold">
                  Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.'); ?>
                </p>
              </div>

              <div>
                <p class="text-white/70">Tanggal</p>
                <p class="font-bold">
                  <?= date('d M Y', strtotime($pembayaran['tanggal'])); ?>
                </p>
              </div>
            </div>
          <?php } else { ?>
            <div class="bg-yellow-500/20 border border-yellow-500/30 p-4 rounded-xl text-yellow-400">
              Belum ada informasi pembayaran.
            </div>
          <?php } ?>
        </div>

        <!-- ================= PRODUK / BARANG ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold mb-4">Produk Tersedia</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($p = mysqli_fetch_assoc($produk_q)) { ?>
              <div class="bg-white/10 border border-white/20 rounded-xl overflow-hidden hover:scale-[1.02] transition">
                <img
                  src="../uploads/<?= htmlspecialchars($p['gambar']); ?>"
                  class="w-full h-48 object-cover"
                  alt="<?= htmlspecialchars($p['nama_barang']); ?>">

                <div class="p-4">
                  <h5 class="font-bold text-lg mb-2">
                    <?= htmlspecialchars($p['nama_barang']); ?>
                  </h5>

                  <p class="text-white/70 text-sm mb-3">
                    <?= mb_strimwidth(strip_tags($p['deskripsi']), 0, 70, '...'); ?>
                  </p>

                  <p class="text-blue-400 font-bold mb-4">
                    Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
                  </p>

                  <a
                    href="detail-barang.php?id=<?= $p['id']; ?>"
                    class="block text-center bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg transition">
                    Lihat Detail
                  </a>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>

      </div>
    </main>
  </div>

</body>

</html>