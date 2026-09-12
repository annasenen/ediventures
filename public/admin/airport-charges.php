<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';


$pageTitle =
    'Airport Charges | EdiVentures Admin';

$metaDescription =
    'Manage airport pickup and drop-off access charges.';

$canonicalUrl =
    'https://www.ediventures.co.uk/admin/airport-charges.php';


$settingsService =
    new AirportSettingsService($pdo);


/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(
            random_bytes(32)
        );
}

$csrfToken =
    $_SESSION['csrf_token'];


/*
|--------------------------------------------------------------------------
| ESCAPE HELPER
|--------------------------------------------------------------------------
*/

function airportChargeEscape(
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
| SAVE ERROR
|--------------------------------------------------------------------------
*/

$saveError = '';


/*
|--------------------------------------------------------------------------
| PROCESS POST
|--------------------------------------------------------------------------
|
| No JavaScript is required to save charges.
|
| Browser values are validated server-side and the service performs
| another validation before writing to the database.
|
*/

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
    | ACTION
    |--------------------------------------------------------------------------
    */

    $action =
        $_POST['action'] ?? '';

    if (
        !is_string($action) ||
        $action !== 'save_airport_charge'
    ) {
        http_response_code(400);

        exit(
            'Invalid airport charge request.'
        );
    }


    try {

        /*
        |--------------------------------------------------------------------------
        | AIRPORT
        |--------------------------------------------------------------------------
        */

        $airportID =
            (int)(
                $_POST['airportID'] ?? 0
            );

        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY AIRPORT AGAINST DATABASE
        |--------------------------------------------------------------------------
        */

        $airport =
            $settingsService
                ->getAirportById(
                    $airportID
                );

        if (!$airport) {
            throw new InvalidArgumentException(
                'The selected airport does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CHARGE VALUES
        |--------------------------------------------------------------------------
        */

        $pickupRaw =
            $_POST['pickupCharge'] ?? '';

        $dropoffRaw =
            $_POST['dropoffCharge'] ?? '';


        if (
            !is_string($pickupRaw) &&
            !is_numeric($pickupRaw)
        ) {
            throw new InvalidArgumentException(
                'Invalid pickup charge.'
            );
        }

        if (
            !is_string($dropoffRaw) &&
            !is_numeric($dropoffRaw)
        ) {
            throw new InvalidArgumentException(
                'Invalid drop-off charge.'
            );
        }


        $pickupText =
            trim(
                (string)$pickupRaw
            );

        $dropoffText =
            trim(
                (string)$dropoffRaw
            );


        /*
         * Blank means £0.00 for airport access charges.
         *
         * This is different from Airport Prices:
         * an airport may legitimately have no access charge.
         */
        if ($pickupText === '') {
            $pickupText = '0.00';
        }

        if ($dropoffText === '') {
            $dropoffText = '0.00';
        }


        /*
         * Ordinary positive decimal money only.
         *
         * Examples:
         * 0
         * 6
         * 6.50
         * 12.00
         */
        $moneyPattern =
            '/^\d{1,8}(?:\.\d{1,2})?$/';


        if (
            !preg_match(
                $moneyPattern,
                $pickupText
            )
        ) {
            throw new InvalidArgumentException(
                'Please enter a valid pickup charge using pounds and up to two decimal places.'
            );
        }


        if (
            !preg_match(
                $moneyPattern,
                $dropoffText
            )
        ) {
            throw new InvalidArgumentException(
                'Please enter a valid drop-off charge using pounds and up to two decimal places.'
            );
        }


        $pickupCharge =
            (float)$pickupText;

        $dropoffCharge =
            (float)$dropoffText;


        /*
        |--------------------------------------------------------------------------
        | ACTIVE STATE
        |--------------------------------------------------------------------------
        */

        $isActive =
            (
                $_POST['isActive'] ?? '0'
            ) === '1';


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        $settingsService
            ->saveAirportCharge(
                $airportID,
                $pickupCharge,
                $dropoffCharge,
                $isActive
            );


        /*
        |--------------------------------------------------------------------------
        | POST / REDIRECT / GET
        |--------------------------------------------------------------------------
        */

        $redirectQuery =
            http_build_query([
                'airportID' =>
                    $airportID,

                'saved' =>
                    1
            ]);


        header(
            'Location: /admin/airport-charges.php?' .
            $redirectQuery .
            '#airportChargeSaveMessage'
        );

        exit;


    } catch (
        InvalidArgumentException $e
    ) {

        $saveError =
            $e->getMessage();


    } catch (
        PDOException $e
    ) {

        error_log(
            'Airport charge save database error: ' .
            $e->getMessage()
        );

        $saveError =
            'The airport charges could not be saved.';


    } catch (
        Throwable $e
    ) {

        error_log(
            'Airport charge save error: ' .
            $e->getMessage()
        );

        $saveError =
            'Something went wrong while saving the airport charges.';
    }
}


/*
|--------------------------------------------------------------------------
| LOAD AIRPORTS
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| SELECT CURRENT AIRPORT
|--------------------------------------------------------------------------
*/

$selectedAirportID =
    isset($_GET['airportID'])
        ? (int)$_GET['airportID']
        : (
            isset($airports[0])
                ? (int)$airports[0]['airportID']
                : 0
        );


/*
|--------------------------------------------------------------------------
| LOAD CURRENT CHARGE
|--------------------------------------------------------------------------
*/

$currentCharge = null;


if ($selectedAirportID > 0) {

    $allCharges =
        $settingsService
            ->getAirportCharges();

    foreach ($allCharges as $charge) {

        if (
            (int)$charge['airportID']
            === $selectedAirportID
        ) {
            $currentCharge =
                $charge;

            break;
        }
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
                        Airport Charges
                    </h1>

                    <p class="page-hero-text">
                        Manage airport pickup and drop-off access charges
                        separately from the journey fare.
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
                        Airport Access Charges
                    </h2>

                    <p class="text-muted mb-0">
                        These charges are added to the base airport fare
                        when calculating the customer's total price.
                    </p>

                </div>

            </div>


            <!-- =====================================================
                 SUCCESS
            ====================================================== -->

            <?php if (
                isset($_GET['saved']) &&
                $_GET['saved'] === '1'
            ): ?>

                <div
                    class="alert alert-success"
                    id="airportChargeSaveMessage"
                >
                    <i
                        class="
                            fa-solid
                            fa-circle-check
                            me-1
                        "
                    ></i>

                    Airport charges saved successfully.
                </div>

            <?php endif; ?>


            <!-- =====================================================
                 ERROR
            ====================================================== -->

            <?php if ($saveError !== ''): ?>

                <div class="alert alert-danger">

                    <?= airportChargeEscape(
                        $saveError
                    ) ?>

                </div>

            <?php endif; ?>


            <?php if (empty($airports)): ?>

                <div class="alert alert-warning">

                    <strong>
                        No active airports are available.
                    </strong>

                    <div class="mt-1">
                        Add or activate an airport before configuring
                        airport charges.
                    </div>

                </div>


            <?php else: ?>


                <!-- =================================================
                     AIRPORT SELECTOR
                ================================================== -->

                <div
                    class="card border-0 shadow-sm mb-4 admin-scroll-target"
                    id="airportChargeSelector"
                >

                    <div class="card-body p-4">

                        <form
                            method="get"
                            action="/admin/airport-charges.php#airportChargeSelector"
                            id="airportChargeSelectorForm"
                        >

                            <input
                                type="hidden"
                                name="loaded"
                                value="1"
                            >

                            <div
                                class="
                                    row
                                    g-3
                                    align-items-end
                                "
                            >

                                <div class="col-lg-8">

                                    <label
                                        for="airportID"
                                        class="form-label fw-semibold"
                                    >
                                        Airport
                                    </label>

                                    <select
                                        id="airportID"
                                        name="airportID"
                                        class="form-select"
                                        required
                                    >

                                        <?php foreach (
                                            $airports
                                            as $airport
                                        ): ?>

                                            <option
                                                value="<?= (int)$airport['airportID'] ?>"
                                                <?= (
                                                    (int)$airport['airportID']
                                                    === $selectedAirportID
                                                )
                                                    ? 'selected'
                                                    : ''
                                                ?>
                                            >
                                                <?= airportChargeEscape(
                                                    $airport['airportName']
                                                ) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div class="col-lg-4">

                                    <button
                                        type="submit"
                                        id="airportChargeLoadButton"
                                        class="
                                            btn
                                            btn-brand
                                            w-100
                                        "
                                    >
                                        <i
                                            class="
                                                fa-solid
                                                fa-arrow-right
                                                me-1
                                            "
                                        ></i>

                                        Load Charges
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     CHARGE FORM
                ================================================== -->

                <div id="airportChargeFormSection">

                    <?php

                    $selectedAirportName = '';

                    foreach (
                        $airports
                        as $airport
                    ) {

                        if (
                            (int)$airport['airportID']
                            === $selectedAirportID
                        ) {
                            $selectedAirportName =
                                (string)$airport[
                                    'airportName'
                                ];

                            break;
                        }
                    }

                    ?>


                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-4">

                            <div class="mb-4">

                                <h3 class="h5 mb-1">

                                    <?= airportChargeEscape(
                                        $selectedAirportName
                                    ) ?>

                                </h3>

                                <p class="text-muted mb-0">
                                    Set the airport access fee for each
                                    journey direction.
                                </p>

                            </div>


                            <form
                                method="post"
                                action="/admin/airport-charges.php"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= airportChargeEscape(
                                        $csrfToken
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="save_airport_charge"
                                >

                                <input
                                    type="hidden"
                                    name="airportID"
                                    value="<?= (int)$selectedAirportID ?>"
                                >


                                <div class="row g-4">


                                    <!-- PICKUP -->

                                    <div class="col-md-6 col-lg-4">

                                        <label
                                            for="pickupCharge"
                                            class="form-label fw-semibold"
                                        >
                                            Pickup Charge
                                        </label>

                                        <div class="input-group admin-money-input">

                                            <span class="input-group-text">
                                                £
                                            </span>

                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                id="pickupCharge"
                                                name="pickupCharge"
                                                class="
                                                    form-control
                                                    text-end
                                                "
                                                value="<?= airportChargeEscape(
                                                    number_format(
                                                        (float)(
                                                            $currentCharge[
                                                                'pickupCharge'
                                                            ] ?? 0
                                                        ),
                                                        2,
                                                        '.',
                                                        ''
                                                    )
                                                ) ?>"
                                                maxlength="11"
                                                autocomplete="off"
                                                required
                                            >

                                        </div>

                                        <div
                                            class="
                                                form-text
                                                mt-2
                                            "
                                        >
                                            Added when collecting the
                                            customer from this airport.
                                        </div>

                                    </div>


                                    <!-- DROP-OFF -->

                                    <div class="col-md-6 col-lg-4">

                                        <label
                                            for="dropoffCharge"
                                            class="form-label fw-semibold"
                                        >
                                            Drop-off Charge
                                        </label>

                                        <div class="input-group admin-money-input">

                                            <span class="input-group-text">
                                                £
                                            </span>

                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                id="dropoffCharge"
                                                name="dropoffCharge"
                                                class="
                                                    form-control
                                                    text-end
                                                "
                                                value="<?= airportChargeEscape(
                                                    number_format(
                                                        (float)(
                                                            $currentCharge[
                                                                'dropoffCharge'
                                                            ] ?? 0
                                                        ),
                                                        2,
                                                        '.',
                                                        ''
                                                    )
                                                ) ?>"
                                                maxlength="11"
                                                autocomplete="off"
                                                required
                                            >

                                        </div>

                                        <div
                                            class="
                                                form-text
                                                mt-2
                                            "
                                        >
                                            Added when taking the
                                            customer to this airport.
                                        </div>

                                    </div>

                                </div>


                                <!-- ACTIVE -->

                                <div
                                    class="
                                        d-flex
                                        justify-content-between
                                        align-items-center
                                        flex-wrap
                                        gap-3
                                        mt-4
                                        pt-4
                                        border-top
                                    "
                                >

                                    <div>

                                        <div class="fw-semibold">
                                            Charge active
                                        </div>

                                        <div class="small text-muted">
                                            When inactive, no airport access
                                            charge will be added for this airport.
                                        </div>

                                    </div>


                                    <div
                                        class="
                                            form-check
                                            form-switch
                                            m-0
                                        "
                                    >

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="airportChargeActive"
                                            name="isActive"
                                            value="1"
                                            <?= (
                                                $currentCharge === null ||
                                                (int)$currentCharge[
                                                    'isActive'
                                                ] === 1
                                            )
                                                ? 'checked'
                                                : ''
                                            ?>
                                        >

                                        <label
                                            class="form-check-label"
                                            for="airportChargeActive"
                                        >
                                            Active
                                        </label>

                                    </div>

                                </div>


                                <!-- SAVE -->

                                <div
                                    class="
                                        d-flex
                                        justify-content-end
                                        mt-4
                                    "
                                >

                                    <button
                                        type="submit"
                                        class="
                                            btn
                                            btn-brand
                                            px-4
                                        "
                                    >
                                        <i
                                            class="
                                                fa-solid
                                                fa-floppy-disk
                                                me-1
                                            "
                                        ></i>

                                        Save Charges
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     EXPLANATION
                ================================================== -->

                <div
                    class="
                        small
                        text-muted
                        mt-3
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-circle-info
                            me-1
                        "
                    ></i>

                    These are airport access charges only.
                    The base journey fare is managed separately
                    under Airport Prices.

                </div>


            <?php endif; ?>

        </div>

    </section>

</main>


<script src="/assets/js/airport-charges.js"></script>

<?php

include __DIR__ . '/../../includes/footer.php';

?>