<?php
/**
 * reset_admin_password.php
 * ---------------------------------------------------------------------
 * ONE-TIME SETUP SCRIPT.
 * The sample rows in database.sql use placeholder password hashes that
 * will NOT verify (they exist only so the table isn't empty). Run this
 * script ONCE in your browser after importing database.sql to generate
 * real, working password hashes for the seeded accounts.
 *
 * Default passwords created:
 *   admin    -> admin123
 *   jsmith   -> lecturer123
 *   mgarcia  -> lecturer123
 *
 * DELETE THIS FILE after running it once - never leave it on a live server.
 * ---------------------------------------------------------------------
 */
require_once 'config/db.php';

$accounts = [
    'admin'   => 'admin123',
    'jsmith'  => 'lecturer123',
    'mgarcia' => 'lecturer123',
];

$results = [];
foreach ($accounts as $username => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $hash, $username);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    $results[] = "$username -> password set to '$plainPassword' (rows updated: $affected)";
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Password Reset</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="alert alert-success">
        <h4>Password reset complete</h4>
        <ul>
            <?php foreach ($results as $line): ?>
                <li><?php echo htmlspecialchars($line); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="alert alert-danger">
        <strong>Important:</strong> Delete this file (reset_admin_password.php) now that you've run it.
    </div>
    <a href="login.php" class="btn btn-primary">Go to Login</a>
</div>
</body>
</html>
