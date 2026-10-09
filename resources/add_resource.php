<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Add Resource';
$active = 'resources';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean_input($conn, $_POST['resource_name']);
    $type     = clean_input($conn, $_POST['resource_type']);
    $capacity = (int)$_POST['capacity'];
    $status   = clean_input($conn, $_POST['status']);

    if (strlen($name) < 2) $errors[] = 'Resource name is too short.';
    if (!in_array($type, ['classroom','laboratory','lecture_hall','equipment'])) $errors[] = 'Invalid resource type.';
    if ($capacity < 0) $errors[] = 'Capacity cannot be negative.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO resources (resource_name, resource_type, capacity, status) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssis", $name, $type, $capacity, $status);
        if (mysqli_stmt_execute($stmt)) {
            header('Location: manage_resources.php?msg=added');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-building-add"></i> Add Resource</h4>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <div class="card">
    <div class="card-body">
      <form method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-6">
          <label class="form-label">Resource Name</label>
          <input type="text" name="resource_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['resource_name'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Type</label>
          <select name="resource_type" class="form-select" required>
            <option value="classroom">Classroom</option>
            <option value="laboratory">Laboratory</option>
            <option value="lecture_hall">Lecture Hall</option>
            <option value="equipment">Equipment</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Capacity</label>
          <input type="number" name="capacity" class="form-control" min="0" value="<?php echo htmlspecialchars($_POST['capacity'] ?? '0'); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Resource</button>
          <a href="manage_resources.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
