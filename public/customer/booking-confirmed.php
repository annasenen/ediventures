<?php

session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$bookingID = (int)($_GET["bookingID"] ?? 0);
$customerID = (int)$_SESSION["customerID"];

if ($bookingID < 1) {
    header("Location: /customer/dashboard.php");
    exit;
}

$sql = "
    SELECT *
    FROM bookings
    WHERE bookingID = ?
      AND customerID = ?
      AND status = 'confirmed'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $bookingID,
    $customerID
]);

$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: /customer/dashboard.php");
    exit;
}

$pageTitle = "Booking Confirmed | EdiVentures";
$metaDescription = "Your EdiVentures booking has been confirmed.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/booking-confirmed.php";

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
                    Booking Confirmed
                </p>

                <h1 class="page-title">
                    Your Booking Is Confirmed
                </h1>

                <p class="page-hero-text">
                    Your deposit has been received and your journey is confirmed.
                </p>

            </div>
        </div>
    </div>

</section>


<section class="py-5 bg-light">

    <div class="container">

        <div class="booking-form-card mx-auto">

            <div class="alert alert-success">
                Your booking has been confirmed successfully.
            </div>

            <p>
                <strong>Booking reference:</strong>
                #<?= htmlspecialchars((string)$booking["bookingID"]) ?>
            </p>

            <p>
                <strong>Total price:</strong>
                £<?= number_format((float)$booking["totalPrice"], 2) ?>
            </p>

            <p>
                <strong>Deposit paid:</strong>
                £<?= number_format((float)$booking["depositAmount"], 2) ?>
            </p>

            <p>
                <strong>Balance to driver:</strong>
                £<?= number_format(
                    (float)$booking["totalPrice"] -
                    (float)$booking["depositAmount"],
                    2
                ) ?>
            </p>

            <div class="mt-4">

                <a
                    href="/customer/my-bookings.php"
                    class="btn btn-brand rounded-pill px-4"
                >
                    View My Bookings
                </a>

            </div>

        </div>

    </div>

</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>