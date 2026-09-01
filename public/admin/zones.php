<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';


$pageTitle =
    "Zones | EdiVentures Admin";

$metaDescription =
    "Manage booking zones and postcode rules.";

$canonicalUrl =
    "https://www.ediventures.co.uk/admin/zones.php";


$settingsService =
    new AirportSettingsService($pdo);


/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );
}

$csrfToken =
    $_SESSION["csrf_token"];


/*
|--------------------------------------------------------------------------
| LOAD ZONES
|--------------------------------------------------------------------------
*/

$zones =
    $settingsService->getZones();


include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';

?>

<main>

    <section
        class="account-header text-white"
        style="
            background:
                linear-gradient(
                    rgba(0,0,0,0.72),
                    rgba(0,0,0,0.72)
                ),
                url('/assets/img/passenger-plane-airport-transfer.jpg')
                center/cover no-repeat;
        "
    >

        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <p class="eyebrow mb-3">
                        Admin Area
                    </p>

                    <h1 class="page-title">
                        Zones
                    </h1>

                    <p class="page-hero-text">
                        Manage booking areas and postcode rules used
                        to determine pricing zones.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <section class="py-5 bg-light">

        <div class="container">


            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div
                class="
                    d-flex
                    justify-content-between
                    align-items-center
                    flex-wrap
                    gap-3
                    mb-4
                "
            >

                <div>

                    <h2 class="section-title mb-1">
                        Booking Zones
                    </h2>

                    <p class="text-muted mb-0">
                        More specific postcode rules take priority
                        over broader postcode rules.
                    </p>

                </div>


                <div class="d-flex gap-2">

                    <a
                        href="/admin/airport-settings.php"
                        class="
                            btn
                            btn-outline-dark
                            rounded-pill
                            px-4
                        "
                    >
                        Back
                    </a>


                    <button
                        type="button"
                        class="
                            btn
                            btn-brand
                            rounded-pill
                            px-4
                        "
                        id="addZoneButton"
                    >

                        <i
                            class="
                                fa-solid
                                fa-plus
                                me-2
                            "
                        ></i>

                        Add Zone

                    </button>

                </div>

            </div>



            <!-- =====================================================
                 ZONE LIST
            ====================================================== -->

            <div
                class="admin-zone-list"
                id="zoneList"
            >

                <div
                    class="admin-empty-state <?= !empty($zones)
                        ? 'd-none'
                        : '' ?>"
                    id="zoneEmptyState"
                >

                    <i
                        class="
                            fa-solid
                            fa-map-location-dot
                        "
                    ></i>

                    <h3>
                        No zones yet
                    </h3>

                    <p>
                        Add your first booking zone and
                        then assign postcode rules to it.
                    </p>

                </div>


                <?php foreach ($zones as $zone): ?>

                    <div
                        class="admin-zone-row"
                        data-zone-row="<?= (int)$zone["zoneID"] ?>"
                    >


                        <!-- LEFT -->

                        <div class="admin-zone-row__main">

                            <div class="admin-zone-row__icon">

                                <i
                                    class="
                                        fa-solid
                                        fa-map-location-dot
                                    "
                                ></i>

                            </div>


                            <div>

                                <div
                                    class="admin-zone-row__title"
                                    data-zone-name
                                >
                                    <?= htmlspecialchars(
                                        $zone["zoneName"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>


                                <div
                                    class="admin-zone-row__description"
                                    data-zone-description
                                    <?= empty($zone["description"])
                                        ? 'hidden'
                                        : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        $zone["description"] ?? "",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>


                                <div
                                    class="admin-zone-row__meta"
                                >

                                    <span
                                        data-zone-postcode-count
                                    >
                                        <?= (int)$zone["postcodeCount"] ?>
                                    </span>

                                    <span
                                        data-zone-postcode-label
                                    >
                                        <?= (int)$zone["postcodeCount"] === 1
                                            ? "postcode rule"
                                            : "postcode rules" ?>
                                    </span>

                                </div>

                            </div>

                        </div>



                        <!-- RIGHT -->

                        <div class="admin-zone-row__actions">


                            <!-- ACTIVE / INACTIVE -->

                            <button
                                type="button"
                                class="
                                    admin-status-button
                                    js-zone-toggle
                                    <?= (int)$zone["isActive"] === 1
                                        ? "is-active"
                                        : "is-inactive" ?>
                                "
                                data-zone-id="<?= (int)$zone["zoneID"] ?>"
                                data-active="<?= (int)$zone["isActive"] ?>"
                                aria-label="Change zone status"
                            >

                                <span
                                    class="admin-status-dot"
                                ></span>

                                <span
                                    class="js-zone-status-label"
                                >
                                    <?= (int)$zone["isActive"] === 1
                                        ? "Active"
                                        : "Inactive" ?>
                                </span>

                            </button>



                            <!-- EDIT -->

                            <button
                                type="button"
                                class="
                                    admin-icon-button
                                    js-zone-edit
                                "
                                data-zone-id="<?= (int)$zone["zoneID"] ?>"
                                aria-label="Edit zone"
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-pen
                                    "
                                ></i>

                            </button>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </section>

</main>



<!-- =========================================================
     ZONE DRAWER
========================================================= -->

<div
    class="
        offcanvas
        offcanvas-end
        admin-drawer
    "
    tabindex="-1"
    id="zoneDrawer"
    aria-labelledby="zoneDrawerTitle"
>

    <div class="offcanvas-header">

        <div>

            <p class="section-label mb-1">
                Zone Settings
            </p>

            <h2
                class="offcanvas-title"
                id="zoneDrawerTitle"
            >
                Add Zone
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


        <!-- AJAX MESSAGE -->

        <div
            id="zoneAjaxMessage"
            class="alert d-none"
            role="status"
            aria-live="polite"
        ></div>



        <!-- =====================================================
             ZONE FORM
        ====================================================== -->

        <form
            id="zoneForm"
            novalidate
        >

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
                value="save_zone"
            >

            <input
                type="hidden"
                name="zoneID"
                id="zoneID"
                value=""
            >



            <!-- ZONE NAME -->

            <div class="mb-4">

                <label
                    for="zoneName"
                    class="form-label"
                >
                    Zone name
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="zoneName"
                    name="zoneName"
                    maxlength="50"
                    placeholder="Example: Zone A"
                    autocomplete="off"
                    required
                >

                <div
                    class="invalid-feedback"
                    id="zoneNameFeedback"
                >
                    Please enter a zone name.
                </div>

            </div>



            <!-- DESCRIPTION -->

            <div class="mb-4">

                <label
                    for="zoneDescription"
                    class="form-label"
                >
                    Description
                </label>

                <textarea
                    class="form-control"
                    id="zoneDescription"
                    name="description"
                    maxlength="255"
                    rows="3"
                    placeholder="Example: South Queensferry and nearby areas"
                ></textarea>

            </div>



            <!-- ACTIVE -->

            <div class="admin-setting-row mb-4">

                <div>

                    <strong>
                        Active
                    </strong>

                    <div class="small text-muted">
                        Allow this zone to be used by
                        the booking system.
                    </div>

                </div>


                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="isActive"
                        id="zoneActive"
                        checked
                    >

                </div>

            </div>



            <!-- ACTIONS -->

            <div class="admin-drawer-actions mb-4">

                <button
                    type="button"
                    class="
                        btn
                        admin-secondary-button
                    "
                    data-bs-dismiss="offcanvas"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="
                        btn
                        btn-brand
                        admin-save-button
                    "
                    id="saveZoneButton"
                >
                    Save Zone
                </button>

            </div>

        </form>



        <!-- =====================================================
             POSTCODE MANAGEMENT
        ====================================================== -->

        <div
            id="zonePostcodeSection"
            class="d-none"
        >

            <hr class="my-4">


            <div class="admin-zone-postcodes">


                <div class="mb-3">

                    <h3 class="h6 mb-1">
                        Postcode Rules
                    </h3>

                    <p class="small text-muted mb-0">
                        More specific postcode rules take
                        priority over broader rules.
                    </p>

                </div>



                <!-- NO POSTCODES -->

                <p
                    class="
                        small
                        text-muted
                        d-none
                    "
                    id="zoneNoPostcodes"
                >
                    No postcode rules have been added yet.
                </p>



                <!-- CHIPS -->

                <div
                    class="
                        admin-zone-chip-list
                        mb-3
                    "
                    id="zonePostcodeList"
                ></div>



                <!-- ADD POSTCODE -->

                <form
                    id="zonePostcodeForm"
                    novalidate
                >

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
                        value="add_postcode"
                    >

                    <input
                        type="hidden"
                        name="zoneID"
                        id="postcodeZoneID"
                        value=""
                    >


                    <label
                        for="postcodePrefix"
                        class="form-label"
                    >
                        Add postcode rule
                    </label>


                    <div class="input-group">

                        <input
                            type="text"
                            class="
                                form-control
                                text-uppercase
                            "
                            id="postcodePrefix"
                            name="postcodePrefix"
                            placeholder="Example: EH30 9"
                            maxlength="12"
                            autocomplete="off"
                            required
                        >


                        <button
                            type="submit"
                            class="btn btn-brand"
                            id="addPostcodeButton"
                        >
                            Add
                        </button>

                    </div>


                    <div class="form-text">

                        Use broad areas such as EH30 for normal
                        coverage. Add more specific rules only
                        where different pricing is needed.

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     AJAX TOAST
========================================================= -->

<div
    class="
        position-fixed
        bottom-0
        end-0
        p-3
    "
    style="z-index: 1100;"
>

    <div
        id="zoneToast"
        class="
            toast
            align-items-center
            border-0
        "
        role="status"
        aria-live="polite"
        aria-atomic="true"
    >

        <div class="d-flex">

            <div
                class="toast-body"
                id="zoneToastMessage"
            ></div>

            <button
                type="button"
                class="
                    btn-close
                    btn-close-white
                    me-2
                    m-auto
                "
                data-bs-dismiss="toast"
                aria-label="Close"
            ></button>

        </div>

    </div>

</div>



<script src="/assets/js/admin-zones.js"></script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>