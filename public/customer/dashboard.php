<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "Customer Dashboard | EdiVentures";
$metaDescription = "Manage your EdiVentures bookings, custom tour requests, quotes and payments.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/dashboard.php";

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/background-image.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Customer Dashboard</p>
                <h1 class="page-title">Welcome, <?= htmlspecialchars($_SESSION["customerName"]) ?></h1>
                <p class="page-hero-text">
                    Manage your bookings, custom tour requests, quotes and deposit payments in one place.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-calendar-check"></i></span>
                    <h3>My Bookings</h3>
                    <p>View confirmed tours, airport transfers and future journeys.</p>
                    <a href="/customer/my-bookings.php" class="text-link">View bookings</a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-map-location-dot"></i></span>
                    <h3>Custom Tours</h3>
                    <p>Track your build-your-own tour requests and progress.</p>
                    <a href="/customer/custom-tour-requests.php" class="text-link">View requests</a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-file-invoice"></i></span>
                    <h3>My Quotes</h3>
                    <p>Review custom quotes and accept them when ready.</p>
                    <a href="#" class="text-link">Coming soon</a>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-credit-card"></i></span>
                    <h3>Payments</h3>
                    <p>View deposit payments and remaining balances.</p>
                    <a href="#" class="text-link">Coming soon</a>
                </div>
            </div>

        </div>

        <div class="custom-tour-box mt-5">
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <h2 class="section-title">Need a custom tour?</h2>
                    <p class="mb-0">
                        Tell us where you would like to go and we will prepare a custom route and quote.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="/build-your-tour.php" class="btn btn-brand rounded-pill px-4">
                        Build Your Own Tour
                    </a>
                    <a href="/logout.php" class="btn btn-outline-dark rounded-pill px-4 ms-lg-2 mt-2 mt-lg-0">
                        Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>