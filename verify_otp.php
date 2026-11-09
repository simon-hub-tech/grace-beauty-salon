
<?php
require_once __DIR__ . '/config/database.php';

$message = "";
$messageType = "";

$email = trim($_GET['email'] ?? $_POST['email'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please provide a valid email address.";
        $messageType = "error";

    } elseif (!preg_match('/^\d{6}$/', $otp)) {

        $message = "Please enter a valid six-digit OTP.";
        $messageType = "error";

    } else {

        try {

            $stmt = $conn->prepare("
                SELECT id, otp_code, otp_expires_at, is_verified
                FROM users
                WHERE email = ? AND role = 'customer'
                LIMIT 1
            ");

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {

                $message = "Customer account not found.";
                $messageType = "error";

            } elseif ((int)$user['is_verified'] === 1) {

                $message = "Your email is already verified.";
                $messageType = "success";

            } elseif (
                empty($user['otp_code']) ||
                !hash_equals((string)$user['otp_code'], $otp)
            ) {

                $message = "Incorrect OTP. Please try again.";
                $messageType = "error";

            } elseif (
                empty($user['otp_expires_at']) ||
                strtotime($user['otp_expires_at']) < time()
            ) {

                $message = "Your OTP has expired. Please register again or request a new code.";
                $messageType = "error";

            } else {

                // Verify account only if OTP is still valid
                $update = $conn->prepare("
                    UPDATE users
                    SET
                        is_verified = 1,
                        otp_code = NULL,
                        otp_expires_at = NULL
                    WHERE id = ?
                    AND is_verified = 0
                    AND otp_code = ?
                    AND otp_expires_at >= NOW()
                ");

                $update->execute([
                    $user['id'],
                    $otp
                ]);

                if ($update->rowCount() > 0) {

                    $message = "Email verified successfully! You can now log in.";
                    $messageType = "success";

                } else {

                    $message = "OTP verification failed or the code has expired. Please try again.";
                    $messageType = "error";
                }
            }

        } catch (PDOException $e) {

            error_log("OTP verification error: " . $e->getMessage());

            $message = "A database error occurred. Please try again.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Email Verification | Grace Beauty Salon</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-pink-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-8">

        <div class="text-center mb-6">

            <h1 class="text-3xl font-bold text-pink-600">
                Grace Beauty Salon
            </h1>

            <h2 class="text-xl font-semibold mt-4">
                Verify Your Email
            </h2>

            <p class="text-gray-500 mt-2">
                Enter the six-digit code sent to your email.
            </p>

        </div>

        <?php if ($message !== ""): ?>

            <div class="mb-4 p-3 rounded-lg
                <?= $messageType === 'success'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700' ?>">

                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form method="POST" action="verify_otp.php" class="space-y-4">

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars(
                        $email,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                    readonly
                    class="w-full border rounded-lg p-3 bg-gray-50"
                >

            </div>

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Six-Digit OTP
                </label>

                <input
                    type="text"
                    name="otp"
                    required
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                    placeholder="Enter your OTP"
                    class="w-full border rounded-lg p-3 text-center tracking-widest text-xl focus:ring-2 focus:ring-pink-400 outline-none"
                >

            </div>

            <button
                type="submit"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold p-3 rounded-lg">

                Verify Email

            </button>

        </form>

        <?php if ($messageType === 'success'): ?>

            <a
                href="login.php"
                class="block text-center mt-5 bg-green-600 hover:bg-green-700 text-white p-3 rounded-lg">

                Proceed to Login

            </a>

        <?php endif; ?>

    </div>

</body>

</html>
