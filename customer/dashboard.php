
<?php

require_once __DIR__ . '/../includes/auth.php';

requireRole(['customer']);

require_once __DIR__ . '/../config/database.php';

$customer_id = $_SESSION['user_id'];

$pageTitle = 'Customer Dashboard';
$activePage = 'dashboard';

$message = '';
$messageType = '';

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {

    $service = trim($_POST['service'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    $allowedServices = [
        'Hair Styling',
        'Hair Treatment',
        'Manicure',
        'Pedicure',
        'Facial Treatment',
        'Makeup',
        'Massage'
    ];

    if (
        !in_array($service, $allowedServices, true) ||
        empty($appointment_date) ||
        empty($appointment_time)
    ) {
        $message = 'Please complete all required fields.';
        $messageType = 'error';

    } elseif ($appointment_date < date('Y-m-d')) {

        $message = 'Please select today or a future date.';
        $messageType = 'error';

    } elseif (
        $appointment_date === date('Y-m-d') &&
        $appointment_time <= date('H:i')
    ) {

        $message = 'Please select a future appointment time.';
        $messageType = 'error';

    } else {

        try {

            $stmt = $conn->prepare("
                INSERT INTO appointments
                (
                    customer_id,
                    service,
                    appointment_date,
                    appointment_time,
                    notes
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $customer_id,
                $service,
                $appointment_date,
                $appointment_time,
                $notes
            ]);

            $message = 'Your appointment has been booked successfully!';
            $messageType = 'success';

        } catch (PDOException $e) {

            $message = 'Unable to book appointment. Please try again.';
            $messageType = 'error';

        }
    }
}

// Count customer appointments
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM appointments
    WHERE customer_id = ?
");

$stmt->execute([$customer_id]);

$totalAppointments = $stmt->fetchColumn();

// Count pending appointments
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM appointments
    WHERE customer_id = ?
    AND status = 'pending'
");

$stmt->execute([$customer_id]);

$pendingAppointments = $stmt->fetchColumn();

// Get recent appointments
$stmt = $conn->prepare("
    SELECT *
    FROM appointments
    WHERE customer_id = ?
    ORDER BY appointment_date DESC, appointment_time DESC
    LIMIT 5
");

$stmt->execute([$customer_id]);

$appointments = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

?>

<main class="flex-1 p-6 md:p-8">

    <div class="mb-8">

        <h2 class="text-3xl font-bold text-gray-800">
            Welcome, <?= escape($_SESSION['full_name']) ?>!
        </h2>

        <p class="text-gray-600 mt-2">
            Manage your salon appointments here.
        </p>

    </div>

    <?php if ($message !== ''): ?>

        <div class="mb-6 p-4 rounded-lg
            <?= $messageType === 'success'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700' ?>">

            <?= escape($message) ?>

        </div>

    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div class="bg-white p-6 rounded-xl shadow">

            <p class="text-gray-500">Total Appointments</p>

            <h3 class="text-3xl font-bold text-pink-600 mt-2">
                <?= (int)$totalAppointments ?>
            </h3>

        </div>

        <div class="bg-white p-6 rounded-xl shadow">

            <p class="text-gray-500">Pending Appointments</p>

            <h3 class="text-3xl font-bold text-yellow-600 mt-2">
                <?= (int)$pendingAppointments ?>
            </h3>

        </div>

    </div>

    <!-- Appointment Booking Form -->
    <section id="booking" class="bg-white p-6 rounded-xl shadow mb-8">

        <h3 class="text-2xl font-bold text-gray-800 mb-6">
            Book an Appointment
        </h3>

        <form method="POST" action="dashboard.php" class="space-y-5">

            <div>

                <label class="block font-medium text-gray-700 mb-2">
                    Select Service
                </label>

                <select name="service" required
                        class="w-full border border-gray-300 rounded-lg p-3">

                    <option value="">Choose a service</option>
                    <option value="Hair Styling">Hair Styling</option>
                    <option value="Hair Treatment">Hair Treatment</option>
                    <option value="Manicure">Manicure</option>
                    <option value="Pedicure">Pedicure</option>
                    <option value="Facial Treatment">Facial Treatment</option>
                    <option value="Makeup">Makeup</option>
                    <option value="Massage">Massage</option>

                </select>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>

                    <label class="block font-medium text-gray-700 mb-2">
                        Appointment Date
                    </label>

                    <input
                        type="date"
                        name="appointment_date"
                        min="<?= date('Y-m-d') ?>"
                        required
                        class="w-full border border-gray-300 rounded-lg p-3"
                    >

                </div>

                <div>

                    <label class="block font-medium text-gray-700 mb-2">
                        Appointment Time
                    </label>

                    <input
                        type="time"
                        name="appointment_time"
                        required
                        class="w-full border border-gray-300 rounded-lg p-3"
                    >

                </div>

            </div>

            <div>

                <label class="block font-medium text-gray-700 mb-2">
                    Additional Notes (Optional)
                </label>

                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Enter any special requests"
                    class="w-full border border-gray-300 rounded-lg p-3"
                ></textarea>

            </div>

            <button
                type="submit"
                name="book_appointment"
                class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg font-semibold"
            >
                Book Appointment
            </button>

        </form>

    </section>

    <!-- Recent Appointments -->
    <section class="bg-white p-6 rounded-xl shadow">

        <div class="flex justify-between items-center mb-6">

            <h3 class="text-2xl font-bold text-gray-800">
                Recent Appointments
            </h3>

            <a href="my_appointments.php"
               class="text-pink-600 font-semibold hover:underline">
                View All
            </a>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="bg-pink-100">

                    <tr>
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
                                    <?= escape($appointment['service']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_date']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($appointment['appointment_time']) ?>
                                </td>

                                <td class="p-3">
                                    <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700">
                                        <?= escape(ucfirst($appointment['status'])) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="p-5 text-center text-gray-500">
                                No appointments booked yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
