<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Edit Time Slot';
$active = 'timeslots';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM timeslots WHERE slot_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$slot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$slot) {
    header('Location: manage_timeslots.php?error=Time slot not found.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day        = clean_input($conn, $_POST['day']);
    $start_time = clean_input($conn, $_POST['start_time']);
    $end_time   = clean_input($conn, $_POST['end_time']);

    if ($start_time >= $end_time) $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
        $stmt2 = mysqli_prepare($conn, "SELECT slot_id FROM timeslots WHERE day = ? AND slot_id != ? AND NOT (end_time <= ? OR start_time >= ?)");
        mysqli_stmt_bind_param($stmt2, "siss", $day, $id, $start_time, $end_time);
        mysqli_stmt_execute($stmt2);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt2)) > 0) {
            $errors[] = 'This overlaps an existing time slot on the same day.';
        }
    }

    if (empty($errors)) {
        $stmt3 = mysqli_prepare($conn, "UPDATE timeslots SET day=?, start_time=?, end_time=? WHERE slot_id=?");
        mysqli_stmt_bind_param($stmt3, "sssi", $day, $start_time, $end_time, $id);
        if (mysqli_stmt_execute($stmt3)) {
            header('Location: manage_timeslots.php?msg=updated');
            exit;
        }
        $errors[] = 'Database error: ' . mysqli_error($conn);
    }
    $slot = array_merge($slot, compact('day','start_time','end_time'));
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <h4 class="mb-3"><i class="bi bi-pencil-square"></i> Edit Time Slot</h4>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <div class="card">
    <div class="card-body">
      <form id="timeslotForm" method="POST" class="needs-validation row g-3" novalidate>
        <div class="col-md-4">
          <label class="form-label">Day</label>
          <select name="day" class="form-select" required>
            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $d): ?>
              <option value="<?php echo $d; ?>" <?php echo $slot['day']===$d?'selected':''; ?>><?php echo $d; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Start Time</label>
          <input type="time" id="start_time" name="start_time" class="form-control" required value="<?php echo substr($slot['start_time'],0,5); ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">End Time</label>
          <input type="time" id="end_time" name="end_time" class="form-control" required value="<?php echo substr($slot['end_time'],0,5); ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Time Slot</button>
          <a href="manage_timeslots.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
