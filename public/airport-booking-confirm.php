<?php
session_start();

$pageTitle = "Confirm Airport Booking | EdiVentures";
$metaDescription = "Confirm your airport transfer booking with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-booking-confirm.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pendingBooking = [
        "journey_type" => $_POST["journey_type"] ?? "",
        "airport" => $_POST["airport"] ?? "",
        "airport_name" => $_POST["airport_name"] ?? "",
        "zone_name" => $_POST["zone_name"] ?? "",

        "postcode" => $_POST["postcode"] ?? "",
        "house_number" => $_POST["house_number"] ?? "",
        "street" => $_POST["street"] ?? "",
        "town_city" => $_POST["town_city"] ?? "",

        "travel_date" => $_POST["travel_date"] ?? "",
        "travel_hour" => $_POST["travel_hour"] ?? "",
        "travel_minute" => $_POST["travel_minute"] ?? "",
        "flight_number" => $_POST["flight_number"] ?? "",

        "passengers" => $_POST["passengers"] ?? "",
        "large_cases" => $_POST["large_cases"] ?? "",
        "small_bags" => $_POST["small_bags"] ?? "",

        "vehicle_id" => $_POST["vehicle_id"] ?? "",
        "journey_start" => $_POST["journey_start"] ?? "",
        "journey_end" => $_POST["journey_end"] ?? "",

        "base_price" => $_POST["base_price"] ?? "0",
        "airport_charge" => $_POST["airport_charge"] ?? "0",
        "total_price" => $_POST["total_price"] ?? "0",
        "deposit_amount" => $_POST["deposit_amount"] ?? "0",
        "remaining_balance" => $_POST["remaining_balance"] ?? "0"
    ];

    $_SESSION["pending_airport_booking"] = $pendingBooking;

} elseif (isset($_SESSION["pending_airport_booking"])) {
    $pendingBooking = $_SESSION["pending_airport_booking"];

} else {
    header("Location: /airport-booking.php");
    exit;
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
                <p class="eyebrow mb-3">Secure Booking</p>
                <h1 class="page-title">Confirm Your Airport Transfer</h1>
                <p class="page-hero-text">
                    Review your airport transfer before continuing to secure your booking.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <div class="booking-form-card mx-auto">

            <h2 class="section-title mb-3">Booking Confirmation</h2>

            <div class="custom-tour-box mb-4">
                <h3>Your Journey</h3>

                <p>
                    <strong>Journey type:</strong>
                    <?= htmlspecialchars(ucfirst($pendingBooking["journey_type"])) ?>
                </p>

                <p>
                    <strong>Airport:</strong>
                    <?= htmlspecialchars($pendingBooking["airport_name"]) ?>
                </p>

                <p>
                    <strong>Date and time:</strong>
                    <?= htmlspecialchars(date("d/m/Y H:i", strtotime($pendingBooking["journey_start"]))) ?>
                </p>

                <p>
                    <strong>Address:</strong>
                    <?= htmlspecialchars(
                        $pendingBooking["house_number"] . ", " .
                        $pendingBooking["street"] . ", " .
                        $pendingBooking["town_city"] . ", " .
                        $pendingBooking["postcode"]
                    ) ?>
                </p>

                <p>
                    <strong>Passengers:</strong>
                    <?= htmlspecialchars($pendingBooking["passengers"]) ?>
                </p>

                <p>
                    <strong>Luggage:</strong>
                    <?= htmlspecialchars($pendingBooking["large_cases"]) ?>
                    large suitcase(s),
                    <?= htmlspecialchars($pendingBooking["small_bags"]) ?>
                    small bag(s)
                </p>

                <?php if (!empty($pendingBooking["flight_number"])): ?>
                    <p>
                        <strong>Flight number:</strong>
                        <?= htmlspecialchars($pendingBooking["flight_number"]) ?>
                    </p>
                <?php endif; ?>

                <a href="/airport-booking.php" class="btn btn-outline-dark rounded-pill px-4 mt-2">
                    Change Journey Details
                </a>
            </div>

            <div class="custom-tour-box mb-4">
                <h3>Booking Summary</h3>

                <table class="table">
                    <tr>
                        <td>
                            Journey Price
                            <div class="small text-muted">
                                Private hire journey for your selected airport route.
                            </div>
                        </td>
                        <td class="text-end">
                            £<?= number_format((float)$pendingBooking["base_price"], 2) ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Airport Charge
                            <div class="small text-muted">
                                Airport pickup/drop-off charge added to the booking.
                            </div>
                        </td>
                        <td class="text-end">
                            £<?= number_format((float)$pendingBooking["airport_charge"], 2) ?>
                        </td>
                    </tr>

                    <tr class="fw-bold">
                        <td>Total Price</td>
                        <td class="text-end">
                            £<?= number_format((float)$pendingBooking["total_price"], 2) ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Deposit Due Today
                            <div class="small text-muted">
                                20% deposit required to secure your booking.
                            </div>
                        </td>
                        <td class="text-end">
                            £<?= number_format((float)$pendingBooking["deposit_amount"], 2) ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Balance Payable to Driver
                            <div class="small text-muted">
                                Remaining balance paid to the driver on the day.
                            </div>
                        </td>
                        <td class="text-end">
                            £<?= number_format((float)$pendingBooking["remaining_balance"], 2) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if (!isset($_SESSION["customerID"])): ?>

                <div class="alert alert-info">
                    To secure this booking, please log in or create an account.
                    Your journey details have been saved for this session.
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="/login.php?redirect=airport-booking-confirm" class="btn btn-brand btn-lg rounded-pill px-5">
                        Log In
                    </a>

                    <a href="/register.php?redirect=airport-booking-confirm" class="btn btn-outline-dark btn-lg rounded-pill px-5">
                        Create Account
                    </a>
                </div>

            <?php else: ?>

                <div class="alert alert-success">
                    You are logged in as
                    <strong><?= htmlspecialchars($_SESSION["customerName"]) ?></strong>.
                    You can now continue to secure your booking.
                </div>

                <form action="/airport-booking-save.php" method="post">
                    <button type="submit" class="btn btn-brand btn-lg rounded-pill px-5">
                        Proceed to Deposit Payment
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>