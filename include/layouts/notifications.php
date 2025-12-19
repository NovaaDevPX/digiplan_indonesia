<?php
$success = $_GET['success'] ?? null;
$error   = $_GET['error'] ?? null;

function getMessage($key)
{
  $messages = [
    'item_procurement_success' => 'Barang pengadaan berhasil diinput!',
    'itemnotfound' => 'Barang tidak ditemukan! Silakan input barang terlebih dahulu.',
    'failed' => 'Terjadi kesalahan, silakan coba lagi!',
    'itemfound' => 'Data Barang Ditemukan!, Barang siap untuk diadakan.',
    'barang_masuk' => 'Data Barang Masuk Berhasil Disimpan, Siap untuk didistribusikan !',
    'cancel_item_request' => 'Permintaan barang berhasil dibatalkan.',
    'diterima' => ' Barang telah diterima !',
    'user_added' => ' User berhasil ditambahkan !',
    'user_updated' => ' User berhasil diupdate !',
    'user_deleted' => ' User berhasil dihapus !',

    'updated' => ' Data berhasil diperbarui !',
    'added' => ' Data berhasil ditambahkan !',
    'item_approv' => 'Barang berhasil di setujui !',
    'item_decline' => 'Barang berhasil di tolak !',
    'item_added' => ' Barang berhasil ditambahkan !',
    'item_updated' => ' Barang berhasil diupdate !',
    'item_deleted' => ' Barang berhasil dihapus !',
    'item_request_sent' => ' Permintaan barang berhasil dikirim !',
    'save_failed' => ' Permintaan barang gagal disimpan !',
    'item_procurement_success' => ' Barang pengadaan berhasil diinput !',
    'item_distribution_success' => ' Barang distribusi berhasil diinput !',
    'add_failed' => ' Gagal menambahkan barang !',
    'update_failed' => ' Gagal mengupdate barang !',
    'delete_failed' => ' Gagal menghapus barang !',
    'deleted' => ' Data berhasil dihapus !',
    'failed' => ' Terjadi kesalahan,
    silakan coba lagi !',
    'itemnotfound' => ' Data Barang tidak ditemukan !',
  ];

  return $messages[$key] ?? $key;
}
?>

<div id="notif-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

<script>
  function showNotification(message, type = 'success', duration = 7000, action = null) {
    const container = document.getElementById('notif-container');
    const notif = document.createElement('div');

    notif.innerHTML = `
      <div>${message}</div>
      ${action ? `
        <a href="${action.link}"
          style="display:inline-block;margin-top:12px;padding:8px 14px;
          background:#fff;color:#000;border-radius:8px;font-weight:600;">
          ${action.label}
        </a>
      ` : ''}
    `;

    notif.style.cssText = `
      background: ${type === 'success'
        ? 'linear-gradient(135deg,#28c76f,#20a55f)'
        : 'linear-gradient(135deg,#ff4e4e,#d93636)'};
      padding: 16px 20px;
      border-radius: 12px;
      color: #fff;
      box-shadow: 0 6px 20px rgba(0,0,0,.25);
      margin-top: 10px;
      opacity: 0;
      transform: translateX(50px);
      transition: all .4s ease;
    `;

    container.appendChild(notif);

    setTimeout(() => {
      notif.style.opacity = '1';
      notif.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
      notif.style.opacity = '0';
      notif.style.transform = 'translateX(50px)';
      setTimeout(() => notif.remove(), 400);
    }, duration);
  }

  <?php if ($error === 'itemnotfound'): ?>
    showNotification(
      "<?= getMessage($error); ?>",
      "error",
      9000, {
        label: "Tambah Barang",
        link: "item.php?openModal=1" +
          "&nama_barang=<?= urlencode($_GET['nama_barang'] ?? '') ?>" +
          "&merk=<?= urlencode($_GET['merk'] ?? '') ?>" +
          "&warna=<?= urlencode($_GET['warna'] ?? '') ?>"
      }
    );
  <?php elseif ($success): ?>
    showNotification("<?= getMessage($success); ?>", "success");
  <?php elseif ($error): ?>
    showNotification("<?= getMessage($error); ?>", "error");
  <?php endif; ?>
</script>