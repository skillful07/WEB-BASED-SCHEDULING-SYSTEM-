<?php
/**
 * authenticate.php
 * Handles POST from login.php. Uses a prepared statement (SQL-injection
 * safe) and password_verify() against the bcrypt hash stored in the DB.
 */
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = clean_input($conn, $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=empty');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT user_id, username, full_name, password, role, status FROM users WHERE username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if ($user['status'] !== 'active') {
        header('Location: login.php?error=inactive');
        exit;
    }

    if (password_verify($password, $user['password'])) {
        // Regenerate session ID on privilege change to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['user_id'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['full_name']     = $user['full_name'];
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();

        header('Location: dashboard.php');
        exit;
    }
}

header('Location: login.php?error=invalid');
exit;
