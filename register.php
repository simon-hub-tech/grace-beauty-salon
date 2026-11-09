
<?php
require_once __DIR__ . '/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || $email === "" || $phone === "" || $password === "") {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $messageType = "error";

    } else {

        try {

            // Check whether email already exists
            $check = $conn->prepare(
                "SELECT id FROM users WHERE email = ?"
            );

            $check->execute([$email]);

            if ($check->fetch()) {

                $message = "This email address is already registered.";
                $messageType = "error";

            } else {

                // Generate six-digit OTP
                $otp = (string) random_int(100000, 999999);

                // OTP expires after 10 minutes
                $otp_expiry = date(
                    "Y-m-d H:i:s",
                    time() + 600
                );

                // Hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Start transaction
                $conn->beginTransaction();

                // Save customer details
                $stmt = $conn->prepare("
                    INSERT INTO users
                    (
                        full_name,
                        email,
                        phone,
                        password,
                        role,
                        is_verified,
                        otp_code,
                        otp_expires_at
                    )
                    VALUES (?, ?, ?, ?, 'customer', 0, ?, ?)
                ");

                $stmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $hashed_password,
                    $otp,
                    $otp_expiry
                ]);

                // Load email configuration
                $emailConfig = require __DIR__ . '/config/email.php';

                // Send OTP email
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $mail->Host = $emailConfig['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $emailConfig['username'];
                $mail->Password = $emailConfig['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $emailConfig['port'];

                $mail->setFrom(
                    $emailConfig['from_email'],
                    $emailConfig['from_name']
                );

                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = "Grace Beauty Salon - Email Verification";

                $safeName = htmlspecialchars(
                    $full_name,
                    ENT_QUOTES,
                    'UTF-8'
                );

                $mail->Body = "
                    <div style='font-family:Arial;padding:20px;'>
                        <h2 style='color:#db2777;'>
                            Grace Beauty Salon
                        </h2>

                        <p>Hello $safeName,</p>

                        <p>
                            Thank you for registering with
                            Grace Beauty Salon.
                        </p>

                        <p>Your email verification code is:</p>

                        <h1 style='color:#db2777;letter-spacing:5px;'>
                            $otp
                        </h1>

                        <p>This code expires in 10 minutes.</p>

                        <p>
                            If you did not register,
                            please ignore this email.
                        </p>
                    </div>
                ";

                $mail->AltBody =
                    "Your Grace Beauty Salon verification code is: "
                    . $otp
                    . ". It expires in 10 minutes.";

                $mail->send();

                // Commit only after email is sent successfully
                $conn->commit();

                $message = "Registration successful! Check your email for your OTP.";
                $messageType = "success";

                header(
                    "refresh:3;url=verify_otp.php?email="
                    . urlencode($email)
                );
            }

        } catch (Exception $e) {

            // Roll back account if email sending fails
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log(
                "Registration email error: " . $e->getMessage()
            );

            $message = "Registration could not be completed. Please try again later.";
            $messageType = "error";

        } catch (PDOException $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log(
                "Registration database error: " . $e->getMessage()
            );

            if ($e->getCode() === '23000') {
                $message = "This email address is already registered.";
            } else {
                $message = "A database error occurred. Please try again.";
            }

            $messageType = "error";

        } catch (Throwable $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log(
                "Registration error: " . $e->getMessage()
            );

            $message = "An unexpected error occurred. Please try again.";
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

    <title>Customer Registration | Grace Beauty Salon</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-pink-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-8">

        <div class="text-center mb-6">

            <h1 class="text-3xl font-bold text-pink-600">
                Grace Beauty Salon
            </h1>

            <p class="text-gray-500 mt-2">
                Create your customer account
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

        <form method="POST" action="register.php" class="space-y-4">

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Full Name
                </label>

                <input
                    type="text"
                    name="full_name"
                    required
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                    placeholder="Enter your full name"
                >

            </div>

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    required
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                    placeholder="Enter your email"
                >

            </div>

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Phone Number
                </label>

                <input
                    type="tel"
                    name="phone"
                    required
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                    placeholder="Enter your phone number"
                >

            </div>

            <div>

                <label class="block text-gray-700 font-medium mb-1">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-pink-400 outline-none"
                    placeholder="Create a password"
                >

            </div>

            <button
                type="submit"
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-semibold p-3 rounded-lg transition">

                Register

            </button>

        </form>

        <p class="text-center text-gray-600 mt-6">

            Already have an account?

            <a href="login.php" class="text-pink-600 font-semibold">
                Login
            </a>

        </p>

    </div>

</body>

</html>
