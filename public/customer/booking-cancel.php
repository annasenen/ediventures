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

    $cancelled = $paymentService->cancelPendingBooking(
        $bookingID,
        $customerID
    );

    if ($cancelled) {
        $_SESSION["booking_message"] =
            "Your booking has been cancelled and the vehicle has been released.";
    } else {
        $_SESSION["booking_message"] =
            "This booking could not be cancelled.";
    }

} catch (Throwable $e) {
    error_log(
        "Booking cancellation error: " .
        $e->getMessage()
    );

    $_SESSION["booking_message"] =
        "Something went wrong while cancelling the booking.";
}

header("Location: /customer/dashboard.php");
exit;