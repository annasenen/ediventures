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

if (
    !is_string($action)
) {
    respond(
        false,
        [
            'message' =>
                'Invalid zone action.'
        ],
        400
    );
}


$allowedActions = [
    'get_zone',
    'save_zone',
    'toggle_zone',
    'add_postcode',
    'remove_postcode'
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
                'Invalid zone action.'
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
| HELPER - BUILD ZONE RESPONSE
|--------------------------------------------------------------------------
*/

function buildZoneResponse(
    AirportSettingsService $settingsService,
    int $zoneID
): array {

    $zone =
        $settingsService->getZoneById(
            $zoneID
        );

    if (!$zone) {
        throw new InvalidArgumentException(
            'The selected zone does not exist.'
        );
    }


    $postcodeRows =
        $settingsService->getZonePostcodes(
            $zoneID
        );


    $postcodes = [];

    foreach (
        $postcodeRows
        as $postcode
    ) {

        $postcodes[] = [

            'zonePostcodeID' =>
                (int)$postcode['zonePostcodeID'],

            'postcodePrefix' =>
                $postcode['postcodePrefix'],

            'displayValue' =>
                $settingsService
                    ->formatPostcodePrefixForDisplay(
                        $postcode['postcodePrefix']
                    )
        ];
    }


    return [

        'zoneID' =>
            (int)$zone['zoneID'],

        'zoneName' =>
            (string)$zone['zoneName'],

        'description' =>
            (string)(
                $zone['description'] ?? ''
            ),

        'isActive' =>
            (int)$zone['isActive'] === 1,

        'postcodeCount' =>
            count($postcodes),

        'postcodes' =>
            $postcodes
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
    | GET ZONE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'get_zone'
    ) {

        $zoneID =
            (int)(
                $_POST['zoneID'] ?? 0
            );

        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Invalid zone.'
            );
        }


        respond(
            true,
            [
                'zone' =>
                    buildZoneResponse(
                        $settingsService,
                        $zoneID
                    )
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | SAVE / CREATE ZONE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'save_zone'
    ) {

        $zoneID =
            (int)(
                $_POST['zoneID'] ?? 0
            );


        $savedZoneID =
            $settingsService->saveZone(

                $zoneID > 0
                    ? $zoneID
                    : null,

                $_POST['zoneName'] ?? '',

                $_POST['description'] ?? '',

                isset(
                    $_POST['isActive']
                )
            );


        respond(
            true,
            [
                'zone' =>
                    buildZoneResponse(
                        $settingsService,
                        $savedZoneID
                    ),

                'created' =>
                    $zoneID < 1,

                'message' =>
                    $zoneID < 1
                        ? 'Zone added.'
                        : 'Zone saved.'
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | TOGGLE ZONE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'toggle_zone'
    ) {

        $zoneID =
            (int)(
                $_POST['zoneID'] ?? 0
            );

        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Invalid zone.'
            );
        }


        $isActive =
            (
                $_POST['isActive'] ?? '0'
            ) === '1';


        $settingsService
            ->setZoneActive(
                $zoneID,
                $isActive
            );


        respond(
            true,
            [
                'zone' =>
                    buildZoneResponse(
                        $settingsService,
                        $zoneID
                    ),

                'message' =>
                    $isActive
                        ? 'Zone activated.'
                        : 'Zone deactivated.'
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | ADD POSTCODE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'add_postcode'
    ) {

        $zoneID =
            (int)(
                $_POST['zoneID'] ?? 0
            );

        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Invalid zone.'
            );
        }


        $settingsService
            ->addZonePostcode(
                $zoneID,
                $_POST['postcodePrefix'] ?? ''
            );


        respond(
            true,
            [
                'zone' =>
                    buildZoneResponse(
                        $settingsService,
                        $zoneID
                    ),

                'message' =>
                    'Postcode rule added.'
            ]
        );
    }



    /*
    |--------------------------------------------------------------------------
    | REMOVE POSTCODE
    |--------------------------------------------------------------------------
    */

    if (
        $action === 'remove_postcode'
    ) {

        $zoneID =
            (int)(
                $_POST['zoneID'] ?? 0
            );

        $zonePostcodeID =
            (int)(
                $_POST['zonePostcodeID'] ?? 0
            );


        if (
            $zoneID < 1 ||
            $zonePostcodeID < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid postcode rule.'
            );
        }


        /*
         * Security/integrity check:
         * make sure this postcode actually
         * belongs to the supplied zone.
         */
        $postcodeRows =
            $settingsService
                ->getZonePostcodes(
                    $zoneID
                );


        $belongsToZone = false;

        foreach (
            $postcodeRows
            as $postcode
        ) {

            if (
                (int)$postcode['zonePostcodeID'] ===
                $zonePostcodeID
            ) {

                $belongsToZone = true;

                break;
            }
        }


        if (!$belongsToZone) {
            throw new InvalidArgumentException(
                'The postcode rule does not belong to this zone.'
            );
        }


        $settingsService
            ->removeZonePostcode(
                $zonePostcodeID
            );


        respond(
            true,
            [
                'zone' =>
                    buildZoneResponse(
                        $settingsService,
                        $zoneID
                    ),

                'message' =>
                    'Postcode rule removed.'
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
        'Zone AJAX database error: ' .
        $e->getMessage()
    );


    /*
     * Duplicate zone name.
     */
    if (
        $e->getCode() === '23000'
    ) {

        respond(
            false,
            [
                'message' =>
                    'A zone with these details already exists.'
            ],
            409
        );
    }


    respond(
        false,
        [
            'message' =>
                'This zone change could not be saved.'
        ],
        500
    );


} catch (
    Throwable $e
) {

    error_log(
        'Zone AJAX error: ' .
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