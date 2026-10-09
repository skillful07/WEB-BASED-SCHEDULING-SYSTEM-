<?php
/**
 * includes/auth_check.php
 * ---------------------------------------------------------------------
 * Authentication middleware. Include this (after db.php) at the top of
 * every page that requires a logged-in user. Optionally restrict a page
 * to admins only by setting $require_admin = true before including this.
 * ---------------------------------------------------------------------
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (isset($base_path) ? $base_path : '') . 'login.php');
    exit;
}

// Idle timeout: 30 minutes
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . (isset($base_path) ? $base_path : '') . 'login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

if (!empty($require_admin) && $_SESSION['role'] !== 'admin') {
    header('Location: ' . (isset($base_path) ? $base_path : '') . 'dashboard.php?error=access_denied');
    exit;
}
