
<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Redirect users according to their roles
function redirectUser($role)
{
    switch ($role) {

        case 'admin':
            header("Location: admin/dashboard.php");
            exit;

        case 'salonist':
            header("Location: salonist/dashboard.php");
            exit;

        case 'customer':
            header("Location: customer/dashboard.php");
            exit;

        default:
            $_SESSION = [];
            session_destroy();

            header("Location: login.php");
            exit;
    }
}

// Redirect already logged-in users
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    redirectUser($_SESSION['role']);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        try {

            $stmt = $conn->prepare("
                SELECT
                    id,
                    full_name,
                    email,
                    password,
                    role,
                    is_verified
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                // Customers must verify their email
                if (
                    $user['role'] === 'customer' &&
                    (int)$user['is_verified'] !== 1
                ) {

                    $error = "Please verify your email before logging in.";

                } else {

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Store user information
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect to the correct dashboard
                    redirectUser($user['role']);
                }

            } else {

                $error = "Invalid email address or password.";

            }

        } catch (PDOException $e) {

            error_log("Login database error: " . $e->getMessage());

            $error = "Unable to log in at the moment. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Grace Beauty Salon</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-pink-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-8">

        <div class="text-center mb-8">

            <h1 class="text-3xl font-bold text-pink-600">
                Grace Beauty Salon
            </h1>

            <p class="text-gray-500 mt-2">
                Welcome back! Please log in to continue.
            </p>

        </div>

        <?php if (!empty($error)): ?>

            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5">

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">

            <div>

                <label class="block text-gray-700 font-medium mb-2">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    required
                    value="<?= htmlspecialchars(
                        $_POST['email'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Enter your email"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                >

            </div>

            <div>

                <label class="block text-gray-700 font-medium mb-2">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    placeholder="Enter your password"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                >

            </div>

            <button
                type="submit"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold p-3 rounded-lg transition">

                Login

            </button>

        </form>

        <p class="text-center text-gray-600 mt-6">

            Don't have an account?

            <a
                href="register.php"
                class="text-pink-600 font-semibold hover:underline">

                Register here

            </a>

        </p>

    </div>

</body>

</html>
