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
      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600
      flex items-center justify-center text-white font-bold shadow">
        <?= strtoupper(substr($name, 0, 1)); ?>
      </div>

      <div class="leading-tight">
        <p class="text-xs text-gray-400">Logged in as</p>
        <p class="text-sm font-semibold text-white">
          <?= htmlspecialchars($name); ?>
        </p>
        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400">
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

  <!-- NAVIGATION -->
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
        $base_url . "customer/dashboard.php",
        "Dashboard",
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
          d="M3 10.5L12 3l9 7.5V21a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>
        </svg>'
      ); ?>

      <?= navItem(
        $base_url . "customer/form-item-request.php",
        "Permintaan Barang",
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 3h11.5"/>
        </svg>'
      ); ?>

      <?= navItem(
        $base_url . "customer/history-item-request.php",
        "Riwayat Permintaan",
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
          d="M12 8v4l3 3M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/>
        </svg>'
      ); ?>

      <?= navItem(
        $base_url . "customer/invoice.php",
        "Invoice",
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
          d="M8 7h8M8 11h8M8 15h5M6 3h12a1 1 0 011 1v17l-3-2-3 2-3-2-3 2V4z"/>
        </svg>'
      ); ?>

    </ul>
  </nav>

  <!-- LOGOUT -->
  <div class="p-3 border-t border-white/10">
    <a href="<?= $base_url ?>auth/logout.php"
      class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm
      text-red-400 hover:bg-red-500/10 transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
          d="M17 16l4-4m0 0l-4-4m4 4H7" />
      </svg>
      Logout
    </a>
  </div>

</aside>