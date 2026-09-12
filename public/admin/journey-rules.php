<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/admin-auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/AirportSettingsService.php';


$pageTitle =
    'Journey Rules | EdiVentures Admin';

$metaDescription =
    'Manage airport pickup and drop-off vehicle blocking rules.';

$canonicalUrl =
    'https://www.ediventures.co.uk/admin/journey-rules.php';


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

function journeyRuleEscape(
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
        $action !== 'save_journey_rules'
    ) {
        http_response_code(400);

        exit(
            'Invalid journey rules request.'
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
        | BLOCK MINUTES
        |--------------------------------------------------------------------------
        */

        $pickupMinutesRaw =
            $_POST['pickupBlockMinutes'] ?? '';

        $dropoffMinutesRaw =
            $_POST['dropoffBlockMinutes'] ?? '';


        if (
            !is_string($pickupMinutesRaw) &&
            !is_numeric($pickupMinutesRaw)
        ) {
            throw new InvalidArgumentException(
                'Invalid pickup block duration.'
            );
        }


        if (
            !is_string($dropoffMinutesRaw) &&
            !is_numeric($dropoffMinutesRaw)
        ) {
            throw new InvalidArgumentException(
                'Invalid drop-off block duration.'
            );
        }


        $pickupMinutesText =
            trim(
                (string)$pickupMinutesRaw
            );

        $dropoffMinutesText =
            trim(
                (string)$dropoffMinutesRaw
            );


        if (
            !preg_match(
                '/^\d+$/',
                $pickupMinutesText
            )
        ) {
            throw new InvalidArgumentException(
                'Pickup block duration must be a whole number of minutes.'
            );
        }


        if (
            !preg_match(
                '/^\d+$/',
                $dropoffMinutesText
            )
        ) {
            throw new InvalidArgumentException(
                'Drop-off block duration must be a whole number of minutes.'
            );
        }


        $pickupMinutes =
            (int)$pickupMinutesText;

        $dropoffMinutes =
            (int)$dropoffMinutesText;


        if ($pickupMinutes < 1) {
            throw new InvalidArgumentException(
                'Pickup block duration must be at least 1 minute.'
            );
        }


        if ($dropoffMinutes < 1) {
            throw new InvalidArgumentException(
                'Drop-off block duration must be at least 1 minute.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ACTIVE STATES
        |--------------------------------------------------------------------------
        */

        $pickupActive =
            (
                $_POST['pickupIsActive'] ??
                '0'
            ) === '1';

        $dropoffActive =
            (
                $_POST['dropoffIsActive'] ??
                '0'
            ) === '1';


        /*
        |--------------------------------------------------------------------------
        | SAVE BOTH RULES ATOMICALLY
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();

        try {

            $settingsService
                ->saveJourneyRule(
                    $airportID,
                    'pickup',
                    $pickupMinutes,
                    $pickupActive
                );

            $settingsService
                ->saveJourneyRule(
                    $airportID,
                    'dropoff',
                    $dropoffMinutes,
                    $dropoffActive
                );

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
        */

        $redirectQuery =
            http_build_query([
                'airportID' =>
                    $airportID,

                'saved' =>
                    1
            ]);

        header(
            'Location: /admin/journey-rules.php?' .
            $redirectQuery .
            '#journeyRuleSaveMessage'
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

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log(
            'Journey rules save database error: ' .
            $e->getMessage()
        );

        $saveError =
            'The journey rules could not be saved.';


    } catch (
        Throwable $e
    ) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log(
            'Journey rules save error: ' .
            $e->getMessage()
        );

        $saveError =
            'Something went wrong while saving the journey rules.';
    }
}


/*
|--------------------------------------------------------------------------
| LOAD ACTIVE AIRPORTS
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
| VERIFY SELECTED AIRPORT IS AVAILABLE
|--------------------------------------------------------------------------
*/

$selectedAirport = null;

foreach ($airports as $airport) {

    if (
        (int)$airport['airportID']
        === $selectedAirportID
    ) {
        $selectedAirport =
            $airport;

        break;
    }
}


if (
    $selectedAirport === null &&
    isset($airports[0])
) {

    $selectedAirport =
        $airports[0];

    $selectedAirportID =
        (int)$airports[0]['airportID'];
}


/*
|--------------------------------------------------------------------------
| LOAD CURRENT RULES
|--------------------------------------------------------------------------
*/

$pickupRule = null;
$dropoffRule = null;


if ($selectedAirportID > 0) {

    $allRules =
        $settingsService
            ->getJourneyRules();

    foreach ($allRules as $rule) {

        if (
            (int)$rule['airportID']
            !== $selectedAirportID
        ) {
            continue;
        }

        if (
            $rule['journeyType']
            === 'pickup'
        ) {
            $pickupRule =
                $rule;
        }

        if (
            $rule['journeyType']
            === 'dropoff'
        ) {
            $dropoffRule =
                $rule;
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
                        Journey Rules
                    </h1>

                    <p class="page-hero-text">
                        Control how long each airport journey blocks
                        the assigned vehicle for availability planning.
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
                        Vehicle Blocking Rules
                    </h2>

                    <p class="text-muted mb-0">
                        Set how long the vehicle remains unavailable
                        for airport pickups and drop-offs.
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
                    id="journeyRuleSaveMessage"
                >
                    <i
                        class="
                            fa-solid
                            fa-circle-check
                            me-1
                        "
                    ></i>

                    Journey rules saved successfully.
                </div>

            <?php endif; ?>


            <!-- =====================================================
                 ERROR
            ====================================================== -->

            <?php if ($saveError !== ''): ?>

                <div class="alert alert-danger">

                    <?= journeyRuleEscape(
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
                        journey rules.
                    </div>

                </div>


            <?php else: ?>


                <!-- =================================================
                     AIRPORT SELECTOR
                ================================================== -->

                <div
                    class="
                        card
                        border-0
                        shadow-sm
                        mb-4
                        admin-scroll-target
                    "
                    id="journeyRuleSelectorSection"
                >

                    <div class="card-body p-4">

                        <form
                            method="get"
                            action="/admin/journey-rules.php#journeyRuleSelectorSection"
                            id="journeyRuleSelectorForm"
                        >

                            <div class="row g-3 align-items-end">

                                <div class="col-lg-6">

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
                                                    : '' ?>
                                            >
                                                <?= journeyRuleEscape(
                                                    $airport['airportName']
                                                ) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div class="col-lg-3">

                                    <button
                                        type="submit"
                                        class="btn btn-brand w-100"
                                        id="journeyRuleLoadButton"
                                    >
                                        Load Rules
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     RULES FORM
                ================================================== -->

                <form
                    method="post"
                    action="/admin/journey-rules.php"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= journeyRuleEscape(
                            $csrfToken
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="save_journey_rules"
                    >

                    <input
                        type="hidden"
                        name="airportID"
                        value="<?= $selectedAirportID ?>"
                    >


                    <div class="row g-4">


                        <!-- =========================================
                             PICKUP
                        ========================================== -->

                        <div class="col-lg-6">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body p-4">

                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-start
                                            gap-3
                                            mb-3
                                        "
                                    >

                                        <div>

                                            <h3 class="h5 mb-1">
                                                Airport Pickup
                                            </h3>

                                            <p class="text-muted small mb-0">
                                                Time reserved when collecting
                                                a passenger from the airport.
                                            </p>

                                        </div>


                                        <div class="form-check form-switch">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                                id="pickupIsActive"
                                                name="pickupIsActive"
                                                value="1"
                                                <?= (
                                                    $pickupRule === null ||
                                                    (int)$pickupRule['isActive']
                                                    === 1
                                                )
                                                    ? 'checked'
                                                    : '' ?>
                                            >

                                            <label
                                                class="form-check-label"
                                                for="pickupIsActive"
                                            >
                                                Active
                                            </label>

                                        </div>

                                    </div>


                                    <label
                                        for="pickupBlockMinutes"
                                        class="form-label fw-semibold"
                                    >
                                        Block duration
                                    </label>

                                    <div
                                        class="input-group"
                                        style="max-width: 180px;"
                                    >

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="pickupBlockMinutes"
                                            name="pickupBlockMinutes"
                                            min="1"
                                            step="1"
                                            inputmode="numeric"
                                            required
                                            value="<?= journeyRuleEscape(
                                                $pickupRule['blockMinutes']
                                                ?? ''
                                            ) ?>"
                                        >

                                        <span class="input-group-text">
                                            min
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =========================================
                             DROP-OFF
                        ========================================== -->

                        <div class="col-lg-6">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body p-4">

                                    <div
                                        class="
                                            d-flex
                                            justify-content-between
                                            align-items-start
                                            gap-3
                                            mb-3
                                        "
                                    >

                                        <div>

                                            <h3 class="h5 mb-1">
                                                Airport Drop-off
                                            </h3>

                                            <p class="text-muted small mb-0">
                                                Time reserved when taking
                                                a passenger to the airport.
                                            </p>

                                        </div>


                                        <div class="form-check form-switch">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                                id="dropoffIsActive"
                                                name="dropoffIsActive"
                                                value="1"
                                                <?= (
                                                    $dropoffRule === null ||
                                                    (int)$dropoffRule['isActive']
                                                    === 1
                                                )
                                                    ? 'checked'
                                                    : '' ?>
                                            >

                                            <label
                                                class="form-check-label"
                                                for="dropoffIsActive"
                                            >
                                                Active
                                            </label>

                                        </div>

                                    </div>


                                    <label
                                        for="dropoffBlockMinutes"
                                        class="form-label fw-semibold"
                                    >
                                        Block duration
                                    </label>

                                    <div
                                        class="input-group"
                                        style="max-width: 180px;"
                                    >

                                        <input
                                            type="number"
                                            class="form-control"
                                            id="dropoffBlockMinutes"
                                            name="dropoffBlockMinutes"
                                            min="1"
                                            step="1"
                                            inputmode="numeric"
                                            required
                                            value="<?= journeyRuleEscape(
                                                $dropoffRule['blockMinutes']
                                                ?? ''
                                            ) ?>"
                                        >

                                        <span class="input-group-text">
                                            min
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div
                        class="
                            d-flex
                            justify-content-end
                            mt-4
                        "
                    >

                        <button
                            type="submit"
                            class="btn btn-brand"
                        >
                            <i
                                class="
                                    fa-solid
                                    fa-floppy-disk
                                    me-1
                                "
                            ></i>

                            Save Journey Rules
                        </button>

                    </div>

                </form>


                <div
                    class="
                        small
                        text-muted
                        mt-4
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-circle-info
                            me-1
                        "
                    ></i>

                    Journey Rules affect vehicle availability only.
                    They do not change the airport fare or airport
                    access charge.

                </div>


            <?php endif; ?>

        </div>

    </section>

</main>

<script src="/assets/js/journey-rules.js"></script>

<?php

include __DIR__ . '/../../includes/footer.php';

?>