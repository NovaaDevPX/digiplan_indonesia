<?php
require '../include/conn.php';
require '../include/auth.php';
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
            xs: '2px',
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
      <div class="max-w-7xl mx-auto space-y-8">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h1 class="text-4xl font-bold text-white mb-2">
            Selamat Datang, <?= htmlspecialchars($_SESSION['name']); ?>!
          </h1>
          <p class="text-white/80">
            Kelola permintaan dan pantau status Anda dengan mudah.
          </p>
        </div>

        <!-- Ringkasan Permintaan -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4">Ringkasan Permintaan</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/10 border border-white/20 p-4 rounded-xl text-center">
              <p class="text-white/70">Total Permintaan</p>
              <h2 class="text-white text-3xl font-bold"><?= $total_permintaan; ?></h2>
            </div>

            <div class="bg-white/10 border border-white/20 p-4 rounded-xl text-center">
              <p class="text-white/70">Permintaan Terbaru</p>
              <h3 class="text-white font-bold">
                <?= ($proses + $diterima + $ditolak) > 0 ? 'Ada Permintaan' : 'Tidak Ada'; ?>
              </h3>
            </div>

            <div class="bg-white/10 border border-white/20 p-4 rounded-xl text-center">
              <p class="text-white/70">Status Terakhir</p>
              <h3 class="font-bold <?= $proses > 0 ? 'text-yellow-400' : ($diterima > 0 ? 'text-green-400' : 'text-red-400'); ?>">
                <?= $proses > 0 ? 'Dalam Proses' : ($diterima > 0 ? 'Diterima' : 'Ditolak'); ?>
              </h3>
            </div>
          </div>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4">Informasi Pembayaran</h2>

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
                <p class="text-white font-bold">
                  Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.'); ?>
                </p>
              </div>

              <div>
                <p class="text-white/70">Tanggal</p>
                <p class="text-white font-bold">
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

        <!-- Produk Tersedia -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4">Produk Tersedia</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($p = mysqli_fetch_assoc($produk_q)) { ?>
              <div class="bg-white/10 border border-white/20 rounded-xl overflow-hidden shadow-lg hover:scale-[1.02] transition-transform">
                <img src="../uploads/<?= htmlspecialchars($p['gambar']); ?>"
                  class="w-full h-48 object-cover"
                  alt="<?= htmlspecialchars($p['nama_barang']); ?>">

                <div class="p-4">
                  <h5 class="text-white font-bold text-lg mb-2">
                    <?= htmlspecialchars($p['nama_barang']); ?>
                  </h5>

                  <p class="text-white/70 text-sm mb-3">
                    <?= mb_strimwidth(strip_tags($p['deskripsi']), 0, 70, '...'); ?>
                  </p>

                  <p class="text-blue-400 font-bold mb-4">
                    Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
                  </p>

                  <a href="detail-barang.php?id=<?= $p['id']; ?>"
                    class="block text-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
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