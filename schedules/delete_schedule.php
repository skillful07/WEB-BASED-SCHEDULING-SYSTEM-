<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM schedules WHERE schedule_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}
header('Location: view_schedules.php?msg=deleted');
exit;
