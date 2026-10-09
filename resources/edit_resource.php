<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Edit Resource';
$active = 'resources';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM resources WHERE resource_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resource = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$resource) {
    header('Location: manage_resources.php?error=Resource not found.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean_input($conn, $_POST['resource_name']);
    $type     = clean_input($conn, $_POST['resource_type']);
    $capacity = (int)$_POST['capacity'];
    $status   = clean_input($conn, $_POST['status']);

    if (strlen($name) < 2) $errors[] = 'Resource name is too short.';
    if ($capacity < 0) $errors[] = 'Capacity cannot be negative.';

    if (empty($errors)) {
        $stmt2 = mysqli_prepare($conn, "UPDATE resources SET resource_name=?, resource_type=?, capacity=?, status=? WHERE resource_id=?");
        mysqli_stmt_bind_param($stmt2, "ssisi", $name, $type, $capacity, $status, $id);
        if (mysqli_stmt_execute($stmt2)) {
            header('Location: manage_resources.php?msg=updated');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
    $resource = array_merge($resource, compact('name','type','capacity','status'));
    $resource['resource_name'] = $name; $resource['resource_type'] = $type;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Resource</h4>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <div class="card">
    <div class="card-body">
      <form method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-6">
          <label class="form-label">Resource Name</label>
          <input type="text" name="resource_name" class="form-control" required value="<?php echo htmlspecialchars($resource['resource_name']); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Type</label>
          <select name="resource_type" class="form-select" required>
            <?php foreach (['classroom','laboratory','lecture_hall','equipment'] as $t): ?>
              <option value="<?php echo $t; ?>" <?php echo $resource['resource_type']===$t?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$t)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Capacity</label>
          <input type="number" name="capacity" class="form-control" min="0" value="<?php echo (int)$resource['capacity']; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <?php foreach (['available','unavailable'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $resource['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Resource</button>
          <a href="manage_resources.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
