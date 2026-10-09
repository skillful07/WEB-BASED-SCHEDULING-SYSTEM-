<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$base_path = '';
$page_title = 'Reports';
$active = 'reports';

// ---- Handle CSV ("Export to Excel") export BEFORE any HTML output -------
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $report = $_GET['report'] ?? 'schedule';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $report . '_report_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($report === 'users') {
        fputcsv($out, ['ID','Username','Full Name','Email','Role','Status','Created At']);
        $res = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id");
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['user_id'],$r['username'],$r['full_name'],$r['email'],$r['role'],$r['status'],$r['created_at']]);
        }
    } elseif ($report === 'resources') {
        fputcsv($out, ['ID','Name','Type','Capacity','Status']);
        $res = mysqli_query($conn, "SELECT * FROM resources ORDER BY resource_id");
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['resource_id'],$r['resource_name'],$r['resource_type'],$r['capacity'],$r['status']]);
        }
    } elseif ($report === 'conflicts') {
        fputcsv($out, ['Schedule ID','Course','Resource','Day','Time','Lecturer','Status']);
        $res = mysqli_query($conn, "SELECT s.*, r.resource_name, t.day, t.start_time, t.end_time, u.full_name
                                     FROM schedules s JOIN resources r ON s.resource_id=r.resource_id
                                     JOIN timeslots t ON s.slot_id=t.slot_id JOIN users u ON s.user_id=u.user_id
                                     WHERE s.status='rejected' ORDER BY s.schedule_id");
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['schedule_id'],$r['course_title'],$r['resource_name'],$r['day'],substr($r['start_time'],0,5).'-'.substr($r['end_time'],0,5),$r['full_name'],$r['status']]);
        }
    } else { // schedule
        fputcsv($out, ['Schedule ID','Course','Resource','Day','Time','Lecturer','Status']);
        $res = mysqli_query($conn, "SELECT s.*, r.resource_name, t.day, t.start_time, t.end_time, u.full_name
                                     FROM schedules s JOIN resources r ON s.resource_id=r.resource_id
                                     JOIN timeslots t ON s.slot_id=t.slot_id JOIN users u ON s.user_id=u.user_id
                                     ORDER BY s.schedule_id");
        while ($r = mysqli_fetch_assoc($res)) {
            fputcsv($out, [$r['schedule_id'],$r['course_title'],$r['resource_name'],$r['day'],substr($r['start_time'],0,5).'-'.substr($r['end_time'],0,5),$r['full_name'],$r['status']]);
        }
    }
    fclose($out);
    exit;
}

// ---- Summary counts for on-screen reports --------------------------------
$userCount     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM users"))['c'];
$resourceCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM resources"))['c'];
$approvedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM schedules WHERE status='approved'"))['c'];
$rejectedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM schedules WHERE status='rejected'"))['c'];

$scheduleReport = mysqli_query($conn, "SELECT s.*, r.resource_name, t.day, t.start_time, t.end_time, u.full_name
                                        FROM schedules s JOIN resources r ON s.resource_id=r.resource_id
                                        JOIN timeslots t ON s.slot_id=t.slot_id JOIN users u ON s.user_id=u.user_id
                                        ORDER BY s.created_at DESC");

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h4 class="mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Reports</h4>
    <div>
      <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
      <a href="?export=csv&report=schedule" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Export Schedule (Excel/CSV)</a>
    </div>
  </div>

  <div class="row g-3 mb-4 no-print">
    <div class="col-md-3 col-sm-6"><div class="card stat-card bg-primary p-3"><div class="small">Users</div><h3><?php echo $userCount; ?></h3></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card bg-success p-3"><div class="small">Resources</div><h3><?php echo $resourceCount; ?></h3></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card bg-secondary p-3"><div class="small">Approved Schedules</div><h3><?php echo $approvedCount; ?></h3></div></div>
    <div class="col-md-3 col-sm-6"><div class="card stat-card bg-warning p-3"><div class="small">Rejected (Conflicts)</div><h3><?php echo $rejectedCount; ?></h3></div></div>
  </div>

  <ul class="nav nav-tabs no-print" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-schedule">Schedule Report</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-users">User Report</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-resources">Resource Report</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-conflicts">Conflict Report</button></li>
  </ul>

  <div class="tab-content border border-top-0 p-3 bg-white rounded-bottom">
    <div class="tab-pane fade show active" id="tab-schedule">
      <div class="d-flex justify-content-end mb-2 no-print"><a href="?export=csv&report=schedule" class="btn btn-sm btn-outline-success">Export CSV</a></div>
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Course</th><th>Resource</th><th>Day/Time</th><th>Lecturer</th><th>Status</th></tr></thead>
        <tbody>
        <?php mysqli_data_seek($scheduleReport, 0); while ($r = mysqli_fetch_assoc($scheduleReport)): ?>
          <tr>
            <td>#<?php echo $r['schedule_id']; ?></td>
            <td><?php echo htmlspecialchars($r['course_title'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($r['resource_name']); ?></td>
            <td><?php echo htmlspecialchars($r['day']).' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5); ?></td>
            <td><?php echo htmlspecialchars($r['full_name']); ?></td>
            <td><?php echo ucfirst($r['status']); ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="tab-pane fade" id="tab-users">
      <div class="d-flex justify-content-end mb-2 no-print"><a href="?export=csv&report=users" class="btn btn-sm btn-outline-success">Export CSV</a></div>
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
        <?php $ures = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id"); while ($u = mysqli_fetch_assoc($ures)): ?>
          <tr><td>#<?php echo $u['user_id']; ?></td><td><?php echo htmlspecialchars($u['username']); ?></td><td><?php echo htmlspecialchars($u['full_name']); ?></td><td><?php echo ucfirst($u['role']); ?></td><td><?php echo ucfirst($u['status']); ?></td></tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="tab-pane fade" id="tab-resources">
      <div class="d-flex justify-content-end mb-2 no-print"><a href="?export=csv&report=resources" class="btn btn-sm btn-outline-success">Export CSV</a></div>
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Capacity</th><th>Status</th></tr></thead>
        <tbody>
        <?php $rres = mysqli_query($conn, "SELECT * FROM resources ORDER BY resource_id"); while ($r = mysqli_fetch_assoc($rres)): ?>
          <tr><td>#<?php echo $r['resource_id']; ?></td><td><?php echo htmlspecialchars($r['resource_name']); ?></td><td><?php echo ucfirst(str_replace('_',' ',$r['resource_type'])); ?></td><td><?php echo $r['capacity']; ?></td><td><?php echo ucfirst($r['status']); ?></td></tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="tab-pane fade" id="tab-conflicts">
      <div class="d-flex justify-content-end mb-2 no-print"><a href="?export=csv&report=conflicts" class="btn btn-sm btn-outline-success">Export CSV</a></div>
      <table class="table table-sm">
        <thead><tr><th>ID</th><th>Course</th><th>Resource</th><th>Day/Time</th><th>Lecturer</th></tr></thead>
        <tbody>
        <?php $cres = mysqli_query($conn, "SELECT s.*, r.resource_name, t.day, t.start_time, t.end_time, u.full_name
                                            FROM schedules s JOIN resources r ON s.resource_id=r.resource_id
                                            JOIN timeslots t ON s.slot_id=t.slot_id JOIN users u ON s.user_id=u.user_id
                                            WHERE s.status='rejected' ORDER BY s.schedule_id");
        if (mysqli_num_rows($cres) === 0): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">No rejected/conflicting schedules on record.</td></tr>
        <?php else: while ($r = mysqli_fetch_assoc($cres)): ?>
          <tr><td>#<?php echo $r['schedule_id']; ?></td><td><?php echo htmlspecialchars($r['course_title']); ?></td><td><?php echo htmlspecialchars($r['resource_name']); ?></td><td><?php echo htmlspecialchars($r['day']).' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5); ?></td><td><?php echo htmlspecialchars($r['full_name']); ?></td></tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <p class="text-muted small mt-3 no-print">
    <i class="bi bi-info-circle"></i> "Export to PDF": use your browser's <strong>Print &rarr; Save as PDF</strong> option
    (button above) — this avoids requiring extra PDF libraries on a bare XAMPP install.
    "Export to Excel" downloads a CSV file, which Excel opens natively.
  </p>
</main>
<style>
  @media print {
    .app-sidebar, .app-navbar, .app-footer, .no-print { display: none !important; }
    .app-content { padding: 0 !important; }
    .tab-pane { display: block !important; opacity: 1 !important; }
  }
</style>
<?php require_once 'includes/footer.php'; ?>
