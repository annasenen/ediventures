<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';

$pageTitle = "Airport Settings | EdiVentures Admin";
$metaDescription = "Manage EdiVentures airport journey rules, pricing and charges.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/airport-settings.php";

$settingsService = new AirportSettingsService($pdo);

$errors = [];
$success = "";


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(
        random_bytes(32)
    );
}

$csrfToken = $_SESSION["csrf_token"];


/*
|--------------------------------------------------------------------------
| PROCESS POST ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken =
        $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals($csrfToken, $submittedToken)
    ) {
        $errors[] =
            "Your session security token is invalid. Please refresh the page and try again.";

    } else {

        $action = $_POST["action"] ?? "";

        try {

            // =====================================================
            // JOURNEY RULE
            // =====================================================

            if ($action === "save_rule") {

                $settingsService->saveJourneyRule(
                    trim($_POST["airportName"] ?? ""),
                    $_POST["journeyType"] ?? "",
                    (int)($_POST["blockMinutes"] ?? 0),
                    isset($_POST["isActive"])
                );

                $success =
                    "Airport journey rule saved successfully.";
            }


            if ($action === "toggle_rule") {

                $settingsService->setJourneyRuleActive(
                    (int)($_POST["ruleID"] ?? 0),
                    (int)($_POST["newState"] ?? 0) === 1
                );

                $success =
                    "Airport journey rule status updated.";
            }


            // =====================================================
            // AIRPORT PRICE
            // =====================================================

            if ($action === "save_price") {

                $settingsService->saveAirportPrice(
                    (int)($_POST["vehicleID"] ?? 0),
                    trim($_POST["airportName"] ?? ""),
                    trim($_POST["zoneName"] ?? ""),
                    $_POST["journeyType"] ?? "",
                    (float)($_POST["basePrice"] ?? 0),
                    isset($_POST["isActive"])
                );

                $success =
                    "Airport price saved successfully.";
            }


            if ($action === "toggle_price") {

                $settingsService->setAirportPriceActive(
                    (int)($_POST["priceID"] ?? 0),
                    (int)($_POST["newState"] ?? 0) === 1
                );

                $success =
                    "Airport price status updated.";
            }


            // =====================================================
            // AIRPORT CHARGE
            // =====================================================

            if ($action === "save_charge") {

                $settingsService->saveAirportCharge(
                    trim($_POST["airportName"] ?? ""),
                    (float)($_POST["pickupCharge"] ?? 0),
                    (float)($_POST["dropoffCharge"] ?? 0),
                    isset($_POST["isActive"])
                );

                $success =
                    "Airport charges saved successfully.";
            }


            if ($action === "toggle_charge") {

                $settingsService->setAirportChargeActive(
                    (int)($_POST["chargeID"] ?? 0),
                    (int)($_POST["newState"] ?? 0) === 1
                );

                $success =
                    "Airport charge status updated.";
            }

        } catch (InvalidArgumentException $e) {

            $errors[] = $e->getMessage();

        } catch (PDOException $e) {

            error_log(
                "Airport settings database error: " .
                $e->getMessage()
            );

            $errors[] =
                "Something went wrong while saving the airport settings.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| LOAD CURRENT DATA
|--------------------------------------------------------------------------
*/

$vehicles =
    $settingsService->getAvailableVehicles();

$journeyRules =
    $settingsService->getJourneyRules();

$airportPrices =
    $settingsService->getAirportPrices();

$airportCharges =
    $settingsService->getAirportCharges();


include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';

?>

<main>

<section
    class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;"
>

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <p class="eyebrow mb-3">
                    Admin Area
                </p>

                <h1 class="page-title">
                    Airport Settings
                </h1>

                <p class="page-hero-text">
                    Manage journey blocking rules, vehicle-specific airport prices and airport charges.
                </p>

            </div>

        </div>

    </div>

</section>


<section class="py-5 bg-light">

<div class="container">


    <div class="mb-4">

        <a
            href="/admin/dashboard.php"
            class="btn btn-outline-dark rounded-pill px-4"
        >
            Back to Admin Dashboard
        </a>

    </div>


    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>



    <!-- ======================================================
         JOURNEY RULES
    ======================================================= -->

    <div class="custom-tour-box mb-5">

        <h2 class="section-title mb-3">
            Airport Journey Rules
        </h2>

        <p class="text-muted">
            Set how long each airport pickup or drop-off blocks the assigned vehicle.
        </p>


        <form method="post" class="row g-3 mb-4">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="save_rule"
            >


            <div class="col-md-4">

                <label class="form-label">
                    Airport Name
                </label>

                <input
                    type="text"
                    name="airportName"
                    class="form-control"
                    required
                >

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Journey Type
                </label>

                <select
                    name="journeyType"
                    class="form-select"
                    required
                >

                    <option value="">
                        Choose...
                    </option>

                    <option value="pickup">
                        Pickup
                    </option>

                    <option value="dropoff">
                        Drop-off
                    </option>

                </select>

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Block Minutes
                </label>

                <input
                    type="number"
                    name="blockMinutes"
                    class="form-control"
                    min="1"
                    required
                >

            </div>


            <div class="col-md-2 d-flex align-items-end">

                <div class="form-check mb-2">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="isActive"
                        checked
                    >

                    <label class="form-check-label">
                        Active
                    </label>

                </div>

            </div>


            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-brand rounded-pill px-4"
                >
                    Save Journey Rule
                </button>

            </div>

        </form>


        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Airport</th>
                        <th>Journey</th>
                        <th>Block Time</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($journeyRules as $rule): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($rule["airportName"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                ucfirst($rule["journeyType"])
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $rule["blockMinutes"]
                            ) ?> min
                        </td>

                        <td>

                            <?php if ((int)$rule["isActive"] === 1): ?>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-end">

                            <form
                                method="post"
                                class="d-inline"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="toggle_rule"
                                >

                                <input
                                    type="hidden"
                                    name="ruleID"
                                    value="<?= htmlspecialchars($rule["airportRuleID"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="newState"
                                    value="<?= (int)$rule["isActive"] === 1 ? 0 : 1 ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-dark rounded-pill px-3"
                                >
                                    <?= (int)$rule["isActive"] === 1
                                        ? "Deactivate"
                                        : "Activate" ?>
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>



    <!-- ======================================================
         AIRPORT PRICES
    ======================================================= -->

    <div class="custom-tour-box mb-5">

        <h2 class="section-title mb-3">
            Vehicle Airport Prices
        </h2>

        <p class="text-muted">
            Set different journey prices for each vehicle, airport, zone and journey type.
        </p>


        <form method="post" class="row g-3 mb-4">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="save_price"
            >


            <div class="col-md-3">

                <label class="form-label">
                    Vehicle
                </label>

                <select
                    name="vehicleID"
                    class="form-select"
                    required
                >

                    <option value="">
                        Choose...
                    </option>

                    <?php foreach ($vehicles as $vehicle): ?>

                        <option
                            value="<?= htmlspecialchars($vehicle["vehicleID"]) ?>"
                        >
                            <?= htmlspecialchars($vehicle["vehicleName"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Airport
                </label>

                <input
                    type="text"
                    name="airportName"
                    class="form-control"
                    required
                >

            </div>


            <div class="col-md-2">

                <label class="form-label">
                    Zone
                </label>

                <input
                    type="text"
                    name="zoneName"
                    class="form-control"
                    placeholder="Zone A"
                    required
                >

            </div>


            <div class="col-md-2">

                <label class="form-label">
                    Journey
                </label>

                <select
                    name="journeyType"
                    class="form-select"
                    required
                >

                    <option value="pickup">
                        Pickup
                    </option>

                    <option value="dropoff">
                        Drop-off
                    </option>

                </select>

            </div>


            <div class="col-md-2">

                <label class="form-label">
                    Price (£)
                </label>

                <input
                    type="number"
                    name="basePrice"
                    class="form-control"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <div class="col-12">

                <div class="form-check mb-3">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="isActive"
                        checked
                    >

                    <label class="form-check-label">
                        Active for online bookings
                    </label>

                </div>


                <button
                    type="submit"
                    class="btn btn-brand rounded-pill px-4"
                >
                    Save Airport Price
                </button>

            </div>

        </form>


        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Vehicle</th>
                        <th>Airport</th>
                        <th>Zone</th>
                        <th>Journey</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($airportPrices as $price): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($price["vehicleName"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($price["airportName"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($price["zoneName"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                ucfirst($price["journeyType"])
                            ) ?>
                        </td>

                        <td>
                            £<?= number_format(
                                (float)$price["basePrice"],
                                2
                            ) ?>
                        </td>

                        <td>

                            <?php if ((int)$price["isActive"] === 1): ?>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-end">

                            <form method="post">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="toggle_price"
                                >

                                <input
                                    type="hidden"
                                    name="priceID"
                                    value="<?= htmlspecialchars($price["airportPriceID"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="newState"
                                    value="<?= (int)$price["isActive"] === 1 ? 0 : 1 ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-dark rounded-pill px-3"
                                >
                                    <?= (int)$price["isActive"] === 1
                                        ? "Deactivate"
                                        : "Activate" ?>
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>



    <!-- ======================================================
         AIRPORT CHARGES
    ======================================================= -->

    <div class="custom-tour-box">

        <h2 class="section-title mb-3">
            Airport Charges
        </h2>

        <p class="text-muted">
            Manage pickup and drop-off access charges applied by each airport.
        </p>


        <form method="post" class="row g-3 mb-4">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="save_charge"
            >


            <div class="col-md-4">

                <label class="form-label">
                    Airport
                </label>

                <input
                    type="text"
                    name="airportName"
                    class="form-control"
                    required
                >

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Pickup Charge (£)
                </label>

                <input
                    type="number"
                    name="pickupCharge"
                    class="form-control"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Drop-off Charge (£)
                </label>

                <input
                    type="number"
                    name="dropoffCharge"
                    class="form-control"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <div class="col-md-2 d-flex align-items-end">

                <div class="form-check mb-2">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="isActive"
                        checked
                    >

                    <label class="form-check-label">
                        Active
                    </label>

                </div>

            </div>


            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-brand rounded-pill px-4"
                >
                    Save Airport Charges
                </button>

            </div>

        </form>


        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Airport</th>
                        <th>Pickup</th>
                        <th>Drop-off</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach ($airportCharges as $charge): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($charge["airportName"]) ?>
                        </td>

                        <td>
                            £<?= number_format(
                                (float)$charge["pickupCharge"],
                                2
                            ) ?>
                        </td>

                        <td>
                            £<?= number_format(
                                (float)$charge["dropoffCharge"],
                                2
                            ) ?>
                        </td>

                        <td>

                            <?php if ((int)$charge["isActive"] === 1): ?>

                                <span class="badge bg-success">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="badge bg-secondary">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-end">

                            <form method="post">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="toggle_charge"
                                >

                                <input
                                    type="hidden"
                                    name="chargeID"
                                    value="<?= htmlspecialchars($charge["airportChargeID"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="newState"
                                    value="<?= (int)$charge["isActive"] === 1 ? 0 : 1 ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-dark rounded-pill px-3"
                                >
                                    <?= (int)$charge["isActive"] === 1
                                        ? "Deactivate"
                                        : "Activate" ?>
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>


</div>

</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>