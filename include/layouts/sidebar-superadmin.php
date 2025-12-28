<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($base_url)) {
  $base_url = "/digiplan_indonesia/";
}

$name = $_SESSION['name'] ?? 'Super Admin';

$rawRole = $_SESSION['role'] ?? 'super_admin';

// Mapping role DB → label tampilan
$roleMap = [
  'super_admin' => 'Super Admin',
  'admin'       => 'Admin',
  'customer'    => 'Customer',
];

$role = $roleMap[$rawRole] ?? ucfirst(str_replace('_', ' ', $rawRole));

?>

<aside class="w-64 h-screen fixed bg-gradient-to-b from-gray-900 via-black to-black
text-gray-200 shadow-2xl flex flex-col">

  <!-- USER INFO -->
  <div class="p-4 border-b border-white/10">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600
      flex items-center justify-center text-white font-bold shadow">
        <?= strtoupper(substr($name, 0, 1)); ?>
      </div>

      <div class="leading-tight">
        <p class="text-xs text-gray-400">Logged in as</p>
        <p class="text-sm font-semibold text-white">
          <?= htmlspecialchars($name); ?>
        </p>
        <span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-400">
          <?= htmlspecialchars($role); ?>
        </span>
      </div>
    </div>
  </div>

  <!-- LOGO -->
  <div class="px-4 py-3">
    <h1 class="text-lg font-bold tracking-wide text-white">
      DigiPlan Indonesia
    </h1>
  </div>

  <!-- NAVIGATION (SCROLLABLE) -->
  <nav class="flex-1 overflow-y-auto px-3 pb-4
  scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
    <ul class="space-y-1 text-sm">

      <?php
      function navItem($href, $label, $icon)
      {
        return "
        <li>
          <a href='{$href}' class='flex items-center gap-3 px-3 py-2.5 rounded-lg
          transition hover:bg-white/10'>
            {$icon}
            <span>{$label}</span>
          </a>
        </li>";
      }
      ?>

      <?= navItem(
        $base_url . "superadmin/dashboard.php",
        "Dashboard",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M3 10.5L12 3l9 7.5V21a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/products.php",
        "Kelola Produk",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4m0 0L4 7"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/item-approval.php",
        "Permintaan Barang",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M9 12h6m-6 4h3M9 4h6l3 3v13a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/procurement.php",
        "Pengadaan Barang",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M12 6v12m6-6H6"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/distribution.php",
        "Distribusi Barang",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M3 7h18M3 12h18M3 17h18"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/item.php",
        "Barang",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/invoice.php",
        "Invoice",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M8 7h8M8 11h8M8 15h5M6 3h12a1 1 0 011 1v17l-3-2-3 2-3-2-3 2V4a1 1 0 011-1z"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/report.php",
        "Laporan",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M4 6h16M4 10h16M4 14h10M4 18h7"/>
  </svg>'
      ); ?>

      <?= navItem(
        $base_url . "superadmin/user-management.php",
        "User Management",
        '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
    d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM4 21v-1a7 7 0 0114 0v1"/>
  </svg>'
      ); ?>


    </ul>
  </nav>

  <!-- LOGOUT -->
  <div class="p-3 border-t border-white/10">
    <a href="<?= $base_url ?>auth/logout.php"
      class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
      text-red-400 hover:bg-red-500/10 transition">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
          d="M17 16l4-4m0 0l-4-4m4 4H7" />
      </svg>
      Logout
    </a>
  </div>

</aside>