<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$admin_id = $_SESSION['user_id'];

/* =========================
   DATA PERMINTAAN
========================= */
$permintaan = $conn->query("
  SELECT 
  pb.id,
  pb.nama_barang,
  pb.jumlah,
  pb.merk,
  pb.warna,
  pb.status,
  u.name AS user_name
FROM permintaan_barang pb
JOIN users u ON pb.user_id = u.id
LEFT JOIN pengadaan_barang pg ON pg.permintaan_id = pb.id
WHERE pb.status = 'disetujui'
  AND pg.id IS NULL
ORDER BY pb.id DESC

");


/* =========================
   DATA PENGADAAN (UNTUK LIST)
========================= */
$pengadaan_list = $conn->query("
  SELECT 
    pg.id,
    pg.kode_pengadaan,
    pg.nama_barang,
    pg.jumlah,
    pg.supplier,
    pg.status_pengadaan,
    pg.tanggal_pengadaan,
    pb.id AS permintaan_id,
    u.name AS user_name
  FROM pengadaan_barang pg
  LEFT JOIN permintaan_barang pb ON pg.permintaan_id = pb.id
  LEFT JOIN users u ON pb.user_id = u.id
  ORDER BY pg.id DESC
");

/* =========================
   DATA BARANG
========================= */
$barang_list = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC");
$barang_all  = $barang_list->fetch_all(MYSQLI_ASSOC);

/* =========================
   DATA TERPILIH SETELAH REDIRECT
========================= */
$selectedPermintaan = null;
$selectedBarang     = null;

if (isset($_GET['permintaan_id'], $_GET['barang_id'])) {
  $pid = (int) $_GET['permintaan_id'];
  $bid = (int) $_GET['barang_id'];

  $selectedPermintaan = $conn
    ->query("SELECT * FROM permintaan_barang WHERE id = $pid")
    ->fetch_assoc();

  $selectedBarang = $conn
    ->query("SELECT * FROM barang WHERE id = $bid")
    ->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
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

<body class="bg-gradient-to-b from-gray-900 to-black overflow-x-hidden">

  <div class="flex min-h-screen">
    <?php include '../include/layouts/sidebar-admin.php'; ?>
    <?php include '../include/layouts/notifications.php'; ?>


    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- ================= HEADER ================= -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold text-white mb-2">Pengadaan Barang</h1>
          <p class="text-white/80">Kelola proses pengadaan barang dengan mudah dan efisien.</p>
        </div>

        <div x-data="procurementData()" class="space-y-8">


          <!-- ================= MODAL KONFIRMASI BARANG TIDAK DITEMUKAN ================= -->
          <div
            x-show="modal.open"
            x-cloak
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60">

            <div
              @click.outside="modal.open = false"
              class="bg-slate-900 border border-white/20 rounded-2xl p-6 w-full max-w-md shadow-2xl">

              <h3 class="text-xl font-bold text-white mb-3">
                Barang Tidak Ditemukan
              </h3>

              <p class="text-white/80 mb-6 leading-relaxed">
                Barang <span class="font-semibold text-white" x-text="modal.nama_barang"></span>
                belum tersedia di sistem.
                <br><br>
                Apakah Anda ingin menambahkan barang baru?
              </p>

              <div class="flex justify-end gap-3">
                <button
                  @click="modal.open = false"
                  class="px-4 py-2 rounded-lg bg-white/10 text-white hover:bg-white/20 transition">
                  Batal
                </button>

                <button
                  @click="konfirmasiTambahBarang()"
                  class="px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold hover:scale-105 transition">
                  Ya, Tambahkan
                </button>
              </div>

            </div>
          </div>


          <!-- ================= DAFTAR PENGADAAN ================= -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl w-full">
            <h2 class="text-xl font-bold text-white mb-4">Daftar Pengadaan Barang</h2>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse rounded-xl overflow-hidden">
                <thead>
                  <tr class="bg-white/20 text-white">
                    <th class="p-4 text-left">Kode Pengadaan</th>
                    <th class="p-4 text-left">Nama Barang</th>
                    <th class="p-4 text-left">Jumlah</th>
                    <th class="p-4 text-left">Supplier</th>
                    <th class="p-4 text-left">Customer</th>
                    <th class="p-4 text-left">Status</th>
                    <th class="p-4 text-left">Tanggal</th>
                    <th class="p-4 text-left">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                  <?php while ($row = $pengadaan_list->fetch_assoc()): ?>
                    <tr class="hover:bg-white/5 transition-colors duration-200">
                      <td class="p-4 text-white/90 font-medium"><?= $row['kode_pengadaan'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['nama_barang'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['jumlah'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['supplier'] ?></td>
                      <td class="p-4 text-white/90"><?= $row['user_name'] ?? 'N/A' ?></td>
                      <td class="p-4 capitalize">
                        <?php
                        switch ($row['status_pengadaan']) {
                          case 'diproses':
                            echo "<span class='px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs whitespace-nowrap'>
              Diproses
            </span>";
                            break;

                          case 'selesai':
                            echo "<span class='px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg text-xs whitespace-nowrap'>
              Selesai
            </span>";
                            break;

                          case 'dibatalkan':
                            echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs whitespace-nowrap'>
              Dibatalkan
            </span>";
                            break;

                          default:
                            echo "<span class='px-3 py-1 bg-gray-500/20 text-gray-300 rounded-lg text-xs whitespace-nowrap'>
              Tidak diketahui
            </span>";
                        }
                        ?>
                      </td>

                      <td class="p-4 text-white/90"><?= $row['tanggal_pengadaan'] ?></td>
                      <td class="p-4 relative">
                        <button onclick="toggleDropdown(<?= $row['id']; ?>, event)"
                          class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-200">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4z
               M10 12a2 2 0 110-4 2 2 0 010 4z
               M10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                          </svg>
                        </button>

                        <div id="dropdown-<?= $row['id']; ?>"
                          class="hidden fixed right-[60px] z-[99999] w-48 bg-slate-900/50 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl">

                          <!-- EDIT -->
                          <?php if ($row['status_pengadaan'] !== 'dibatalkan'): ?>
                            <a href="procurement-edit.php?id=<?= $row['id']; ?>"
                              class="block px-4 py-3 text-white hover:bg-white/10 rounded-t-xl transition-colors duration-200">
                              Edit
                            </a>
                          <?php else: ?>
                            <span
                              class="block px-4 py-3 text-red-300 text-sm cursor-not-allowed opacity-70 rounded-t-xl">
                              Tidak bisa diedit
                            </span>
                          <?php endif; ?>

                          <?php if ($row['status_pengadaan'] === 'diproses'): ?>
                            <a href="procurement-finish.php?id=<?= $row['id']; ?>"
                              onclick="return confirm('Yakin barang sudah diterima dan siap didistribusikan?')"
                              class="block px-4 py-3 text-emerald-300 hover:bg-white/10 transition-colors duration-200">
                              Barang Sudah Diterima
                            </a>
                          <?php endif; ?>

                          <!-- EXPORT PDF -->
                          <a href="single-report-pdf/procurement.php?kode=<?= $row['kode_pengadaan']; ?>"
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

          <!-- ================= TABEL PERMINTAAN ================= -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Pilih Permintaan Barang</h2>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse rounded-xl overflow-hidden">
                <thead>
                  <tr class="bg-white/20 text-white">
                    <th class="p-4 text-left">ID</th>
                    <th class="p-4 text-left">Nama Barang</th>
                    <th class="p-4 text-left">Jumlah</th>
                    <th class="p-4 text-left">Customer</th>
                    <th class="p-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                  <?php if ($permintaan->num_rows > 0): ?>
                    <?php while ($row = $permintaan->fetch_assoc()): ?>
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
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="p-6 text-center text-white/60 italic">
                        Tidak ada data permintaan barang
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>

              </table>
            </div>
          </div>

          <!-- ================= FORM PENGADAAN ================= -->
          <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Form Pengadaan Barang</h2>

            <form action="procurement-func.php" method="POST">

              <!-- hidden -->
              <input type="hidden" name="admin_id" x-model="form.admin_id">
              <input type="hidden" name="permintaan_id" x-model="form.permintaan_id">
              <input type="hidden" name="barang_id" x-model="form.barang_id">
              <input type="hidden" name="tanggal" x-model="form.tanggal">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Barang</label>
                  <input
                    type="text"
                    name="nama_barang"
                    x-model="form.nama_barang"
                    readonly
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Nama Barang">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Jumlah</label>
                  <input
                    type="number"
                    name="jumlah"
                    x-model="form.jumlah"
                    readonly
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Jumlah">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Merk</label>
                  <input
                    type="text"
                    name="merk"
                    x-model="form.merk"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Merk">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Warna</label>
                  <input
                    type="text"
                    name="warna"
                    x-model="form.warna"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Warna">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Harga Total</label>
                  <input
                    type="number"
                    name="harga_total"
                    x-model="form.harga_total"
                    readonly
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Harga Total">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Nama Supplier</label>
                  <input
                    type="text"
                    name="nama_supplier"
                    x-model="form.nama"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Nama Supplier">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Kontak Supplier</label>
                  <input
                    type="text"
                    name="kontak"
                    x-model="form.kontak"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Kontak Supplier">
                </div>

                <div>
                  <label class="block text-sm font-medium text-white/90 mb-2">Alamat Supplier</label>
                  <input
                    type="text"
                    name="alamat"
                    x-model="form.alamat"
                    class="w-full p-3 bg-white/20 border border-white/30 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent backdrop-blur-sm"
                    placeholder="Alamat Supplier">
                </div>

              </div>

              <div class="mt-6">
                <button
                  type="submit"
                  class="w-full py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200 font-semibold">
                  Simpan Pengadaan
                </button>
              </div>

            </form>

          </div>

        </div>

      </div>

    </main>
  </div>

  <script>
    function procurementData() {
      return {
        form: {
          admin_id: <?= $admin_id ?>,
          permintaan_id: '<?= $selectedPermintaan['id'] ?? '' ?>',
          barang_id: '<?= $selectedBarang['id'] ?? '' ?>',
          nama_barang: '<?= $selectedPermintaan['nama_barang'] ?? '' ?>',
          jumlah: '<?= $selectedPermintaan['jumlah'] ?? '' ?>',
          merk: '<?= $selectedPermintaan['merk'] ?? '' ?>',
          warna: '<?= $selectedPermintaan['warna'] ?? '' ?>',
          harga_total: '<?= ($selectedBarang && $selectedPermintaan) ? $selectedBarang['harga'] * $selectedPermintaan['jumlah'] : '' ?>',
          tanggal: '<?= date('Y-m-d') ?>'
        },

        modal: {
          open: false,
          permintaan: null,
          nama_barang: '',
        },

        pilihPermintaan(permintaan, barangList) {
          const barang = barangList.find(b =>
            b.nama_barang === permintaan.nama_barang &&
            b.merk === permintaan.merk &&
            b.warna === permintaan.warna &&
            b.deleted_at === null
          );

          /* =========================
             JIKA BARANG TIDAK ADA
          ========================= */
          if (!barang) {
            this.modal.open = true;
            this.modal.permintaan = permintaan;
            this.modal.nama_barang = permintaan.nama_barang;
            return;
          }

          /* =========================
             JIKA BARANG ADA
          ========================= */
          const p = new URLSearchParams({
            permintaan_id: permintaan.id,
            barang_id: barang.id,
            success: 'itemfound'
          });
          window.location.href = 'procurement.php?' + p.toString();
        },

        konfirmasiTambahBarang() {
          const p = new URLSearchParams({
            openModal: 1,
            from: 'procurement',
            permintaan_id: this.modal.permintaan.id,
            nama_barang: this.modal.permintaan.nama_barang,
            merk: this.modal.permintaan.merk ?? '',
            warna: this.modal.permintaan.warna ?? '',
            jumlah: this.modal.permintaan.jumlah
          });

          window.location.href = '../admin/item.php?' + p.toString();
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