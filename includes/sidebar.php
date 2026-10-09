<?php
// Expects $base_path and optionally $active (string key) to highlight the current menu item
if (!isset($active)) { $active = ''; }
function nav_active($key, $active) { return $key === $active ? 'active' : ''; }
?>
<aside class="app-sidebar">
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('dashboard', $active); ?>" href="<?php echo $base_path; ?>dashboard.php">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('schedules', $active); ?>" href="<?php echo $base_path; ?>schedules/view_schedules.php">
        <i class="bi bi-calendar-check"></i> Schedules
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('create_schedule', $active); ?>" href="<?php echo $base_path; ?>schedules/create_schedule.php">
        <i class="bi bi-plus-circle"></i> New Schedule
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('resources', $active); ?>" href="<?php echo $base_path; ?>resources/manage_resources.php">
        <i class="bi bi-building"></i> Resources
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('timeslots', $active); ?>" href="<?php echo $base_path; ?>timeslots/manage_timeslots.php">
        <i class="bi bi-clock"></i> Time Slots
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('constraints', $active); ?>" href="<?php echo $base_path; ?>constraints/manage_constraints.php">
        <i class="bi bi-shield-check"></i> Constraints
      </a>
    </li>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('users', $active); ?>" href="<?php echo $base_path; ?>users/manage_users.php">
        <i class="bi bi-people"></i> Users
      </a>
    </li>
    <?php endif; ?>
    <li class="nav-item">
      <a class="nav-link <?php echo nav_active('reports', $active); ?>" href="<?php echo $base_path; ?>reports.php">
        <i class="bi bi-file-earmark-bar-graph"></i> Reports
      </a>
    </li>
  </ul>
</aside>
