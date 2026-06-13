<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "Build Your Own Private Scotland Tour | EdiVentures";
$metaDescription = "Create your own private Scotland tour with EdiVentures. Tell us where you would like to go, your pickup location, date, time and group size, and we will prepare a custom quote.";
$canonicalUrl = "https://www.ediventures.co.uk/build-your-tour.php";

require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerID = $_SESSION["customerID"];

    $preferredDate = $_POST["tour_date"] ?: null;
    $pickupHour = $_POST["pickup_hour"] ?: null;
    $pickupMinute = $_POST["pickup_minute"] ?: null;
    $passengers = $_POST["passengers"] ?: null;
    $tourLength = $_POST["tour_length"] ?: null;
    $pickupLocation = trim($_POST["pickup_location"] ?? "");
    $places = trim($_POST["places"] ?? "");
    $interests = trim($_POST["interests"] ?? "");
    $extraDetails = trim($_POST["extra_details"] ?? "");

    if ($places === "" && $interests === "" && $extraDetails === "") {
        $errors[] = "Please tell us something about the tour you would like.";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO custom_tour_requests
                (customerID, preferredDate, pickupHour, pickupMinute, passengers, tourLength, pickupLocation, places, interests, extraDetails)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customerID,
            $preferredDate,
            $pickupHour,
            $pickupMinute,
            $passengers,
            $tourLength,
            $pickupLocation,
            $places,
            $interests,
            $extraDetails
        ]);

        $success = "Your custom tour request has been sent. We will review it and prepare a quote.";
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero text-white"
    style="background: linear-gradient(rgba(0,0,0,0.68), rgba(0,0,0,0.68)), url('/assets/img/view-loch-lomond-one-day-tour.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Custom Private Tours</p>
                <h1 class="page-title">Build Your Own Scotland Tour</h1>
                <p class="page-hero-text">
                    Tell us where you would like to go, how much time you have, and what kind of places you enjoy.
                    We will prepare a private tour suggestion and quote for your journey.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <h2 class="section-title mb-3">Custom Tour Request</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <div class="mt-3">
                        <a href="/customer/dashboard.php" class="btn btn-brand rounded-pill px-4">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form action="build-your-tour.php" method="post">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="passengers" class="form-label">Number of Passengers</label>
                        <select id="passengers" name="passengers" class="form-select">
                            <option value="">Please select</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> passenger<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tour_date" class="form-label">Preferred Date</label>
                        <input type="date" id="tour_date" name="tour_date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Preferred Pickup Time</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <select name="pickup_hour" class="form-select">
                                    <option value="">Hour</option>
                                    <?php for ($hour = 6; $hour <= 23; $hour++): ?>
                                        <option value="<?= sprintf('%02d', $hour) ?>">
                                            <?= sprintf('%02d', $hour) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <select name="pickup_minute" class="form-select">
                                    <option value="">Minute</option>
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tour_length" class="form-label">How much time do you have?</label>
                        <select id="tour_length" name="tour_length" class="form-select">
                            <option value="">Please select</option>
                            <option value="half_day">Half day</option>
                            <option value="full_day">Full day</option>
                            <option value="custom_hours">Specific number of hours</option>
                            <option value="not_sure">Not sure yet</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="pickup_location" class="form-label">Pickup Location / Hotel</label>
                        <input type="text" id="pickup_location" name="pickup_location" class="form-control" placeholder="Hotel, address or area">
                    </div>

                    <div class="col-12">
                        <label for="places" class="form-label">Places you would like to visit</label>
                        <textarea id="places" name="places" class="form-control" rows="4" placeholder="Example: Stirling Castle, The Kelpies, Loch Lomond, St Andrews..."></textarea>
                    </div>

                    <div class="col-12">
                        <label for="interests" class="form-label">What are you interested in?</label>
                        <textarea id="interests" name="interests" class="form-control" rows="3" placeholder="History, castles, scenery, whisky, golf, family activities, photo stops..."></textarea>
                    </div>

                    <div class="col-12">
                        <label for="extra_details" class="form-label">Any extra details?</label>
                        <textarea id="extra_details" name="extra_details" class="form-control" rows="3" placeholder="Mobility needs, luggage, children, special occasion, lunch stop preferences..."></textarea>
                    </div>

                    <div class="col-12">
                        <div class="quick-contact-box">
                            <strong>Please note:</strong>
                            Custom tours are quoted individually. Once the route and availability are confirmed,
                            the booking can be secured with a 20% deposit.
                        </div>
                    </div>

                    <div class="col-12 d-grid d-md-flex gap-3">
                        <button type="submit" class="btn btn-brand btn-lg rounded-pill px-4">
                            Send Custom Tour Request
                        </button>

                        <a href="/customer/dashboard.php" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                            Back to Dashboard
                        </a>
                    </div>

                </div>

            </form>
        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>