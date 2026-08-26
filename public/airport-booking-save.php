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
require_once __DIR__ . '/../app/Services/AirportPricingService.php';
require_once __DIR__ . '/../app/Services/BookingService.php';
require_once __DIR__ . '/../app/Services/PricingPeriodService.php';

$booking =
    $_SESSION["pending_airport_booking"];

$customerID =
    (int)$_SESSION["customerID"];

try {

    $pricingPeriodService =
        new PricingPeriodService($pdo);

    $pricingService =
        new AirportPricingService(
            $pdo,
            $pricingPeriodService
        );

    $bookingService =
        new BookingService(
            $pdo,
            $pricingService
        );

    $result =
        $bookingService->createAirportBooking(
            $customerID,
            $booking
        );

    /*
     * Availability changed and the replacement vehicle has
     * a different price.
     *
     * Save the recalculated booking and send the customer back
     * to review it before accepting the new price.
     */
    if ($result["status"] === "price_changed") {
        $_SESSION["pending_airport_booking"] =
            $result["booking"];

        header(
            "Location: /airport-booking-check.php?resume=1&price_changed=1"
        );
        exit;
    }

    if ($result["status"] !== "created") {
        throw new RuntimeException(
            "The booking could not be created."
        );
    }

    $bookingID =
        (int)$result["bookingID"];

} catch (InvalidArgumentException | RuntimeException $e) {
    $_SESSION["booking_error"] =
        $e->getMessage();

    header(
        "Location: /airport-booking-check.php?resume=1&availability_changed=1"
    );
    exit;

} catch (PDOException $e) {
    /*
     * Do not expose database information to customers.
     */
    error_log(
        "Airport booking database error: " .
        $e->getMessage()
    );

    $_SESSION["booking_error"] =
        "Something went wrong while securing your booking. Please try again.";

    header(
        "Location: /airport-transfers.php?edit=1"
    );
    exit;

} catch (Throwable $e) {
    /*
     * Catch unexpected server-side failures without leaking
     * application details.
     */
    error_log(
        "Airport booking error: " .
        $e->getMessage()
    );

    $_SESSION["booking_error"] =
        "Something went wrong while securing your booking. Please try again.";

    header(
        "Location: /airport-transfers.php?edit=1"
    );
    exit;
}

unset($_SESSION["pending_airport_booking"]);
unset($_SESSION["airport_booking_draft"]);

header(
    "Location: /customer/booking-created.php?bookingID=" .
    $bookingID
);
exit;