
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/grace-beauty-salon');

function requireRole($allowedRoles)
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
}

function escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
