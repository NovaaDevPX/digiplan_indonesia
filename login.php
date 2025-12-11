<?php
session_start();
require 'function.php';

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['log']) && $_SESSION['log'] === true) {
  switch ($_SESSION['role']) {
    case 'super_admin':
      header('Location: superadmin_dashboard.php');
      break;
    case 'admin':
      header('Location: admin_dashboard.php');
      break;
    default:
      header('Location: customer_dashboard.php');
      break;
  }
  exit;
}

// =========== LOGIC LOGIN =============
if (isset($_POST['login'])) {

  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("
        SELECT users.id, users.name, users.email, users.password, roles.name AS role_name 
        FROM users
        LEFT JOIN roles ON users.role_id = roles.id
        WHERE users.email = ?
        LIMIT 1
    ");

  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // ============ FIX: Gunakan MD5 ============
    if (md5($password) === $user['password']) {

      $_SESSION['log']  = true;
      $_SESSION['user_id']   = $user['id'];
      $_SESSION['name'] = $user['name'];
      $_SESSION['email'] = $user['email'];
      $_SESSION['role'] = $user['role_name'];

      switch ($user['role_name']) {
        case 'super_admin':
          header("Location: superadmin_dashboard.php");
          exit;
        case 'admin':
          header("Location: admin_dashboard.php");
          exit;
        default:
          header("Location: customer_dashboard.php");
          exit;
      }
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Email tidak ditemukan!";
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <title>Login</title>
  <link href="css/styles.css" rel="stylesheet" />
</head>

<body style="background-color: #2c3e50 !important;">
  <div id="layoutAuthentication">
    <div id="layoutAuthentication_content">
      <main>
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-5">
              <div class="card shadow-lg border-0 rounded-lg mt-5">

                <div class="card-header text-center" style="background: transparent !important; border-bottom: 2px solid #007bff;">
                  <img src="assets/logo.png"
                    alt="DigiPlan Indonesia"
                    width="150"
                    style="background: transparent !important; box-shadow: none !important; border-radius: 0 !important;"
                    class="mb-2">
                </div>

                <div class="card-body">
                  <form method="post">
                    <div class="form-floating mb-3">
                      <input class="form-control" name="email" type="email" placeholder="name@example.com" required />
                      <label>Email address</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input class="form-control" name="password" type="password" placeholder="Password" required />
                      <label>Password</label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                      <button class="btn btn-primary" name="login">Login</button>
                      <a class="small text-end ms-3" href="register.php">Belum punya akun? Daftar</a>
                    </div>

                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>

</html>