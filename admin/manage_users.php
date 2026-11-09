<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['admin']);

require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = 'success';

// Create salonist account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_salonist'])) {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please provide a valid name and email address.';
        $messageType = 'error';

    } elseif (strlen($password) < 8) {
        $message = 'Password must contain at least 8 characters.';
        $messageType = 'error';

    } else {

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users
                (full_name, email, phone, password, role, is_verified)
                VALUES (?, ?, ?, ?, 'salonist', 1)
            ");

            $stmt->execute([
                $fullName,
                $email,
                $phone,
                $hashedPassword
            ]);

            $message = 'Salonist account created successfully.';

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $message = 'This email address is already registered.';
            } else {
                $message = 'Unable to create the account. Please try again.';
            }

            $messageType = 'error';
        }
    }
}

// Delete customer or salonist account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {

    $userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$userId || $userId == $_SESSION['user_id']) {

        $message = 'Invalid account selected.';
        $messageType = 'error';

    } else {

        $stmt = $conn->prepare("
            DELETE FROM users
            WHERE id = ? AND role IN ('customer', 'salonist')
        ");

        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            $message = 'User account deleted successfully.';
        } else {
            $message = 'Account could not be found or deleted.';
            $messageType = 'error';
        }
    }
}

// Fetch users
$stmt = $conn->prepare("
    SELECT id, full_name, email, phone, role, created_at
    FROM users
    WHERE role IN ('customer', 'salonist')
    ORDER BY created_at DESC
");

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Users';
$activePage = 'users';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 p-6 md:p-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        Manage Users
    </h2>

    <p class="text-gray-600 mb-6">
        Create salonist accounts and manage registered users.
    </p>

    <?php if ($message !== ''): ?>

        <div class="mb-5 p-4 rounded-lg
            <?= $messageType === 'success'
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800' ?>">
            <?= escape($message) ?>
        </div>

    <?php endif; ?>

    <div class="bg-white p-6 rounded-xl shadow mb-8">

        <h3 class="text-xl font-bold mb-5">
            Create Salonist Account
        </h3>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <input
                type="text"
                name="full_name"
                placeholder="Full Name"
                required
                class="border rounded-lg p-3 w-full"
            >

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
                class="border rounded-lg p-3 w-full"
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone Number"
                class="border rounded-lg p-3 w-full"
            >

            <input
                type="password"
                name="password"
                placeholder="Initial Password (minimum 8 characters)"
                minlength="8"
                required
                class="border rounded-lg p-3 w-full"
            >

            <div class="md:col-span-2">

                <button
                    type="submit"
                    name="create_salonist"
                    class="bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700">
                    Create Salonist
                </button>

            </div>

        </form>

    </div>

    <div class="bg-white p-6 rounded-xl shadow">

        <h3 class="text-xl font-bold mb-5">
            Registered Users
        </h3>

        <div class="overflow-x-auto">

            <table class="w-full text-left text-sm">

                <thead class="bg-pink-100">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Date Registered</th>
                        <th class="p-3">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($users): ?>

                        <?php foreach ($users as $user): ?>

                            <tr class="border-b">

                                <td class="p-3">
                                    <?= escape($user['full_name']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($user['email']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($user['phone'] ?? '') ?>
                                </td>

                                <td class="p-3 capitalize">
                                    <?= escape($user['role']) ?>
                                </td>

                                <td class="p-3">
                                    <?= escape($user['created_at']) ?>
                                </td>

                                <td class="p-3">

                                    <form method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this account?');">

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?= (int)$user['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_user"
                                            class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">
                                No users found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>