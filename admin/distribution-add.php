<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

/* kode distribusi otomatis */
$q = mysqli_query($conn, "SELECT MAX(kode_distribusi) AS maxKode FROM distribusi_barang");
$data = mysqli_fetch_assoc($q);
$no = (int) substr($data['maxKode'], 4, 3);
$kodeDistribusi = 'DST-' . str_pad($no + 1, 3, '0', STR_PAD_LEFT);

/* permintaan siap distribusi + pengadaan selesai */
$query = "
SELECT pb.id AS pengadaan_id,
       pm.id AS permintaan_id,
       pm.kode_permintaan,
       pm.nama_barang,
       pm.jumlah
FROM pengadaan_barang pb
JOIN permintaan_barang pm ON pb.permintaan_id = pm.id
WHERE pm.status = 'siap_distribusi'
AND pb.status_pengadaan = 'selesai'
";
$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
  <title>Tambah Distribusi</title>
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

    <?php include '../include/layouts/sidebar-admin.php'; ?>

    <!-- CONTENT -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Tambah Distribusi Barang</h1>
          <p class="text-white/80">Buat distribusi baru untuk permintaan yang siap dikirim.</p>
        </div>

        <form action="distribution-func.php" method="POST" class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl space-y-6">

          <input type="hidden" name="kode_distribusi" value="<?= $kodeDistribusi ?>">

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Kode Distribusi</label>
            <input type="text" value="<?= $kodeDistribusi ?>" disabled
              class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Pilih Permintaan</label>
            <select name="data" required class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
              <option value="" class="text-black">-- Pilih --</option>
              <?php while ($row = mysqli_fetch_assoc($data)): ?>
                <option class="text-black" value="<?= $row['pengadaan_id'] . '|' . $row['permintaan_id'] ?>">
                  <?= $row['kode_permintaan'] ?> - <?= $row['nama_barang'] ?> (<?= $row['jumlah'] ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Alamat Pengiriman</label>
            <textarea name="alamat" required rows="4" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-white/90 mb-2">Kurir</label>
            <input name="kurir" required class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm">
          </div>

          <div class="pt-4">
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
              Simpan
            </button>
          </div>

        </form>
      </div>
    </main>
  </div>

</body>

</html>