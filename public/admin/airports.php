<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';

$pageTitle = "Manage Airports | EdiVentures Admin";
$metaDescription = "Manage airports available to EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/airports.php";

$settingsService =
    new AirportSettingsService($pdo);


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] =
        bin2hex(random_bytes(32));
}

$csrfToken =
    $_SESSION["csrf_token"];


/*
|--------------------------------------------------------------------------
| LOAD AIRPORTS
|--------------------------------------------------------------------------
*/

$airports =
    $settingsService->getAirports();

$airportReferences =
    $settingsService->getAirportReferences();


include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';

?>

<main>

<section
    class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;"
>
    <div class="container">

        <p class="eyebrow mb-2">
            Airport Settings
        </p>

        <h1 class="page-title mb-2">
            Airports
        </h1>

        <p class="page-hero-text mb-0">
            Add, edit and control airports available to the booking system.
        </p>

    </div>
</section>


<section class="py-5 bg-light">

    <div class="container">


        <!-- PAGE HEADER -->

        <div class="admin-page-toolbar mb-4">

            <div>

                <a
                    href="/admin/airport-settings.php"
                    class="admin-back-link"
                >
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Airport Settings
                </a>

                <h2 class="admin-page-title mt-3 mb-1">
                    Current Airports
                </h2>

                <p class="text-muted mb-0">
                    <span id="airportConfiguredCount">
                        <?= count($airports) ?>
                    </span>
                    <span id="airportConfiguredLabel">
                        airport<?= count($airports) === 1 ? "" : "s" ?>
                    </span>
                    configured
                </p>

            </div>


            <button
                type="button"
                class="btn btn-brand admin-primary-action"
                id="addAirportButton"
            >
                <i class="fa-solid fa-plus me-2"></i>
                Add Airport
            </button>

        </div>


        <!-- SEARCH / FILTER -->

        <div class="admin-filter-bar mb-4">

            <div class="admin-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    id="airportSearch"
                    class="form-control"
                    placeholder="Search airports..."
                    autocomplete="off"
                >

            </div>


            <select
                id="airportStatusFilter"
                class="form-select admin-filter-select"
            >
                <option value="all">
                    All statuses
                </option>

                <option value="active">
                    Active
                </option>

                <option value="inactive">
                    Inactive
                </option>

            </select>

        </div>


        <!-- DESKTOP / TABLET -->

        <div class="admin-data-card d-none d-md-block">

            <div class="table-responsive">

                <table class="table admin-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th>
                                Airport
                            </th>

                            <th>
                                Code
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody id="airportTableBody">

                    <?php foreach ($airports as $airport): ?>

                        <?php
                            $status =
                                (int)$airport["isActive"] === 1
                                    ? "active"
                                    : "inactive";
                        ?>

                        <tr
                            class="airport-item"
                            data-airport-id="<?= (int)$airport["airportID"] ?>"
                            data-name="<?= htmlspecialchars(
                                strtolower($airport["airportName"]),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            data-code="<?= htmlspecialchars(
                                strtolower($airport["airportCode"] ?? ""),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            data-status="<?= $status ?>"
                        >

                            <td>

                                <button
                                    type="button"
                                    class="admin-row-title js-airport-edit"
                                    data-airport-id="<?= (int)$airport["airportID"] ?>"
                                    data-airport-name="<?= htmlspecialchars(
                                        $airport["airportName"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-airport-code="<?= htmlspecialchars(
                                        $airport["airportCode"] ?? "",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-active="<?= (int)$airport["isActive"] ?>"
                                >
                                    <?= htmlspecialchars(
                                        $airport["airportName"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </button>

                            </td>


                            <td>

                                <span class="admin-code">
                                    <?= htmlspecialchars(
                                        $airport["airportCode"] ?? "—",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="admin-status-toggle js-airport-toggle <?= $status ?>"
                                    data-airport-id="<?= (int)$airport["airportID"] ?>"
                                    data-active="<?= (int)$airport["isActive"] ?>"
                                >
                                    <span class="admin-status-dot"></span>

                                    <span class="js-airport-status-label">
                                        <?= (int)$airport["isActive"] === 1
                                            ? "Active"
                                            : "Inactive" ?>
                                    </span>
                                </button>

                            </td>


                            <td class="text-end">

                                <button
                                    type="button"
                                    class="admin-icon-button js-airport-edit"
                                    aria-label="Edit airport"
                                    title="Edit"
                                    data-airport-id="<?= (int)$airport["airportID"] ?>"
                                    data-airport-name="<?= htmlspecialchars(
                                        $airport["airportName"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-airport-code="<?= htmlspecialchars(
                                        $airport["airportCode"] ?? "",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-active="<?= (int)$airport["isActive"] ?>"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- MOBILE CARDS -->

        <div class="d-md-none">

            <div
                id="airportMobileList"
                class="admin-mobile-list"
            >

            <?php foreach ($airports as $airport): ?>

                <?php
                    $status =
                        (int)$airport["isActive"] === 1
                            ? "active"
                            : "inactive";
                ?>

                <div
                    class="admin-mobile-card airport-item"
                    data-airport-id="<?= (int)$airport["airportID"] ?>"
                    data-name="<?= htmlspecialchars(
                        strtolower($airport["airportName"]),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    data-code="<?= htmlspecialchars(
                        strtolower($airport["airportCode"] ?? ""),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    data-status="<?= $status ?>"
                >

                    <div class="d-flex justify-content-between gap-3">

                        <div>

                            <h3 class="admin-mobile-title">
                                <?= htmlspecialchars(
                                    $airport["airportName"],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>

                            <div class="admin-code">
                                <?= htmlspecialchars(
                                    $airport["airportCode"] ?? "—",
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </div>

                        </div>


                        <button
                            type="button"
                            class="admin-icon-button js-airport-edit"
                            aria-label="Edit airport"
                            title="Edit"
                            data-airport-id="<?= (int)$airport["airportID"] ?>"
                            data-airport-name="<?= htmlspecialchars(
                                $airport["airportName"],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            data-airport-code="<?= htmlspecialchars(
                                $airport["airportCode"] ?? "",
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            data-active="<?= (int)$airport["isActive"] ?>"
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>

                    </div>


                    <div class="mt-3">

                        <button
                            type="button"
                            class="admin-status-toggle js-airport-toggle <?= $status ?>"
                            data-airport-id="<?= (int)$airport["airportID"] ?>"
                            data-active="<?= (int)$airport["isActive"] ?>"
                        >
                            <span class="admin-status-dot"></span>

                            <span class="js-airport-status-label">
                                <?= (int)$airport["isActive"] === 1
                                    ? "Active"
                                    : "Inactive" ?>
                            </span>
                        </button>

                    </div>

                </div>

            <?php endforeach; ?>

            </div>

        </div>

    </div>

</section>

</main>


<!-- =========================================================
     AIRPORT DRAWER
========================================================= -->

<div
    class="offcanvas offcanvas-end admin-drawer"
    tabindex="-1"
    id="airportDrawer"
    aria-labelledby="airportDrawerTitle"
>

    <div class="offcanvas-header">

        <div>

            <p class="eyebrow mb-1">
                Airport Settings
            </p>

            <h2
                class="offcanvas-title"
                id="airportDrawerTitle"
            >
                Add Airport
            </h2>

        </div>


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Close"
        ></button>

    </div>


    <div class="offcanvas-body">

        <div
            id="airportAjaxMessage"
            class="alert d-none"
            role="status"
            aria-live="polite"
        ></div>

        <form id="airportDrawerForm" novalidate>

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(
                    $csrfToken,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <input
                type="hidden"
                name="action"
                id="drawerAction"
                value="add_airport"
            >

            <input
                type="hidden"
                name="airportID"
                id="drawerAirportID"
                value=""
            >


            <!-- ADD MODE -->

            <div id="airportAddFields">

                <div class="mb-4">

                    <label
                        for="drawerAirportReference"
                        class="form-label"
                    >
                        Airport
                    </label>

                    <select
                        id="drawerAirportReference"
                        name="airportReferenceID"
                        class="form-select"
                    >

                        <option value="">
                            Choose airport...
                        </option>

                        <?php foreach ($airportReferences as $reference): ?>

                            <option
                                value="<?= (int)$reference["airportReferenceID"] ?>"
                                <?= (int)$reference["isConfigured"] === 1
                                    ? "disabled"
                                    : "" ?>
                            >
                                <?= htmlspecialchars(
                                    $reference["airportName"],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                                (<?= htmlspecialchars(
                                    $reference["iataCode"],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>)
                                —
                                <?= htmlspecialchars(
                                    $reference["cityName"] ?? "",
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>


                    <div class="form-text mt-2">
                        Only verified airports from the reference list can be added.
                    </div>

                </div>

            </div>


            <!-- EDIT MODE -->

            <div
                id="airportEditFields"
                class="d-none"
            >

                <div class="admin-airport-summary mb-4">

                    <div class="admin-airport-summary-icon">
                        <i class="fa-solid fa-plane"></i>
                    </div>

                    <div>

                        <strong
                            id="drawerAirportName"
                            class="d-block"
                        ></strong>

                        <span
                            id="drawerAirportCode"
                            class="text-muted"
                        ></span>

                    </div>

                </div>

            </div>


            <!-- ACTIVE -->

            <div class="admin-setting-row mb-4">

                <div>

                    <strong>
                        Active
                    </strong>

                    <div class="small text-muted">
                        Allow this airport to be used by the booking system.
                    </div>

                </div>


                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="drawerAirportActive"
                        name="isActive"
                        checked
                    >

                </div>

            </div>


            <div class="admin-drawer-actions">

                <button
                    type="button"
                    class="btn admin-secondary-button"
                    data-bs-dismiss="offcanvas"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-brand admin-save-button"
                    id="drawerSaveButton"
                >
                    Add Airport
                </button>

            </div>

        </form>

    </div>

</div>


<!-- =========================================================
     TOAST
========================================================= -->

<div class="toast-container position-fixed bottom-0 end-0 p-3">

    <div
        id="airportToast"
        class="toast admin-toast"
        role="status"
        aria-live="polite"
        aria-atomic="true"
    >
        <div class="toast-body" id="airportToastMessage"></div>
    </div>

</div>


<script src="/assets/js/admin-airports.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
