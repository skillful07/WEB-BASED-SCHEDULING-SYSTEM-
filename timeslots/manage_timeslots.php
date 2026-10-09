<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Manage Time Slots';
$active = 'timeslots';

$slots = mysqli_query($conn, "SELECT * FROM timeslots ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time");

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-clock"></i> Manage Time Slots</h4>
    <a href="add_timeslot.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Time Slot</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-auto-dismiss"><?php
      $msgs = ['added'=>'Time slot added.','updated'=>'Time slot updated.','deleted'=>'Time slot deleted.'];
      echo htmlspecialchars($msgs[$_GET['msg']] ?? '');
    ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>ID</th><th>Day</th><th>Start</th><th>End</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($slots) === 0): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No time slots found.</td></tr>
        <?php else: while ($t = mysqli_fetch_assoc($slots)): ?>
          <tr>
            <td>#<?php echo $t['slot_id']; ?></td>
            <td><?php echo htmlspecialchars($t['day']); ?></td>
            <td><?php echo substr($t['start_time'],0,5); ?></td>
            <td><?php echo substr($t['end_time'],0,5); ?></td>
            <td>
              <a href="edit_timeslot.php?id=<?php echo $t['slot_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <a href="delete_timeslot.php?id=<?php echo $t['slot_id']; ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this time slot? Related schedules will also be removed.');">
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
