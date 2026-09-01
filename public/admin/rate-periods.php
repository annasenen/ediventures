<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';


$pageTitle =
    "Rate Periods | EdiVentures Admin";

$metaDescription =
    "Manage time and day rules used for airport transfer pricing.";

$canonicalUrl =
    "https://www.ediventures.co.uk/admin/rate-periods.php";


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
| LOAD RATE PERIODS
|--------------------------------------------------------------------------
*/

$periods =
    $settingsService->getPricingPeriods();

$coverage =
    $settingsService
        ->getPricingPeriodCoverage();

$dayNames = [
    1 => 'Mon',
    2 => 'Tue',
    3 => 'Wed',
    4 => 'Thu',
    5 => 'Fri',
    6 => 'Sat',
    7 => 'Sun'
];


function ratePeriodDaysFromCsv(
    ?string $daysCsv
): array {

    if (
        $daysCsv === null ||
        trim($daysCsv) === ''
    ) {
        return [];
    }

    $days = [];

    foreach (
        explode(',', $daysCsv)
        as $day
    ) {

        $dayNumber =
            (int)$day;

        if (
            $dayNumber >= 1 &&
            $dayNumber <= 7
        ) {
            $days[] =
                $dayNumber;
        }
    }

    return $days;
}


function ratePeriodDisplayTime(
    ?string $time
): string {

    if (!$time) {
        return '';
    }

    return substr(
        $time,
        0,
        5
    );
}


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
                        Airport Settings
                    </p>

                    <h1 class="page-title">
                        Rate Periods
                    </h1>

                    <p class="page-hero-text">
                        Control which days and times use different
                        airport transfer prices.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <section class="py-5 bg-light">

        <div class="container">


            <!-- PAGE HEADER -->

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
                        Pricing Times
                    </h2>

                    <p class="text-muted mb-0">
                        Set the days and times used for different
                        airport transfer rates.
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
                        id="addRatePeriodButton"
                    >

                        <i
                            class="
                                fa-solid
                                fa-plus
                                me-2
                            "
                        ></i>

                        Add Rate Period

                    </button>

                </div>

            </div>


            <!-- INFORMATION -->

            <div class="alert alert-light border mb-4">

                <div class="d-flex gap-3">

                    <i
                        class="
                            fa-solid
                            fa-circle-info
                            mt-1
                        "
                    ></i>

                    <div class="small">

                        <strong>
                            How this works
                        </strong>

                        <div class="text-muted mt-1">
                            A period can apply to selected days.
                            Overnight times are supported, for example
                            22:00 → 07:00. The booking system evaluates
                            these rules in Europe/London time.
                        </div>

                    </div>

                </div>

            </div>

            <!-- WEEKLY COVERAGE -->

            <div
                class="card border-0 shadow-sm mb-4"
                id="ratePeriodCoverageCard"
            >

                <div class="card-body p-4">

                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-start
                            flex-wrap
                            gap-3
                        "
                    >

                        <div>

                            <div
                                class="
                                    d-flex
                                    align-items-center
                                    gap-2
                                    mb-1
                                "
                            >

                                <i
                                    id="ratePeriodCoverageIcon"
                                    class="
                                        fa-solid
                                        <?= !empty($coverage['isComplete'])
                                            ? 'fa-circle-check text-success'
                                            : 'fa-triangle-exclamation text-warning' ?>
                                    "
                                ></i>

                                <h3
                                    class="h6 mb-0"
                                    id="ratePeriodCoverageTitle"
                                >
                                    <?= !empty($coverage['isComplete'])
                                        ? 'Full week covered'
                                        : 'Rate coverage incomplete' ?>
                                </h3>

                            </div>


                            <p
                                class="small text-muted mb-0"
                                id="ratePeriodCoverageSummary"
                            >
                                <?= !empty($coverage['isComplete'])
                                    ? 'Every day and time has an active rate period.'
                                    : 'Some journey times do not currently have an active rate period.' ?>
                            </p>

                        </div>

                    </div>


                    <div
                        id="ratePeriodCoverageGaps"
                        class="<?= !empty($coverage['isComplete'])
                            ? 'd-none'
                            : '' ?>"
                    >

                        <hr class="my-3">

                        <div class="small">

                            <div class="fw-semibold mb-2">
                                Times without a rate period
                            </div>


                            <div
                                class="d-flex flex-column gap-2"
                                id="ratePeriodCoverageGapList"
                            >

                                <?php
                                foreach (
                                    $coverage['gaps'] ?? []
                                    as $gap
                                ):
                                ?>

                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-center
                                            flex-wrap
                                            gap-2
                                        "
                                    >

                                        <span class="text-muted">
                                            <?= htmlspecialchars(
                                                (string)(
                                                    $gap['dayLabel'] ?? ''
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars(
                                                (string)(
                                                    $gap['startTime'] ?? ''
                                                ) .
                                                ' → ' .
                                                (string)(
                                                    $gap['endTime'] ?? ''
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- RATE PERIOD LIST -->

            <div
                class="admin-zone-list"
                id="ratePeriodList"
            >

                <div
                    class="admin-empty-state <?= !empty($periods)
                        ? 'd-none'
                        : '' ?>"
                    id="ratePeriodEmptyState"
                >

                    <i
                        class="
                            fa-solid
                            fa-clock
                        "
                    ></i>

                    <h3>
                        No rate periods yet
                    </h3>

                    <p>
                        Add your first pricing time rule.
                    </p>

                </div>


                <?php foreach ($periods as $period): ?>

                    <?php
                        $days =
                            ratePeriodDaysFromCsv(
                                $period["daysCsv"] ?? null
                            );

                        $dayLabels = [];

                        foreach ($days as $day) {
                            if (isset($dayNames[$day])) {
                                $dayLabels[] =
                                    $dayNames[$day];
                            }
                        }

                        $startDisplay =
                            ratePeriodDisplayTime(
                                $period["startTime"] ?? ""
                            );

                        $endDisplay =
                            ratePeriodDisplayTime(
                                $period["endTime"] ?? ""
                            );

                        $isOvernight =
                            ($period["startTime"] ?? "") >
                            ($period["endTime"] ?? "");
                    ?>

                    <div
                        class="admin-zone-row"
                        data-rate-period-row="<?= (int)$period["pricingPeriodID"] ?>"
                    >


                        <!-- LEFT -->

                        <div class="admin-zone-row__main">

                            <div class="admin-zone-row__icon">

                                <i
                                    class="
                                        fa-solid
                                        fa-clock
                                    "
                                ></i>

                            </div>


                            <div>

                                <div
                                    class="admin-zone-row__title"
                                    data-rate-period-name
                                >
                                    <?= htmlspecialchars(
                                        $period["periodName"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>


                                <div
                                    class="admin-zone-row__description"
                                    data-rate-period-time
                                >
                                    <?= htmlspecialchars(
                                        $startDisplay .
                                        " → " .
                                        $endDisplay .
                                        ($isOvernight
                                            ? " · overnight"
                                            : ""),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>


                                <div
                                    class="admin-zone-row__meta"
                                >

                                    <span
                                        data-rate-period-days
                                    >
                                        <?= htmlspecialchars(
                                            implode(
                                                " · ",
                                                $dayLabels
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- RIGHT -->

                        <div class="admin-zone-row__actions">


                            <button
                                type="button"
                                class="
                                    admin-status-button
                                    js-rate-period-toggle
                                    <?= (int)$period["isActive"] === 1
                                        ? "is-active"
                                        : "is-inactive" ?>
                                "
                                data-rate-period-id="<?= (int)$period["pricingPeriodID"] ?>"
                                data-active="<?= (int)$period["isActive"] ?>"
                                aria-label="Change rate period status"
                            >

                                <span
                                    class="admin-status-dot"
                                ></span>

                                <span
                                    class="js-rate-period-status-label"
                                >
                                    <?= (int)$period["isActive"] === 1
                                        ? "Active"
                                        : "Inactive" ?>
                                </span>

                            </button>


                            <button
                                type="button"
                                class="
                                    admin-icon-button
                                    js-rate-period-edit
                                "
                                data-rate-period-id="<?= (int)$period["pricingPeriodID"] ?>"
                                aria-label="Edit rate period"
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


<!-- RATE PERIOD DRAWER -->

<div
    class="
        offcanvas
        offcanvas-end
        admin-drawer
    "
    tabindex="-1"
    id="ratePeriodDrawer"
    aria-labelledby="ratePeriodDrawerTitle"
>

    <div class="offcanvas-header">

        <div>

            <p class="section-label mb-1">
                Rate Period Settings
            </p>

            <h2
                class="offcanvas-title"
                id="ratePeriodDrawerTitle"
            >
                Add Rate Period
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
            id="ratePeriodAjaxMessage"
            class="alert d-none"
            role="status"
            aria-live="polite"
        ></div>


        <form
            id="ratePeriodForm"
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
                name="pricingPeriodID"
                id="pricingPeriodID"
                value=""
            >


            <!-- NAME -->

            <div class="mb-4">

                <label
                    for="periodName"
                    class="form-label"
                >
                    Rate period name
                </label>

                <input
                    type="text"
                    class="form-control"
                    id="periodName"
                    name="periodName"
                    maxlength="100"
                    placeholder="Example: Weekday Day"
                    autocomplete="off"
                    required
                >

                <div class="invalid-feedback">
                    Please enter a rate period name.
                </div>

            </div>


            <!-- TIME -->

            <div class="row g-3 mb-4">

                <div class="col-6">

                    <label
                        for="periodStartTime"
                        class="form-label"
                    >
                        Start time
                    </label>

                    <input
                        type="time"
                        class="form-control"
                        id="periodStartTime"
                        name="startTime"
                        step="60"
                        required
                    >

                </div>


                <div class="col-6">

                    <label
                        for="periodEndTime"
                        class="form-label"
                    >
                        End time
                    </label>

                    <input
                        type="time"
                        class="form-control"
                        id="periodEndTime"
                        name="endTime"
                        step="60"
                        required
                    >

                </div>

            </div>


            <div
                class="
                    alert
                    alert-light
                    border
                    small
                    mb-4
                "
                id="ratePeriodTimeHint"
            >
                If the end time is earlier than the start
                time, the period continues after midnight.
            </div>


            <!-- DAYS -->

            <fieldset class="mb-4">

                <legend class="form-label">
                    Days
                </legend>

                <div
                    class="
                        d-flex
                        flex-wrap
                        gap-2
                    "
                    id="ratePeriodDays"
                >

                    <?php foreach ($dayNames as $dayNumber => $dayName): ?>

                        <input
                            type="checkbox"
                            class="btn-check js-rate-period-day"
                            id="ratePeriodDay<?= $dayNumber ?>"
                            value="<?= $dayNumber ?>"
                            autocomplete="off"
                        >

                        <label
                            class="
                                btn
                                btn-outline-dark
                                rounded-pill
                                px-3
                            "
                            for="ratePeriodDay<?= $dayNumber ?>"
                        >
                            <?= htmlspecialchars(
                                $dayName,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div
                    class="
                        small
                        text-danger
                        mt-2
                        d-none
                    "
                    id="ratePeriodDaysError"
                >
                    Choose at least one day.
                </div>

            </fieldset>



            <!-- ACTIVE -->

            <div class="admin-setting-row mb-4">

                <div>

                    <strong>
                        Active
                    </strong>

                    <div class="small text-muted">
                        Allow this period to be used
                        by airport pricing.
                    </div>

                </div>


                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="isActive"
                        id="ratePeriodActive"
                        checked
                    >

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="admin-drawer-actions">

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
                    id="saveRatePeriodButton"
                >
                    Save Rate Period
                </button>

            </div>

        </form>

    </div>

</div>


<!-- AJAX TOAST -->

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
        id="ratePeriodToast"
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
                id="ratePeriodToastMessage"
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


<script src="/assets/js/admin-rate-periods.js"></script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>
