<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_login() {
    return isset($_SESSION['user_id']);
}

function cek_login() {
    if (!is_login()) {
        header('Location: login.php');
        exit;
    }
}

function cek_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
