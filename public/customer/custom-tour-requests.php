<?php
session_start();

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "My Custom Tour Requests | EdiVentures";
$metaDescription = "View your custom tour requests with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/customer/custom-tour-requests.php";

require_once __DIR__ . '/../../config/database.php';

$customerID = $_SESSION["customerID"];

$sql = "SELECT *
        FROM custom_tour_requests
        WHERE customerID = ?
        ORDER BY createdAt DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$customerID]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';
?>

<main>

<section class="page-hero text-white"
    style="background: linear-gradient(rgba(0,0,0,0.70), rgba(0,0,0,0.70)), url('/assets/img/background-image.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Customer Account</p>
                <h1 class="page-title">My Custom Tour Requests</h1>
                <p class="page-hero-text">
                    View the custom tours you have requested and track their progress.
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
                    <h2 class="section-title mb-0">Your Requests</h2>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="/build-your-tour.php" class="btn btn-brand rounded-pill px-4">
                        New Custom Tour Request
                    </a>
                </div>
            </div>
        </div>

        <?php if (empty($requests)): ?>

            <div class="alert alert-info">
                You have not submitted any custom tour requests yet.
            </div>

        <?php else: ?>

            <div class="row g-4">
                <?php foreach ($requests as $request): ?>
                    <div class="col-lg-6">
                        <div class="custom-tour-box h-100">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <h3 class="mb-0">Request #<?= htmlspecialchars($request["requestID"]) ?></h3>

                                <span class="badge bg-warning text-dark">
                                    <?= htmlspecialchars(ucfirst($request["status"])) ?>
                                </span>
                            </div>

                            <p class="small text-muted">
                                Submitted: <?= htmlspecialchars($request["createdAt"]) ?>
                            </p>

                            <?php if (!empty($request["preferredDate"])): ?>
                                <p><strong>Preferred date:</strong> <?= htmlspecialchars($request["preferredDate"]) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["pickupHour"])): ?>
                                <p>
                                    <strong>Preferred time:</strong>
                                    <?= htmlspecialchars($request["pickupHour"]) ?>:<?= htmlspecialchars($request["pickupMinute"] ?? "00") ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($request["passengers"])): ?>
                                <p><strong>Passengers:</strong> <?= htmlspecialchars($request["passengers"]) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["tourLength"])): ?>
                                <p><strong>Tour length:</strong> <?= htmlspecialchars(str_replace("_", " ", $request["tourLength"])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["pickupLocation"])): ?>
                                <p><strong>Pickup:</strong> <?= htmlspecialchars($request["pickupLocation"]) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["places"])): ?>
                                <p><strong>Places:</strong><br><?= nl2br(htmlspecialchars($request["places"])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["interests"])): ?>
                                <p><strong>Interests:</strong><br><?= nl2br(htmlspecialchars($request["interests"])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($request["extraDetails"])): ?>
                                <p><strong>Extra details:</strong><br><?= nl2br(htmlspecialchars($request["extraDetails"])) ?></p>
                            <?php endif; ?>

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