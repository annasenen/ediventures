<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "Booking Created | EdiVentures";
$metaDescription = "Your airport transfer booking has been created.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/booking-created.php";

$bookingID = $_GET["bookingID"] ?? "";

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Booking Created</p>
                <h1 class="page-title">Your Booking Has Been Created</h1>
                <p class="page-hero-text">
                    Your airport transfer booking has been saved and is waiting for deposit payment.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <div class="alert alert-success">
                Your booking has been created successfully.
            </div>

            <?php if ($bookingID !== ""): ?>
                <p>
                    <strong>Booking reference:</strong>
                    #<?= htmlspecialchars($bookingID) ?>
                </p>
            <?php endif; ?>

            <p>
                The next step will be secure online deposit payment. For now, this booking is saved with the status:
                <strong>pending payment</strong>.
            </p>

            <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                <a href="/customer/dashboard.php" class="btn btn-brand rounded-pill px-4">
                    Go to Dashboard
                </a>

                <a href="/airport-transfers.php" class="btn btn-outline-dark rounded-pill px-4">
                    Book Another Airport Transfer
                </a>
            </div>

        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>