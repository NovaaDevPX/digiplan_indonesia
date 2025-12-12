<?php
require 'item-request-func.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Permintaan Barang | DigiPlan Indonesia</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body class="bg-gray-100">

  <?php include '../include/layouts/sidebar-admin.php'; ?>

  <div class="flex">

    <!-- Content -->
    <main class="ml-64 p-10 w-full">
      <div class="container mx-auto">
        <h2 class="text-2xl font-semibold mb-6">Daftar Permintaan Barang dari Customer</h2>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-800 text-white">
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">No</th>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">Nama Customer</th>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">Nama Barang</th>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">Jumlah</th>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-sm font-medium uppercase">Tanggal</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php $no = 1;
                while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?= $no++ ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['nama_user']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= $row['jumlah']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php
                      switch ($row['status']) {
                        case 'proses':
                          echo "<span class='px-2 py-1 rounded bg-blue-400 text-white text-xs font-semibold'>Menunggu verifikasi admin</span>";
                          break;
                        case 'accepted_by_superadmin':
                          echo "<span class='px-2 py-1 rounded bg-green-500 text-white text-xs font-semibold'>Sudah diverifikasi</span>";
                          break;
                        case 'reject':
                          echo "<span class='px-2 py-1 rounded bg-red-500 text-white text-xs font-semibold'>Ditolak oleh Super Admin</span>";
                          break;
                        case 'Ditolak':
                          echo "<span class='px-2 py-1 rounded bg-red-500 text-white text-xs font-semibold'>Ditolak</span>";
                          break;
                        default:
                          echo "<span class='px-2 py-1 rounded bg-yellow-300 text-gray-800 text-xs font-semibold'>Proses</span>";
                      }
                      ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

</body>

</html>