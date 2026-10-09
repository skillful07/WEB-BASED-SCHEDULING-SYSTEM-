<?php
require_once '../config/db.php';
$require_admin = true;
require_once '../includes/auth_check.php';

$id = (int)($_GET['id'] ?? 0);

if ($id === (int)$_SESSION['user_id']) {
    header('Location: manage_users.php?error=You cannot delete your own account while logged in.');
    exit;
}

if ($id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header('Location: manage_users.php?msg=deleted');
exit;
