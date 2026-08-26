<?php

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';

$pageTitle = "Manage Airports | EdiVentures Admin";
$metaDescription = "Manage airports available to EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/airports.php";

$settingsService =
    new AirportSettingsService($pdo);

$errors = [];


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
| POST ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken =
        $_POST["csrf_token"] ?? "";

    if (
        !is_string($submittedToken) ||
        !hash_equals(
            $csrfToken,
            $submittedToken
        )
    ) {
        $errors[] =
            "Your session security token is invalid. Please refresh the page and try again.";

    } else {

        $action =
            $_POST["action"] ?? "";

        try {

            /*
             * Add or edit airport.
             */
            if ($action === "add_airport") {

                $settingsService->addAirportFromReference(
                    (int)($_POST["airportReferenceID"] ?? 0),
                    isset($_POST["isActive"])
                );

                header(
                    "Location: /admin/airports.php?saved=1"
                );

                exit;
            }


            if ($action === "update_airport") {

                $settingsService->setAirportActive(
                    (int)($_POST["airportID"] ?? 0),
                    isset($_POST["isActive"])
                );

                header(
                    "Location: /admin/airports.php?saved=1"
                );

                exit;
            }


            /*
             * Toggle active status.
             */
            if ($action === "toggle_airport") {

                $settingsService->setAirportActive(
                    (int)($_POST["airportID"] ?? 0),
                    (int)($_POST["newState"] ?? 0) === 1
                );

                header(
                    "Location: /admin/airports.php?status_updated=1"
                );

                exit;
            }

        } catch (InvalidArgumentException $e) {

            $errors[] =
                $e->getMessage();

        } catch (PDOException $e) {

            error_log(
                "Airport admin database error: " .
                $e->getMessage()
            );

            $errors[] =
                "The airport could not be saved. Please check the details and try again.";
        }
    }
}


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
                    <?= count($airports) ?>
                    airport<?= count($airports) === 1 ? "" : "s" ?>
                    configured
                </p>

            </div>


            <button
                type="button"
                class="btn btn-brand admin-primary-action"
                data-bs-toggle="offcanvas"
                data-bs-target="#airportDrawer"
                onclick="openAddAirport()"
            >
                <i class="fa-solid fa-plus me-2"></i>
                Add Airport
            </button>

        </div>


        <!-- ERRORS -->

        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger">

                <?php foreach ($errors as $error): ?>

                    <div>
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- SEARCH / FILTER -->

        <div class="admin-filter-bar mb-4">

            <div class="admin-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    id="airportSearch"
                    class="form-control"
                    placeholder="Search airports..."
                    oninput="filterAirports()"
                >

            </div>


            <select
                id="airportStatusFilter"
                class="form-select admin-filter-select"
                onchange="filterAirports()"
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
                            data-name="<?= htmlspecialchars(
                                strtolower($airport["airportName"])
                            ) ?>"
                            data-code="<?= htmlspecialchars(
                                strtolower($airport["airportCode"] ?? "")
                            ) ?>"
                            data-status="<?= $status ?>"
                        >

                            <td>

                                <button
                                    type="button"
                                    class="admin-row-title"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#airportDrawer"
                                    onclick='openEditAirport(
                                        <?= json_encode([
                                            "airportID" =>
                                                (int)$airport["airportID"],

                                            "airportName" =>
                                                $airport["airportName"],

                                            "airportCode" =>
                                                $airport["airportCode"] ?? "",

                                            "isActive" =>
                                                (int)$airport["isActive"]
                                        ]) ?>
                                    )'
                                >
                                    <?= htmlspecialchars(
                                        $airport["airportName"]
                                    ) ?>
                                </button>

                            </td>


                            <td>

                                <span class="admin-code">
                                    <?= htmlspecialchars(
                                        $airport["airportCode"] ?? "—"
                                    ) ?>
                                </span>

                            </td>


                            <td>

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
                                        value="toggle_airport"
                                    >

                                    <input
                                        type="hidden"
                                        name="airportID"
                                        value="<?= htmlspecialchars(
                                            $airport["airportID"]
                                        ) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="newState"
                                        value="<?= (int)$airport["isActive"] === 1
                                            ? 0
                                            : 1 ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="admin-status-toggle <?= $status ?>"
                                    >

                                        <span class="admin-status-dot"></span>

                                        <?= (int)$airport["isActive"] === 1
                                            ? "Active"
                                            : "Inactive" ?>

                                    </button>

                                </form>

                            </td>


                            <td class="text-end">

                                <button
                                    type="button"
                                    class="admin-icon-button"
                                    aria-label="Edit airport"
                                    title="Edit"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#airportDrawer"
                                    onclick='openEditAirport(
                                        <?= json_encode([
                                            "airportID" =>
                                                (int)$airport["airportID"],

                                            "airportName" =>
                                                $airport["airportName"],

                                            "airportCode" =>
                                                $airport["airportCode"] ?? "",

                                            "isActive" =>
                                                (int)$airport["isActive"]
                                        ]) ?>
                                    )'
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
                    data-name="<?= htmlspecialchars(
                        strtolower($airport["airportName"])
                    ) ?>"
                    data-code="<?= htmlspecialchars(
                        strtolower($airport["airportCode"] ?? "")
                    ) ?>"
                    data-status="<?= $status ?>"
                >

                    <div class="d-flex justify-content-between gap-3">

                        <div>

                            <h3 class="admin-mobile-title">
                                <?= htmlspecialchars(
                                    $airport["airportName"]
                                ) ?>
                            </h3>

                            <div class="admin-code">
                                <?= htmlspecialchars(
                                    $airport["airportCode"] ?? "—"
                                ) ?>
                            </div>

                        </div>


                        <button
                            type="button"
                            class="admin-icon-button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#airportDrawer"
                            onclick='openEditAirport(
                                <?= json_encode([
                                    "airportID" =>
                                        (int)$airport["airportID"],

                                    "airportName" =>
                                        $airport["airportName"],

                                    "airportCode" =>
                                        $airport["airportCode"] ?? "",

                                    "isActive" =>
                                        (int)$airport["isActive"]
                                ]) ?>
                            )'
                        >
                            <i class="fa-solid fa-pen"></i>
                        </button>

                    </div>


                    <div class="mt-3">

                        <form method="post">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="toggle_airport"
                            >

                            <input
                                type="hidden"
                                name="airportID"
                                value="<?= htmlspecialchars(
                                    $airport["airportID"]
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="newState"
                                value="<?= (int)$airport["isActive"] === 1
                                    ? 0
                                    : 1 ?>"
                            >

                            <button
                                type="submit"
                                class="admin-status-toggle <?= $status ?>"
                            >

                                <span class="admin-status-dot"></span>

                                <?= (int)$airport["isActive"] === 1
                                    ? "Active"
                                    : "Inactive" ?>

                            </button>

                        </form>

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

        <form method="post" id="airportDrawerForm">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken) ?>"
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
                                value="<?= htmlspecialchars(
                                    $reference["airportReferenceID"]
                                ) ?>"

                                <?= (int)$reference["isConfigured"] === 1
                                    ? "disabled"
                                    : "" ?>
                            >

                                <?= htmlspecialchars(
                                    $reference["airportName"]
                                ) ?>

                                (<?= htmlspecialchars(
                                    $reference["iataCode"]
                                ) ?>)

                                —
                                <?= htmlspecialchars(
                                    $reference["cityName"] ?? ""
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

<?php if (
    (isset($_GET["saved"]) && $_GET["saved"] === "1") ||
    (
        isset($_GET["status_updated"]) &&
        $_GET["status_updated"] === "1"
    )
): ?>

<div class="toast-container position-fixed bottom-0 end-0 p-3">

    <div
        id="airportToast"
        class="toast admin-toast"
        role="status"
    >

        <div class="toast-body">

            <i class="fa-solid fa-circle-check me-2"></i>

            <?= isset($_GET["saved"])
                ? "Airport saved"
                : "Airport status updated" ?>

        </div>

    </div>

</div>

<?php endif; ?>


<script>

function openAddAirport()
{
    document.getElementById(
        "airportDrawerTitle"
    ).textContent = "Add Airport";

    document.getElementById(
        "drawerAction"
    ).value = "add_airport";

    document.getElementById(
        "drawerAirportID"
    ).value = "";

    document.getElementById(
        "drawerAirportReference"
    ).value = "";

    document.getElementById(
        "drawerAirportActive"
    ).checked = true;

    document.getElementById(
        "airportAddFields"
    ).classList.remove("d-none");

    document.getElementById(
        "airportEditFields"
    ).classList.add("d-none");

    document.getElementById(
        "drawerAirportReference"
    ).required = true;

    document.getElementById(
        "drawerSaveButton"
    ).textContent = "Add Airport";
}


function openEditAirport(airport)
{
    document.getElementById(
        "airportDrawerTitle"
    ).textContent = "Airport Settings";

    document.getElementById(
        "drawerAction"
    ).value = "update_airport";

    document.getElementById(
        "drawerAirportID"
    ).value = airport.airportID;

    document.getElementById(
        "drawerAirportName"
    ).textContent =
        airport.airportName;

    document.getElementById(
        "drawerAirportCode"
    ).textContent =
        airport.airportCode ?? "";

    document.getElementById(
        "drawerAirportActive"
    ).checked =
        Number(airport.isActive) === 1;

    document.getElementById(
        "airportAddFields"
    ).classList.add("d-none");

    document.getElementById(
        "airportEditFields"
    ).classList.remove("d-none");

    document.getElementById(
        "drawerAirportReference"
    ).required = false;

    document.getElementById(
        "drawerSaveButton"
    ).textContent = "Save";
}


function filterAirports()
{
    const search =
        document.getElementById(
            "airportSearch"
        ).value
        .toLowerCase()
        .trim();

    const status =
        document.getElementById(
            "airportStatusFilter"
        ).value;

    const items =
        document.querySelectorAll(
            ".airport-item"
        );

    items.forEach(item => {

        const name =
            item.dataset.name ?? "";

        const code =
            item.dataset.code ?? "";

        const itemStatus =
            item.dataset.status ?? "";

        const matchesSearch =
            name.includes(search) ||
            code.includes(search);

        const matchesStatus =
            status === "all" ||
            status === itemStatus;

        item.style.display =
            matchesSearch &&
            matchesStatus
                ? ""
                : "none";
    });
}


document.addEventListener(
    "DOMContentLoaded",
    function () {

        const toastElement =
            document.getElementById(
                "airportToast"
            );

        if (toastElement) {

            const toast =
                new bootstrap.Toast(
                    toastElement,
                    {
                        delay: 2500
                    }
                );

            toast.show();
        }
    }
);

</script>


<?php include __DIR__ . '/../../includes/footer.php'; ?>