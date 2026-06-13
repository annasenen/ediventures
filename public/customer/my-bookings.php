<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "My Bookings | EdiVentures";
$metaDescription = "View your EdiVentures bookings.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/my-bookings.php";

require_once __DIR__ . '/../../config/database.php';

$customerID = $_SESSION["customerID"];

$sql = "SELECT *
        FROM bookings
        WHERE customerID = ?
        ORDER BY journeyStart DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$customerID]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Customer Account</p>
                <h1 class="page-title">My Bookings</h1>
                <p class="page-hero-text">
                    View your airport transfers, tours and future journeys.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <?php if (empty($bookings)): ?>

            <div class="booking-form-card mx-auto">
                <div class="alert alert-info mb-0">
                    You do not have any bookings yet.
                </div>

                <div class="mt-4">
                    <a href="/airport-transfers.php" class="btn btn-brand rounded-pill px-4">
                        Book Airport Transfer
                    </a>
                </div>
            </div>

        <?php else: ?>

            <div class="row g-4">
                <?php foreach ($bookings as $booking): ?>
                    <div class="col-lg-6">
                        <div class="custom-tour-box h-100">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <h3 class="mb-0">
                                    Booking #<?= htmlspecialchars($booking["bookingID"]) ?>
                                </h3>

                                <span class="badge bg-warning text-dark">
                                    <?= htmlspecialchars(str_replace("_", " ", ucfirst($booking["status"]))) ?>
                                </span>
                            </div>

                            <p>
                                <strong>Type:</strong>
                                <?= htmlspecialchars(str_replace("_", " ", ucfirst($booking["bookingType"]))) ?>
                            </p>

                            <p>
                                <strong>Journey:</strong>
                                <?= htmlspecialchars(ucfirst($booking["journeyType"])) ?>
                                -
                                <?= htmlspecialchars($booking["airportName"]) ?>
                            </p>

                            <p>
                                <strong>Date and time:</strong>
                                <?= htmlspecialchars(date("d/m/Y H:i", strtotime($booking["journeyStart"]))) ?>
                            </p>

                            <p>
                                <strong>Pickup:</strong><br>
                                <?= htmlspecialchars($booking["pickupAddress"]) ?>
                            </p>

                            <p>
                                <strong>Drop-off:</strong><br>
                                <?= htmlspecialchars($booking["dropoffAddress"]) ?>
                            </p>

                            <p>
                                <strong>Passengers:</strong>
                                <?= htmlspecialchars($booking["passengers"]) ?>
                            </p>

                            <p>
                                <strong>Total price:</strong>
                                £<?= number_format((float)$booking["totalPrice"], 2) ?>
                            </p>

                            <p>
                                <strong>Deposit:</strong>
                                £<?= number_format((float)$booking["depositAmount"], 2) ?>
                            </p>

                            <p class="mb-0">
                                <strong>Balance to driver:</strong>
                                £<?= number_format((float)$booking["totalPrice"] - (float)$booking["depositAmount"], 2) ?>
                            </p>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <div class="mt-4">
            <a href="/customer/dashboard.php" class="btn btn-outline-dark rounded-pill px-4">
                Back to Dashboard
            </a>
        </div>

    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>