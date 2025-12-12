<?php
require 'item-approval-func.php';
$data_permintaan = [];
while ($row = $result->fetch_assoc()) {
  $data_permintaan[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Persetujuan Barang | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
  <?php include '../include/base-url.php'; ?>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-100 overflow-x-hidden" x-data="{ openModalId: null }">
  <div class="flex">
    <?php include '../include/layouts/sidebar-superadmin.php'; ?>

    <!-- Content -->
    <main class="ml-64 p-10 w-full">
      <h2 class="text-2xl font-semibold mb-6">Persetujuan Permintaan Barang</h2>

      <div class="backdrop-blur-xl bg-white/60 p-6 rounded-xl shadow">
        <div class="overflow-x-auto">
          <table class="min-w-max w-full divide-y divide-gray-200">
            <thead class="bg-gray-800 text-white sticky top-0 z-10">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">No</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Customer</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Admin</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Barang</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Jumlah</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Status</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Tanggal</th>
                <th class="px-4 py-2 text-left text-sm font-medium uppercase">Aksi</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <?php $no = 1;
              foreach ($data_permintaan as $row): ?>
                <tr class="hover:bg-gray-100">
                  <td class="px-4 py-2 whitespace-nowrap"><?= $no++ ?></td>
                  <td class="px-4 py-2 max-w-xs truncate"><?= htmlspecialchars($row['nama_user']); ?></td>
                  <td class="px-4 py-2 max-w-xs truncate">
                    <?= $row['nama_admin'] ?: '<i class="text-gray-400">Belum diverifikasi</i>'; ?>
                  </td>
                  <td class="px-4 py-2 max-w-sm truncate"><?= htmlspecialchars($row['nama_barang']); ?></td>
                  <td class="px-4 py-2 whitespace-nowrap"><?= $row['jumlah']; ?></td>
                  <td class="px-4 py-2 whitespace-nowrap">
                    <?php
                    if ($row['status'] == 'accepted_by_superadmin') echo "<span class='px-2 py-1 rounded bg-green-500 text-white text-xs font-semibold'>Diterima</span>";
                    elseif ($row['status'] == 'reject') echo "<span class='px-2 py-1 rounded bg-red-500 text-white text-xs font-semibold'>Ditolak</span>";
                    elseif ($row['status'] == 'proses') echo "<span class='px-2 py-1 rounded bg-blue-400 text-white text-xs font-semibold'>Menunggu Super Admin</span>";
                    else echo "<span class='px-2 py-1 rounded bg-yellow-300 text-gray-800 text-xs font-semibold'>Proses</span>";
                    ?>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap"><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                  <td class="px-4 py-2 whitespace-nowrap relative">
                    <?php if ($row['status'] == 'proses'): ?>
                      <div x-data="{ open: false }" class="relative inline-block text-left">
                        <button @click="open = !open"
                          class="inline-flex justify-center items-center w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                          </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false"
                          class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg z-50">
                          <!-- Form Terima -->
                          <form action="item-approval-func.php" method="POST">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <input type="hidden" name="aksi" value="terima">
                            <button type="submit" class="block w-full text-left px-4 py-2 text-green-600 hover:bg-green-100">Terima</button>
                          </form>
                          <!-- Button Tolak buka modal -->
                          <button @click="openModalId = <?= $row['id']; ?>; open=false"
                            class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-100">Tolak</button>
                        </div>
                      </div>
                    <?php else: ?>
                      <span class="text-gray-500 text-sm">Selesai</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal Tolak (satu modal saja) -->
  <div x-show="openModalId !== null" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-96">
      <h3 class="text-lg font-semibold mb-4">Alasan Penolakan</h3>
      <form action="item-approval-func.php" method="POST">
        <input type="hidden" name="id" :value="openModalId">
        <input type="hidden" name="aksi" value="tolak">
        <textarea name="catatan_admin" class="w-full border rounded p-2 mb-4" placeholder="Masukkan catatan..." required></textarea>
        <div class="flex justify-end gap-2">
          <button type="button" @click="openModalId = null" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
          <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">Tolak</button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>