
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

$emailConfig = require __DIR__ . '/config/email.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $emailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $emailConfig['username'];
    $mail->Password   = $emailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $emailConfig['port'];

    $mail->setFrom(
        $emailConfig['from_email'],
        $emailConfig['from_name']
    );

    // Replace with your own email address
    $mail->addAddress('mukhwana@kabarak.ac.ke');

    $mail->isHTML(true);
    $mail->Subject = 'Grace Beauty Salon Email Test';

    $mail->Body = '
        <h2>Grace Beauty Salon</h2>
        <p>Congratulations!</p>
        <p>Your email configuration is working successfully.</p>
    ';

    $mail->send();

    echo "Email sent successfully!";

} catch (Exception $e) {
    echo "Email could not be sent. Error: " . $mail->ErrorInfo;
}

?>
