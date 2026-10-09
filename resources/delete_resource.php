<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM resources WHERE resource_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}
header('Location: manage_resources.php?msg=deleted');
exit;
