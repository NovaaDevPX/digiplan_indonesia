<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['super_admin']);

$admin_id = $_SESSION['user_id'];

/* =========================
   DATA PERMINTAAN (BELUM PENGADAAN)
========================= */
$permintaan = $conn->query("
  SELECT 
    pb.*,
    u.name AS user_name
  FROM permintaan_barang pb
  JOIN users u ON pb.user_id = u.id
  LEFT JOIN pengadaan_barang pg ON pg.permintaan_id = pb.id
  WHERE pb.status = 'disetujui'
    AND pg.id IS NULL
  ORDER BY pb.id DESC
");

/* =========================
   DATA PENGADAAN
========================= */
$pengadaan_list = $conn->query("
  SELECT 
    pg.*,
    u.name AS customer
  FROM pengadaan_barang pg
  JOIN permintaan_barang pb ON pg.permintaan_id = pb.id
  JOIN users u ON pb.user_id = u.id
  ORDER BY pg.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title>Pengadaan Barang | DigiPlan Indonesia</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>

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
</head>

<body class="bg-gradient-to-b from-gray-900 to-black text-white">

  <?php include '../include/layouts/notifications.php'; ?>

  <div class="flex min-h-screen">

    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <!-- CONTENT -->
    <main class="ml-64 p-10 w-full flex-1" x-data="procurement()">

      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold mb-2">Pengadaan Barang</h1>
          <p class="text-white/80">Kelola proses pengadaan barang dari permintaan yang disetujui.</p>
        </div>

        <!-- ================= DAFTAR PENGADAAN ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl mb-10">
          <h2 class="text-2xl font-semibold mb-6">Daftar Pengadaan</h2>

          <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-xl overflow-hidden">
              <thead class="bg-white/20">
                <tr>
                  <th class="p-4 text-left">Kode</th>
                  <th class="p-4 text-left">Barang</th>
                  <th class="p-4 text-left">Jumlah</th>
                  <th class="p-4 text-left">Supplier</th>
                  <th class="p-4 text-left">Status</th>
                  <th class="p-4 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($pg = $pengadaan_list->fetch_assoc()): ?>
                  <tr class="border-b border-white/10 hover:bg-white/5 transition">
                    <td class="p-4 font-medium"><?= $pg['kode_pengadaan'] ?></td>
                    <td class="p-4"><?= $pg['nama_barang'] ?></td>
                    <td class="p-4"><?= $pg['jumlah'] ?></td>
                    <td class="p-4"><?= $pg['supplier'] ?></td>
                    <td class="p-4">
                      <span class="px-3 py-1 rounded-full text-sm bg-indigo-500/20 text-indigo-300">
                        <?= $pg['status_pengadaan'] ?>
                      </span>
                    </td>
                    <td class="p-4 relative">
                      <button onclick="toggleDropdown(<?= $pg['id']; ?>, event)"
                        class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 6a2 2 0 110-4 2 2 0 010 4z
               M10 12a2 2 0 110-4 2 2 0 010 4z
               M10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                      </button>

                      <div id="dropdown-<?= $pg['id']; ?>"
                        class="hidden fixed right-[60px] z-[99999] w-48 bg-slate-900/50 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl">

                        <!-- EDIT (jika nanti ada) -->
                        <a href="procurement-edit.php?id=<?= $pg['id']; ?>"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                          Edit
                        </a>

                        <!-- BARANG DITERIMA -->
                        <?php if ($pg['status_pengadaan'] === 'diproses'): ?>
                          <a href="procurement-finish.php?id=<?= $pg['id']; ?>"
                            onclick="return confirm('Yakin barang sudah diterima dan siap didistribusikan?')"
                            class="block px-4 py-3 text-emerald-300 hover:bg-white/10 transition-colors duration-200">
                            Barang Sudah Diterima
                          </a>
                        <?php endif; ?>

                        <!-- EXPORT PDF -->
                        <a href="single-report-pdf/procurement.php?kode=<?= $pg['kode_pengadaan']; ?>"
                          target="_blank"
                          class="block px-4 py-3 text-white hover:bg-white/10 rounded-b-xl transition-colors duration-200">
                          Export PDF
                        </a>
                      </div>
                    </td>

                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= PILIH PERMINTAAN ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl mb-10">
          <h2 class="text-2xl font-semibold mb-6">Permintaan Disetujui</h2>

          <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-xl overflow-hidden">
              <thead class="bg-white/20">
                <tr>
                  <th class="p-4 text-left">ID</th>
                  <th class="p-4 text-left">Barang</th>
                  <th class="p-4 text-left">Jumlah</th>
                  <th class="p-4 text-left">Customer</th>
                  <th class="p-4 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($p = $permintaan->fetch_assoc()): ?>
                  <tr class="border-b border-white/10 hover:bg-white/5 transition">
                    <td class="p-4"><?= $p['id'] ?></td>
                    <td class="p-4"><?= $p['nama_barang'] ?></td>
                    <td class="p-4"><?= $p['jumlah'] ?></td>
                    <td class="p-4"><?= $p['user_name'] ?></td>
                    <td class="p-4">
                      <button
                        @click='pilihPermintaan(<?= json_encode($p) ?>)'
                        class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 rounded-xl shadow-lg transform hover:scale-105 transition">
                        Pilih
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= FORM ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-8 rounded-2xl shadow-2xl">
          <h2 class="text-2xl font-semibold text-white mb-6">Form Pengadaan</h2>

          <!-- NOTIFIKASI REALTIME -->
          <div
            x-show="notif.show"
            x-transition
            :class="notif.type === 'error'
    ? 'bg-red-500/20 border-red-400 text-red-200'
    : notif.type === 'warning'
    ? 'bg-yellow-500/20 border-yellow-400 text-yellow-200'
    : 'bg-emerald-500/20 border-emerald-400 text-emerald-200'"
            class="mb-6 p-4 border rounded-xl">
            <p class="font-semibold" x-text="notif.title"></p>
            <p class="text-sm mt-1" x-text="notif.message"></p>
          </div>


          <form action="procurement-func.php" method="POST" class="space-y-8">
            <input type="hidden" name="barang_id" :value="form.barang_id">
            <input type="hidden" name="admin_id" :value="form.admin_id">
            <input type="hidden" name="permintaan_id" :value="form.permintaan_id">

            <!-- INFO BARANG -->
            <div>
              <h3 class="text-lg font-semibold text-white/90 mb-4">Informasi Barang</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm text-white/80 mb-2">Nama Barang</label>
                  <input readonly name="nama_barang" :value="form.nama_barang"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
                <div>
                  <label class="block text-sm text-white/80 mb-2">Merk</label>
                  <input readonly name="merk" :value="form.merk"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
                <div>
                  <label class="block text-sm text-white/80 mb-2">Warna</label>
                  <input readonly name="warna" :value="form.warna"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
              </div>
            </div>

            <!-- JUMLAH & HARGA -->
            <div>
              <h3 class="text-lg font-semibold text-white/90 mb-4">Jumlah & Harga</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm text-white/80 mb-2">
                    Jumlah Pengadaan
                    <span class="text-xs text-white/50">(min: sesuai permintaan)</span>
                  </label>
                  <input type="number" name="jumlah" x-model="form.jumlah" :min="form.min_jumlah"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
                <div>
                  <label class="block text-sm text-white/80 mb-2">Harga Satuan</label>
                  <input type="number" name="harga_satuan" x-model="form.harga_satuan" @input="hitungTotal"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
                <div>
                  <label class="block text-sm text-white/80 mb-2">Total Harga</label>
                  <input readonly name="harga_total" :value="form.harga_total"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white">
                </div>
              </div>
            </div>

            <!-- DATA SUPPLIER -->
            <div>
              <h3 class="text-lg font-semibold text-white/90 mb-4">Data Supplier</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm text-white/80 mb-2">Nama Supplier</label>
                  <input name="supplier" x-model="form.supplier"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50">
                </div>
                <div>
                  <label class="block text-sm text-white/80 mb-2">Kontak Supplier</label>
                  <input name="kontak_supplier" x-model="form.kontak_supplier"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50">
                </div>
              </div>

              <div class="mt-6">
                <label class="block text-sm text-white/80 mb-2">Alamat Supplier</label>
                <input name="alamat_supplier" x-model="form.alamat_supplier"
                  class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50">
              </div>
            </div>

            <!-- SUBMIT -->
            <button
              class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 rounded-xl shadow-lg transform hover:scale-105 transition font-semibold text-white">
              Simpan Pengadaan
            </button>
          </form>
        </div>

      </div>
    </main>
  </div>

  <script>
    function procurement() {
      return {

        /* ==========================
           STATE NOTIFIKASI
        ========================== */
        notif: {
          show: false,
          type: 'success',
          title: '',
          message: ''
        },

        /* ==========================
           STATE FORM
        ========================== */
        form: {
          admin_id: <?= $admin_id ?>,
          permintaan_id: '',
          barang_id: null,

          nama_barang: '',
          merk: '',
          warna: '',

          jumlah: 0,
          min_jumlah: 0,

          harga_satuan: 0,
          harga_total: 0,

          supplier: '',
          kontak_supplier: '',
          alamat_supplier: ''
        },

        /* ==========================
           PILIH PERMINTAAN
        ========================== */
        async pilihPermintaan(p) {
          // RESET
          this.resetForm();
          this.form.permintaan_id = p.id;
          this.form.nama_barang = p.nama_barang;
          this.form.merk = p.merk;
          this.form.warna = p.warna;

          /* Query Params */
          const params = new URLSearchParams({
            nama_barang: p.nama_barang,
            merk: p.merk,
            warna: p.warna,
            jumlah: p.jumlah
          });

          let data = null;

          try {
            const res = await fetch('ajax/get-barang-by-permintaan.php?' + params);

            if (!res.ok) {
              throw new Error("Server response invalid");
            }

            data = await res.json();
          } catch (e) {
            this.showNotif(
              "error",
              "Gagal Mengambil Data",
              "Terjadi kesalahan ketika mengambil data barang."
            );
            return;
          }

          /* ❌ BARANG TIDAK ADA */
          if (!data.found) {
            this.showNotif(
              "error",
              "Barang Tidak Ditemukan",
              "Barang ini tidak ada di gudang. Pengadaan wajib dilakukan."
            );
            return;
          }

          /* ==========================
             BARANG ADA
          ========================== */

          this.form.barang_id = data.barang_id ?? null;
          this.form.harga_satuan = Number(data.harga ?? 0);

          // Jika stok cukup → jumlah_pengadaan = 0 (tidak perlu beli)
          if (data.status === "cukup") {
            this.form.jumlah = 0;
            this.form.min_jumlah = 0;
          } else {
            // Jika stok kurang → jumlah_pengadaan > 0
            this.form.jumlah = Number(data.jumlah_pengadaan ?? 0);
            this.form.min_jumlah = Number(data.jumlah_pengadaan ?? 0);
          }

          // Supplier otomatis
          this.form.supplier = data.supplier ?? '';
          this.form.kontak_supplier = data.kontak_supplier ?? '';
          this.form.alamat_supplier = data.alamat_supplier ?? '';

          this.hitungTotal();

          /* 🔔 NOTIFIKASI */
          this.showNotif(
            data.status === "cukup" ? "success" : "warning",
            data.status === "cukup" ? "Stok Tersedia" : "Stok Tidak Cukup",
            data.message
          );
        },

        /* ==========================
           HITUNG TOTAL
        ========================== */
        hitungTotal() {
          const jumlah = Number(this.form.jumlah || 0);
          const harga_satuan = Number(this.form.harga_satuan || 0);

          this.form.harga_total = jumlah * harga_satuan;
        },

        /* ==========================
           RESET FORM
        ========================== */
        resetForm() {
          this.notif.show = false;

          this.form.barang_id = null;
          this.form.jumlah = 0;
          this.form.min_jumlah = 0;
          this.form.harga_satuan = 0;
          this.form.harga_total = 0;

          this.form.supplier = '';
          this.form.kontak_supplier = '';
          this.form.alamat_supplier = '';
        },

        /* ==========================
           SHOW NOTIFICATION
        ========================== */
        showNotif(type, title, message) {
          this.notif = {
            show: true,
            type,
            title,
            message
          };
        }
      }
    }
  </script>

  <script>
    function toggleDropdown(id, event) {
      event.stopPropagation();

      const current = document.getElementById("dropdown-" + id);

      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        if (d !== current) d.classList.add("hidden");
      });

      current.classList.toggle("hidden");
    }

    document.addEventListener("click", function() {
      document.querySelectorAll("[id^='dropdown-']").forEach(d => {
        d.classList.add("hidden");
      });
    });
  </script>


</body>

</html>