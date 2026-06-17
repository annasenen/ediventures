<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$pageTitle = "Manage Vehicles | EdiVentures Admin";
$metaDescription = "Manage EdiVentures vehicles.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/vehicles.php";

$errors = [];
$success = "";

if (isset($_POST["archiveVehicleID"])) {
    $archiveVehicleID = (int)$_POST["archiveVehicleID"];

    $activeCountSql = "SELECT COUNT(*)
                       FROM vehicles
                       WHERE isActive = 1
                       AND isArchived = 0
                       AND vehicleID != ?";

    $activeCountStmt = $pdo->prepare($activeCountSql);
    $activeCountStmt->execute([$archiveVehicleID]);
    $remainingActiveVehicles = (int)$activeCountStmt->fetchColumn();

    if ($remainingActiveVehicles < 1) {
        $errors[] = "You cannot archive this vehicle because at least one active vehicle must remain in the fleet.";
    } else {
        $archiveSql = "UPDATE vehicles
                       SET isArchived = 1,
                           isActive = 0
                       WHERE vehicleID = ?";

        $archiveStmt = $pdo->prepare($archiveSql);
        $archiveStmt->execute([$archiveVehicleID]);

        $success = "Vehicle archived successfully.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehicleName = trim($_POST["vehicleName"] ?? "");
    $passengerCapacity = (int)($_POST["passengerCapacity"] ?? 6);
    $minimumNoticeHours = (int)($_POST["minimumNoticeHours"] ?? 1);
    $vehiclePriority = (int)($_POST["vehiclePriority"] ?? 1);
    $isActive = isset($_POST["isActive"]) ? 1 : 0;
    $isPrimaryVehicle = isset($_POST["isPrimaryVehicle"]) ? 1 : 0;

    if ($vehicleName === "") {
        $errors[] = "Vehicle name is required.";
    }

    if ($passengerCapacity < 1) {
        $errors[] = "Passenger capacity must be at least 1.";
    }

    if ($minimumNoticeHours < 0) {
        $errors[] = "Minimum notice cannot be negative.";
    }

    if ($vehiclePriority < 1) {
        $errors[] = "Priority must be at least 1.";
    }

    if (empty($errors)) {
        if ($isPrimaryVehicle === 1) {
            $pdo->exec("UPDATE vehicles SET isPrimaryVehicle = 0");
        }
        if (!empty($_POST["vehicleID"])) {
            $vehicleID = (int)$_POST["vehicleID"];

            $sql = "UPDATE vehicles
                    SET vehicleName = ?,
                        passengerCapacity = ?,
                        isActive = ?,
                        isPrimaryVehicle = ?,
                        minimumNoticeHours = ?,
                        vehiclePriority = ?
                    WHERE vehicleID = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $vehicleName,
                $passengerCapacity,
                $isActive,
                $isPrimaryVehicle,
                $minimumNoticeHours,
                $vehiclePriority,
                $vehicleID
            ]);

            $success = "Vehicle updated successfully.";
        } else {
            $sql = "INSERT INTO vehicles
                    (vehicleName, passengerCapacity, isActive, isPrimaryVehicle, minimumNoticeHours, vehiclePriority)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $vehicleName,
                $passengerCapacity,
                $isActive,
                $isPrimaryVehicle,
                $minimumNoticeHours,
                $vehiclePriority
            ]);

            $success = "Vehicle added successfully.";
        }
    }
}

$editVehicle = null;

if (isset($_GET["edit"])) {
    $editID = (int)$_GET["edit"];

    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE vehicleID = ?");
    $stmt->execute([$editID]);
    $editVehicle = $stmt->fetch(PDO::FETCH_ASSOC);
}

$vehicles = $pdo->query("
    SELECT *
    FROM vehicles
    WHERE isArchived = 0
    ORDER BY vehiclePriority ASC, vehicleID ASC
")->fetchAll(PDO::FETCH_ASSOC);

$archivedVehicles = $pdo->query("
    SELECT *
    FROM vehicles
    WHERE isArchived = 1
    ORDER BY vehicleID ASC
")->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 class="page-title">Manage Vehicles</h1>
                <p class="page-hero-text">
                    Add vehicles, activate or deactivate cars, set minimum notice and control booking priority.
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
                    <h2 class="section-title mb-0">Fleet Control</h2>
                    <p class="mb-0">
                        Vehicles with lower priority numbers are checked first during online booking.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="/admin/dashboard.php" class="btn btn-outline-dark rounded-pill px-4">
                        Back to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>

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
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <div class="col-lg-5">
                <div class="booking-form-card">

                    <h2 class="section-title mb-3">
                        <?= $editVehicle ? "Edit Vehicle" : "Add Vehicle" ?>
                    </h2>

                    <form action="/admin/vehicles.php<?= $editVehicle ? '?edit=' . htmlspecialchars($editVehicle['vehicleID']) : '' ?>" method="post">

                        <?php if ($editVehicle): ?>
                            <input type="hidden" name="vehicleID" value="<?= htmlspecialchars($editVehicle["vehicleID"]) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="vehicleName" class="form-label">Vehicle Name *</label>
                            <input type="text" id="vehicleName" name="vehicleName" class="form-control"
                                   value="<?= htmlspecialchars($editVehicle["vehicleName"] ?? "") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="passengerCapacity" class="form-label">Passenger Capacity *</label>
                            <input type="number" id="passengerCapacity" name="passengerCapacity" class="form-control"
                                   value="<?= htmlspecialchars($editVehicle["passengerCapacity"] ?? 6) ?>" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="minimumNoticeHours" class="form-label">Minimum Notice Hours *</label>
                            <input type="number" id="minimumNoticeHours" name="minimumNoticeHours" class="form-control"
                                   value="<?= htmlspecialchars($editVehicle["minimumNoticeHours"] ?? 1) ?>" min="0" required>
                            <div class="form-text">
                                Example: Car 1 = 1 hour, Car 2 = 12 hours.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vehiclePriority" class="form-label">Vehicle Priority *</label>
                            <input type="number" id="vehiclePriority" name="vehiclePriority" class="form-control"
                                   value="<?= htmlspecialchars($editVehicle["vehiclePriority"] ?? 1) ?>" min="1" required>
                            <div class="form-text">
                                Lower number is checked first. Example: Car 1 = 1, Car 2 = 2.
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="isActive" name="isActive"
                                <?= (!$editVehicle || (int)$editVehicle["isActive"] === 1) ? "checked" : "" ?>>
                            <label class="form-check-label" for="isActive">
                                Vehicle active for online bookings
                            </label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="isPrimaryVehicle" name="isPrimaryVehicle"
                                <?= ($editVehicle && (int)$editVehicle["isPrimaryVehicle"] === 1) ? "checked" : "" ?>>
                            <label class="form-check-label" for="isPrimaryVehicle">
                                Primary vehicle
                            </label>
                            <div class="form-text">
                                The primary vehicle is checked first during online bookings. Only one vehicle can be primary at a time.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand rounded-pill px-4">
                            <?= $editVehicle ? "Update Vehicle" : "Add Vehicle" ?>
                        </button>

                        <?php if ($editVehicle): ?>
                            <a href="/admin/vehicles.php" class="btn btn-outline-dark rounded-pill px-4 ms-2">
                                Cancel Edit
                            </a>
                        <?php endif; ?>

                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="custom-tour-box">
                    <h2 class="section-title mb-3">Current Vehicles</h2>

                    <?php if (empty($vehicles)): ?>

                        <div class="alert alert-info">
                            No vehicles have been added yet.
                        </div>

                    <?php else: ?>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Capacity</th>
                                        <th>Notice</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($vehicle["vehicleName"]) ?></strong>

                                                <?php if ((int)$vehicle["isPrimaryVehicle"] === 1): ?>
                                                    <span class="badge bg-primary ms-2">Primary</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?= htmlspecialchars($vehicle["passengerCapacity"]) ?></td>

                                            <td><?= htmlspecialchars($vehicle["minimumNoticeHours"]) ?> hr</td>

                                            <td><?= htmlspecialchars($vehicle["vehiclePriority"]) ?></td>

                                            <td>
                                                <?php if ((int)$vehicle["isActive"] === 1): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-end">
                                                <a href="/admin/vehicles.php?edit=<?= htmlspecialchars($vehicle["vehicleID"]) ?>"
                                                    class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                        Edit
                                                    </a>

                                                    <form action="/admin/vehicles.php" method="post" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to archive this vehicle?');">
                                                        <input type="hidden" name="archiveVehicleID" value="<?= htmlspecialchars($vehicle["vehicleID"]) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                            Archive
                                                        </button>
                                                    </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>

                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>