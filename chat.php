<?php
require 'function.php';
require 'cek.php';
cek_role(['admin','super_admin','customer']); // akses semua role
$me = $_SESSION['user_id'];
$me_name = $_SESSION['name'];

// folder upload chat
$uploadDir = __DIR__ . '/uploads/chat/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// helper: dapatkan role name (coba roles table, kalau nggak ada, fallback)
function getRoleName($conn, $role_id) {
    $role_name = null;
    // coba ambil dari tabel roles jika ada
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'roles'");
    if (mysqli_num_rows($check) > 0) {
        $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM roles WHERE id='".intval($role_id)."' LIMIT 1"));
        if ($r) $role_name = $r['name'];
    }
    // fallback mapping jika role_name masih null
    if (!$role_name) {
        $map = [1=>'super_admin', 2=>'admin', 3=>'customer'];
        $role_name = $map[intval($role_id)] ?? 'user';
    }
    return $role_name;
}

// ========== AJAX endpoints (all handled in this file) ==========
if (isset($_GET['ajax'])) {
    $ajax = $_GET['ajax'];

    // 1) ambil daftar user (dengan opsi search)
    if ($ajax === 'users') {
        $qstr = '';
        if (!empty($_GET['q'])) {
            $q = mysqli_real_escape_string($conn, $_GET['q']);
            $qstr = " AND (u.name LIKE '%$q%' OR u.email LIKE '%$q%')";
        }
        // ambil semua user selain saya
        $sql = "SELECT u.id, u.name, u.email, u.role_id, r.name AS role_name,
                       COALESCE(u.last_active, NULL) AS last_active
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id != '$me' $qstr
                ORDER BY u.name ASC";
        $res = mysqli_query($conn, $sql);
        $out = [];
        while ($row = mysqli_fetch_assoc($res)) {
            // fallback jika role_name null
            if (empty($row['role_name'])) $row['role_name'] = getRoleName($conn, $row['role_id']);
            // unread count between me and user
            $uid = $row['id'];
            $c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM chat WHERE pengirim_id='$uid' AND penerima_id='$me' AND dibaca=0"));
            $row['unread'] = intval($c['c']);
            $out[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($out);
        exit;
    }

    // 2) load messages (dengan search & group)
    if ($ajax === 'load') {
        $other = intval($_GET['user'] ?? 0);
        $group = isset($_GET['group']) ? intval($_GET['group']) : null;
        $search = '';
        if (!empty($_GET['q'])) {
            $search = " AND c.pesan LIKE '%".mysqli_real_escape_string($conn, $_GET['q'])."%'";
        }
        $rows = [];
        if ($group) {
            $sql = "SELECT c.*, u.name AS pengirim_name FROM chat c LEFT JOIN users u ON c.pengirim_id=u.id
                    WHERE c.group_id = '$group' $search ORDER BY c.waktu ASC";
        } else {
            $sql = "SELECT c.*, u.name AS pengirim_name FROM chat c LEFT JOIN users u ON c.pengirim_id=u.id
                    WHERE (c.pengirim_id='$me' AND c.penerima_id='$other') OR (c.pengirim_id='$other' AND c.penerima_id='$me')
                    $search
                    ORDER BY c.waktu ASC";
        }
        $res = mysqli_query($conn, $sql);
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;

        // tandai dibaca untuk pesan yg ditujukan ke saya (hanya di chat personal)
        if (!$group && $other) {
            mysqli_query($conn, "UPDATE chat SET dibaca=1 WHERE penerima_id='$me' AND pengirim_id='$other' AND dibaca=0");
        }

        header('Content-Type: application/json');
        echo json_encode($rows);
        exit;
    }

    // 3) kirim pesan (bisa text, file image, audio) - method POST
    if ($ajax === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $to = intval($_POST['to'] ?? 0);
        $group = isset($_POST['group']) && $_POST['group'] !== '' ? intval($_POST['group']) : null;
        $pesan = trim($_POST['pesan'] ?? '');
        $tipe = 'text'; $lampiran = null;

        // handle file upload jika ada (image / audio)
        if (!empty($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $f = $_FILES['file'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowedImg = ['png','jpg','jpeg','gif','webp'];
            $allowedAudio = ['mp3','wav','ogg','m4a'];
            $new = time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest = $uploadDir . $new;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $lampiran = 'uploads/chat/' . $new;
                if (in_array($ext, $allowedImg)) $tipe = 'image';
                elseif (in_array($ext, $allowedAudio)) $tipe = 'audio';
                else $tipe = 'text';
            }
        }

        // jika pesan kosong dan tidak ada lampiran -> fail
        if ($pesan === '' && !$lampiran) { echo json_encode(['ok'=>false,'msg'=>'Pesan kosong']); exit; }

        // insert ke DB (jika belum ada kolom 'tipe'/'lampiran' di DB, simpan tipe di pesan sebagai JSON fallback)
        $pesan_safe = mysqli_real_escape_string($conn, $pesan);
        // jika kolom tipe/lampiran ada
        $hasCols = mysqli_query($conn, "SHOW COLUMNS FROM chat LIKE 'tipe'");
        if (mysqli_num_rows($hasCols) > 0) {
            $group_sql = $group ? ", group_id='$group'" : ", group_id=NULL";
            $lamp = $lampiran ? ", lampiran='".mysqli_real_escape_string($conn,$lampiran)."'" : ", lampiran=NULL";
            mysqli_query($conn, "INSERT INTO chat (pengirim_id,penerima_id,pesan,tipe,lampiran,group_id) VALUES ('$me','".($to?$to:0)."','".($pesan_safe)."','$tipe','".($lampiran?mysqli_real_escape_string($conn,$lampiran):'')."',".($group? $group : "NULL").")");
        } else {
            // fallback: simpan pesan sebagai JSON string
            $payload = json_encode(['text'=>$pesan, 'type'=>$tipe, 'file'=>$lampiran]);
            mysqli_query($conn, "INSERT INTO chat (pengirim_id,penerima_id,pesan) VALUES ('$me','".($to?$to:0)."','".mysqli_real_escape_string($conn,$payload)."')");
        }

        // optional: tambah notifikasi (jika ada fungsi)
        if (function_exists('tambahNotifikasi')) {
            if ($group) {
                // notifikasi ke semua anggota grup? implementasi tergantung struktur grup (tidak saya buat di sini)
            } else {
                tambahNotifikasi($to, mysqli_insert_id($conn), "Pesan baru dari $me_name");
            }
        }

        echo json_encode(['ok'=>true]);
        exit;
    }

    // 4) unread count (badge)
    if ($ajax === 'unread') {
        $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM chat WHERE penerima_id='$me' AND dibaca=0"));
        echo json_encode(['unread'=>intval($r['c'])]);
        exit;
    }

    // 5) typing indicator (simple: set transient in files)
    if ($ajax === 'typing') {
        $other = intval($_POST['user'] ?? 0);
        $status = $_POST['status'] ?? 'stop'; // 'start' or 'stop'
        $file = sys_get_temp_dir() . "/chat_typing_{$other}_{$me}.tmp";
        if ($status === 'start') file_put_contents($file, time());
        else if (file_exists($file)) unlink($file);
        echo "ok"; exit;
    }

    exit;
} // end ajax

// ==================================================================
// HTML + JS UI (single file) - bagian tampilan
// ==================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Chat | DigiPlan Indonesia</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <style>
    .chat-container { display:flex; gap:18px; }
    .user-list { width:320px; border:1px solid #ddd; height:640px; overflow:auto; padding:8px; }
    .chat-box { flex:1; border:1px solid #ddd; display:flex; flex-direction:column; height:640px; padding:8px; }
    .messages { flex:1; overflow:auto; background:#f7f9fb; padding:12px; }
    .msg-me { text-align:right; margin:10px 0; }
    .msg-other { text-align:left; margin:10px 0; }
    .bubble { display:inline-block; padding:10px 14px; border-radius:14px; max-width:70%; }
    .me { background:#0d6efd; color:#fff; }
    .other { background:#e9ecef; color:#000; }
    .meta { font-size:11px; opacity:.7; margin-top:6px; }
    .input-area { display:flex; gap:8px; margin-top:10px; align-items:center; }
    textarea { flex:1; resize:vertical; }
    .small-muted { font-size:12px; color:#666; }
    .user-item { padding:8px; border-bottom:1px solid #efefef; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
    .user-left { display:flex; gap:10px; align-items:center; }
    .avatar { width:40px;height:40px;border-radius:50%;background:#ddd;display:inline-block; }
    .badge-unread { background:#dc3545;color:#fff;padding:3px 7px;border-radius:12px;font-size:12px; }
    .typing { font-style:italic; font-size:13px; color:#666; }
    .search-input { margin-bottom:8px; }
  </style>
</head>

<body class="sb-nav-fixed">

<div id="notif-container" style="position: fixed; top: 20px; right: 20px; z-index: 2000;"></div>

<script>
function tampilkanNotifikasi(pesan) {
    let box = document.createElement('div');
    box.innerHTML = pesan;
    box.className = 'alert alert-info shadow';
    box.style = 'margin-bottom:10px; min-width:250px;';
    document.getElementById('notif-container').appendChild(box);
    setTimeout(() => box.remove(), 5000);
}

function cekNotifikasi() {
    fetch('get_notifikasi.php')
        .then(res => res.json())
        .then(data => {
            data.forEach(n => tampilkanNotifikasi(n.pesan));
        });
}

setInterval(cekNotifikasi, 10000); // cek tiap 10 detik
</script>

<!-- Top Navbar -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="index.php">DigiPlan Indonesia</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <ul class="navbar-nav ms-auto me-3 me-lg-4"></ul>
</nav>

<!-- Layout wrapper -->
<div id="layoutSidenav">

    <!-- SIDEBAR -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">

            <div class="sb-sidenav-menu">
                <div class="nav">
                    <?php
                        // pastikan variabel role ada
                        $role = $_SESSION['role'] ?? '';

                        if ($role == 'customer') {
                            include 'sidebar_customer.php';
                        } elseif ($role == 'admin') {
                            include 'sidebar_admin.php';
                        } elseif ($role == 'super_admin') {
                            include 'sidebar_superadmin.php';
                        } else {
                            echo "<div class='p-3 text-danger'>Role tidak dikenali</div>";
                        }
                    ?>
                </div>
            </div>

            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <?= htmlspecialchars($_SESSION['name']); ?>
            </div>
        </nav>
    </div>

<div class="container-fluid px-4 mt-4">
  <h2>Chat</h2>
  <div class="card mt-3">
    <div class="card-body">
      <div class="chat-container">
        <!-- left: user list -->
        <div class="user-list">
          <input type="text" id="searchUser" class="form-control search-input" placeholder="Cari user...">
          <div id="usersArea">Memuat...</div>
        </div>

        <!-- right: chat box -->
        <div class="chat-box">
          <div id="chatHeader"><b>Pilih user/room</b></div>
          <div class="messages" id="messages"></div>

          <div class="small-muted" id="typingIndicator"></div>

          <div class="input-area">
            <input type="file" id="fileInput" accept="image/*,audio/*" style="display:none;">
            <button id="attachBtn" class="btn btn-light" title="Lampirkan gambar/audio">📎</button>

            <textarea id="pesan" rows="2" class="form-control" placeholder="Tulis pesan..." disabled></textarea>

            <button id="recordBtn" class="btn btn-outline-secondary" title="Rekam audio">🎤</button>

            <button id="kirimBtn" class="btn btn-primary" disabled>Kirim</button>
          </div>

          <div class="mt-2 d-flex justify-content-between align-items-center">
            <div>
              <small id="unreadBadge" class="badge bg-danger" style="display:none"></small>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
let me = <?= json_encode($me); ?>;
let meName = <?= json_encode($me_name); ?>;
let selectedUser = 0;
let selectedGroup = 0;
let pollTimer = null;
let typingTimer = null;
let isTyping = false;
let recording = false;
let mediaRecorder = null;
let recordedChunks = [];

// util escape
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// load users (with search)
function loadUsers(q='') {
  fetch('chat.php?ajax=users' + (q? '&q=' + encodeURIComponent(q):''))
    .then(r=>r.json()).then(data=>{
      let html = '';
      data.forEach(u=>{
       html += `<div class="user-item" onclick="selectUser(${u.id},'${esc(u.name)}')">
          <div class="user-left">
            <div class="avatar"></div>
            <div>
              <div><b>${esc(u.name)}</b></div>
              <small class="small-muted">${esc(u.role_name||'user')}</small>
            </div>
          </div>
          <div>
            ${u.unread ? `<span class="badge-unread">${u.unread}</span>` : ''}
          </div>
        </div>`;
      });
      if (!data.length) html = '<div class="small-muted">Tidak ada user</div>';
      document.getElementById('usersArea').innerHTML = html;
    });
}

// select a user to chat
function selectUser(id, name) {
  selectedUser = id;
  selectedGroup = 0;
  document.getElementById('chatHeader').innerHTML = '<b>Chat dengan:</b> ' + esc(name);
  document.getElementById('pesan').disabled = false;
  document.getElementById('kirimBtn').disabled = false;
  loadMessages();
  if (pollTimer) clearInterval(pollTimer);
  pollTimer = setInterval(loadMessages, 1800);
}

// load messages
function loadMessages() {
  if (!selectedUser && !selectedGroup) return;
  let url = 'chat.php?ajax=load' + (selectedUser ? '&user=' + selectedUser : '') + (selectedGroup ? '&group=' + selectedGroup : '');
  fetch(url).then(r=>r.json()).then(data=>{
    let html = '';
    data.forEach(m=>{
      let meMsg = (m.pengirim_id == me);
      let tipe = m.tipe || (isJson(m.pesan) ? JSON.parse(m.pesan).type || 'text' : 'text');
      let payload = m.pesan;
      if (isJson(payload)) {
        payload = JSON.parse(payload);
      }
      html += `<div class="${meMsg ? 'msg-me' : 'msg-other'}">`;
      html += `<div class="bubble ${meMsg ? 'me' : 'other'}">`;
      html += `<small>${meMsg ? 'Saya' : esc(m.pengirim_name || 'User')}</small><br>`;
      // kalau pesan dikirim sebagai JSON payload (fallback)
      if (typeof payload === 'object') {
        if (payload.type === 'image' && payload.file) {
          html += `<div><img src="${esc(payload.file)}" style="max-width:300px;border-radius:8px;cursor:pointer" onclick="window.open('${esc(payload.file)}')"></div>`;
        } else if (payload.type === 'audio' && payload.file) {
          html += `<div><audio controls src="${esc(payload.file)}"></audio></div>`;
        }
        if (payload.text) html += `<div>${esc(payload.text)}</div>`;
      } else {
        // jika kolom tipe/lampiran terpisah
        if (tipe === 'image' && m.lampiran) {
          html += `<div><img src="${esc(m.lampiran)}" style="max-width:300px;border-radius:8px;cursor:pointer" onclick="window.open('${esc(m.lampiran)}')"></div>`;
        } else if (tipe === 'audio' && m.lampiran) {
          html += `<div><audio controls src="${esc(m.lampiran)}"></audio></div>`;
        }
        html += `<div>${esc(payload)}</div>`;
      }
      html += `<div class="meta">${m.waktu}</div>`;
      html += `</div></div>`;
    });
    document.getElementById('messages').innerHTML = html;
    document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
    // clear unread badge
    updateUnreadBadge();
    // check typing indicator files (simple)
    checkTypingStatus();
  });
}

// send message (text or file)
function sendMessage() {
  if ((!selectedUser && !selectedGroup) || (document.getElementById('pesan').value.trim()==='' && !document.getElementById('fileInput').files.length && !recordedChunks.length)) return;
  let fd = new FormData();
  if (selectedUser) fd.append('to', selectedUser);
  if (selectedGroup) fd.append('group', selectedGroup);
  fd.append('pesan', document.getElementById('pesan').value.trim());

  // file attach
  if (document.getElementById('fileInput').files.length) {
    fd.append('file', document.getElementById('fileInput').files[0]);
  }
  // audio recorded
  if (recordedChunks.length) {
    const blob = new Blob(recordedChunks, { type: 'audio/webm' });
    fd.append('file', blob, 'voice_' + Date.now() + '.webm');
  }

  fetch('chat.php?ajax=send', { method:'POST', body: fd })
    .then(r=>r.json()).then(res=>{
      if (res.ok) {
        document.getElementById('pesan').value = '';
        document.getElementById('fileInput').value = '';
        recordedChunks = []; recording = false;
        loadMessages();
      } else alert('Gagal kirim: ' + (res.msg || ''));
    });
}

// unread badge
function updateUnreadBadge() {
  fetch('chat.php?ajax=unread').then(r=>r.json()).then(j=>{
    const el = document.getElementById('unreadBadge');
    if (j.unread > 0) { el.style.display='inline-block'; el.innerText = j.unread; } else el.style.display='none';
  });
}

// typing indicator (simple)
document.getElementById('pesan').addEventListener('input', function(){
  if (!selectedUser) return;
  if (!isTyping) {
    isTyping = true;
    navigatorSendTyping('start');
    clearTimeout(typingTimer);
    typingTimer = setTimeout(()=>{ isTyping=false; navigatorSendTyping('stop'); }, 2000);
  } else {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(()=>{ isTyping=false; navigatorSendTyping('stop'); }, 2000);
  }
});
function navigatorSendTyping(status){
  let fd = new FormData();
  fd.append('user', selectedUser);
  fd.append('status', status);
  fetch('chat.php?ajax=typing', { method:'POST', body: fd });
}
function checkTypingStatus(){
  // simplified: try to fetch a temp file created by other user (server writes tmp files)
  // we don't implement server->client push here; but this will do a quick check by calling an endpoint if needed
  // for now we'll clear indicator
  document.getElementById('typingIndicator').innerText = '';
}

// audio record (MediaRecorder)
document.getElementById('recordBtn').addEventListener('click', async function(){
  if (recording) {
    // stop
    mediaRecorder.stop();
    recording = false;
    this.innerText = '🎤';
  } else {
    // start recording
    if (!navigator.mediaDevices) { alert('Browser tidak mendukung perekaman'); return; }
    const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
    mediaRecorder = new MediaRecorder(stream);
    recordedChunks = [];
    mediaRecorder.ondataavailable = e => { if (e.data.size>0) recordedChunks.push(e.data); };
    mediaRecorder.onstop = e => { /* will be sent on sendMessage */ };
    mediaRecorder.start();
    recording = true;
    this.innerText = '⏹️';
  }
});

// attach button
document.getElementById('attachBtn').addEventListener('click', ()=> document.getElementById('fileInput').click());
document.getElementById('fileInput').addEventListener('change', ()=> {
  // preview optional
});

// send button
document.getElementById('kirimBtn').addEventListener('click', sendMessage);

// search user
document.getElementById('searchUser').addEventListener('input', function(){
  loadUsers(this.value);
});

// initial load
loadUsers();
updateUnreadBadge();
setInterval(updateUnreadBadge, 5000);

// helper
function isJson(str) {
  try { JSON.parse(str); return true; } catch(e){ return false; }
}
</script>
</main>
</div>

<!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>

</body>
</html>
