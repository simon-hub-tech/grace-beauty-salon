<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);

require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {

    $appointmentId = filter_var(
        $_POST['appointment_id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $salonistId = $_POST['salonist_id'] ?? '';
    $status = $_POST['status'] ?? '';

    $allowedStatuses = [
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ];

    if (!$appointmentId || !in_array($status, $allowedStatuses, true)) {
        $message = 'Invalid appointment information.';
        $messageType = 'error';

    } else {

        $assignedSalonist = null;

        if ($salonistId !== '') {

            $checkSalonist = $conn->prepare("
                SELECT id FROM users
                WHERE id = ? AND role = 'salonist'
            ");

            $checkSalonist->execute([$salonistId]);

            if (!$checkSalonist->fetch()) {
                $message = 'The selected salonist does not exist.';
                $messageType = 'error';
            } else {
                $assignedSalonist = (int)$salonistId;
            }
        }

        if ($message === '') {

            $stmt = $conn->prepare("
                UPDATE appointments
                SET salonist_id = ?, status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $assignedSalonist,
                $status,
                $appointmentId
            ]);

            $message = 'Appointment updated successfully.';
        }
    }
}

// Get salonists
$stmt = $conn->prepare("
    SELECT id, full_name
    FROM users
    WHERE role = 'salonist'
    ORDER BY full_name ASC
");
$stmt->execute();
$salonists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get appointments
$stmt = $conn->prepare("
    SELECT
        a.id,
        a.service,
        a.appointment_date,
        a.appointment_time,
        a.notes,
        a.status,
        a.salonist_id,
        customer.full_name AS customer_name,
        customer.phone AS customer_phone
    FROM appointments a
    INNER JOIN users customer ON a.customer_id = customer.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Appointments';
$activePage = 'appointments';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        Manage Appointments
    </h2>

    <p class="text-gray-600 mb-6">
        View bookings, assign salonists and update appointment statuses.
    </p>

    <?php if ($message !== ''): ?>
        <div class="mb-5 p-4 rounded-lg
            <?= $messageType === 'success'
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800' ?>">
            <?= escape($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow p-6">

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
                        <th class="p-3">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($appointments): ?>

                        <?php foreach ($appointments as $appointment): ?>

                            <?php $formId = 'appointment-form-' . (int)$appointment['id']; ?>

                            <tr class="border-b align-top">

                                <td class="p-3">
                                    <form id="<?= escape($formId) ?>" method="POST">
                                        <input
                                            type="hidden"
                                            name="appointment_id"
                                            value="<?= (int)$appointment['id'] ?>"
                                        >
                                    </form>

                                    <p class="font-semibold">
                                        <?= escape($appointment['customer_name']) ?>
                                    </p>

                                    <p class="text-gray-500">
                                        <?= escape($appointment['customer_phone'] ?? '') ?>
                                    </p>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['service']) ?>

                                    <?php if (!empty($appointment['notes'])): ?>
                                        <p class="text-gray-500 mt-1">
                                            <?= escape($appointment['notes']) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_date']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape(substr($appointment['appointment_time'], 0, 5)) ?>
                                </td>

                                <td class="p-3">
                                    <select
                                        name="salonist_id"
                                        form="<?= escape($formId) ?>"
                                        class="border rounded p-2 w-36">

                                        <option value="">Not assigned</option>

                                        <?php foreach ($salonists as $salonist): ?>
                                            <option
                                                value="<?= (int)$salonist['id'] ?>"
                                                <?= (int)$appointment['salonist_id'] === (int)$salonist['id'] ? 'selected' : '' ?>
                                            >
                                                <?= escape($salonist['full_name']) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>

                                <td class="p-3">
                                    <select
                                        name="status"
                                        form="<?= escape($formId) ?>"
                                        class="border rounded p-2">

                                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status): ?>
                                            <option
                                                value="<?= escape($status) ?>"
                                                <?= $appointment['status'] === $status ? 'selected' : '' ?>
                                            >
                                                <?= ucfirst($status) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>

                                <td class="p-3">
                                    <button
                                        type="submit"
                                        name="update_appointment"
                                        value="1"
                                        form="<?= escape($formId) ?>"
                                        class="bg-pink-600 text-white px-3 py-2 rounded hover:bg-pink-700 whitespace-nowrap">
                                        Update
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">
                                No appointments have been booked yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>