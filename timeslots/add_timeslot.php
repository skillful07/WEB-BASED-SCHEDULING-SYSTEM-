<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Add Time Slot';
$active = 'timeslots';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day        = clean_input($conn, $_POST['day']);
    $start_time = clean_input($conn, $_POST['start_time']);
    $end_time   = clean_input($conn, $_POST['end_time']);

    $validDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    if (!in_array($day, $validDays)) $errors[] = 'Invalid day.';
    if ($start_time >= $end_time) $errors[] = 'End time must be after start time.';

    // Prevent overlapping slots on the same day
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT slot_id FROM timeslots WHERE day = ? AND NOT (end_time <= ? OR start_time >= ?)");
        mysqli_stmt_bind_param($stmt, "sss", $day, $start_time, $end_time);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $errors[] = 'This overlaps an existing time slot on the same day.';
        }
    }

    if (empty($errors)) {
        $stmt2 = mysqli_prepare($conn, "INSERT INTO timeslots (day, start_time, end_time) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt2, "sss", $day, $start_time, $end_time);
        if (mysqli_stmt_execute($stmt2)) {
            header('Location: manage_timeslots.php?msg=added');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-clock-history"></i> Add Time Slot</h4>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <div class="card">
    <div class="card-body">
      <form id="timeslotForm" method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-4">
          <label class="form-label">Day</label>
          <select name="day" class="form-select" required>
            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $d): ?>
              <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Start Time</label>
          <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">End Time</label>
          <input type="time" id="end_time" name="end_time" class="form-control" required>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Time Slot</button>
          <a href="manage_timeslots.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
