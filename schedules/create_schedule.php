<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_once 'constraint_engine.php';

$base_path = '../';
$page_title = 'New Schedule';
$active = 'create_schedule';
$errors = [];
$success = false;

// ---- Data for the form dropdowns ---------------------------------------
$resources = mysqli_query($conn, "SELECT * FROM resources WHERE status = 'available' ORDER BY resource_name");
$slots     = mysqli_query($conn, "SELECT * FROM timeslots ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time");
$users     = mysqli_query($conn, "SELECT * FROM users WHERE status = 'active' ORDER BY full_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_title = clean_input($conn, $_POST['course_title']);
    $resource_id  = (int)$_POST['resource_id'];
    $slot_id      = (int)$_POST['slot_id'];
    $user_id      = (int)$_POST['user_id'];

    if ($resource_id <= 0 || $slot_id <= 0 || $user_id <= 0) {
        $errors[] = 'Please select a resource, time slot, and user.';
    } else {
        // =========== STEP 1: RUN CONSTRAINT ALGORITHM ===========
        $result = validate_schedule($conn, $resource_id, $slot_id, $user_id);

        if (!$result['valid']) {
            // =========== STEP 2a: CONFLICT -> REJECT ===========
            $errors = $result['errors'];
        } else {
            // =========== STEP 2b: NO CONFLICT -> SAVE ===========
            $stmt = mysqli_prepare($conn, "INSERT INTO schedules (course_title, resource_id, slot_id, user_id, status) VALUES (?,?,?,?,'approved')");
            mysqli_stmt_bind_param($stmt, "siii", $course_title, $resource_id, $slot_id, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-plus-circle"></i> Create New Schedule</h4>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <i class="bi bi-check-circle"></i> Schedule approved and saved — no conflicts detected.
      <a href="view_schedules.php" class="alert-link">View all schedules</a>.
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <strong><i class="bi bi-x-octagon"></i> Schedule rejected — the constraint engine found the following conflict(s):</strong>
      <ul class="mb-0 mt-2">
        <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form id="scheduleForm" method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-12">
          <label class="form-label">Course / Event Title</label>
          <input type="text" name="course_title" class="form-control" placeholder="e.g. Database Systems 201" value="<?php echo htmlspecialchars($_POST['course_title'] ?? ''); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Resource</label>
          <select id="resource_id" name="resource_id" class="form-select" required>
            <option value="">-- Select Resource --</option>
            <?php mysqli_data_seek($resources, 0); while ($r = mysqli_fetch_assoc($resources)): ?>
              <option value="<?php echo $r['resource_id']; ?>" <?php echo (isset($_POST['resource_id']) && $_POST['resource_id']==$r['resource_id'])?'selected':''; ?>>
                <?php echo htmlspecialchars($r['resource_name']) . ' (' . ucfirst(str_replace('_',' ',$r['resource_type'])) . ', cap. ' . $r['capacity'] . ')'; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Time Slot</label>
          <select id="slot_id" name="slot_id" class="form-select" required>
            <option value="">-- Select Time Slot --</option>
            <?php mysqli_data_seek($slots, 0); while ($t = mysqli_fetch_assoc($slots)): ?>
              <option value="<?php echo $t['slot_id']; ?>" <?php echo (isset($_POST['slot_id']) && $_POST['slot_id']==$t['slot_id'])?'selected':''; ?>>
                <?php echo htmlspecialchars($t['day']) . ' ' . substr($t['start_time'],0,5) . '–' . substr($t['end_time'],0,5); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Assign User / Lecturer</label>
          <select id="user_id" name="user_id" class="form-select" required>
            <option value="">-- Select User --</option>
            <?php mysqli_data_seek($users, 0); while ($u = mysqli_fetch_assoc($users)): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo (isset($_POST['user_id']) && $_POST['user_id']==$u['user_id'])?'selected':''; ?>>
                <?php echo htmlspecialchars($u['full_name']) . ' (' . ucfirst($u['role']) . ')'; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Run Constraint Validation &amp; Save</button>
          <a href="view_schedules.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body small text-muted">
      <i class="bi bi-info-circle"></i> When you submit, the system automatically checks for room clashes,
      lecturer clashes, time conflicts, and resource availability before saving. Conflicting schedules are
      rejected with a clear explanation.
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
