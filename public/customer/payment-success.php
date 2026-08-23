<?php

session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /customer/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/PaymentService.php';

$bookingID = (int)($_POST["bookingID"] ?? 0);
$customerID = (int)$_SESSION["customerID"];

try {
    $paymentService = new PaymentService($pdo);

    $confirmed = $paymentService->confirmPayment(
        $bookingID,
        $customerID
    );

    if (!$confirmed) {
        $_SESSION["booking_message"] =
            "The booking could not be confirmed. The payment hold may have expired.";

        header("Location: /customer/dashboard.php");
        exit;
    }

    $_SESSION["booking_message"] =
        "Payment simulated successfully. Your booking is now confirmed.";

    header(
        "Location: /customer/booking-confirmed.php?bookingID=" .
        $bookingID
    );
    exit;

} catch (Throwable $e) {
    error_log(
        "Simulated payment confirmation error: " .
        $e->getMessage()
    );

    $_SESSION["booking_message"] =
        "Something went wrong while confirming the booking.";

    header("Location: /customer/dashboard.php");
    exit;
}