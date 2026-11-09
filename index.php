
<?php
session_start();

if (isset($_SESSION['user_id'], $_SESSION['role'])) {

    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;

        case 'salonist':
            header("Location: salonist/dashboard.php");
            exit;

        case 'customer':
            header("Location: customer/dashboard.php");
            exit;
    }
}

header("Location: login.php");
exit;
