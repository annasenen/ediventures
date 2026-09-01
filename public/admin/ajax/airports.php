<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../includes/admin-auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/Services/AirportSettingsService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');


function respond(
    bool $success,
    array $data = [],
    int $statusCode = 200
): never {

    http_response_code($statusCode);

    echo json_encode(
        array_merge(
            ['success' => $success],
            $data
        ),
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


function loadAirportById(
    PDO $pdo,
    int $airportID
): array {

    $stmt = $pdo->prepare("
        SELECT
            airportID,
            airportReferenceID,
            airportName,
            airportCode,
            isActive
        FROM airports
        WHERE airportID = ?
        LIMIT 1
    ");

    $stmt->execute([
        $airportID
    ]);

    $airport =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$airport) {
        throw new InvalidArgumentException(
            'The selected airport does not exist.'
        );
    }

    return [
        'airportID' =>
            (int)$airport['airportID'],

        'airportReferenceID' =>
            (int)$airport['airportReferenceID'],

        'airportName' =>
            (string)$airport['airportName'],

        'airportCode' =>
            (string)($airport['airportCode'] ?? ''),

        'isActive' =>
            (int)$airport['isActive'] === 1
    ];
}


function loadAirportByReferenceId(
    PDO $pdo,
    int $airportReferenceID
): array {

    $stmt = $pdo->prepare("
        SELECT airportID
        FROM airports
        WHERE airportReferenceID = ?
        LIMIT 1
    ");

    $stmt->execute([
        $airportReferenceID
    ]);

    $airportID =
        (int)$stmt->fetchColumn();

    if ($airportID < 1) {
        throw new RuntimeException(
            'The added airport could not be loaded.'
        );
    }

    return loadAirportById(
        $pdo,
        $airportID
    );
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
                'Your session security token is invalid. Please refresh the page and try again.'
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
                'Invalid airport action.'
        ],
        400
    );
}

$allowedActions = [
    'add_airport',
    'update_airport',
    'toggle_airport'
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
                'Invalid airport action.'
        ],
        400
    );
}


$settingsService =
    new AirportSettingsService($pdo);


try {


    /*
    |--------------------------------------------------------------------------
    | ADD AIRPORT
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'add_airport'
    ) {

        $airportReferenceID =
            (int)(
                $_POST['airportReferenceID'] ?? 0
            );

        if ($airportReferenceID < 1) {
            throw new InvalidArgumentException(
                'Please choose an airport.'
            );
        }

        $isActive =
            isset($_POST['isActive']) &&
            $_POST['isActive'] === '1';

        $settingsService
            ->addAirportFromReference(
                $airportReferenceID,
                $isActive
            );

        $airport =
            loadAirportByReferenceId(
                $pdo,
                $airportReferenceID
            );

        respond(
            true,
            [
                'airport' => $airport,
                'message' =>
                    'Airport added.'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE AIRPORT
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'update_airport'
    ) {

        $airportID =
            (int)(
                $_POST['airportID'] ?? 0
            );

        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Invalid airport.'
            );
        }

        $isActive =
            isset($_POST['isActive']) &&
            $_POST['isActive'] === '1';

        /*
         * Loading first prevents an update request
         * against an airport that does not exist.
         */
        loadAirportById(
            $pdo,
            $airportID
        );

        $settingsService
            ->setAirportActive(
                $airportID,
                $isActive
            );

        respond(
            true,
            [
                'airport' =>
                    loadAirportById(
                        $pdo,
                        $airportID
                    ),

                'message' =>
                    'Airport settings saved.'
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOGGLE AIRPORT
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'toggle_airport'
    ) {

        $airportID =
            (int)(
                $_POST['airportID'] ?? 0
            );

        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Invalid airport.'
            );
        }

        $isActive =
            (
                $_POST['isActive'] ?? '0'
            ) === '1';

        loadAirportById(
            $pdo,
            $airportID
        );

        $settingsService
            ->setAirportActive(
                $airportID,
                $isActive
            );

        respond(
            true,
            [
                'airport' =>
                    loadAirportById(
                        $pdo,
                        $airportID
                    ),

                'message' =>
                    $isActive
                        ? 'Airport activated.'
                        : 'Airport deactivated.'
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
        'Airport AJAX database error: ' .
        $e->getMessage()
    );

    respond(
        false,
        [
            'message' =>
                'The airport change could not be saved.'
        ],
        500
    );


} catch (
    Throwable $e
) {

    error_log(
        'Airport AJAX error: ' .
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
