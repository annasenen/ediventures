<?php

session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/PaymentService.php';

$bookingID = (int)($_GET["bookingID"] ?? 0);
$customerID = (int)$_SESSION["customerID"];

if ($bookingID < 1) {
    header("Location: /customer/dashboard.php");
    exit;
}

$paymentService = new PaymentService($pdo);

$booking = $paymentService->getPendingBookingForCustomer(
    $bookingID,
    $customerID
);

if (!$booking) {
    header("Location: /customer/dashboard.php");
    exit;
}

$pageTitle = "Complete Your Booking | EdiVentures";
$metaDescription = "Complete your EdiVentures airport transfer booking.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/booking-created.php";

$holdIsActive =
    $booking["status"] === "pending_payment" &&
    !empty($booking["holdExpiresAt"]) &&
    strtotime($booking["holdExpiresAt"]) > time();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;">

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">

                <p class="eyebrow mb-3">
                    Complete Booking
                </p>

                <h1 class="page-title">
                    Complete Your Deposit Payment
                </h1>

                <p class="page-hero-text">
                    Your journey is temporarily reserved while you complete the deposit payment.
                </p>

            </div>
        </div>
    </div>

</section>


<section class="py-5 bg-light">

    <div class="container">

        <div class="booking-form-card mx-auto">

            <?php if ($holdIsActive): ?>

                <div class="alert alert-info">
                    <strong>Your vehicle is currently reserved.</strong>
                    Complete the deposit payment before the reservation expires.
                </div>

                <p>
                    <strong>Booking reference:</strong>
                    #<?= htmlspecialchars((string)$booking["bookingID"]) ?>
                </p>

                <p>
                    <strong>Total journey price:</strong>
                    £<?= number_format((float)$booking["totalPrice"], 2) ?>
                </p>

                <p>
                    <strong>Deposit due:</strong>
                    £<?= number_format((float)$booking["depositAmount"], 2) ?>
                </p>

                <p>
                    <strong>Reservation expires:</strong>
                    <?= htmlspecialchars($booking["holdExpiresAt"]) ?> UTC
                </p>

                <div class="alert alert-secondary mt-4">
                    Secure online payment will be connected later.
                    For development, this page represents the payment stage.
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">

                    <form
                        action="/customer/payment-success.php"
                        method="post"
                    >

                        <input
                            type="hidden"
                            name="bookingID"
                            value="<?= htmlspecialchars((string)$booking["bookingID"]) ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-brand rounded-pill px-4"
                        >
                            Simulate Successful Payment
                        </button>

                    </form>

                    <form
                        action="/customer/booking-cancel.php"
                        method="post"
                        onsubmit="return confirm('Are you sure you want to cancel this booking?');"
                    >

                        <input
                            type="hidden"
                            name="bookingID"
                            value="<?= htmlspecialchars((string)$booking["bookingID"]) ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-outline-danger rounded-pill px-4"
                        >
                            Cancel Booking
                        </button>

                    </form>

                </div>

            <?php else: ?>

                <div class="alert alert-warning">
                    This reservation is no longer active.
                    Please check availability again before booking.
                </div>

                <a
                    href="/airport-transfers.php"
                    class="btn btn-brand rounded-pill px-4"
                >
                    Check Availability Again
                </a>

            <?php endif; ?>

        </div>

    </div>

</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>