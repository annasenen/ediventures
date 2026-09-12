<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';


$pageTitle =
    "Airport Prices | EdiVentures Admin";

$metaDescription =
    "Manage vehicle-specific airport transfer prices.";

$canonicalUrl =
    "https://www.ediventures.co.uk/admin/airport-prices.php";


$settingsService =
    new AirportSettingsService($pdo);


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
|
| We are not saving anything on this page yet, but the token is added now
| because the AJAX save endpoint will use it in the next stage.
|
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
| SAVE AIRPORT PRICE MATRIX
|--------------------------------------------------------------------------
|
| JavaScript is not required for this.
|
| All submitted values are checked again on the server before they reach
| the database. The transaction ensures that the matrix is saved as one
| operation: either everything succeeds or nothing is changed.
|
*/

$saveError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    $submittedToken =
        $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !hash_equals(
            $csrfToken,
            $submittedToken
        )
    ) {
        http_response_code(403);

        exit(
            'Your session security token is invalid. ' .
            'Please refresh the page and try again.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION ALLOW-LIST
    |--------------------------------------------------------------------------
    */

    $action =
        $_POST['action'] ?? '';

    if (
        !is_string($action) ||
        $action !== 'save_price_matrix'
    ) {
        http_response_code(400);

        exit(
            'Invalid airport pricing request.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONTEXT
    |--------------------------------------------------------------------------
    */

    $postedVehicleID =
        (int)(
            $_POST['vehicleID'] ?? 0
        );

    $postedAirportID =
        (int)(
            $_POST['airportID'] ?? 0
        );

    $postedZoneID =
        (int)(
            $_POST['zoneID'] ?? 0
        );

    $postedJourneyType =
        $_POST['journeyType'] ?? '';

    if (
        $postedVehicleID < 1 ||
        $postedAirportID < 1 ||
        $postedZoneID < 1
    ) {
        http_response_code(422);

        exit(
            'The pricing configuration is incomplete.'
        );
    }

    if (
        !is_string($postedJourneyType) ||
        !in_array(
            $postedJourneyType,
            [
                'pickup',
                'dropoff'
            ],
            true
        )
    ) {
        http_response_code(422);

        exit(
            'Invalid journey type.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD TRUSTED MATRIX DEFINITION
    |--------------------------------------------------------------------------
    |
    | Do not trust rate-period IDs or passenger-band IDs simply because
    | they arrived from the browser.
    |
    | We rebuild the allowed matrix from the database.
    |
    */

    try {

        $trustedMatrix =
            $settingsService
                ->getAirportPriceMatrix(
                    $postedVehicleID,
                    $postedAirportID,
                    $postedZoneID,
                    $postedJourneyType
                );


        $allowedPeriodIDs = [];

        foreach (
            $trustedMatrix['pricingPeriods']
            as $period
        ) {
            $allowedPeriodIDs[
                (int)$period['pricingPeriodID']
            ] = true;
        }


        $allowedBandIDs = [];

        foreach (
            $trustedMatrix['passengerBands']
            as $band
        ) {
            $allowedBandIDs[
                (int)$band['passengerBandID']
            ] = true;
        }


        /*
        |--------------------------------------------------------------------------
        | PRICE VALUES
        |--------------------------------------------------------------------------
        |
        | Expected structure:
        |
        | prices[pricingPeriodID][passengerBandID] = 36.00
        |
        */

        $submittedPrices =
            $_POST['prices'] ?? [];

        if (!is_array($submittedPrices)) {
            throw new InvalidArgumentException(
                'Invalid airport pricing data.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();

        try {

            foreach (
                $submittedPrices
                as $pricingPeriodIDRaw =>
                    $bandPrices
            ) {

                $pricingPeriodID =
                    (int)$pricingPeriodIDRaw;

                /*
                 * A forged/unexpected period ID is rejected.
                 */
                if (
                    $pricingPeriodID < 1 ||
                    !isset(
                        $allowedPeriodIDs[
                            $pricingPeriodID
                        ]
                    )
                ) {
                    throw new InvalidArgumentException(
                        'Invalid rate period in the submitted prices.'
                    );
                }


                if (!is_array($bandPrices)) {
                    throw new InvalidArgumentException(
                        'Invalid passenger pricing data.'
                    );
                }


                foreach (
                    $bandPrices
                    as $passengerBandIDRaw =>
                        $priceRaw
                ) {

                    $passengerBandID =
                        (int)$passengerBandIDRaw;


                    /*
                     * A forged/unexpected passenger band is rejected.
                     */
                    if (
                        $passengerBandID < 1 ||
                        !isset(
                            $allowedBandIDs[
                                $passengerBandID
                            ]
                        )
                    ) {
                        throw new InvalidArgumentException(
                            'Invalid passenger band in the submitted prices.'
                        );
                    }


                    /*
                     * Blank means:
                     *
                     * "No price is configured for this combination."
                     *
                     * We do not turn a blank input into £0.00.
                     */
                    if (
                        !is_string($priceRaw) &&
                        !is_numeric($priceRaw)
                    ) {
                        throw new InvalidArgumentException(
                            'Invalid airport price.'
                        );
                    }

                    $priceText =
                        trim(
                            (string)$priceRaw
                        );


                    if ($priceText === '') {

                        /*
                        * Blank means this combination must not have
                        * an active online price.
                        *
                        * Never trust a price ID supplied by the browser.
                        * The existing price ID comes from the trusted
                        * matrix that we rebuilt from the database.
                        */

                        $existingPrice =
                            $trustedMatrix[
                                'prices'
                            ][
                                $pricingPeriodID
                            ][
                                $passengerBandID
                            ] ?? null;


                        if (
                            $existingPrice !== null &&
                            isset(
                                $existingPrice[
                                    'airportPriceID'
                                ]
                            )
                        ) {

                            $settingsService
                                ->setAirportPriceActive(
                                    (int)$existingPrice[
                                        'airportPriceID'
                                    ],
                                    false
                                );
                        }


                        continue;
                    }


                    /*
                     * Accept ordinary decimal money values only.
                     *
                     * Examples:
                     *
                     * 36
                     * 36.5
                     * 36.50
                     */
                    if (
                        !preg_match(
                            '/^\d{1,8}(?:\.\d{1,2})?$/',
                            $priceText
                        )
                    ) {
                        throw new InvalidArgumentException(
                            'Please enter prices using pounds and up to two decimal places.'
                        );
                    }


                    $basePrice =
                        (float)$priceText;


                    /*
                     * Service performs another independent validation.
                     */
                    $settingsService
                        ->saveAirportPrice(
                            $postedVehicleID,
                            $postedAirportID,
                            $postedZoneID,
                            $postedJourneyType,
                            $pricingPeriodID,
                            $passengerBandID,
                            $basePrice,
                            true
                        );
                }
            }


            $pdo->commit();


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }


        /*
        |--------------------------------------------------------------------------
        | POST / REDIRECT / GET
        |--------------------------------------------------------------------------
        |
        | Prevent accidental duplicate form submission if the admin refreshes.
        |
        */

        $redirectQuery =
            http_build_query([
                'vehicleID' =>
                    $postedVehicleID,

                'airportID' =>
                    $postedAirportID,

                'zoneID' =>
                    $postedZoneID,

                'journeyType' =>
                    $postedJourneyType,

                'saved' =>
                    1
            ]);


        header(
            'Location: /admin/airport-prices.php?' .
            $redirectQuery
        );

        exit;


    } catch (
        InvalidArgumentException $e
    ) {

        /*
         * This is an admin validation message and is safe to display.
         */
        $saveError =
            $e->getMessage();


    } catch (
        PDOException $e
    ) {

        error_log(
            'Airport price save database error: ' .
            $e->getMessage()
        );

        $saveError =
            'The airport prices could not be saved.';


    } catch (
        Throwable $e
    ) {

        error_log(
            'Airport price save error: ' .
            $e->getMessage()
        );

        $saveError =
            'Something went wrong while saving the airport prices.';
    }
}


/*
|--------------------------------------------------------------------------
| ESCAPE HELPER
|--------------------------------------------------------------------------
*/

function airportPriceEscape(
    mixed $value
): string {

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| RATE PERIOD DISPLAY HELPERS
|--------------------------------------------------------------------------
*/

function airportPriceDisplayTime(
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


function airportPriceDisplayDays(
    ?string $daysCsv
): string {

    if (
        $daysCsv === null ||
        trim($daysCsv) === ''
    ) {
        return '';
    }

    $dayNames = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun'
    ];

    $result = [];

    foreach (
        explode(',', $daysCsv)
        as $day
    ) {

        $dayNumber =
            (int)$day;

        if (
            isset(
                $dayNames[
                    $dayNumber
                ]
            )
        ) {
            $result[] =
                $dayNames[
                    $dayNumber
                ];
        }
    }

    return implode(
        ', ',
        $result
    );
}


/*
|--------------------------------------------------------------------------
| LOAD MASTER DATA
|--------------------------------------------------------------------------
*/

$vehicles =
    array_values(
        array_filter(
            $settingsService
                ->getAvailableVehicles(),
            static function (
                array $vehicle
            ): bool {
                return
                    (int)$vehicle['isActive']
                    === 1;
            }
        )
    );


$airports =
    array_values(
        array_filter(
            $settingsService
                ->getAirports(),
            static function (
                array $airport
            ): bool {
                return
                    (int)$airport['isActive']
                    === 1;
            }
        )
    );


$zones =
    array_values(
        array_filter(
            $settingsService
                ->getZones(),
            static function (
                array $zone
            ): bool {
                return
                    (int)$zone['isActive']
                    === 1;
            }
        )
    );


/*
|--------------------------------------------------------------------------
| SELECT CURRENT CONTEXT
|--------------------------------------------------------------------------
|
| For the first page load we automatically choose the first active option.
|
| Later AJAX will allow these selectors to change the matrix instantly
| without reloading the page.
|
*/

$vehicleID =
    isset($_GET['vehicleID'])
        ? (int)$_GET['vehicleID']
        : (
            isset($vehicles[0])
                ? (int)$vehicles[0]['vehicleID']
                : 0
        );


$airportID =
    isset($_GET['airportID'])
        ? (int)$_GET['airportID']
        : (
            isset($airports[0])
                ? (int)$airports[0]['airportID']
                : 0
        );


$zoneID =
    isset($_GET['zoneID'])
        ? (int)$_GET['zoneID']
        : (
            isset($zones[0])
                ? (int)$zones[0]['zoneID']
                : 0
        );


$journeyType =
    $_GET['journeyType'] ??
    'pickup';


if (
    !in_array(
        $journeyType,
        [
            'pickup',
            'dropoff'
        ],
        true
    )
) {
    $journeyType =
        'pickup';
}


/*
|--------------------------------------------------------------------------
| LOAD MATRIX
|--------------------------------------------------------------------------
*/

$matrix = null;

$matrixError = '';


if (
    $vehicleID > 0 &&
    $airportID > 0 &&
    $zoneID > 0
) {

    try {

        $matrix =
            $settingsService
                ->getAirportPriceMatrix(
                    $vehicleID,
                    $airportID,
                    $zoneID,
                    $journeyType
                );

    } catch (
        InvalidArgumentException $e
    ) {

        $matrixError =
            $e->getMessage();

    } catch (
        PDOException $e
    ) {

        error_log(
            'Airport price matrix database error: ' .
            $e->getMessage()
        );

        $matrixError =
            'The airport prices could not be loaded.';
    }
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
                        Airport Prices
                    </h1>

                    <p class="page-hero-text">
                        Set base airport transfer fares by vehicle,
                        area, journey direction, rate period and
                        passenger group.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <section class="py-5 bg-light">

        <div class="container">

            <div class="mb-4">

                <a
                    href="/admin/airport-settings.php"
                    class="btn btn-outline-secondary btn-sm"
                >
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Airport Settings
                </a>

            </div>


            <!-- =====================================================
                 PAGE INTRO
            ====================================================== -->

            <div
                class="
                    d-flex
                    justify-content-between
                    align-items-start
                    flex-wrap
                    gap-3
                    mb-4
                "
            >

                <div>

                    <h2 class="section-title mb-1">
                        Base Airport Fares
                    </h2>

                    <p class="text-muted mb-0">
                        Choose the journey configuration, then manage
                        the base fare for each rate period and
                        passenger group.
                    </p>

                </div>

            </div>


            <!-- =====================================================
                 CONFIGURATION SELECTORS
            ====================================================== -->

            <div
                class="card border-0 shadow-sm mb-4 admin-scroll-target"
                id="airportPriceSelectorSection"
            >

                <div class="card-body p-4">

                    <form
                        method="get"
                        action="/admin/airport-prices.php#airportPriceSelectorSection"
                        id="airportPriceSelectorForm"
                    >

                        <div class="row g-3 align-items-end">


                            <!-- AIRPORT -->

                            <div class="col-lg-3 col-md-6">

                                <label
                                    for="airportID"
                                    class="form-label fw-semibold"
                                >
                                    Airport
                                </label>

                                <input
                                    type="hidden"
                                    name="loaded"
                                    value="1"
                                >

                                <select
                                    id="airportID"
                                    name="airportID"
                                    class="form-select"
                                    required
                                >

                                    <?php foreach ($airports as $airport): ?>

                                        <option
                                            value="<?= (int)$airport['airportID'] ?>"
                                            <?= (
                                                (int)$airport['airportID']
                                                === $airportID
                                            ) ? 'selected' : '' ?>
                                        >
                                            <?= airportPriceEscape(
                                                $airport['airportName']
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ZONE -->

                            <div class="col-lg-3 col-md-6">

                                <label
                                    for="zoneID"
                                    class="form-label fw-semibold"
                                >
                                    Zone
                                </label>

                                <select
                                    id="zoneID"
                                    name="zoneID"
                                    class="form-select"
                                    required
                                >

                                    <?php foreach ($zones as $zone): ?>

                                        <option
                                            value="<?= (int)$zone['zoneID'] ?>"
                                            <?= (
                                                (int)$zone['zoneID']
                                                === $zoneID
                                            ) ? 'selected' : '' ?>
                                        >
                                            <?= airportPriceEscape(
                                                $zone['zoneName']
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- JOURNEY TYPE -->

                            <div class="col-lg-3 col-md-6">

                                <label
                                    for="journeyType"
                                    class="form-label fw-semibold"
                                >
                                    Journey
                                </label>

                                <select
                                    id="journeyType"
                                    name="journeyType"
                                    class="form-select"
                                >

                                    <option
                                        value="pickup"
                                        <?= (
                                            $journeyType ===
                                            'pickup'
                                        ) ? 'selected' : '' ?>
                                    >
                                        Airport Pickup
                                    </option>

                                    <option
                                        value="dropoff"
                                        <?= (
                                            $journeyType ===
                                            'dropoff'
                                        ) ? 'selected' : '' ?>
                                    >
                                        Airport Drop-off
                                    </option>

                                </select>

                            </div>


                            <!-- VEHICLE -->

                            <div class="col-lg-3 col-md-6">

                                <label
                                    for="vehicleID"
                                    class="form-label fw-semibold"
                                >
                                    Vehicle
                                </label>

                                <select
                                    id="vehicleID"
                                    name="vehicleID"
                                    class="form-select"
                                    required
                                >

                                    <?php foreach ($vehicles as $vehicle): ?>

                                        <option
                                            value="<?= (int)$vehicle['vehicleID'] ?>"
                                            <?= (
                                                (int)$vehicle['vehicleID']
                                                === $vehicleID
                                            ) ? 'selected' : '' ?>
                                        >
                                            <?= airportPriceEscape(
                                                $vehicle['vehicleName']
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>


                        <div
                            class="
                                d-flex
                                justify-content-end
                                mt-3
                            "
                        >

                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="airportPriceLoadButton"
                            >
                                <i class="fa-solid fa-table me-1"></i>
                                Load Prices
                            </button>

                        </div>

                    </form>

                </div>

            </div>


            <!-- =====================================================
                 MISSING MASTER DATA
            ====================================================== -->

            <?php if (
                empty($vehicles) ||
                empty($airports) ||
                empty($zones)
            ): ?>

                <div class="alert alert-warning">

                    <strong>
                        Airport pricing cannot be configured yet.
                    </strong>

                    <div class="mt-1">
                        Make sure there is at least one active airport,
                        zone and vehicle.
                    </div>

                </div>

            <?php endif; ?>


            <!-- =====================================================
                 MATRIX ERROR
            ====================================================== -->

            <?php if ($matrixError !== ''): ?>

                <div class="alert alert-danger">
                    <?= airportPriceEscape(
                        $matrixError
                    ) ?>
                </div>

            <?php endif; ?>


            <?php if ($matrix !== null): ?>

                <?php if (
                    isset($_GET['saved']) &&
                    $_GET['saved'] === '1'
                ): ?>

                    <div
                        class="alert alert-success"
                        id="airportPriceSaveMessage"
                    >
                        <i class="fa-solid fa-circle-check me-1"></i>
                        Airport prices saved successfully.
                    </div>

                <?php endif; ?>


                <?php if (
                    isset($saveError) &&
                    $saveError !== ''
                ): ?>

                    <div class="alert alert-danger">
                        <?= airportPriceEscape(
                            $saveError
                        ) ?>
                    </div>

                <?php endif; ?>


                <!-- =================================================
                     CURRENT CONTEXT SUMMARY
                ================================================== -->
                <div id="airportPriceMatrixSection">

                    <div
                        class="
                            d-flex
                            justify-content-between
                            align-items-center
                            flex-wrap
                            gap-3
                            mb-3
                        "
                    >

                        <div>

                            <h3 class="h5 mb-1">

                                <?= airportPriceEscape(
                                    $matrix['airport']['airportName']
                                ) ?>

                                <span class="text-muted">
                                    →
                                </span>

                                <?= airportPriceEscape(
                                    $matrix['zone']['zoneName']
                                ) ?>

                            </h3>

                            <div class="small text-muted">

                                <?= airportPriceEscape(
                                    $matrix['vehicle']['vehicleName']
                                ) ?>

                                ·

                                <?= (
                                    $matrix['journeyType']
                                    === 'pickup'
                                )
                                    ? 'Airport Pickup'
                                    : 'Airport Drop-off'
                                ?>

                            </div>

                        </div>


                        <!-- COMPLETENESS -->

                        <div>

                            <?php if (
                                $matrix['isComplete']
                            ): ?>

                                <span
                                    class="
                                        badge
                                        rounded-pill
                                        text-bg-success
                                        px-3
                                        py-2
                                    "
                                >
                                    <i
                                        class="
                                            fa-solid
                                            fa-circle-check
                                            me-1
                                        "
                                    ></i>

                                    All prices configured
                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                        badge
                                        rounded-pill
                                        text-bg-warning
                                        px-3
                                        py-2
                                    "
                                >
                                    <i
                                        class="
                                            fa-solid
                                            fa-triangle-exclamation
                                            me-1
                                        "
                                    ></i>

                                    <?= (int)$matrix['missingCount'] ?>
                                    price<?= (
                                        (int)$matrix['missingCount']
                                        === 1
                                    ) ? '' : 's' ?>
                                    need configuring
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- =================================================
                        PRICE MATRIX
                    ================================================== -->

                    <form
                        method="post"
                        action="/admin/airport-prices.php"
                        id="airportPriceMatrixForm"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= airportPriceEscape(
                                $csrfToken
                            ) ?>"
                        >

                        <input
                            type="hidden"
                            name="action"
                            value="save_price_matrix"
                        >

                        <input
                            type="hidden"
                            name="vehicleID"
                            value="<?= (int)$vehicleID ?>"
                        >

                        <input
                            type="hidden"
                            name="airportID"
                            value="<?= (int)$airportID ?>"
                        >

                        <input
                            type="hidden"
                            name="zoneID"
                            value="<?= (int)$zoneID ?>"
                        >

                        <input
                            type="hidden"
                            name="journeyType"
                            value="<?= airportPriceEscape(
                                $journeyType
                            ) ?>"
                        >


                        <div class="card border-0 shadow-sm">

                        <div class="card-body p-0">

                            <?php if (
                                empty(
                                    $matrix[
                                        'pricingPeriods'
                                    ]
                                )
                            ): ?>

                                <div class="p-4 text-muted">

                                    No active Rate Periods are available
                                    for this pricing configuration.

                                </div>

                            <?php elseif (
                                empty(
                                    $matrix[
                                        'passengerBands'
                                    ]
                                )
                            ): ?>

                                <div class="p-4 text-muted">

                                    No active airport passenger bands
                                    are configured.

                                </div>

                            <?php else: ?>

                                <div class="table-responsive">

                                    <table
                                        class="
                                            table
                                            table-hover
                                            align-middle
                                            mb-0
                                        "
                                    >

                                        <thead>

                                            <tr>

                                                <th
                                                    scope="col"
                                                    class="ps-4 py-3"
                                                >
                                                    Rate Period
                                                </th>

                                                <?php foreach (
                                                    $matrix[
                                                        'passengerBands'
                                                    ]
                                                    as $band
                                                ): ?>

                                                    <th
                                                        scope="col"
                                                        class="
                                                            text-center
                                                            py-3
                                                        "
                                                    >

                                                        <div>
                                                            <?= airportPriceEscape(
                                                                $band[
                                                                    'bandName'
                                                                ]
                                                            ) ?>
                                                        </div>

                                                        <div
                                                            class="
                                                                small
                                                                text-muted
                                                                fw-normal
                                                            "
                                                        >

                                                            <?= (int)$band[
                                                                'minPassengers'
                                                            ] ?>

                                                            –

                                                            <?= (int)$band[
                                                                'maxPassengers'
                                                            ] ?>

                                                            passengers

                                                        </div>

                                                    </th>

                                                <?php endforeach; ?>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            <?php foreach (
                                                $matrix[
                                                    'pricingPeriods'
                                                ]
                                                as $period
                                            ): ?>

                                                <?php

                                                $pricingPeriodID =
                                                    (int)$period[
                                                        'pricingPeriodID'
                                                    ];

                                                $periodActive =
                                                    (int)$period[
                                                        'isActive'
                                                    ] === 1;

                                                ?>

                                                <tr>

                                                    <th
                                                        scope="row"
                                                        class="ps-4 py-3"
                                                    >

                                                        <div
                                                            class="
                                                                d-flex
                                                                align-items-center
                                                                gap-2
                                                                flex-wrap
                                                            "
                                                        >

                                                            <span>
                                                                <?= airportPriceEscape(
                                                                    $period[
                                                                        'periodName'
                                                                    ]
                                                                ) ?>
                                                            </span>


                                                            <?php if (
                                                                !$periodActive
                                                            ): ?>

                                                                <span
                                                                    class="
                                                                        badge
                                                                        text-bg-secondary
                                                                    "
                                                                >
                                                                    Inactive
                                                                </span>

                                                            <?php endif; ?>

                                                        </div>


                                                        <div
                                                            class="
                                                                small
                                                                text-muted
                                                                fw-normal
                                                                mt-1
                                                            "
                                                        >

                                                            <?= airportPriceEscape(
                                                                airportPriceDisplayDays(
                                                                    $period[
                                                                        'daysCsv'
                                                                    ] ?? ''
                                                                )
                                                            ) ?>

                                                            ·

                                                            <?= airportPriceEscape(
                                                                airportPriceDisplayTime(
                                                                    $period[
                                                                        'startTime'
                                                                    ] ?? ''
                                                                )
                                                            ) ?>

                                                            –

                                                            <?= airportPriceEscape(
                                                                airportPriceDisplayTime(
                                                                    $period[
                                                                        'endTime'
                                                                    ] ?? ''
                                                                )
                                                            ) ?>

                                                        </div>

                                                    </th>


                                                    <?php foreach (
                                                        $matrix[
                                                            'passengerBands'
                                                        ]
                                                        as $band
                                                    ): ?>

                                                        <?php

                                                        $passengerBandID =
                                                            (int)$band[
                                                                'passengerBandID'
                                                            ];

                                                        $price =
                                                            $matrix[
                                                                'prices'
                                                            ][
                                                                $pricingPeriodID
                                                            ][
                                                                $passengerBandID
                                                            ] ?? null;

                                                        ?>

                                                        <td
                                                            class="
                                                                text-center
                                                                align-top
                                                                py-3
                                                                px-2
                                                            "
                                                        >

                                                            <?php if ($periodActive): ?>

                                                                <div
                                                                    class="
                                                                        d-flex
                                                                        flex-column
                                                                        align-items-center
                                                                    "
                                                                >

                                                                    <div
                                                                        class="
                                                                            input-group
                                                                            input-group-sm
                                                                        "
                                                                        style="max-width: 130px;"
                                                                    >

                                                                        <span class="input-group-text">
                                                                            £
                                                                        </span>

                                                                        <input
                                                                            type="text"
                                                                            inputmode="decimal"
                                                                            class="form-control text-end"
                                                                            name="prices[<?= $pricingPeriodID ?>][<?= $passengerBandID ?>]"
                                                                            value="<?= (
                                                                                $price !== null &&
                                                                                $price['isActive']
                                                                            )
                                                                                ? airportPriceEscape(
                                                                                    number_format(
                                                                                        (float)$price['basePrice'],
                                                                                        2,
                                                                                        '.',
                                                                                        ''
                                                                                    )
                                                                                )
                                                                                : ''
                                                                            ?>"
                                                                            placeholder="0.00"
                                                                            maxlength="11"
                                                                            autocomplete="off"
                                                                            aria-label="<?= airportPriceEscape(
                                                                                $period['periodName']
                                                                            ) ?> price for <?= airportPriceEscape(
                                                                                $band['bandName']
                                                                            ) ?>"
                                                                        >

                                                                    </div>


                                                                    <!--
                                                                        Always reserve the same space beneath the input.

                                                                        This keeps active and inactive price boxes
                                                                        aligned at exactly the same height.
                                                                    -->
                                                                    <div
                                                                        class="
                                                                            small
                                                                            mt-1
                                                                            text-muted
                                                                        "
                                                                        style="
                                                                            min-height: 1.25rem;
                                                                            line-height: 1.25rem;
                                                                        "
                                                                    >

                                                                        <?php if (
                                                                            $price !== null &&
                                                                            !$price['isActive']
                                                                        ): ?>

                                                                            Inactive · previous £<?= number_format(
                                                                                (float)$price['basePrice'],
                                                                                2
                                                                            ) ?>

                                                                        <?php else: ?>

                                                                            &nbsp;

                                                                        <?php endif; ?>

                                                                    </div>

                                                                </div>


                                                            <?php else: ?>

                                                                <div
                                                                    class="
                                                                        d-flex
                                                                        flex-column
                                                                        align-items-center
                                                                    "
                                                                >

                                                                    <div
                                                                        style="
                                                                            min-height: 31px;
                                                                            display: flex;
                                                                            align-items: center;
                                                                        "
                                                                    >

                                                                        <?php if ($price !== null): ?>

                                                                            <span class="text-muted">
                                                                                £<?= number_format(
                                                                                    (float)$price['basePrice'],
                                                                                    2
                                                                                ) ?>
                                                                            </span>

                                                                        <?php else: ?>

                                                                            <span class="text-muted">
                                                                                —
                                                                            </span>

                                                                        <?php endif; ?>

                                                                    </div>

                                                                    <div
                                                                        class="
                                                                            small
                                                                            text-muted
                                                                            mt-1
                                                                        "
                                                                        style="
                                                                            min-height: 1.25rem;
                                                                            line-height: 1.25rem;
                                                                        "
                                                                    >
                                                                        Historical
                                                                    </div>

                                                                </div>

                                                            <?php endif; ?>

                                                        </td>

                                                    <?php endforeach; ?>

                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>

                                    </table>

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                        <div
                            class="
                                d-flex
                                justify-content-end
                                mt-3
                            "
                        >

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                            >
                                <i class="fa-solid fa-floppy-disk me-1"></i>
                                Save Prices
                            </button>

                        </div>

                    </form>


                    <div class="small text-muted mt-3">

                        <i
                            class="
                                fa-solid
                                fa-circle-info
                                me-1
                            "
                        ></i>

                        Airport charges are managed separately and are
                        added to these base fares during booking.

                    </div>

                </div>


            <?php endif; ?>


            <input
                type="hidden"
                id="airportPriceCsrfToken"
                value="<?= airportPriceEscape(
                    $csrfToken
                ) ?>"
            >

        </div>

    </section>

</main>

<script src="/assets/js/airport-prices.js"></script>

<?php

include __DIR__ . '/../../includes/footer.php';

?>