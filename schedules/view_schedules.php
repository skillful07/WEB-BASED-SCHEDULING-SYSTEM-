<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'View Schedules';
$active = 'schedules';

// ---- Filters ------------------------------------------------------------
$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$status = isset($_GET['status']) ? clean_input($conn, $_GET['status']) : '';
$sort   = in_array($_GET['sort'] ?? '', ['newest','oldest','day']) ? $_GET['sort'] : 'newest';

// ---- Pagination -----------------------------------------------------------
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
if ($search !== '') {
    $where .= " AND (s.course_title LIKE '%$search%' OR r.resource_name LIKE '%$search%' OR u.full_name LIKE '%$search%')";
}
if ($status !== '' && in_array($status, ['approved','rejected','pending'])) {
    $where .= " AND s.status = '$status'";
}

$orderBy = "s.created_at DESC";
if ($sort === 'oldest') $orderBy = "s.created_at ASC";
if ($sort === 'day')    $orderBy = "FIELD(t.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time";

$countSql = "SELECT COUNT(*) c FROM schedules s
             JOIN resources r ON s.resource_id = r.resource_id
             JOIN timeslots t ON s.slot_id = t.slot_id
             JOIN users u ON s.user_id = u.user_id $where";
$total = mysqli_fetch_assoc(mysqli_query($conn, $countSql))['c'];
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT s.*, r.resource_name, t.day, t.start_time, t.end_time, u.full_name
        FROM schedules s
        JOIN resources r ON s.resource_id = r.resource_id
        JOIN timeslots t ON s.slot_id = t.slot_id
        JOIN users u ON s.user_id = u.user_id
        $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$schedules = mysqli_query($conn, $sql);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

function qs($overrides) {
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query($params);
}
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-calendar-check"></i> View Schedules</h4>
    <a href="create_schedule.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Schedule</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-auto-dismiss"><?php
      $msgs = ['updated'=>'Schedule updated and re-validated successfully.','deleted'=>'Schedule deleted.'];
      echo htmlspecialchars($msgs[$_GET['msg']] ?? '');
    ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search course, resource, or lecturer..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <?php foreach (['approved','pending','rejected'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $status===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <select name="sort" class="form-select">
            <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Newest First</option>
            <option value="oldest" <?php echo $sort==='oldest'?'selected':''; ?>>Oldest First</option>
            <option value="day" <?php echo $sort==='day'?'selected':''; ?>>By Day/Time</option>
          </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-funnel"></i> Apply</button></div>
        <div class="col-auto"><a href="view_schedules.php" class="btn btn-outline-secondary">Reset</a></div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>Course</th><th>Resource</th><th>Day / Time</th><th>Lecturer</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($schedules) === 0): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No schedules match your filters.</td></tr>
        <?php else: while ($s = mysqli_fetch_assoc($schedules)): ?>
          <tr>
            <td><?php echo htmlspecialchars($s['course_title'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($s['resource_name']); ?></td>
            <td><?php echo htmlspecialchars($s['day']) . ' ' . substr($s['start_time'],0,5) . '–' . substr($s['end_time'],0,5); ?></td>
            <td><?php echo htmlspecialchars($s['full_name']); ?></td>
            <td>
              <?php $badge = ['approved'=>'success','rejected'=>'danger','pending'=>'secondary'][$s['status']] ?? 'secondary'; ?>
              <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($s['status']); ?></span>
            </td>
            <td>
              <a href="update_schedule.php?id=<?php echo $s['schedule_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <a href="delete_schedule.php?id=<?php echo $s['schedule_id']; ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this schedule?');"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white">
      <nav>
        <ul class="pagination mb-0 justify-content-center">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?php echo $p===$page?'active':''; ?>">
              <a class="page-link" href="<?php echo qs(['page'=>$p]); ?>"><?php echo $p; ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
    <?php endif; ?>
  </div>
</main>
<?php require_once '../includes/footer.php'; ?>
