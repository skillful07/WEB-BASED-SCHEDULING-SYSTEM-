<?php
// Expects $base_path (either '' for root pages or '../' for subfolder pages)
// and optionally $page_title to be set before including this file.
if (!isset($base_path)) { $base_path = ''; }
if (!isset($page_title)) { $page_title = 'Scheduling System'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?> | Scheduling System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?php echo $base_path; ?>css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid">
    <button class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" onclick="document.querySelector('.app-sidebar').classList.toggle('show')">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand fw-bold" href="<?php echo $base_path; ?>dashboard.php">
      <i class="bi bi-calendar2-week"></i> Scheduling System
    </a>
    <div class="ms-auto d-flex align-items-center text-white">
      <?php if (isset($_SESSION['full_name'])): ?>
        <span class="me-3 small">
          <i class="bi bi-person-circle"></i>
          <?php echo htmlspecialchars($_SESSION['full_name']); ?>
          <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
        </span>
        <a href="<?php echo $base_path; ?>logout.php" class="btn btn-sm btn-outline-light">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="app-wrapper">
