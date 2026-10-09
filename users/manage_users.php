<?php
require_once '../config/db.php';
$require_admin = true;
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Manage Users';
$active = 'users';

$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';

$sql = "SELECT * FROM users WHERE 1=1";
if ($search !== '') {
    $sql .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
$sql .= " ORDER BY user_id DESC";
$users = mysqli_query($conn, $sql);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-people"></i> Manage Users</h4>
    <a href="add_user.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-auto-dismiss"><?php
      $msgs = ['added'=>'User added successfully.','updated'=>'User updated successfully.','deleted'=>'User deleted successfully.'];
      echo htmlspecialchars($msgs[$_GET['msg']] ?? '');
    ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search username, name, or email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
        <?php if ($search !== ''): ?>
        <div class="col-auto"><a href="manage_users.php" class="btn btn-outline-secondary">Clear</a></div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($users) === 0): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
        <?php else: while ($u = mysqli_fetch_assoc($users)): ?>
          <tr>
            <td>#<?php echo $u['user_id']; ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
            <td><?php echo htmlspecialchars($u['email'] ?: '—'); ?></td>
            <td><span class="badge bg-info text-dark text-capitalize"><?php echo htmlspecialchars($u['role']); ?></span></td>
            <td><span class="badge bg-<?php echo $u['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
            <td>
              <a href="edit_user.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <a href="delete_user.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete user &quot;<?php echo htmlspecialchars(addslashes($u['username'])); ?>&quot;? This cannot be undone.');">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
