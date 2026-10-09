<?php
require_once '../config/db.php';
$require_admin = true;
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Add User';
$active = 'users';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = clean_input($conn, $_POST['username']);
    $full_name = clean_input($conn, $_POST['full_name']);
    $email     = clean_input($conn, $_POST['email']);
    $password  = $_POST['password'];
    $role      = clean_input($conn, $_POST['role']);
    $status    = clean_input($conn, $_POST['status']);

    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!in_array($role, ['admin','lecturer','staff'])) $errors[] = 'Invalid role.';

    // Uniqueness check
    if (empty($errors)) {
        $chk = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($chk, "s", $username);
        mysqli_stmt_execute($chk);
        if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0) {
            $errors[] = 'That username is already taken.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, full_name, email, password, role, status) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $full_name, $email, $hash, $role, $status);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: manage_users.php?msg=added');
            exit;
        } else {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-person-plus"></i> Add User</h4>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required minlength="3" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="lecturer">Lecturer</option>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save User</button>
          <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
