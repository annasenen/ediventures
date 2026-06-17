<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$pageTitle = "Manage Bookings | EdiVentures Admin";
$metaDescription = "Manage EdiVentures bookings.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/bookings.php";

$sql = "SELECT 
            b.*,
            c.fullName,
            c.email,
            c.phone,
            v.vehicleName
        FROM bookings b
        JOIN customers c ON b.customerID = c.customerID
        LEFT JOIN vehicles v ON b.vehicleID = v.vehicleID
        ORDER BY b.journeyStart DESC";

$stmt = $pdo->query($sql);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="page-title">Manage Bookings</h1>
                <p class="page-hero-text">
                    View airport transfers, customer details, assigned vehicles and payment status.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <div class="custom-tour-box mb-4">
            <div class="row align-items-center gy-3">
                <div class="col-lg-8">
                    <h2 class="section-title mb-0">All Bookings</h2>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="/admin/dashboard.php" class="btn btn-outline-dark rounded-pill px-4">
                        Back to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($bookings)): ?>

            <div class="alert alert-info">
                No bookings have been created yet.
            </div>

        <?php else: ?>

            <div class="table-responsive custom-tour-box">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th>Journey</th>
                            <th>Date / Time</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Deposit</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong>#<?= htmlspecialchars($booking["bookingID"]) ?></strong><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(str_replace("_", " ", $booking["bookingType"])) ?>
                                    </small>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($booking["fullName"]) ?></strong><br>
                                    <small><?= htmlspecialchars($booking["email"]) ?></small><br>
                                    <small><?= htmlspecialchars($booking["phone"]) ?></small>
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars(ucfirst($booking["journeyType"])) ?></strong><br>
                                    <small><?= htmlspecialchars($booking["airportName"] ?? "") ?></small><br>
                                    <small>
                                        From: <?= htmlspecialchars($booking["pickupAddress"] ?? "") ?>
                                    </small><br>
                                    <small>
                                        To: <?= htmlspecialchars($booking["dropoffAddress"] ?? "") ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars(date("d/m/Y H:i", strtotime($booking["journeyStart"]))) ?><br>
                                    <small class="text-muted">
                                        Blocked until <?= htmlspecialchars(date("H:i", strtotime($booking["journeyEnd"]))) ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars($booking["vehicleName"] ?? "Not assigned") ?>
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars(str_replace("_", " ", ucfirst($booking["status"]))) ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    £<?= number_format((float)$booking["totalPrice"], 2) ?>
                                </td>

                                <td class="text-end">
                                    £<?= number_format((float)$booking["depositAmount"], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>