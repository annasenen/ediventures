<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

if (!isset($_SESSION["pending_airport_booking"])) {
    header("Location: /airport-booking.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$booking = $_SESSION["pending_airport_booking"];
$customerID = $_SESSION["customerID"];

$pickupAddress = "";
$dropoffAddress = "";

$customerAddress = $booking["house_number"] . ", " .
                   $booking["street"] . ", " .
                   $booking["town_city"] . ", " .
                   $booking["postcode"];

if ($booking["journey_type"] === "pickup") {
    $pickupAddress = $booking["airport_name"];
    $dropoffAddress = $customerAddress;
} else {
    $pickupAddress = $customerAddress;
    $dropoffAddress = $booking["airport_name"];
}

$luggageDetails = $booking["large_cases"] . " large suitcase(s), " .
                  $booking["small_bags"] . " small bag(s)";

$sql = "INSERT INTO bookings (
            customerID,
            vehicleID,
            bookingType,
            journeyType,
            airportName,
            zoneName,
            pickupAddress,
            dropoffAddress,
            flightNumber,
            passengers,
            luggageDetails,
            journeyStart,
            journeyEnd,
            basePrice,
            airportCharge,
            totalPrice,
            depositPercent,
            depositAmount,
            status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $customerID,
    $booking["vehicle_id"],
    "airport_transfer",
    $booking["journey_type"],
    $booking["airport_name"],
    $booking["zone_name"],
    $pickupAddress,
    $dropoffAddress,
    $booking["flight_number"],
    $booking["passengers"],
    $luggageDetails,
    $booking["journey_start"],
    $booking["journey_end"],
    $booking["base_price"],
    $booking["airport_charge"],
    $booking["total_price"],
    20,
    $booking["deposit_amount"],
    "pending_payment"
]);

$bookingID = $pdo->lastInsertId();

unset($_SESSION["pending_airport_booking"]);

header("Location: /customer/booking-created.php?bookingID=" . $bookingID);
exit;