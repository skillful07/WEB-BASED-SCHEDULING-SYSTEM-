<?php
/**
 * config/db.php
 * ---------------------------------------------------------------------
 * Central database connection (MySQLi, procedural) + session bootstrap.
 * Every protected page includes this file first.
 * ---------------------------------------------------------------------
 */

// Start the session (needed on every page that checks login state)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Database credentials -------------------------------------------
// Defaults match a fresh XAMPP/WAMP install. Change if your setup differs.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'scheduling_system');

// ---- Connect -----------------------------------------------------------
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Fail safely with a readable message instead of a raw stack trace
    die('<div style="font-family:sans-serif;padding:20px;color:#842029;background:#f8d7da;border-radius:6px;">
        <h3>Database Connection Failed</h3>
        <p>' . htmlspecialchars(mysqli_connect_error()) . '</p>
        <p>Check that MySQL/XAMPP is running and that the <code>scheduling_system</code>
        database has been imported (see database/database.sql).</p>
        </div>');
}

mysqli_set_charset($conn, 'utf8mb4');

/**
 * Helper: escape/sanitize a plain string input (defense in depth on top
 * of prepared statements used throughout the app).
 */
function clean_input($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    return mysqli_real_escape_string($conn, $data);
}
