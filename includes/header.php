
<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$fullName = $_SESSION['full_name'] ?? 'User';
$userRole = $_SESSION['role'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= escape($pageTitle) ?> | Grace Beauty Salon</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-pink-50 min-h-screen">

<header class="bg-pink-600 text-white px-6 py-4 shadow">

    <div class="flex justify-between items-center">

        <div>
            <h1 class="text-xl md:text-2xl font-bold">
                Grace Beauty Salon
            </h1>

            <p class="text-sm text-pink-100">
                Appointment Booking System
            </p>
        </div>

        <div class="flex items-center gap-4">

            <div class="hidden sm:block text-right">
                <p class="font-semibold">
                    <?= escape($fullName) ?>
                </p>

                <p class="text-sm text-pink-100 capitalize">
                    <?= escape($userRole) ?>
                </p>
            </div>

            <a href="<?= BASE_URL ?>/logout.php"
               class="bg-white text-pink-600 px-4 py-2 rounded-lg font-semibold hover:bg-pink-100">
                Logout
            </a>

        </div>

    </div>

</header>

<div class="flex flex-col md:flex-row min-h-screen">
