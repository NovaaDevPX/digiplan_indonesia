<?php
// Mulai sesi & koneksi
require '../include/conn.php';
require '../include/auth.php';
include 'dashboard-func.php';

cek_role(['customer']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Dashboard Customer | DigiPlan Indonesia</title>
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

  <script>
    function tampilkanNotifikasi(pesan) {
      let box = document.createElement('div');
      box.innerHTML = pesan;
      box.className = 'alert alert-info shadow';
      box.style = 'margin-bottom:10px; min-width:250px;';
      document.getElementById('notif-container').appendChild(box);
      setTimeout(() => box.remove(), 5000);
    }

    function cekNotifikasi() {
      fetch('get_notifikasi.php')
        .then(res => res.json())
        .then(data => {
          data.forEach(n => tampilkanNotifikasi(n.pesan));
        });
    }

    setInterval(cekNotifikasi, 10000); // cek tiap 10 detik
  </script>

  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-customer.php'; ?>


    <!-- Main Content -->
    <main class="ml-64 p-10 w-full flex-1">
      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
          <p class="text-white/80">Kelola permintaan dan pantau status Anda dengan mudah.</p>
        </div>

        <div class="space-y-8">

          <!-- Ringkasan Permintaan -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Ringkasan Permintaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="backdrop-blur-sm bg-white/10 border border-white/20 p-4 rounded-xl text-center">
                <h6 class="text-white/70 mb-2">Total Permintaan</h6>
                <h2 class="text-white font-bold text-3xl"><?= $total_permintaan; ?></h2>
              </div>
              <div class="backdrop-blur-sm bg-white/10 border border-white/20 p-4 rounded-xl text-center">
                <h6 class="text-white/70 mb-2">Permintaan Terbaru</h6>
                <h4 class="text-white font-bold">
                  <?= $proses + $diterima + $ditolak > 0 ? "Ada Permintaan" : "Tidak Ada"; ?>
                </h4>
              </div>
              <div class="backdrop-blur-sm bg-white/10 border border-white/20 p-4 rounded-xl text-center">
                <h6 class="text-white/70 mb-2">Status Terakhir</h6>
                <h4 class="text-yellow-400 font-bold">
                  <?= $proses > 0 ? "Dalam Proses" : ($diterima > 0 ? "Diterima" : "Ditolak"); ?>
                </h4>
              </div>
            </div>
          </div>

          <!-- Status Permintaan -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Status Permintaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="backdrop-blur-sm bg-blue-500/20 border border-blue-500/30 p-4 rounded-xl text-center">
                <h5 class="text-blue-400 mb-2">Dalam Proses </h5>
                <h3 class="text-white font-bold text-2xl"><?= $proses; ?></h3>
              </div>
              <div class="backdrop-blur-sm bg-green-500/20 border border-green-500/30 p-4 rounded-xl text-center">
                <h5 class="text-green-400 mb-2">Diterima </h5>
                <h3 class="text-white font-bold text-2xl"><?= $diterima; ?></h3>
              </div>
              <div class="backdrop-blur-sm bg-red-500/20 border border-red-500/30 p-4 rounded-xl text-center">
                <h5 class="text-red-400 mb-2">Ditolak </h5>
                <h3 class="text-white font-bold text-2xl"><?= $ditolak; ?></h3>
              </div>
            </div>
          </div>

          <!-- Informasi Pembayaran -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Informasi Pembayaran</h2>
            <?php if ($pembayaran) { ?>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <h6 class="text-white/70 mb-2">Status Pembayaran</h6>
                  <span class="font-bold text-lg <?= $pembayaran['status'] == 'lunas' ? 'text-green-400' : 'text-yellow-400'; ?>">
                    <?= strtoupper($pembayaran['status']); ?>
                  </span>
                </div>
                <div>
                  <h6 class="text-white/70 mb-2">Jumlah</h6>
                  <span class="font-bold text-lg text-white">
                    Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.'); ?>
                  </span>
                </div>
                <div>
                  <h6 class="text-white/70 mb-2">Tanggal</h6>
                  <span class="font-bold text-lg text-white">
                    <?= date('d M Y', strtotime($pembayaran['tanggal'])); ?>
                  </span>
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
                <div class="backdrop-blur-sm bg-white/10 border border-white/20 rounded-xl overflow-hidden shadow-lg">
                  <img src="uploads/<?= $p['gambar']; ?>" class="w-full h-48 object-cover" alt="<?= htmlspecialchars($p['nama_produk']); ?>">
                  <div class="p-4">
                    <h5 class="text-white font-bold text-lg mb-2"><?= htmlspecialchars($p['nama_produk']); ?></h5>
                    <p class="text-white/70 text-sm mb-2">
                      <?= substr($p['deskripsi'], 0, 60); ?>...
                    </p>
                    <h6 class="text-blue-400 font-bold mb-3">
                      Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
                    </h6>
                    <a href="detail_produk.php?id=<?= $p['id']; ?>" class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                      Lihat Detail
                    </a>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>

        </div>

      </div>
    </main>
  </div>

</body>

</html>