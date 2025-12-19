<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['customer']);

$user_id = $_SESSION['user_id'];

/* ======================
   DATA RIWAYAT PERMINTAAN
====================== */
$query = $conn->prepare("
  SELECT 
    pm.*,
    d.id AS distribusi_id,
    d.status_distribusi
  FROM permintaan_barang pm
  LEFT JOIN distribusi_barang d 
    ON d.permintaan_id = pm.id
  WHERE pm.user_id = ?
    AND pm.deleted_at IS NULL
  ORDER BY pm.created_at DESC
");

$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Riwayat Permintaan | DigiPlan Indonesia</title>

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

    <?php include '../include/layouts/sidebar-customer.php'; ?>

    <main class="ml-64 p-10 w-full flex-1">

      <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl mb-8">
          <h1 class="text-4xl font-bold mb-2">Riwayat Permintaan</h1>
          <p class="text-white/80">Daftar seluruh permintaan barang yang pernah Anda ajukan.</p>
        </div>

        <!-- TABLE -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-xl overflow-hidden">
              <thead>
                <tr class="bg-white/20">
                  <th class="p-4 text-left">Kode</th>
                  <th class="p-4 text-left">Barang</th>
                  <th class="p-4 text-left">Jumlah</th>
                  <th class="p-4 text-left">Status</th>
                  <th class="p-4 text-left">Tanggal</th>
                  <th class="p-4 text-left">Aksi</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-white/10">
                <?php if ($result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-white/5 transition">

                      <td class="p-4 font-medium"><?= $row['kode_permintaan'] ?></td>
                      <td class="p-4"><?= $row['nama_barang'] ?></td>
                      <td class="p-4"><?= $row['jumlah'] ?></td>

                      <!-- STATUS -->
                      <td class="p-4 capitalize">
                        <?php
                        // PRIORITAS: butuh konfirmasi penerimaan
                        if (
                          $row['status'] === 'selesai' &&
                          $row['status_distribusi'] === 'dikirim'
                        ) {
                          echo "<span class='px-3 py-1 bg-orange-500/20 text-orange-300 rounded-lg text-xs'>
            Menunggu Konfirmasi Penerimaan
          </span>";
                        } else {

                          switch ($row['status']) {
                            case 'diajukan':
                              echo "<span class='px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg text-xs'>Diajukan</span>";
                              break;
                            case 'disetujui':
                              echo "<span class='px-3 py-1 bg-green-500/20 text-green-300 rounded-lg text-xs'>Disetujui</span>";
                              break;
                            case 'ditolak':
                              echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs'>Ditolak</span>";
                              break;
                            case 'dibatalkan':
                              echo "<span class='px-3 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs'>Dibatalkan</span>";
                              break;
                            case 'dalam_pengadaan':
                              echo "<span class='px-3 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs'>Dalam Pengadaan</span>";
                              break;
                            case 'siap_distribusi':
                              echo "<span class='px-3 py-1 bg-purple-500/20 text-purple-300 rounded-lg text-xs'>Siap Distribusi</span>";
                              break;
                            case 'selesai':
                              echo "<span class='px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-lg text-xs'>Selesai</span>";
                              break;
                            default:
                              echo "-";
                          }
                        }
                        ?>
                      </td>


                      <td class="p-4"><?= date('d M Y', strtotime($row['created_at'])) ?></td>

                      <td class="p-4 relative">

                        <!-- BUTTON TITIK TIGA -->
                        <button onclick="toggleDropdownA(<?= $row['id']; ?>, event)"
                          class="text-white/70 hover:text-white p-2 rounded-lg hover:bg-white/10 transition">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4z
               M10 12a2 2 0 110-4 2 2 0 010 4z
               M10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                          </svg>
                        </button>

                        <!-- DROPDOWN -->
                        <div id="dropdown-a-<?= $row['id']; ?>"
                          class="hidden fixed right-[60px] z-[99999] w-48 bg-slate-900/50 backdrop-blur-xl border border-white/30 rounded-xl shadow-2xl">

                          <!-- DETAIL -->
                          <button
                            onclick='showDetail(<?= json_encode($row) ?>)'
                            class="w-full text-left px-4 py-3 text-white hover:bg-white/10 transition rounded-t-xl">
                            Detail
                          </button>

                          <!-- BATALKAN PERMINTAAN -->
                          <?php if ($row['status'] === 'diajukan'): ?>
                            <form action="cancel-item-request.php" method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan permintaan ini?');">
                              <input type="hidden" name="permintaan_id" value="<?= $row['id']; ?>">
                              <button type="submit"
                                class="w-full text-left px-4 py-3 text-red-300 hover:bg-red-500/10 transition">
                                Batalkan Permintaan
                              </button>
                            </form>
                          <?php endif; ?>


                          <!-- KONFIRMASI DITERIMA -->
                          <?php if (
                            $row['status'] === 'selesai' &&
                            $row['status_distribusi'] === 'dikirim'
                          ): ?>
                            <a href="distribution-received.php?id=<?= $row['distribusi_id']; ?>"
                              onclick="return confirm('Apakah Anda yakin barang sudah diterima?')"
                              class="block px-4 py-3 text-emerald-300 hover:bg-white/10 transition">
                              Konfirmasi Diterima
                            </a>
                          <?php endif; ?>

                          <!-- SUDAH DITERIMA -->
                          <?php if ($row['status_distribusi'] === 'diterima'): ?>
                            <span
                              class="block px-4 py-3 text-gray-400 cursor-not-allowed rounded-b-xl">
                              Barang Diterima
                            </span>
                          <?php endif; ?>

                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="p-6 text-center text-white/60 italic">
                      Belum ada permintaan barang
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>

            </table>
          </div>
        </div>

      </div>

    </main>
  </div>

  <!-- MODAL DETAIL -->
  <div id="modal"
    class="fixed inset-0 bg-black/70 backdrop-blur-md hidden items-center justify-center z-50">

    <div
      class="bg-gradient-to-b from-slate-900 to-slate-950 border border-white/20 rounded-2xl shadow-2xl w-full max-w-xl p-6 transform scale-95 opacity-0 transition-all duration-300"
      id="modalContent">

      <!-- HEADER -->
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold">Detail Permintaan</h3>
        <button onclick="closeModal()"
          class="text-white/60 hover:text-white text-xl">&times;</button>
      </div>

      <!-- CONTENT -->
      <div class="grid grid-cols-2 gap-4 text-sm">

        <div>
          <p class="text-white/50">Kode Permintaan</p>
          <p class="font-semibold" id="d_kode"></p>
        </div>

        <div>
          <p class="text-white/50">Status</p>
          <span id="d_status"
            class="inline-block  px-3 py-1 rounded-lg text-xs font-medium"></span>
        </div>

        <div>
          <p class="text-white/50">Nama Barang</p>
          <p class="font-semibold" id="d_barang"></p>
        </div>

        <div>
          <p class="text-white/50">Jumlah</p>
          <p class="font-semibold" id="d_jumlah"></p>
        </div>

        <div>
          <p class="text-white/50">Merk</p>
          <p id="d_merk">-</p>
        </div>

        <div>
          <p class="text-white/50">Warna</p>
          <p id="d_warna">-</p>
        </div>
      </div>

      <!-- CATATAN -->
      <div class="mt-6">
        <p class="text-white/50 mb-1">Catatan Admin</p>
        <div
          class="bg-white/5 border border-white/10 rounded-xl p-4 text-sm text-white/80 italic min-h-[60px]"
          id="d_catatan">
          -
        </div>
      </div>

      <!-- FOOTER -->
      <div class="mt-6 text-right">
        <button onclick="closeModal()"
          class="px-5 py-2 bg-indigo-600/80 hover:bg-indigo-700 rounded-xl transition">
          Tutup
        </button>
      </div>
    </div>
  </div>


  <!-- SCRIPT -->
  <script>
    function showDetail(data) {
      const modal = document.getElementById('modal');
      const content = document.getElementById('modalContent');

      modal.classList.remove('hidden');
      modal.classList.add('flex');

      setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
      }, 50);

      document.getElementById('d_kode').innerText = data.kode_permintaan;
      document.getElementById('d_barang').innerText = data.nama_barang;
      document.getElementById('d_merk').innerText = data.merk ?? '-';
      document.getElementById('d_warna').innerText = data.warna ?? '-';
      document.getElementById('d_jumlah').innerText = data.jumlah;
      document.getElementById('d_catatan').innerText = data.catatan_admin ?? '-';

      // STATUS BADGE
      const statusEl = document.getElementById('d_status');
      let statusText = data.status.replace('_', ' ');

      let statusClass = 'bg-gray-500/20 text-gray-300';

      switch (data.status) {
        case 'diajukan':
          statusClass = 'bg-blue-500/20 text-blue-300 capitalize';
          break;
        case 'disetujui':
          statusClass = 'bg-green-500/20 text-green-300 capitalize';
          break;
        case 'ditolak':
          statusClass = 'bg-red-500/20 text-red-300 capitalize';
          break;
        case 'dibatalkan':
          statusClass = 'bg-red-500/20 text-red-300 capitalize';
          break;
        case 'dalam_pengadaan':
          statusClass = 'bg-yellow-500/20 text-yellow-300 capitalize';
          break;
        case 'siap_distribusi':
          statusClass = 'bg-purple-500/20 text-purple-300 capitalize';
          break;
        case 'selesai':
          statusClass = 'bg-emerald-500/20 text-emerald-300 capitalize';
          break;
      }

      statusEl.className = `inline-block px-3 py-1 rounded-lg text-xs font-medium ${statusClass}`;
      statusEl.innerText = statusText;
    }

    function closeModal() {
      const modal = document.getElementById('modal');
      const content = document.getElementById('modalContent');

      content.classList.add('scale-95', 'opacity-0');
      content.classList.remove('scale-100', 'opacity-100');

      setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }, 200);
    }
  </script>
  <script>
    function toggleDropdownA(id, event) {
      event.stopPropagation();

      const current = document.getElementById("dropdown-a-" + id);

      // Tutup dropdown lain
      document.querySelectorAll("[id^='dropdown-a-']").forEach(d => {
        if (d !== current) d.classList.add("hidden");
      });

      // Toggle dropdown sekarang
      current.classList.toggle("hidden");
    }

    // Tutup dropdown kalau klik di luar
    document.addEventListener("click", function() {
      document.querySelectorAll("[id^='dropdown-a-']").forEach(d => {
        d.classList.add("hidden");
      });
    });
  </script>


</body>

</html>