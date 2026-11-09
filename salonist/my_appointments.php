
<?php

require_once __DIR__ . '/../includes/auth.php';

requireRole(['salonist']);

require_once __DIR__ . '/../config/database.php';

$salonist_id = $_SESSION['user_id'];

$pageTitle = 'My Assigned Appointments';
$activePage = 'appointments';

// Retrieve appointments assigned to this salonist
$stmt = $conn->prepare("
    SELECT
        appointments.*,
        users.full_name AS customer_name,
        users.phone AS customer_phone,
        users.email AS customer_email
    FROM appointments
    INNER JOIN users
        ON appointments.customer_id = users.id
    WHERE appointments.salonist_id = ?
    ORDER BY appointments.appointment_date ASC,
             appointments.appointment_time ASC
");

$stmt->execute([$salonist_id]);

$appointments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="flex-1 p-6 md:p-8">

    <div class="mb-8">

        <h2 class="text-3xl font-bold text-gray-800">
            My Assigned Appointments
        </h2>

        <p class="text-gray-600 mt-2">
            View customer details and appointments assigned to you.
        </p>

    </div>

    <div class="bg-white p-6 rounded-xl shadow">

        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="bg-pink-100">

                    <tr>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Service</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Time</th>
                        <th class="p-3">Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (count($appointments) > 0): ?>

                        <?php foreach ($appointments as $appointment): ?>

                            <tr class="border-b">

                                <td class="p-3">
                                    <?= escape($appointment['customer_name']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['customer_phone'] ?? '') ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['customer_email']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['service']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_date']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_time']) ?>
                                </td>

                                <td class="p-3">

                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'confirmed' => 'bg-green-100 text-green-700',
                                        'completed' => 'bg-blue-100 text-blue-700',
                                        'cancelled' => 'bg-red-100 text-red-700'
                                    ];

                                    $status = $appointment['status'];
                                    $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-700';
                                    ?>

                                    <span class="px-3 py-1 rounded-full text-sm <?= $statusClass ?>">
                                        <?= escape(ucfirst($status)) ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="7" class="p-6 text-center text-gray-500">
                                No appointments have been assigned to you yet.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
