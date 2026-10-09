<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$base_path = '';
$page_title = 'Dashboard';
$active = 'dashboard';

// ---- Totals for stat cards -------------------------------------------
$totalUsers     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users"))['c'];
$totalResources = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM resources"))['c'];
$totalSlots     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM timeslots"))['c'];
$totalSchedules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM schedules"))['c'];

// ---- Recent schedules --------------------------------------------------
$recentQuery = "SELECT s.schedule_id, s.course_title, r.resource_name, t.day, t.start_time, t.end_time,
                       u.full_name, s.status
                FROM schedules s
                JOIN resources r ON s.resource_id = r.resource_id
                JOIN timeslots t ON s.slot_id = t.slot_id
                JOIN users u ON s.user_id = u.user_id
                ORDER BY s.created_at DESC LIMIT 5";
$recent = mysqli_query($conn, $recentQuery);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<main class="app-content">
  <h3 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</h3>

  <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
    <div class="alert alert-danger alert-auto-dismiss">You don't have permission to access that page.</div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
      <div class="card stat-card bg-primary p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div><div class="small">Total Users</div><h3 class="mb-0"><?php echo $totalUsers; ?></h3></div>
          <i class="bi bi-people stat-icon"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card stat-card bg-success p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div><div class="small">Total Resources</div><h3 class="mb-0"><?php echo $totalResources; ?></h3></div>
          <i class="bi bi-building stat-icon"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card stat-card bg-warning p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div><div class="small">Total Time Slots</div><h3 class="mb-0"><?php echo $totalSlots; ?></h3></div>
          <i class="bi bi-clock stat-icon"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="card stat-card bg-secondary p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div><div class="small">Total Schedules</div><h3 class="mb-0"><?php echo $totalSchedules; ?></h3></div>
          <i class="bi bi-calendar-check stat-icon"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-white fw-semibold">Recent Schedules</div>
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr><th>Course</th><th>Resource</th><th>Day / Time</th><th>Lecturer</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($recent) === 0): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No schedules yet. <a href="schedules/create_schedule.php">Create one</a>.</td></tr>
          <?php else: while ($row = mysqli_fetch_assoc($recent)): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['course_title'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
              <td><?php echo htmlspecialchars($row['day']) . ' ' . substr($row['start_time'],0,5) . '–' . substr($row['end_time'],0,5); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td>
                <?php
                  $badge = ['approved'=>'success','rejected'=>'danger','pending'=>'secondary'][$row['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php require_once 'includes/footer.php'; ?>
