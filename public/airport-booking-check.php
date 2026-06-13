<?php
session_start();

$pageTitle = "Airport Booking Check | EdiVentures";
$metaDescription = "Check airport transfer availability with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-booking-check.php";

require_once __DIR__ . '/../config/database.php';

$errors = [];
$availableVehicle = null;
$basePrice = null;
$airportCharge = 0.00;
$totalPrice = 0.00;
$depositAmount = 0.00;
$remainingBalance = 0.00;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /airport-booking.php");
    exit;
}

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

$airportNames = [
    "edinburgh" => "Edinburgh Airport",
    "glasgow" => "Glasgow Airport",
    "prestwick" => "Glasgow Prestwick Airport",
    "dundee" => "Dundee Airport",
    "newcastle" => "Newcastle Airport"
];

$airportName = $airportNames[$airportCode] ?? "";

if ($journeyType === "") {
    $errors[] = "Please choose airport pickup or drop-off.";
}

if ($airportName === "") {
    $errors[] = "This airport needs manual confirmation. Please request a quote.";
}

if ($postcode === "" || $houseNumber === "" || $street === "" || $townCity === "") {
    $errors[] = "Please enter the full journey address.";
}

if ($travelDate === "" || $travelHour === "" || $travelMinute === "") {
    $errors[] = "Please choose travel date and time.";
}

if ($passengers === "") {
    $errors[] = "Please choose number of passengers.";
}

if ($largeCases === "" || $smallBags === "") {
    $errors[] = "Please choose luggage details.";
}

if ($oversizedLuggage === "yes" || $extraStop === "yes") {
    $errors[] = "This booking needs manual confirmation because of oversized luggage, unusual items or an extra stop.";
}

$journeyStart = null;
$journeyEnd = null;
$zoneName = "";

if (empty($errors)) {
    $journeyStartText = $travelDate . " " . $travelHour . ":" . $travelMinute . ":00";
    $journeyStart = DateTime::createFromFormat("Y-m-d H:i:s", $journeyStartText);

    if (!$journeyStart) {
        $errors[] = "Invalid travel date or time.";
    }
}

if (empty($errors)) {
    $blockHours = 2;

    if ($airportCode !== "edinburgh") {
        $blockHours = 3;
    }

    $journeyEnd = clone $journeyStart;
    $journeyEnd->modify("+{$blockHours} hours");

    $cleanPostcode = strtoupper(str_replace(' ', '', $postcode));
    $postcodePrefix4 = substr($cleanPostcode, 0, 4);

    if (
        $postcodePrefix4 === "EH30" ||
        $postcodePrefix4 === "EH29" ||
        $postcodePrefix4 === "EH28"
    ) {
        $zoneName = "Zone A";
    } elseif (
        $postcodePrefix4 === "EH12" ||
        $postcodePrefix4 === "KY11"
    ) {
        $zoneName = "Zone B";
    } else {
        $errors[] = "This postcode is outside the current automatic booking zones. Please request a quote.";
    }
}

if (empty($errors)) {
    $vehicleSql = "SELECT *
                   FROM vehicles
                   WHERE isActive = 1
                   ORDER BY vehicleID ASC";

    $vehicleStmt = $pdo->query($vehicleSql);
    $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

    $now = new DateTime();
    $journeyStartString = $journeyStart->format("Y-m-d H:i:s");
    $journeyEndString = $journeyEnd->format("Y-m-d H:i:s");

    foreach ($vehicles as $vehicle) {
        $minimumNoticeHours = (int)$vehicle["minimumNoticeHours"];

        $earliestAllowed = clone $now;
        $earliestAllowed->modify("+{$minimumNoticeHours} hours");

        if ($journeyStart < $earliestAllowed) {
            continue;
        }

        $blockSql = "SELECT blockID
                     FROM vehicle_blocks
                     WHERE vehicleID = ?
                     AND blockStart < ?
                     AND blockEnd > ?
                     LIMIT 1";

        $blockStmt = $pdo->prepare($blockSql);
        $blockStmt->execute([
            $vehicle["vehicleID"],
            $journeyEndString,
            $journeyStartString
        ]);

        if ($blockStmt->fetch()) {
            continue;
        }

        $bookingSql = "SELECT bookingID
                       FROM bookings
                       WHERE vehicleID = ?
                       AND status IN ('pending', 'confirmed')
                       AND journeyStart < ?
                       AND journeyEnd > ?
                       LIMIT 1";

        $bookingStmt = $pdo->prepare($bookingSql);
        $bookingStmt->execute([
            $vehicle["vehicleID"],
            $journeyEndString,
            $journeyStartString
        ]);

        if ($bookingStmt->fetch()) {
            continue;
        }

        $availableVehicle = $vehicle;
        break;
    }

    if (!$availableVehicle) {
        $errors[] = "Sorry, no vehicle is available for this date and time. Please choose another time or request a quote.";
    }
}

if (empty($errors)) {
    $priceSql = "SELECT basePrice
                 FROM airport_pricing
                 WHERE airportName = ?
                 AND zoneName = ?
                 AND journeyType = ?
                 AND isActive = 1
                 LIMIT 1";

    $priceStmt = $pdo->prepare($priceSql);
    $priceStmt->execute([
        $airportName,
        $zoneName,
        $journeyType
    ]);

    $priceRow = $priceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$priceRow) {
        $errors[] = "Price is not available for this airport and zone yet. Please request a quote.";
    } else {
        $basePrice = (float)$priceRow["basePrice"];

        $chargeSql = "SELECT pickupCharge, dropoffCharge
                      FROM airport_charges
                      WHERE airportName = ?
                      AND isActive = 1
                      LIMIT 1";

        $chargeStmt = $pdo->prepare($chargeSql);
        $chargeStmt->execute([$airportName]);

        $chargeRow = $chargeStmt->fetch(PDO::FETCH_ASSOC);

        if ($chargeRow) {
            if ($journeyType === "pickup") {
                $airportCharge = (float)$chargeRow["pickupCharge"];
            } else {
                $airportCharge = (float)$chargeRow["dropoffCharge"];
            }
        }

        $totalPrice = $basePrice + $airportCharge;
        $depositAmount = round($totalPrice * 0.20, 2);
        $remainingBalance = $totalPrice - $depositAmount;
    }
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
                <p class="eyebrow mb-3">Airport Transfer</p>
                <h1 class="page-title">Airport Booking Check</h1>
                <p class="page-hero-text">
                    We check vehicle availability before showing the booking summary.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <?php if (!empty($errors)): ?>

            <div class="booking-form-card mx-auto">
                <h2 class="section-title mb-3">Booking not available online</h2>

                <div class="alert alert-warning">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a href="/airport-booking.php" class="btn btn-brand rounded-pill px-4">
                        Try Another Date or Time
                    </a>

                    <a href="/quote.php" class="btn btn-outline-dark rounded-pill px-4">
                        Request a Quote
                    </a>
                </div>
            </div>

        <?php else: ?>

            <div class="booking-form-card mx-auto">
                <h2 class="section-title mb-3">Vehicle available</h2>

                <div class="alert alert-success">
                    Good news. We have availability for this airport transfer.
                </div>

                <div class="custom-tour-box mb-4">
                    <h3>Journey Summary</h3>

                    <p><strong>Journey type:</strong> <?= htmlspecialchars(ucfirst($journeyType)) ?></p>
                    <p><strong>Airport:</strong> <?= htmlspecialchars($airportName) ?></p>
                    <p><strong>Zone:</strong> <?= htmlspecialchars($zoneName) ?></p>
                    <p><strong>Date and time:</strong> <?= htmlspecialchars($journeyStart->format("d/m/Y H:i")) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($houseNumber . ", " . $street . ", " . $townCity . ", " . $postcode) ?></p>
                    <p><strong>Passengers:</strong> <?= htmlspecialchars($passengers) ?></p>
                    <p><strong>Luggage:</strong> <?= htmlspecialchars($largeCases) ?> large suitcase(s), <?= htmlspecialchars($smallBags) ?> small bag(s)</p>

                    <?php if ($flightNumber !== ""): ?>
                        <p><strong>Flight number:</strong> <?= htmlspecialchars($flightNumber) ?></p>
                    <?php endif; ?>
                </div>

                <div class="custom-tour-box mb-4">
                    <h3>Booking Summary</h3>

                    <table class="table">
                        <tr>
                            <td>Journey Price</td>
                            <td class="text-end">£<?= number_format($basePrice, 2) ?></td>
                        </tr>

                        <tr>
                            <td>Airport Charge</td>
                            <td class="text-end">£<?= number_format($airportCharge, 2) ?></td>
                        </tr>

                        <tr class="fw-bold">
                            <td>Total Price</td>
                            <td class="text-end">£<?= number_format($totalPrice, 2) ?></td>
                        </tr>

                        <tr>
                            <td>Deposit Due Today (20%)</td>
                            <td class="text-end">£<?= number_format($depositAmount, 2) ?></td>
                        </tr>

                        <tr>
                            <td>Balance Payable to Driver</td>
                            <td class="text-end">£<?= number_format($remainingBalance, 2) ?></td>
                        </tr>
                    </table>
                </div>

                <form action="/airport-booking-confirm.php" method="post">

                    <input type="hidden" name="journey_type" value="<?= htmlspecialchars($journeyType) ?>">
                    <input type="hidden" name="airport" value="<?= htmlspecialchars($airportCode) ?>">
                    <input type="hidden" name="airport_name" value="<?= htmlspecialchars($airportName) ?>">
                    <input type="hidden" name="zone_name" value="<?= htmlspecialchars($zoneName) ?>">

                    <input type="hidden" name="postcode" value="<?= htmlspecialchars($postcode) ?>">
                    <input type="hidden" name="house_number" value="<?= htmlspecialchars($houseNumber) ?>">
                    <input type="hidden" name="street" value="<?= htmlspecialchars($street) ?>">
                    <input type="hidden" name="town_city" value="<?= htmlspecialchars($townCity) ?>">

                    <input type="hidden" name="travel_date" value="<?= htmlspecialchars($travelDate) ?>">
                    <input type="hidden" name="travel_hour" value="<?= htmlspecialchars($travelHour) ?>">
                    <input type="hidden" name="travel_minute" value="<?= htmlspecialchars($travelMinute) ?>">
                    <input type="hidden" name="flight_number" value="<?= htmlspecialchars($flightNumber) ?>">

                    <input type="hidden" name="passengers" value="<?= htmlspecialchars($passengers) ?>">
                    <input type="hidden" name="large_cases" value="<?= htmlspecialchars($largeCases) ?>">
                    <input type="hidden" name="small_bags" value="<?= htmlspecialchars($smallBags) ?>">

                    <input type="hidden" name="vehicle_id" value="<?= htmlspecialchars($availableVehicle["vehicleID"]) ?>">
                    <input type="hidden" name="journey_start" value="<?= htmlspecialchars($journeyStart->format("Y-m-d H:i:s")) ?>">
                    <input type="hidden" name="journey_end" value="<?= htmlspecialchars($journeyEnd->format("Y-m-d H:i:s")) ?>">

                    <input type="hidden" name="base_price" value="<?= htmlspecialchars($basePrice) ?>">
                    <input type="hidden" name="airport_charge" value="<?= htmlspecialchars($airportCharge) ?>">
                    <input type="hidden" name="total_price" value="<?= htmlspecialchars($totalPrice) ?>">
                    <input type="hidden" name="deposit_amount" value="<?= htmlspecialchars($depositAmount) ?>">
                    <input type="hidden" name="remaining_balance" value="<?= htmlspecialchars($remainingBalance) ?>">

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <button type="submit" class="btn btn-brand btn-lg rounded-pill px-5">
                            Continue to Secure Booking
                        </button>

                        <a href="/airport-booking.php" class="btn btn-outline-dark btn-lg rounded-pill px-5">
                            Change Details
                        </a>
                    </div>

                </form>
            </div>

        <?php endif; ?>

    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>