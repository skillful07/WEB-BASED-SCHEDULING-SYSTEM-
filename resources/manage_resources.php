<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$base_path = '../';
$page_title = 'Manage Resources';
$active = 'resources';

$search = isset($_GET['search']) ? clean_input($conn, $_GET['search']) : '';
$sql = "SELECT * FROM resources WHERE 1=1";
if ($search !== '') {
    $sql .= " AND (resource_name LIKE '%$search%' OR resource_type LIKE '%$search%')";
}
$sql .= " ORDER BY resource_id DESC";
$resources = mysqli_query($conn, $sql);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>
<main class="app-content">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-building"></i> Manage Resources</h4>
    <a href="add_resource.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Resource</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-auto-dismiss"><?php
      $msgs = ['added'=>'Resource added.','updated'=>'Resource updated.','deleted'=>'Resource deleted.'];
      echo htmlspecialchars($msgs[$_GET['msg']] ?? '');
    ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Search name or type..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-search"></i> Search</button></div>
        <?php if ($search !== ''): ?><div class="col-auto"><a href="manage_resources.php" class="btn btn-outline-secondary">Clear</a></div><?php endif; ?>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($resources) === 0): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No resources found.</td></tr>
        <?php else: while ($r = mysqli_fetch_assoc($resources)): ?>
          <tr>
            <td>#<?php echo $r['resource_id']; ?></td>
            <td><?php echo htmlspecialchars($r['resource_name']); ?></td>
            <td class="text-capitalize"><?php echo htmlspecialchars(str_replace('_',' ', $r['resource_type'])); ?></td>
            <td><?php echo (int)$r['capacity']; ?></td>
            <td><span class="badge bg-<?php echo $r['status']==='available'?'success':'secondary'; ?>"><?php echo ucfirst($r['status']); ?></span></td>
            <td>
              <a href="edit_resource.php?id=<?php echo $r['resource_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <a href="delete_resource.php?id=<?php echo $r['resource_id']; ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete resource &quot;<?php echo htmlspecialchars(addslashes($r['resource_name'])); ?>&quot;? Related schedules will also be removed.');">
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
