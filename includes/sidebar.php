
<?php

$role = $_SESSION['role'] ?? '';

$menus = [

    'customer' => [
        ['label' => 'Dashboard', 'url' => 'customer/dashboard.php', 'key' => 'dashboard'],
        ['label' => 'My Appointments', 'url' => 'customer/my_appointments.php', 'key' => 'appointments']
    ],

    'salonist' => [
        ['label' => 'Dashboard', 'url' => 'salonist/dashboard.php', 'key' => 'dashboard'],
        ['label' => 'My Appointments', 'url' => 'salonist/my_appointments.php', 'key' => 'appointments']
    ],

    'admin' => [
        ['label' => 'Dashboard', 'url' => 'admin/dashboard.php', 'key' => 'dashboard'],
        ['label' => 'Manage Users', 'url' => 'admin/manage_users.php', 'key' => 'users'],
        ['label' => 'Manage Appointments', 'url' => 'admin/manage_appointments.php', 'key' => 'appointments']
    ]

];

$activePage = $activePage ?? 'dashboard';

?>

<aside class="bg-white w-full md:w-64 shadow-md p-5 shrink-0">

    <h2 class="text-lg font-bold text-gray-800 mb-5">
        Navigation
    </h2>

    <nav class="space-y-2">

        <?php foreach ($menus[$role] ?? [] as $menu): ?>

            <a href="<?= BASE_URL ?>/<?= escape($menu['url']) ?>"
               class="block p-3 rounded-lg transition
               <?= $activePage === $menu['key']
                    ? 'bg-pink-600 text-white'
                    : 'text-gray-700 hover:bg-pink-100' ?>">

                <?= escape($menu['label']) ?>

            </a>

        <?php endforeach; ?>

    </nav>

</aside>
