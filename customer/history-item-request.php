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
                        ?>
                      </td>

                      <td class="p-4"><?= date('d M Y', strtotime($row['created_at'])) ?></td>

                      <!-- AKSI -->
                      <td class="p-4 space-y-2">

                        <!-- DETAIL -->
                        <button
                          onclick='showDetail(<?= json_encode($row) ?>)'
                          class="block w-full px-4 py-2 bg-indigo-500/20 text-indigo-300 rounded-lg text-xs hover:bg-indigo-500/30 transition">
                          Detail
                        </button>

                        <!-- KONFIRMASI DITERIMA -->
                        <?php if (
                          $row['status'] === 'selesai' &&
                          $row['status_distribusi'] === 'dikirim'
                        ): ?>
                          <a href="distribution-received.php?id=<?= $row['distribusi_id']; ?>"
                            onclick="return confirm('Apakah Anda yakin barang sudah diterima?')"
                            class="block w-full px-4 py-2 bg-emerald-600/80 text-white rounded-lg text-xs hover:bg-emerald-700 transition text-center">
                            Konfirmasi Diterima
                          </a>
                        <?php endif; ?>

                        <!-- SUDAH DITERIMA -->
                        <?php if ($row['status_distribusi'] === 'diterima'): ?>
                          <span class="block w-full px-4 py-2 bg-gray-500/20 text-gray-300 rounded-lg text-xs text-center">
                            Barang Diterima
                          </span>
                        <?php endif; ?>

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
    class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-slate-900 border border-white/20 rounded-2xl p-6 w-full max-w-lg">
      <h3 class="text-xl font-bold mb-4">Detail Permintaan</h3>

      <div class="space-y-2 text-sm">
        <p><b>Kode:</b> <span id="d_kode"></span></p>
        <p><b>Barang:</b> <span id="d_barang"></span></p>
        <p><b>Merk:</b> <span id="d_merk"></span></p>
        <p><b>Warna:</b> <span id="d_warna"></span></p>
        <p><b>Jumlah:</b> <span id="d_jumlah"></span></p>
        <p><b>Status:</b> <span id="d_status"></span></p>
        <p><b>Catatan Admin:</b></p>
        <p class="text-white/80 italic" id="d_catatan"></p>
      </div>

      <div class="mt-6 text-right">
        <button onclick="closeModal()"
          class="px-4 py-2 bg-gray-600/30 rounded-lg hover:bg-gray-600/50 transition">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <!-- SCRIPT -->
  <script>
    function showDetail(data) {
      document.getElementById('modal').classList.remove('hidden');
      document.getElementById('modal').classList.add('flex');

      document.getElementById('d_kode').innerText = data.kode_permintaan;
      document.getElementById('d_barang').innerText = data.nama_barang;
      document.getElementById('d_merk').innerText = data.merk ?? '-';
      document.getElementById('d_warna').innerText = data.warna ?? '-';
      document.getElementById('d_jumlah').innerText = data.jumlah;
      document.getElementById('d_status').innerText = data.status.replace('_', ' ');
      document.getElementById('d_catatan').innerText = data.catatan_admin ?? '-';
    }

    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
      document.getElementById('modal').classList.remove('flex');
    }
  </script>

</body>

</html>