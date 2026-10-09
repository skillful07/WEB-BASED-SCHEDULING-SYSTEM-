<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Edit Constraint';
$active = 'constraints';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM constraints_tbl WHERE constraint_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$c = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$c) {
    header('Location: manage_constraints.php?error=Not found.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean_input($conn, $_POST['constraint_name']);
    $type        = clean_input($conn, $_POST['constraint_type']);
    $description = clean_input($conn, $_POST['description']);

    if (strlen($name) < 3) $errors[] = 'Constraint name is too short.';

    if (empty($errors)) {
        $stmt2 = mysqli_prepare($conn, "UPDATE constraints_tbl SET constraint_name=?, constraint_type=?, description=? WHERE constraint_id=?");
        mysqli_stmt_bind_param($stmt2, "sssi", $name, $type, $description, $id);
        if (mysqli_stmt_execute($stmt2)) {
            header('Location: manage_constraints.php?msg=updated');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
    $c = array_merge($c, compact('name','type','description'));
    $c['constraint_name'] = $name; $c['constraint_type'] = $type;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Constraint</h4>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <div class="card">
    <div class="card-body">
      <form method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-6">
          <label class="form-label">Constraint Name</label>
          <input type="text" name="constraint_name" class="form-control" required minlength="3" value="<?php echo htmlspecialchars($c['constraint_name']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Type</label>
          <select name="constraint_type" class="form-select" required>
            <option value="hard" <?php echo $c['constraint_type']==='hard'?'selected':''; ?>>Hard (blocks conflicting schedules)</option>
            <option value="soft" <?php echo $c['constraint_type']==='soft'?'selected':''; ?>>Soft (preference only)</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($c['description']); ?></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Constraint</button>
          <a href="manage_constraints.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
