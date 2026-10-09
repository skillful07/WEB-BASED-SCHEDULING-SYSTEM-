<?php
require_once 'config/db.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$base_path = '';
$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Scheduling System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
  <div class="card auth-card shadow p-4">
    <div class="text-center mb-3">
      <i class="bi bi-calendar2-week text-primary" style="font-size:2.5rem;"></i>
      <h4 class="mt-2 mb-0">Scheduling System</h4>
      <small class="text-muted">Sign in to continue</small>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger alert-auto-dismiss py-2">
        <?php
          $errors = [
            'invalid'  => 'Invalid username or password.',
            'inactive' => 'This account has been deactivated. Contact the administrator.',
            'empty'    => 'Please fill in both fields.',
          ];
          echo htmlspecialchars($errors[$_GET['error']] ?? 'Login failed. Please try again.');
        ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert alert-warning alert-auto-dismiss py-2">Your session expired. Please log in again.</div>
    <?php endif; ?>
    <?php if (isset($_GET['loggedout'])): ?>
      <div class="alert alert-success alert-auto-dismiss py-2">You have been logged out.</div>
    <?php endif; ?>

    <form id="loginForm" action="authenticate.php" method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required minlength="3" autofocus>
        <div class="invalid-feedback">Please enter your username.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="4">
        <div class="invalid-feedback">Please enter your password.</div>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right"></i> Login
      </button>
    </form>
    <p class="text-center text-muted small mt-3 mb-0">
      Default admin: <code>admin / admin123</code> (after running reset_admin_password.php)
    </p>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/validation.js"></script>
</body>
</html>
