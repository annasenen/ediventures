<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$pageTitle = "Admin Dashboard | EdiVentures";
$metaDescription = "EdiVentures admin dashboard.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/dashboard.php";

$stats = [
    "bookings" => 0,
    "pendingPayments" => 0,
    "customers" => 0,
    "customRequests" => 0
];

$stats["bookings"] = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

$stats["pendingPayments"] = (int)$pdo->query("
    SELECT COUNT(*)
    FROM bookings
    WHERE status = 'pending_payment'
")->fetchColumn();

$stats["customers"] = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$stats["customRequests"] = (int)$pdo->query("
    SELECT COUNT(*)
    FROM custom_tour_requests
    WHERE status = 'pending'
")->fetchColumn();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/background-image.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Admin Area</p>
                <h1 class="page-title">EdiVentures Admin Dashboard</h1>
                <p class="page-hero-text">
                    Manage bookings, vehicles, availability, prices, customers and quote requests.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <div class="row g-4 mb-5">

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-calendar-check"></i></span>
                    <h3>Total Bookings</h3>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars($stats["bookings"]) ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-credit-card"></i></span>
                    <h3>Pending Payments</h3>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars($stats["pendingPayments"]) ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-users"></i></span>
                    <h3>Customers</h3>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars($stats["customers"]) ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="step-box">
                    <span><i class="fa-solid fa-map-location-dot"></i></span>
                    <h3>Custom Requests</h3>
                    <p class="display-6 fw-bold mb-0"><?= htmlspecialchars($stats["customRequests"]) ?></p>
                </div>
            </div>

        </div>

        <div class="custom-tour-box">
            <h2 class="section-title mb-4">Admin Tools</h2>

            <div class="row g-4">

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-list-check"></i>
                        <h3>Manage Bookings</h3>
                        <p>View airport transfers, tour bookings and payment status.</p>
                        <a href="/admin/bookings.php" class="text-link">View bookings</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-car"></i>
                        <h3>Manage Vehicles</h3>
                        <p>Enable or disable cars and change minimum notice periods.</p>
                        <a href="/admin/vehicles.php" class="text-link">Manage vehicles</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-ban"></i>
                        <h3>Manual Blocks</h3>
                        <p>Block full days, time slots, holidays, repairs or private jobs.</p>
                        <a href="#" class="text-link">Coming soon</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-plane"></i>
                        <h3>Airport Settings</h3>
                        <p>
                            Control journey rules, vehicle prices and airport charges.
                        </p>
                        <a
                            href="/admin/airport-settings.php"
                            class="text-link"
                        >
                            Manage airport settings
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-map"></i>
                        <h3>Tour Management</h3>
                        <p>Manage fixed tours, custom tour requests and quote workflows.</p>
                        <a href="#" class="text-link">Coming soon</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box">
                        <i class="fa-solid fa-user-gear"></i>
                        <h3>Customers</h3>
                        <p>View customer accounts and booking history.</p>
                        <a href="#" class="text-link">Coming soon</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>