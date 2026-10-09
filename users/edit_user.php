<?php
require_once '../config/db.php';
$require_admin = true;
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Edit User';
$active = 'users';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    header('Location: manage_users.php?error=User not found.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = clean_input($conn, $_POST['full_name']);
    $email       = clean_input($conn, $_POST['email']);
    $role        = clean_input($conn, $_POST['role']);
    $status      = clean_input($conn, $_POST['status']);
    $newPassword = $_POST['password'];

    if (!in_array($role, ['admin','lecturer','staff'])) $errors[] = 'Invalid role.';

    if (empty($errors)) {
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt2 = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, role=?, status=?, password=? WHERE user_id=?");
                mysqli_stmt_bind_param($stmt2, "sssssi", $full_name, $email, $role, $status, $hash, $id);
            }
        } else {
            $stmt2 = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, role=?, status=? WHERE user_id=?");
            mysqli_stmt_bind_param($stmt2, "ssssi", $full_name, $email, $role, $status, $id);
        }

        if (empty($errors) && mysqli_stmt_execute($stmt2)) {
            header('Location: manage_users.php?msg=updated');
            exit;
        } elseif (empty($errors)) {
            $errors[] = 'Database error: ' . mysqli_error($conn);
        }
    }
    // Keep edited values in view on error
    $user = array_merge($user, ['full_name'=>$full_name,'email'=>$email,'role'=>$role,'status'=>$status]);
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-pencil-square"></i> Edit User: <?php echo htmlspecialchars($user['username']); ?></h4>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
  <?php endforeach; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-6">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">New Password <span class="text-muted small">(leave blank to keep current)</span></label>
          <input type="password" name="password" class="form-control" minlength="6">
        </div>
        <div class="col-md-6">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <?php foreach (['lecturer','staff','admin'] as $r): ?>
              <option value="<?php echo $r; ?>" <?php echo $user['role']===$r?'selected':''; ?>><?php echo ucfirst($r); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <?php foreach (['active','inactive'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $user['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update User</button>
          <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
