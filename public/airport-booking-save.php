<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

if (!isset($_SESSION["pending_airport_booking"])) {
    header("Location: /airport-transfers.php?edit=1");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/BookingService.php';

$booking = $_SESSION["pending_airport_booking"];
$customerID = $_SESSION["customerID"];

try {
    $bookingService = new BookingService($pdo);

    $bookingID = $bookingService->createAirportBooking(
        (int)$customerID,
        $booking
    );

} catch (InvalidArgumentException $e) {
    $_SESSION['booking_error'] = $e->getMessage();
    header("Location: /airport-transfers.php?edit=1");
    exit;
}

unset($_SESSION["pending_airport_booking"]);
unset($_SESSION["airport_booking_draft"]);

header("Location: /customer/booking-created.php?bookingID=" . $bookingID);
exit;