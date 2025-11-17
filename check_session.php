<?php
// check_session.php
// Starts session (if not already started), exposes $user_id and $role, and provides require_role($expected)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$role    = $_SESSION['role'] ?? null;

function require_login() {
    global $user_id;
    if (!$user_id) {
        header('Location: login.php');
        exit;
    }
}

function require_role($expected) {
    global $user_id, $role;
    if (!$user_id) {
        header('Location: login.php');
        exit;
    }
    if (is_array($expected)) {
        if (!in_array($role, $expected, true)) {
            header('Location: login.php');
            exit;
        }
    } else {
        if ($role !== $expected) {
            header('Location: login.php');
            exit;
        }
    }
}

