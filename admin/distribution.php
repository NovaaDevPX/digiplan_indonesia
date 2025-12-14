<?php
require '../include/conn.php';
require '../include/auth.php';
cek_role(['admin']);

$query = "
SELECT d.*, 
       pb.kode_pengadaan,
       pm.kode_permintaan,
       pm.nama_barang,
       pm.jumlah
FROM distribusi_barang d
JOIN pengadaan_barang pb ON d.pengadaan_id = pb.id
JOIN permintaan_barang pm ON d.permintaan_id = pm.id
ORDER BY d.created_at DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Distribusi Barang</title>
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
          <h1 class="text-4xl font-bold text-white mb-2">Distribusi Barang</h1>
          <p class="text-white/80">Kelola distribusi barang kepada customer dengan mudah.</p>
        </div>

        <!-- Header with Button -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-white">Daftar Distribusi</h2>
          <a href="distribution-add.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl shadow-lg transform hover:scale-105 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah Distribusi
          </a>
        </div>

        <!-- Table -->
        <div class="backdrop-blur-xl bg-white/10 border border-white/20 p-6 rounded-2xl shadow-2xl">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse rounded-xl overflow-hidden">
              <thead>
                <tr class="bg-white/20 text-white">
                  <th class="p-4 text-left">Kode</th>
                  <th class="p-4 text-left">Permintaan</th>
                  <th class="p-4 text-left">Barang</th>
                  <th class="p-4 text-left">Jumlah</th>
                  <th class="p-4 text-left">Kurir</th>
                  <th class="p-4 text-left">Status</th>
                  <th class="p-4 text-left">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <tr class="hover:bg-white/5 transition-colors duration-200">
                    <td class="p-4 text-white/90 font-medium"><?= $row['kode_distribusi'] ?></td>
                    <td class="p-4 text-white/90"><?= $row['kode_permintaan'] ?></td>
                    <td class="p-4 text-white/90"><?= $row['nama_barang'] ?></td>
                    <td class="p-4 text-white/90"><?= $row['jumlah'] ?></td>
                    <td class="p-4 text-white/90"><?= $row['kurir'] ?></td>
                    <td class="p-4">
                      <?php if ($row['status_distribusi'] === 'dibatalkan'): ?>
                        <span class="px-3 py-1 rounded-lg bg-red-500/20 text-red-300 text-xs font-semibold border border-red-500/30">
                          Dibatalkan
                        </span>
                      <?php else: ?>
                        <span class="px-3 py-1 rounded-lg bg-green-500/20 text-green-300 text-xs font-semibold border border-green-500/30 capitalize">
                          <?= $row['status_distribusi'] ?>
                        </span>
                      <?php endif; ?>

                    </td>
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

                        <?php if ($row['status_distribusi'] !== 'dibatalkan'): ?>
                          <!-- Edit -->
                          <a href="distribution-edit.php?id=<?= $row['id']; ?>"
                            class="block px-4 py-3 text-white hover:bg-white/10 transition-colors duration-200">
                            Edit
                          </a>
                        <?php endif; ?>

                        <!-- Export PDF -->
                        <a href="singgle-report-pdf/distribution.php?kode=<?= $row['kode_distribusi']; ?>"
                          target="_blank"
                          class="block px-4 py-3 text-white hover:bg-white/10 transition-colors duration-200">
                          Export PDF
                        </a>

                        <?php if ($row['status_distribusi'] === 'dikirim'): ?>
                          <a href="distribution-cancel.php?id=<?= $row['id']; ?>"
                            onclick="return confirm('Yakin membatalkan distribusi ini?')"
                            class="block px-4 py-3 text-yellow-300 hover:bg-yellow-500/20 rounded-b-xl transition-colors duration-200">
                            Batalkan
                          </a>
                        <?php endif; ?>

                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

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

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if (isset($_GET['success']) && $_GET['pdf'] == true): ?>
    <script>
      Swal.fire({
        title: 'Berhasil!',
        text: 'Distribusi barang berhasil ditambahkan. Apakah Anda ingin membuat laporan distribusi?',
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buat Laporan',
        cancelButtonText: 'Tidak',
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280'
      }).then((result) => {
        if (result.isConfirmed) {
          window.open(
            'singgle-report-pdf/distribution.php?kode=<?= $_GET["kode"]; ?>',
            '_blank'
          );
        }
      });
    </script>
  <?php endif; ?>

</body>

</html>