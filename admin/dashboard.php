<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);

require_once __DIR__ . '/../config/database.php';

// Get system statistics
$totalCustomers = $conn->query(
    "SELECT COUNT(*) FROM users WHERE role = 'customer'"
)->fetchColumn();

$totalSalonists = $conn->query(
    "SELECT COUNT(*) FROM users WHERE role = 'salonist'"
)->fetchColumn();

$totalAppointments = $conn->query(
    "SELECT COUNT(*) FROM appointments"
)->fetchColumn();

$pendingAppointments = $conn->query(
    "SELECT COUNT(*) FROM appointments WHERE status = 'pending'"
)->fetchColumn();

// Get recent appointments
$stmt = $conn->prepare("
    SELECT
        a.id,
        a.service,
        a.appointment_date,
        a.appointment_time,
        a.status,
        customer.full_name AS customer_name,
        salonist.full_name AS salonist_name
    FROM appointments a
    INNER JOIN users customer ON a.customer_id = customer.id
    LEFT JOIN users salonist ON a.salonist_id = salonist.id
    ORDER BY a.created_at DESC
    LIMIT 5
");

$stmt->execute();
$recentAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Administrator Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        Administrator Dashboard
    </h2>

    <p class="text-gray-600 mb-6">
        Welcome to the Grace Beauty Salon management system.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500">Total Customers</p>
            <h3 class="text-3xl font-bold text-pink-600 mt-2">
                <?= (int)$totalCustomers ?>
            </h3>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500">Total Salonists</p>
            <h3 class="text-3xl font-bold text-pink-600 mt-2">
                <?= (int)$totalSalonists ?>
            </h3>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500">Total Appointments</p>
            <h3 class="text-3xl font-bold text-pink-600 mt-2">
                <?= (int)$totalAppointments ?>
            </h3>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <p class="text-gray-500">Pending Appointments</p>
            <h3 class="text-3xl font-bold text-pink-600 mt-2">
                <?= (int)$pendingAppointments ?>
            </h3>
        </div>

    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <h3 class="text-xl font-bold text-gray-800 mb-5">
            Recent Appointments
        </h3>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-pink-100 text-gray-700">
                    <tr>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Service</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Time</th>
                        <th class="p-3">Salonist</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($recentAppointments): ?>

                        <?php foreach ($recentAppointments as $appointment): ?>

                            <tr class="border-b">

                                <td class="p-3">
                                    <?= escape($appointment['customer_name']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['service']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_date']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape(substr($appointment['appointment_time'], 0, 5)) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['salonist_name'] ?? 'Not assigned') ?>
                                </td>

                                <td class="p-3 capitalize">
                                    <?= escape($appointment['status']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                No appointments have been booked yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <a href="manage_appointments.php"
           class="inline-block mt-5 bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700">
            Manage Appointments
        </a>

    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>