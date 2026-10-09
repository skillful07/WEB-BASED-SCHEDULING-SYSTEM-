<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Manage Constraints';
$active = 'constraints';

$constraints = mysqli_query($conn, "SELECT * FROM constraints_tbl ORDER BY constraint_type, constraint_id");

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Manage Constraints</h4>
    <a href="add_constraint.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Constraint</a>
  </div>

  <div class="alert alert-info">
    <strong>Hard constraints</strong> are enforced automatically by the constraint engine and will
    <em>block</em> a schedule if violated (room clash, lecturer clash, time conflict, resource availability).
    <strong>Soft constraints</strong> are preferences that are recorded but do not block saving.
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-auto-dismiss"><?php
      $msgs = ['added'=>'Constraint added.','updated'=>'Constraint updated.','deleted'=>'Constraint deleted.'];
      echo htmlspecialchars($msgs[$_GET['msg']] ?? '');
    ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($constraints) === 0): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No constraints defined.</td></tr>
        <?php else: while ($c = mysqli_fetch_assoc($constraints)): ?>
          <tr>
            <td>#<?php echo $c['constraint_id']; ?></td>
            <td><?php echo htmlspecialchars($c['constraint_name']); ?></td>
            <td><span class="badge bg-<?php echo $c['constraint_type']==='hard'?'danger':'warning text-dark'; ?>"><?php echo ucfirst($c['constraint_type']); ?></span></td>
            <td><?php echo htmlspecialchars($c['description']); ?></td>
            <td>
              <a href="edit_constraint.php?id=<?php echo $c['constraint_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <a href="delete_constraint.php?id=<?php echo $c['constraint_id']; ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this constraint definition?');"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
