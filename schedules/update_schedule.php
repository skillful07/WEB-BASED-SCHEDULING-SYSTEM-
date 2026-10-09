<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';
require_once 'constraint_engine.php';

$base_path = '../';
$page_title = 'Update Schedule';
$active = 'schedules';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM schedules WHERE schedule_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$schedule) {
    header('Location: view_schedules.php?error=Schedule not found.');
    exit;
}

$resources = mysqli_query($conn, "SELECT * FROM resources ORDER BY resource_name");
$slots     = mysqli_query($conn, "SELECT * FROM timeslots ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time");
$users     = mysqli_query($conn, "SELECT * FROM users ORDER BY full_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_title = clean_input($conn, $_POST['course_title']);
    $resource_id  = (int)$_POST['resource_id'];
    $slot_id      = (int)$_POST['slot_id'];
    $user_id      = (int)$_POST['user_id'];

    // =========== REVALIDATE CONSTRAINTS (excluding this schedule itself) ===========
    $result = validate_schedule($conn, $resource_id, $slot_id, $user_id, $id);

    if (!$result['valid']) {
        $errors = $result['errors'];
        $schedule = array_merge($schedule, compact('course_title','resource_id','slot_id','user_id'));
    } else {
        $stmt2 = mysqli_prepare($conn, "UPDATE schedules SET course_title=?, resource_id=?, slot_id=?, user_id=?, status='approved' WHERE schedule_id=?");
        mysqli_stmt_bind_param($stmt2, "siiii", $course_title, $resource_id, $slot_id, $user_id, $id);
        if (mysqli_stmt_execute($stmt2)) {
            header('Location: view_schedules.php?msg=updated');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-pencil-square"></i> Update Schedule #<?php echo $schedule['schedule_id']; ?></h4>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <strong><i class="bi bi-x-octagon"></i> Update rejected — conflict(s) found on re-validation:</strong>
      <ul class="mb-0 mt-2"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form id="scheduleForm" method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-12">
          <label class="form-label">Course / Event Title</label>
          <input type="text" name="course_title" class="form-control" value="<?php echo htmlspecialchars($schedule['course_title']); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Resource</label>
          <select id="resource_id" name="resource_id" class="form-select" required>
            <?php mysqli_data_seek($resources, 0); while ($r = mysqli_fetch_assoc($resources)): ?>
              <option value="<?php echo $r['resource_id']; ?>" <?php echo $schedule['resource_id']==$r['resource_id']?'selected':''; ?>>
                <?php echo htmlspecialchars($r['resource_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Time Slot</label>
          <select id="slot_id" name="slot_id" class="form-select" required>
            <?php mysqli_data_seek($slots, 0); while ($t = mysqli_fetch_assoc($slots)): ?>
              <option value="<?php echo $t['slot_id']; ?>" <?php echo $schedule['slot_id']==$t['slot_id']?'selected':''; ?>>
                <?php echo htmlspecialchars($t['day']) . ' ' . substr($t['start_time'],0,5) . '–' . substr($t['end_time'],0,5); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Assign User / Lecturer</label>
          <select id="user_id" name="user_id" class="form-select" required>
            <?php mysqli_data_seek($users, 0); while ($u = mysqli_fetch_assoc($users)): ?>
              <option value="<?php echo $u['user_id']; ?>" <?php echo $schedule['user_id']==$u['user_id']?'selected':''; ?>>
                <?php echo htmlspecialchars($u['full_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Revalidate &amp; Save Changes</button>
          <a href="view_schedules.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
