<?php

session_start();

$pageTitle = "Airport Booking Check | EdiVentures";
$metaDescription = "Check airport transfer availability with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-booking-check.php";

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/VehicleAvailabilityService.php';
require_once __DIR__ . '/../app/Services/AirportPricingService.php';
require_once __DIR__ . '/../app/Services/AirportJourneyRuleService.php';
require_once __DIR__ . '/../app/Services/ZoneService.php';
require_once __DIR__ . '/../app/Services/PricingPeriodService.php';

$errors = [];

$availableVehicle = null;

$basePrice = 0.00;
$airportCharge = 0.00;
$totalPrice = 0.00;
$depositAmount = 0.00;
$remainingBalance = 0.00;

$journeyType = "";
$airportCode = "";
$postcode = "";
$houseNumber = "";
$street = "";
$townCity = "";
$travelDate = "";
$travelHour = "";
$travelMinute = "";
$flightNumber = "";
$passengers = "";
$largeCases = "";
$smallBags = "";
$oversizedLuggage = "";
$extraStop = "";

$journeyStart = null;
$journeyEnd = null;
$zoneName = "";

$isResume = (
    isset($_GET["resume"]) &&
    $_GET["resume"] === "1"
);

/*
|--------------------------------------------------------------------------
| LOAD BOOKING REQUEST
|--------------------------------------------------------------------------
|
| A new booking arrives by POST.
|
| After login/register, the customer returns using ?resume=1 and the
| original journey data is loaded from the PHP session.
|
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $journeyType = $_POST["journey_type"] ?? "";
    $airportCode = $_POST["airport"] ?? "";
    $postcode = strtoupper(trim($_POST["postcode"] ?? ""));
    $houseNumber = trim($_POST["house_number"] ?? "");
    $street = trim($_POST["street"] ?? "");
    $townCity = trim($_POST["town_city"] ?? "");
    $travelDate = $_POST["travel_date"] ?? "";
    $travelHour = $_POST["travel_hour"] ?? "";
    $travelMinute = $_POST["travel_minute"] ?? "";
    $flightNumber = strtoupper(trim($_POST["flight_number"] ?? ""));
    $passengers = $_POST["passengers"] ?? "";
    $largeCases = $_POST["large_cases"] ?? "";
    $smallBags = $_POST["small_bags"] ?? "";
    $oversizedLuggage = $_POST["oversized_luggage"] ?? "";
    $extraStop = $_POST["extra_stop"] ?? "";

    $_SESSION["airport_booking_draft"] = [
    "journey_type" => $journeyType,
    "airport" => $airportCode,
    "postcode" => $postcode,
    "house_number" => $houseNumber,
    "street" => $street,
    "town_city" => $townCity,
    "travel_date" => $travelDate,
    "travel_hour" => $travelHour,
    "travel_minute" => $travelMinute,
    "flight_number" => $flightNumber,
    "passengers" => $passengers,
    "large_cases" => $largeCases,
    "small_bags" => $smallBags,
    "oversized_luggage" => $oversizedLuggage,
    "extra_stop" => $extraStop
];

} elseif (
    $isResume &&
    isset($_SESSION["pending_airport_booking"])
) {

    $savedBooking = $_SESSION["pending_airport_booking"];

    $journeyType = $savedBooking["journey_type"] ?? "";
    $airportCode = $savedBooking["airport"] ?? "";
    $postcode = $savedBooking["postcode"] ?? "";
    $houseNumber = $savedBooking["house_number"] ?? "";
    $street = $savedBooking["street"] ?? "";
    $townCity = $savedBooking["town_city"] ?? "";
    $travelDate = $savedBooking["travel_date"] ?? "";
    $travelHour = $savedBooking["travel_hour"] ?? "";
    $travelMinute = $savedBooking["travel_minute"] ?? "";
    $flightNumber = $savedBooking["flight_number"] ?? "";
    $passengers = $savedBooking["passengers"] ?? "";
    $largeCases = $savedBooking["large_cases"] ?? "";
    $smallBags = $savedBooking["small_bags"] ?? "";

    /*
     * These were already checked before the customer was sent
     * to login/register.
     */
    $oversizedLuggage = "no";
    $extraStop = "no";

} else {

    header("Location: /airport-transfers.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AIRPORT INFORMATION
|--------------------------------------------------------------------------
*/

$airportNames = [
    "edinburgh" => "Edinburgh Airport",
    "glasgow" => "Glasgow Airport",
    "prestwick" => "Glasgow Prestwick Airport",
    "dundee" => "Dundee Airport",
    "newcastle" => "Newcastle Airport"
];

$airportName = $airportNames[$airportCode] ?? "";

/*
|--------------------------------------------------------------------------
| SERVER-SIDE VALIDATION
|--------------------------------------------------------------------------
*/

if (!in_array($journeyType, ["pickup", "dropoff"], true)) {
    $errors[] = "Please choose airport pickup or drop-off.";
}

if ($airportName === "") {
    $errors[] = "This airport needs manual confirmation. Please request a quote.";
}

if (
    $postcode === "" ||
    $houseNumber === "" ||
    $street === "" ||
    $townCity === ""
) {
    $errors[] = "Please enter the full journey address.";
}

if (
    $travelDate === "" ||
    $travelHour === "" ||
    $travelMinute === ""
) {
    $errors[] = "Please choose travel date and time.";
}

if ($passengers === "" || (int)$passengers < 1) {
    $errors[] = "Please choose number of passengers.";
}

if ($largeCases === "" || $smallBags === "") {
    $errors[] = "Please choose luggage details.";
}

if ($oversizedLuggage === "yes" || $extraStop === "yes") {
    $errors[] = "This booking needs manual confirmation because of oversized luggage, unusual items or an extra stop.";
}

/*
|--------------------------------------------------------------------------
| BUILD JOURNEY DATE/TIME
|--------------------------------------------------------------------------
*/

if (empty($errors)) {

    $journeyStartText =
        $travelDate . " " .
        $travelHour . ":" .
        $travelMinute . ":00";

    $londonTimezone = new DateTimeZone('Europe/London');

    $journeyStart = DateTime::createFromFormat(
        "Y-m-d H:i:s",
        $journeyStartText,
        $londonTimezone
    );

    if (!$journeyStart) {
        $errors[] = "Invalid travel date or time.";
    } else {
        $journeyStart->setTimezone(
            new DateTimeZone('UTC')
        );
    }
}

/*
|--------------------------------------------------------------------------
| TEMPORARY JOURNEY BLOCK + ZONE LOGIC
|--------------------------------------------------------------------------
|
| We will later move these rules into database/admin-controlled services.
|
*/

if (empty($errors)) {

    try {
        $journeyRuleService =
            new AirportJourneyRuleService($pdo);

        $journeyEnd =
            $journeyRuleService->calculateJourneyEnd(
                $journeyStart,
                $airportName,
                $journeyType
            );

        if (!$journeyEnd) {
            $errors[] =
                "Online booking timing is not configured for this airport and journey type yet.";
        }

    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }

    try {

        $zoneService =
            new ZoneService($pdo);

        $zone =
            $zoneService->findZoneByPostcode(
                $postcode
            );

        if (!$zone) {

            $errors[] =
                "This postcode is outside the current automatic booking zones. Please request a quote.";

        } else {

            $zoneName =
                $zone["zoneName"];
        }

    } catch (InvalidArgumentException $e) {

        $errors[] =
            $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| VEHICLE AVAILABILITY SERVICE
|--------------------------------------------------------------------------
*/

if (empty($errors)) {

    try {

        $availabilityService =
            new VehicleAvailabilityService($pdo);

        $availableVehicle =
            $availabilityService->findAvailableVehicle(
                $journeyStart,
                $journeyEnd,
                (int)$passengers
            );

        if (!$availableVehicle) {
            $errors[] = "Sorry, no vehicle is available for this date and time. Please choose another time or request a quote.";
        }

    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| AIRPORT PRICING SERVICE
|--------------------------------------------------------------------------
*/

if (empty($errors)) {

    try {

        $pricingPeriodService =
            new PricingPeriodService($pdo);

        $pricingService =
            new AirportPricingService(
                $pdo,
                $pricingPeriodService
            );

        $pricing =
            $pricingService->getAirportBookingPrice(
                (int)$availableVehicle["vehicleID"],
                $airportName,
                $zoneName,
                $journeyType,
                $journeyStart,
                (int)$passengers,
                20
            );

        if (!$pricing) {

            $errors[] =
                "Price is not available for this airport, vehicle and journey yet. Please request a quote.";

        } else {

            $basePrice = $pricing["basePrice"];
            $airportCharge = $pricing["airportCharge"];
            $totalPrice = $pricing["totalPrice"];
            $depositAmount = $pricing["depositAmount"];
            $remainingBalance = $pricing["remainingBalance"];
        }

    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| SAVE SUCCESSFUL QUOTE IN SESSION
|--------------------------------------------------------------------------
|
| This means login/register cannot lose the journey.
|
| When the customer returns after login we run the availability and pricing
| services again, so we do NOT blindly trust the old vehicle/price.
|
*/

if (empty($errors)) {

    $_SESSION["pending_airport_booking"] = [

        "journey_type" => $journeyType,

        "airport" => $airportCode,
        "airport_name" => $airportName,

        /*
         * Zone stays internal.
         * We need it for booking/pricing but do not display it.
         */
        "zone_name" => $zoneName,

        "postcode" => $postcode,
        "house_number" => $houseNumber,
        "street" => $street,
        "town_city" => $townCity,

        "travel_date" => $travelDate,
        "travel_hour" => $travelHour,
        "travel_minute" => $travelMinute,

        "flight_number" => $flightNumber,

        "passengers" => $passengers,
        "large_cases" => $largeCases,
        "small_bags" => $smallBags,

        /*
         * These values are freshly calculated by our services.
         */
        "vehicle_id" => $availableVehicle["vehicleID"],

        "journey_start" =>
            $journeyStart->format("Y-m-d H:i:s"),

        "journey_end" =>
            $journeyEnd->format("Y-m-d H:i:s"),

        "base_price" => $basePrice,
        "airport_charge" => $airportCharge,
        "total_price" => $totalPrice,
        "deposit_amount" => $depositAmount,
        "remaining_balance" => $remainingBalance
    ];

    /*
     * If login becomes necessary, tell the authentication flow exactly
     * where this customer should return.
     */
    if (!isset($_SESSION["customerID"])) {
        $_SESSION["after_login_redirect"] =
            "/airport-booking-check.php?resume=1";
    }
}

$displayJourneyStart = null;

if ($journeyStart instanceof DateTimeInterface) {
    $displayJourneyStart = DateTimeImmutable::createFromInterface(
        $journeyStart
    )->setTimezone(
        new DateTimeZone('Europe/London')
    );
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <p class="eyebrow mb-3">
                    Airport Transfer
                </p>

                <h1 class="page-title">
                    Your Airport Transfer
                </h1>

                <p class="page-hero-text">
                    Check your journey details and price before securing your booking.
                </p>

            </div>

        </div>

    </div>

</section>


<section class="py-5 bg-light">

    <div class="container">

        <?php if (!empty($errors)): ?>

            <div class="booking-form-card mx-auto">

                <h2 class="section-title mb-3">
                    <?php if (
                        isset($_GET["availability_changed"]) &&
                        $_GET["availability_changed"] === "1"
                    ): ?>

                        Availability changed while you were booking

                    <?php else: ?>

                        Booking not available online

                    <?php endif; ?>
                </h2>

                <div class="alert alert-warning">

                    <?php if (
                        isset($_GET["availability_changed"]) &&
                        $_GET["availability_changed"] === "1"
                    ): ?>

                        Unfortunately, this journey is no longer available at the selected time.
                        Please choose another time or request a quote.

                    <?php else: ?>

                        <ul class="mb-0">

                            <?php foreach ($errors as $error): ?>

                                <li>
                                    <?= htmlspecialchars($error) ?>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    <?php endif; ?>

                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">

                    <a
                        href="/airport-transfers.php?edit=1"
                        class="btn btn-brand rounded-pill px-4"
                    >
                        Change Journey
                    </a>

                    <a
                        href="/quote.php"
                        class="btn btn-outline-dark rounded-pill px-4"
                    >
                        Request a Quote
                    </a>

                </div>

            </div>


        <?php else: ?>


            <div class="booking-form-card mx-auto">

            <?php if (
                isset($_GET["availability_changed"]) &&
                $_GET["availability_changed"] === "1"
            ): ?>

                <div class="alert alert-warning">
                    <strong>Availability changed while you were booking.</strong>
                </div>

            <?php endif; ?>

            <?php if (
                isset($_GET["price_changed"]) &&
                $_GET["price_changed"] === "1"
            ): ?>

                <div class="alert alert-warning">
                    <strong>Availability changed while you were booking.</strong>
                    We found another suitable vehicle and recalculated the price.
                    Please review the updated amount before continuing.
                </div>

            <?php endif; ?>

                <?php if (
                    !isset($_GET["availability_changed"]) ||
                    $_GET["availability_changed"] !== "1"
                ): ?>

                    <div class="alert alert-success">

                        <i class="fa-solid fa-circle-check me-2"></i>

                        This journey is currently available.

                    </div>

                <?php endif; ?>


                <!-- YOUR JOURNEY -->

                <div class="custom-tour-box mb-4">

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">

                        <h2 class="section-title mb-0">
                            Your Journey
                        </h2>

                        <a
                            href="/airport-transfers.php?edit=1"
                            class="btn btn-sm btn-outline-dark rounded-pill px-3"
                        >
                            <i class="fa-solid fa-pen me-1"></i>
                            Change
                        </a>

                    </div>


                    <p class="mb-2">

                        <strong>
                            <?= htmlspecialchars($airportName) ?>
                            <?= $journeyType === "pickup" ? "Pickup" : "Drop-off" ?>
                        </strong>

                    </p>


                    <p class="mb-2">

                        <i class="fa-regular fa-calendar me-2"></i>

                        <?= htmlspecialchars(
                            $displayJourneyStart->format("d/m/Y")
                        ) ?>

                        at

                        <?= htmlspecialchars(
                            $displayJourneyStart->format("H:i")
                        ) ?>

                    </p>


                    <p class="mb-2">

                        <i class="fa-solid fa-location-dot me-2"></i>

                        <?= htmlspecialchars(
                            $houseNumber . ", " .
                            $street . ", " .
                            $townCity . ", " .
                            $postcode
                        ) ?>

                    </p>


                    <p class="mb-2">

                        <i class="fa-solid fa-user-group me-2"></i>

                        <?= htmlspecialchars($passengers) ?>
                        passenger<?= (int)$passengers === 1 ? "" : "s" ?>

                    </p>


                    <p class="mb-0">

                        <i class="fa-solid fa-suitcase me-2"></i>

                        <?= htmlspecialchars($largeCases) ?>
                        large suitcase(s),

                        <?= htmlspecialchars($smallBags) ?>
                        small bag(s)

                    </p>


                    <?php if ($flightNumber !== ""): ?>

                        <p class="mt-2 mb-0">

                            <strong>Flight:</strong>

                            <?= htmlspecialchars($flightNumber) ?>

                        </p>

                    <?php endif; ?>

                </div>


                <!-- PRICE -->

                <div class="custom-tour-box mb-4">

                    <h2 class="section-title mb-3">
                        Price
                    </h2>


                    <table class="table align-middle mb-0">

                        <tbody>

                            <tr>

                                <td>

                                    <strong>Journey price</strong>

                                    <div class="small text-muted">
                                        Private hire journey for your selected route.
                                    </div>

                                </td>

                                <td class="text-end">
                                    £<?= number_format($basePrice, 2) ?>
                                </td>

                            </tr>


                            <tr>

                                <td>

                                    <strong>Airport charge</strong>

                                    <div class="small text-muted">
                                        Charge applied by the airport for pickup or drop-off access.
                                    </div>

                                </td>

                                <td class="text-end">
                                    £<?= number_format($airportCharge, 2) ?>
                                </td>

                            </tr>


                            <tr class="fw-bold">

                                <td class="fs-5">
                                    Total
                                </td>

                                <td class="text-end fs-5">
                                    £<?= number_format($totalPrice, 2) ?>
                                </td>

                            </tr>


                            <tr>

                                <td>

                                    <strong>Pay today — 20% deposit</strong>

                                    <div class="small text-muted">
                                        Secures your booking.
                                    </div>

                                </td>

                                <td class="text-end fw-bold">
                                    £<?= number_format($depositAmount, 2) ?>
                                </td>

                            </tr>


                            <tr>

                                <td>

                                    <strong>Balance to driver</strong>

                                    <div class="small text-muted">
                                        Remaining amount payable on the day.
                                    </div>

                                </td>

                                <td class="text-end">
                                    £<?= number_format($remainingBalance, 2) ?>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- CUSTOMER ACTION -->

                <?php if (!isset($_SESSION["customerID"])): ?>

                    <div class="alert alert-info">

                        <strong>Ready to book?</strong>

                        Log in or create an account to continue.
                        Your journey is saved while you sign in.

                    </div>


                    <div class="d-flex flex-column flex-sm-row gap-3">

                        <a
                            href="/login.php"
                            class="btn btn-brand btn-lg rounded-pill px-5"
                        >
                            Log In to Continue
                        </a>

                        <a
                            href="/register.php"
                            class="btn btn-outline-dark btn-lg rounded-pill px-5"
                        >
                            Create Account
                        </a>

                    </div>


                <?php else: ?>


                    <div class="alert alert-success">

                        Logged in as

                        <strong>
                            <?= htmlspecialchars(
                                $_SESSION["customerName"]
                            ) ?>
                        </strong>.

                    </div>


                    <div class="d-flex flex-column flex-sm-row gap-3">

                        <form
                            action="/airport-booking-save.php"
                            method="post"
                        >
                            <button
                                type="submit"
                                class="btn btn-brand btn-lg rounded-pill px-5"
                            >
                                Proceed to Deposit Payment
                            </button>
                        </form>

                        <form
                            action="/airport-booking-cancel-quote.php"
                            method="post"
                        >
                            <button
                                type="submit"
                                class="btn btn-outline-dark btn-lg rounded-pill px-5"
                            >
                                Cancel / Start Again
                            </button>
                        </form>

                    </div>


                <?php endif; ?>

            </div>


        <?php endif; ?>

    </div>

</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>