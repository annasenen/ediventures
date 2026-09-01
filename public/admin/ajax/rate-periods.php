<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/admin-auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/Services/AirportSettingsService.php';


header(
    'Content-Type: application/json; charset=utf-8'
);

header(
    'Cache-Control: no-store, no-cache, must-revalidate'
);


/*
|--------------------------------------------------------------------------
| JSON RESPONSE
|--------------------------------------------------------------------------
*/

function respond(
    bool $success,
    array $data = [],
    int $statusCode = 200
): never {

    http_response_code(
        $statusCode
    );

    echo json_encode(
        array_merge(
            [
                'success' => $success
            ],
            $data
        ),
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    respond(
        false,
        [
            'message' =>
                'Invalid request method.'
        ],
        405
    );
}


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

$submittedToken =
    $_POST['csrf_token'] ?? '';

$sessionToken =
    $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals(
        $sessionToken,
        $submittedToken
    )
) {
    respond(
        false,
        [
            'message' =>
                'Your session security token is invalid. ' .
                'Please refresh the page and try again.'
        ],
        403
    );
}


/*
|--------------------------------------------------------------------------
| ACTION
|--------------------------------------------------------------------------
*/

$action =
    $_POST['action'] ?? '';

if (!is_string($action)) {
    respond(
        false,
        [
            'message' =>
                'Invalid rate period action.'
        ],
        400
    );
}


$allowedActions = [
    'get_period',
    'save_period',
    'toggle_period'
];

if (
    !in_array(
        $action,
        $allowedActions,
        true
    )
) {
    respond(
        false,
        [
            'message' =>
                'Invalid rate period action.'
        ],
        400
    );
}


$settingsService =
    new AirportSettingsService(
        $pdo
    );


/*
|--------------------------------------------------------------------------
| RESPONSE HELPERS
|--------------------------------------------------------------------------
*/

function dayLabels(
    array $days
): array {

    $labels = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun'
    ];

    $result = [];

    foreach ($days as $day) {

        $dayNumber =
            (int)$day;

        if (isset($labels[$dayNumber])) {
            $result[] =
                $labels[$dayNumber];
        }
    }

    return $result;
}


function buildPeriodResponse(
    AirportSettingsService $settingsService,
    int $pricingPeriodID
): array {

    $period =
        $settingsService
            ->getPricingPeriodById(
                $pricingPeriodID
            );

    if (!$period) {
        throw new InvalidArgumentException(
            'The selected rate period does not exist.'
        );
    }

    $days =
        array_map(
            'intval',
            $period['days'] ?? []
        );

    $startTime =
        (string)$period['startTime'];

    $endTime =
        (string)$period['endTime'];

    return [
        'pricingPeriodID' =>
            (int)$period['pricingPeriodID'],

        'periodName' =>
            (string)$period['periodName'],

        'startTime' =>
            substr(
                $startTime,
                0,
                5
            ),

        'endTime' =>
            substr(
                $endTime,
                0,
                5
            ),

        'priority' =>
            (int)$period['priority'],

        'isActive' =>
            (int)$period['isActive'] === 1,

        'isOvernight' =>
            $startTime > $endTime,

        'days' =>
            $days,

        'dayLabels' =>
            dayLabels(
                $days
            )
    ];
}


/*
|--------------------------------------------------------------------------
| PROCESS
|--------------------------------------------------------------------------
*/

try {


    /*
    |--------------------------------------------------------------------------
    | GET PERIOD
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'get_period'
    ) {

        $pricingPeriodID =
            (int)(
                $_POST['pricingPeriodID'] ?? 0
            );

        if ($pricingPeriodID < 1) {
            throw new InvalidArgumentException(
                'Invalid rate period.'
            );
        }

        respond(
            true,
            [
                'period' =>
                    buildPeriodResponse(
                        $settingsService,
                        $pricingPeriodID
                    )
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE / CREATE PERIOD
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'save_period'
    ) {

        $pricingPeriodID =
            (int)(
                $_POST['pricingPeriodID'] ?? 0
            );

        $periodName =
            $_POST['periodName'] ?? '';

        $startTime =
            $_POST['startTime'] ?? '';

        $endTime =
            $_POST['endTime'] ?? '';

        /*
        * Priority is an internal implementation detail.
        * Normal rate periods use the standard priority.
        * It is not controlled by the browser/admin form.
        */
        $priority = 10;

        $isActive =
            (
                $_POST['isActive'] ?? '0'
            ) === '1';

        /*
         * Days arrive as repeated days[] values.
         */
        $days =
            $_POST['days'] ?? [];

        if (!is_array($days)) {
            throw new InvalidArgumentException(
                'Invalid day selection.'
            );
        }

        $savedID =
            $settingsService
                ->savePricingPeriod(
                    $pricingPeriodID > 0
                        ? $pricingPeriodID
                        : null,
                    is_string($periodName)
                        ? $periodName
                        : '',
                    is_string($startTime)
                        ? $startTime
                        : '',
                    is_string($endTime)
                        ? $endTime
                        : '',
                    $priority,
                    $days,
                    $isActive
                );

        respond(
            true,
            [
                'period' =>
                    buildPeriodResponse(
                        $settingsService,
                        $savedID
                    ),

                'coverage' =>
                    $settingsService
                        ->getPricingPeriodCoverage(),

                'created' =>
                    $pricingPeriodID < 1,

                'message' =>
                    $pricingPeriodID < 1
                        ? 'Rate period added.'
                        : 'Rate period saved.'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE PERIOD
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'toggle_period'
    ) {

        $pricingPeriodID =
            (int)(
                $_POST['pricingPeriodID'] ?? 0
            );

        if ($pricingPeriodID < 1) {
            throw new InvalidArgumentException(
                'Invalid rate period.'
            );
        }

        $isActive =
            (
                $_POST['isActive'] ?? '0'
            ) === '1';

        $settingsService
            ->setPricingPeriodActive(
                $pricingPeriodID,
                $isActive
            );

        respond(
            true,
            [
                'period' =>
                    buildPeriodResponse(
                        $settingsService,
                        $pricingPeriodID
                    ),

                'coverage' =>
                    $settingsService
                        ->getPricingPeriodCoverage(),

                'message' =>
                    $isActive
                        ? 'Rate period activated.'
                        : 'Rate period deactivated.'
            ]
        );
    }



} catch (
    InvalidArgumentException $e
) {

    respond(
        false,
        [
            'message' =>
                $e->getMessage()
        ],
        422
    );


} catch (
    PDOException $e
) {

    error_log(
        'Rate Period AJAX database error: ' .
        $e->getMessage()
    );

    respond(
        false,
        [
            'message' =>
                'This rate period change could not be saved.'
        ],
        500
    );


} catch (
    Throwable $e
) {

    error_log(
        'Rate Period AJAX error: ' .
        $e->getMessage()
    );

    respond(
        false,
        [
            'message' =>
                'Something went wrong. Please try again.'
        ],
        500
    );
}