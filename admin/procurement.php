<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$admin_id = $_SESSION['user_id'];

$permintaan = $conn->query("
    SELECT pb.*, u.name AS user_name
    FROM permintaan_barang pb
    LEFT JOIN users u ON pb.user_id = u.id
    WHERE pb.id NOT IN (
        SELECT permintaan_id 
        FROM pengadaan_barang
        WHERE permintaan_id IS NOT NULL
    )
    ORDER BY pb.id DESC
");


// Ambil seluruh barang
$barang_list = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Pengadaan Barang | DigiPlan Indonesia</title>
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
  <?php include '../include/base-url.php'; ?>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden" x-data="{ openModal: false, editModalId: null }">
  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-admin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>

    <!-- Content -->
    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Pengadaan Barang</h1>
          <p class="text-white/80">Kelola barang dan lakukan pengadaan barang dengan mudah.</p>
        </div>

        <!-- Procurement Section -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl" x-data="procurementData()">

          <h1 class="text-3xl font-bold text-white mb-6">Pengadaan Barang</h1>

          <!-- TABEL PERMINTAAN -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">

            <h2 class="text-xl font-bold text-white mb-4">Pilih Permintaan Barang</h2>

            <?php
            // FIX: fetch_all hanya sekali
            $barang_all = $barang_list->fetch_all(MYSQLI_ASSOC);
            ?>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse rounded-xl overflow-hidden">
                <thead>
                  <tr class="bg-white/20 text-white">
                    <th class="p-4 text-left">ID</th>
                    <th class="p-4 text-left">Nama Barang</th>
                    <th class="p-4 text-left">Jumlah</th>
                    <th class="p-4 text-left">Customer</th>
                    <th class="p-4 text-left">Aksi</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                  <?php while ($row = $permintaan->fetch_assoc()) : ?>
                    <tr class="hover:bg-white/5 transition-colors duration-200">
                      <td class="p-4 text-white/90"><?= $row['id'] ?></td>
                      <td class="p-4 text-white/90 font-medium"><?= $row['nama_barang'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['jumlah'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['user_name'] ?></td>
                      <td class="p-4 text-center">

                        <button
                          @click="pilihPermintaan(
                  <?= htmlspecialchars(json_encode($row)) ?>,
                  <?= htmlspecialchars(json_encode($barang_all)) ?>
                )"
                          class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg shadow-md transform hover:scale-105 transition-all duration-200">
                          Pilih
                        </button>

                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>


          <!-- FORM PENGADAAN BARANG -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">

            <h2 class="text-xl font-bold text-white mb-4">Form Pengadaan Barang</h2>

            <form method="POST" action="procurement-func.php">

              <input type="hidden" name="admin_id" :value="form.admin_id">
              <input type="hidden" name="permintaan_id" :value="form.permintaan_id">
              <input type="hidden" name="barang_id" :value="form.barang_id">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Barang</label>
                  <input type="text" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.nama_barang" readonly>
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Jumlah</label>
                  <input type="number" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.jumlah" readonly>
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Merk</label>
                  <input type="text" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.merk">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Warna</label>
                  <input type="text" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.warna">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Harga Total</label>
                  <input type="text" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.harga_total" readonly>
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Supplier</label>
                  <input type="text" name="nama" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.nama">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Kontak Supplier</label>
                  <input type="text" name="kontak" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.kontak">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Alamat Supplier</label>
                  <input type="text" name="alamat" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" x-model="form.alamat">
                </div>

                <div class="col-span-2">
                  <label class="block text-sm font-medium text-white/90 mb-2">Deskripsi Barang</label>
                  <textarea name="deskripsi_barang" class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm" rows="4" x-model="form.deskripsi_barang"></textarea>
                </div>

                <div class="col-span-2">
                  <button name="simpan"
                    class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
                    Simpan Pengadaan
                  </button>
                </div>

              </div>

              <!-- HIDDEN INPUTS UNTUK POST -->
              <input type="hidden" name="jumlah" :value="form.jumlah">
              <input type="hidden" name="merk" :value="form.merk">
              <input type="hidden" name="warna" :value="form.warna">
              <input type="hidden" name="harga_total" :value="form.harga_total">
              <input type="hidden" name="tanggal" :value="form.tanggal">
            </form>
          </div>
        </div>

        <!-- Modal Tambah Barang -->
        <div x-show="openModal" x-cloak class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
          <div class="bg-white rounded-lg shadow-lg w-1/3 p-6">
            <h3 class="text-lg font-semibold mb-4">Tambah Barang</h3>
            <form action="item-add.php" method="POST">
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                <input type="text" name="nama_barang" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Merk</label>
                <input type="text" name="merk" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Warna</label>
                <input type="text" name="warna" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Stok</label>
                <input type="number" name="stok" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" value="0">
              </div>
              <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">Harga</label>
                <input type="number" name="harga" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" min="0" step="1000" value="0">
              </div>
              <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="openModal = false" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                <button type="submit" name="tambah_barang" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
              </div>
            </form>
          </div>
        </div>

    </main>

    <script>
      function procurementData() {
        return {
          showTable: false,
          form: {
            admin_id: <?= $admin_id ?>,
            permintaan_id: '',
            barang_id: '',
            nama_barang: '',
            jumlah: '',
            merk: '',
            warna: '',
            harga_total: '',
            nama: '',
            kontak: '',
            alamat: '',
            deskripsi_barang: '',
            tanggal: ''
          },

          pilihPermintaan(permintaan, barangList) {

            let barang = barangList.find(b => b.nama_barang === permintaan.nama_barang);

            if (!barang) {
              window.location.href = "?error=itemnotfound";
              return;
            }

            this.form.permintaan_id = permintaan.id;
            this.form.barang_id = barang.id;
            this.form.nama_barang = permintaan.nama_barang;
            this.form.jumlah = permintaan.jumlah;
            this.form.merk = permintaan.merk || barang.merk;
            this.form.warna = permintaan.warna || barang.warna;
            this.form.deskripsi_barang = permintaan.deskripsi || barang.deskripsi;
            this.form.nama = barang.nama;
            this.form.kontak = barang.kontak;
            this.form.alamat = barang.alamat;
            this.form.harga_total = (barang.harga * permintaan.jumlah).toFixed(2);

            const today = new Date().toISOString().split('T')[0];
            this.form.tanggal = today;
          }
        }
      }
    </script>

  </div>