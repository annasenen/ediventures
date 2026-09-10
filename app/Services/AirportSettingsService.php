<?php

declare(strict_types=1);

class AirportSettingsService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    // =========================================================
    // MASTER DATA
    // =========================================================

    public function getAvailableVehicles(): array
    {
        $sql = "
            SELECT
                vehicleID,
                vehicleName,
                passengerCapacity,
                isActive
            FROM vehicles
            WHERE isArchived = 0
            ORDER BY
                isPrimaryVehicle DESC,
                vehiclePriority ASC,
                vehicleID ASC
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAirports(): array
    {
        $sql = "
            SELECT
                airportID,
                airportName,
                airportCode,
                isActive
            FROM airports
            ORDER BY airportName
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAirportReferences(): array
    {
        $sql = "
            SELECT
                ar.airportReferenceID,
                ar.airportName,
                ar.iataCode,
                ar.cityName,
                ar.countryName,

                CASE
                    WHEN a.airportID IS NULL THEN 0
                    ELSE 1
                END AS isConfigured

            FROM airport_reference ar

            LEFT JOIN airports a
                ON a.airportReferenceID = ar.airportReferenceID

            WHERE ar.isAvailable = 1

            ORDER BY
                ar.countryName,
                ar.cityName,
                ar.airportName
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function addAirportFromReference(
        int $airportReferenceID,
        bool $isActive
    ): void {
        if ($airportReferenceID < 1) {
            throw new InvalidArgumentException(
                'Please choose an airport.'
            );
        }

        /*
        * Load trusted airport reference data.
        */
        $referenceStmt = $this->pdo->prepare("
            SELECT
                airportReferenceID,
                airportName,
                iataCode
            FROM airport_reference
            WHERE airportReferenceID = ?
            AND isAvailable = 1
            LIMIT 1
        ");

        $referenceStmt->execute([
            $airportReferenceID
        ]);

        $reference =
            $referenceStmt->fetch(PDO::FETCH_ASSOC);

        if (!$reference) {
            throw new InvalidArgumentException(
                'The selected airport is not available.'
            );
        }

        /*
        * Do not allow the same airport to be configured twice.
        */
        $existingStmt = $this->pdo->prepare("
            SELECT airportID
            FROM airports
            WHERE airportReferenceID = ?
            LIMIT 1
        ");

        $existingStmt->execute([
            $airportReferenceID
        ]);

        if ($existingStmt->fetchColumn() !== false) {
            throw new InvalidArgumentException(
                'This airport has already been added.'
            );
        }

        /*
        * During our bridge migration we still populate
        * airportName and airportCode as well.
        *
        * Later those legacy columns can be removed.
        */
        $insertStmt = $this->pdo->prepare("
            INSERT INTO airports
            (
                airportReferenceID,
                airportName,
                airportCode,
                isActive
            )
            VALUES (?, ?, ?, ?)
        ");

        $insertStmt->execute([
            $airportReferenceID,
            $reference["airportName"],
            $reference["iataCode"],
            $isActive ? 1 : 0
        ]);
    }

    public function getAirportById(int $airportID): ?array
    {
        if ($airportID < 1) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT
                airportID,
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

        return $airport ?: null;
    }


    public function saveAirport(
        ?int $airportID,
        string $airportName,
        string $airportCode,
        bool $isActive
    ): void {
        $airportName = trim($airportName);

        $airportCode = strtoupper(
            trim($airportCode)
        );

        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport name is required.'
            );
        }

        if (
            $airportCode !== '' &&
            strlen($airportCode) > 10
        ) {
            throw new InvalidArgumentException(
                'Airport code cannot be longer than 10 characters.'
            );
        }

        $airportCodeValue =
            $airportCode !== ''
                ? $airportCode
                : null;


        /*
        * Existing airport.
        */
        if ($airportID !== null && $airportID > 0) {

            $sql = "
                UPDATE airports
                SET
                    airportName = ?,
                    airportCode = ?,
                    isActive = ?
                WHERE airportID = ?
            ";

            $stmt =
                $this->pdo->prepare($sql);

            $stmt->execute([
                $airportName,
                $airportCodeValue,
                $isActive ? 1 : 0,
                $airportID
            ]);

            return;
        }


        /*
        * New airport.
        */
        $sql = "
            INSERT INTO airports
            (
                airportName,
                airportCode,
                isActive
            )
            VALUES (?, ?, ?)
        ";

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            $airportName,
            $airportCodeValue,
            $isActive ? 1 : 0
        ]);
    }


    public function setAirportActive(
        int $airportID,
        bool $isActive
    ): void {
        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Invalid airport.'
            );
        }

        $stmt = $this->pdo->prepare("
            UPDATE airports
            SET isActive = ?
            WHERE airportID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $airportID
        ]);
    }

    // =========================================================
    // ZONES
    // =========================================================

    public function getZones(): array
    {
        $sql = "
            SELECT
                z.zoneID,
                z.zoneName,
                z.description,
                z.isActive,

                COUNT(
                    CASE
                        WHEN zp.isActive = 1
                        THEN zp.zonePostcodeID
                    END
                ) AS postcodeCount

            FROM zones z

            LEFT JOIN zone_postcodes zp
                ON z.zoneID = zp.zoneID

            GROUP BY
                z.zoneID,
                z.zoneName,
                z.description,
                z.isActive

            ORDER BY
                z.zoneName
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getZoneById(
        int $zoneID
    ): ?array {
        if ($zoneID < 1) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT
                zoneID,
                zoneName,
                description,
                isActive
            FROM zones
            WHERE zoneID = ?
            LIMIT 1
        ");

        $stmt->execute([
            $zoneID
        ]);

        $zone =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $zone ?: null;
    }


    public function getZonePostcodes(
        int $zoneID
    ): array {
        if ($zoneID < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare("
            SELECT
                zonePostcodeID,
                postcodePrefix,
                isActive
            FROM zone_postcodes
            WHERE zoneID = ?
            ORDER BY
                CHAR_LENGTH(postcodePrefix) ASC,
                postcodePrefix ASC
        ");

        $stmt->execute([
            $zoneID
        ]);

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }


    public function saveZone(
        ?int $zoneID,
        string $zoneName,
        string $description,
        bool $isActive
    ): int {
        $zoneName =
            trim($zoneName);

        $description =
            trim($description);

        if ($zoneName === '') {
            throw new InvalidArgumentException(
                'Zone name is required.'
            );
        }

        if (strlen($zoneName) > 50) {
            throw new InvalidArgumentException(
                'Zone name cannot be longer than 50 characters.'
            );
        }

        if (strlen($description) > 255) {
            throw new InvalidArgumentException(
                'Zone description cannot be longer than 255 characters.'
            );
        }

        /*
         * Edit existing zone.
         */
        if (
            $zoneID !== null &&
            $zoneID > 0
        ) {
            $stmt = $this->pdo->prepare("
                UPDATE zones
                SET
                    zoneName = ?,
                    description = ?,
                    isActive = ?
                WHERE zoneID = ?
            ");

            $stmt->execute([
                $zoneName,
                $description !== ''
                    ? $description
                    : null,
                $isActive ? 1 : 0,
                $zoneID
            ]);

            return $zoneID;
        }

        /*
         * Add new zone.
         */
        $stmt = $this->pdo->prepare("
            INSERT INTO zones
            (
                zoneName,
                description,
                isActive
            )
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $zoneName,
            $description !== ''
                ? $description
                : null,
            $isActive ? 1 : 0
        ]);

        return (int)$this->pdo->lastInsertId();
    }


    public function setZoneActive(
        int $zoneID,
        bool $isActive
    ): void {
        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Invalid zone.'
            );
        }

        $stmt = $this->pdo->prepare("
            UPDATE zones
            SET isActive = ?
            WHERE zoneID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $zoneID
        ]);
    }


    public function addZonePostcode(
        int $zoneID,
        string $postcodePrefix
    ): void {
        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Invalid zone.'
            );
        }

        /*
         * Admin may enter:
         *
         * EH30
         * EH30 9
         * EH30 9PP
         *
         * We store:
         *
         * EH30
         * EH309
         * EH309PP
         *
         * This matches ZoneService.
         */
        $postcodePrefix = strtoupper(
            preg_replace(
                '/\s+/',
                '',
                trim($postcodePrefix)
            )
        );

        if ($postcodePrefix === '') {
            throw new InvalidArgumentException(
                'Postcode rule is required.'
            );
        }

        if (
            !preg_match(
                '/^[A-Z0-9]+$/',
                $postcodePrefix
            )
        ) {
            throw new InvalidArgumentException(
                'Please enter a valid postcode rule.'
            );
        }

        if (strlen($postcodePrefix) > 8) {
            throw new InvalidArgumentException(
                'The postcode rule is too long.'
            );
        }

        /*
         * Confirm that the zone exists.
         */
        $zoneStmt = $this->pdo->prepare("
            SELECT zoneID
            FROM zones
            WHERE zoneID = ?
            LIMIT 1
        ");

        $zoneStmt->execute([
            $zoneID
        ]);

        if ($zoneStmt->fetchColumn() === false) {
            throw new InvalidArgumentException(
                'The selected zone does not exist.'
            );
        }

        /*
         * Do not duplicate the same postcode rule
         * inside the same zone.
         */
        /*
        * The exact same postcode rule may only
        * belong to one zone.
        *
        * Broader and more specific rules are allowed:
        *
        * Zone A -> EH30
        * Zone B -> EH309
        *
        * ZoneService will use EH309 first because
        * it is the more specific match.
        */
        $existingStmt = $this->pdo->prepare("
            SELECT
                zp.zonePostcodeID,
                zp.zoneID,
                z.zoneName
            FROM zone_postcodes zp

            JOIN zones z
                ON zp.zoneID = z.zoneID

            WHERE zp.postcodePrefix = ?

            LIMIT 1
        ");

        $existingStmt->execute([
            $postcodePrefix
        ]);

        $existing =
            $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {

            throw new InvalidArgumentException(
                $this->formatPostcodePrefixForDisplay(
                    $postcodePrefix
                ) .
                ' is already assigned to ' .
                $existing['zoneName'] .
                '. Please use a more specific postcode rule if this area needs different pricing.'
            );
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO zone_postcodes
            (
                zoneID,
                postcodePrefix,
                isActive
            )
            VALUES (?, ?, 1)
        ");

        $stmt->execute([
            $zoneID,
            $postcodePrefix
        ]);
    }


    public function removeZonePostcode(
        int $zonePostcodeID
    ): void {
        if ($zonePostcodeID < 1) {
            throw new InvalidArgumentException(
                'Invalid postcode rule.'
            );
        }

        $stmt = $this->pdo->prepare("
            DELETE FROM zone_postcodes
            WHERE zonePostcodeID = ?
        ");

        $stmt->execute([
            $zonePostcodeID
        ]);
    }


    public function formatPostcodePrefixForDisplay(
        string $postcodePrefix
    ): string {
        $postcodePrefix = strtoupper(
            preg_replace(
                '/\s+/',
                '',
                trim($postcodePrefix)
            )
        );

        if ($postcodePrefix === '') {
            return '';
        }

        /*
        * Full UK postcode.
        *
        * Example:
        * EH309BQ
        * becomes:
        * EH30 9BQ
        */
        if (
            preg_match(
                '/^([A-Z]{1,2}[0-9][0-9A-Z]?)([0-9][A-Z]{2})$/',
                $postcodePrefix,
                $matches
            )
        ) {
            return
                $matches[1] .
                ' ' .
                $matches[2];
        }

        /*
        * Sector-level rule.
        *
        * Example:
        * EH309
        * becomes:
        * EH30 9
        *
        * EH298
        * becomes:
        * EH29 8
        */
        if (
            preg_match(
                '/^([A-Z]{1,2}[0-9]{2})([0-9])$/',
                $postcodePrefix,
                $matches
            )
        ) {
            return
                $matches[1] .
                ' ' .
                $matches[2];
        }

        /*
        * Broad district rules stay unchanged.
        *
        * Examples:
        * EH30
        * EH29
        * EH6
        */
        return $postcodePrefix;
    }



    // =========================================================
    // RATE / PRICING PERIODS
    // =========================================================

    public function getPricingPeriods(): array
    {
        $sql = "
            SELECT
                pp.pricingPeriodID,
                pp.periodName,
                pp.startTime,
                pp.endTime,
                pp.priority,
                pp.isActive,
                GROUP_CONCAT(
                    ppd.dayOfWeek
                    ORDER BY ppd.dayOfWeek ASC
                    SEPARATOR ','
                ) AS daysCsv
            FROM pricing_periods pp
            LEFT JOIN pricing_period_days ppd
                ON pp.pricingPeriodID = ppd.pricingPeriodID
            GROUP BY
                pp.pricingPeriodID,
                pp.periodName,
                pp.startTime,
                pp.endTime,
                pp.priority,
                pp.isActive
            ORDER BY
                pp.priority DESC,
                pp.periodName ASC,
                pp.pricingPeriodID ASC
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getPricingPeriodById(
        int $pricingPeriodID
    ): ?array {
        if ($pricingPeriodID < 1) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT
                pricingPeriodID,
                periodName,
                startTime,
                endTime,
                priority,
                isActive
            FROM pricing_periods
            WHERE pricingPeriodID = ?
            LIMIT 1
        ");

        $stmt->execute([
            $pricingPeriodID
        ]);

        $period =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$period) {
            return null;
        }

        $period['days'] =
            $this->getPricingPeriodDays(
                $pricingPeriodID
            );

        return $period;
    }


    public function getPricingPeriodDays(
        int $pricingPeriodID
    ): array {
        if ($pricingPeriodID < 1) {
            return [];
        }

        $stmt = $this->pdo->prepare("
            SELECT dayOfWeek
            FROM pricing_period_days
            WHERE pricingPeriodID = ?
            ORDER BY dayOfWeek ASC
        ");

        $stmt->execute([
            $pricingPeriodID
        ]);

        return array_map(
            'intval',
            $stmt->fetchAll(
                PDO::FETCH_COLUMN
            )
        );
    }


    public function savePricingPeriod(
        ?int $pricingPeriodID,
        string $periodName,
        string $startTime,
        string $endTime,
        int $priority,
        array $days,
        bool $isActive
    ): int {
        $periodName =
            trim($periodName);

        if ($periodName === '') {
            throw new InvalidArgumentException(
                'Rate period name is required.'
            );
        }

        if (strlen($periodName) > 100) {
            throw new InvalidArgumentException(
                'Rate period name cannot be longer than 100 characters.'
            );
        }

        $startTime =
            $this->normalisePricingTime(
                $startTime,
                'Start time'
            );

        $endTime =
            $this->normalisePricingTime(
                $endTime,
                'End time'
            );

        if ($startTime === $endTime) {
            throw new InvalidArgumentException(
                'Start time and end time must be different.'
            );
        }

        if (
            $priority < 1 ||
            $priority > 1000
        ) {
            throw new InvalidArgumentException(
                'Priority must be between 1 and 1000.'
            );
        }

        $normalisedDays =
            $this->normalisePricingDays(
                $days
            );

        if (empty($normalisedDays)) {
            throw new InvalidArgumentException(
                'Choose at least one day.'
            );
        }

        /*
        * Active pricing periods must not overlap.
        *
        * Inactive periods are allowed to exist without
        * affecting the live pricing schedule.
        */
        if ($isActive) {

            $this->validatePricingPeriodOverlap(
                $pricingPeriodID,
                $startTime,
                $endTime,
                $normalisedDays
            );
        }

        /*
         * Avoid confusing duplicate names.
         *
         * This is a service-level integrity check.
         */
        $duplicateStmt =
            $this->pdo->prepare("
                SELECT pricingPeriodID
                FROM pricing_periods
                WHERE LOWER(periodName) = LOWER(?)
                  AND (
                        ? IS NULL
                        OR pricingPeriodID <> ?
                      )
                LIMIT 1
            ");

        $duplicateStmt->execute([
            $periodName,
            $pricingPeriodID,
            $pricingPeriodID
        ]);

        if (
            $duplicateStmt->fetchColumn() !==
            false
        ) {
            throw new InvalidArgumentException(
                'A rate period with this name already exists.'
            );
        }

        $this->pdo->beginTransaction();

        try {

            if (
                $pricingPeriodID !== null &&
                $pricingPeriodID > 0
            ) {

                $existing =
                    $this->getPricingPeriodById(
                        $pricingPeriodID
                    );

                if (!$existing) {
                    throw new InvalidArgumentException(
                        'The selected rate period does not exist.'
                    );
                }

                $stmt = $this->pdo->prepare("
                    UPDATE pricing_periods
                    SET
                        periodName = ?,
                        startTime = ?,
                        endTime = ?,
                        priority = ?,
                        isActive = ?
                    WHERE pricingPeriodID = ?
                ");

                $stmt->execute([
                    $periodName,
                    $startTime,
                    $endTime,
                    $priority,
                    $isActive ? 1 : 0,
                    $pricingPeriodID
                ]);

                $savedID =
                    $pricingPeriodID;

            } else {

                $stmt = $this->pdo->prepare("
                    INSERT INTO pricing_periods
                    (
                        periodName,
                        startTime,
                        endTime,
                        priority,
                        isActive
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $periodName,
                    $startTime,
                    $endTime,
                    $priority,
                    $isActive ? 1 : 0
                ]);

                $savedID =
                    (int)$this->pdo
                        ->lastInsertId();
            }

            /*
             * Replace the day assignments as one
             * transaction with the period itself.
             */
            $deleteDays =
                $this->pdo->prepare("
                    DELETE FROM pricing_period_days
                    WHERE pricingPeriodID = ?
                ");

            $deleteDays->execute([
                $savedID
            ]);

            $insertDay =
                $this->pdo->prepare("
                    INSERT INTO pricing_period_days
                    (
                        pricingPeriodID,
                        dayOfWeek
                    )
                    VALUES (?, ?)
                ");

            foreach (
                $normalisedDays
                as $day
            ) {
                $insertDay->execute([
                    $savedID,
                    $day
                ]);
            }

            $this->pdo->commit();

            return $savedID;

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    public function setPricingPeriodActive(
        int $pricingPeriodID,
        bool $isActive
    ): void {

        if ($pricingPeriodID < 1) {
            throw new InvalidArgumentException(
                'Invalid rate period.'
            );
        }

        $existing =
            $this->getPricingPeriodById(
                $pricingPeriodID
            );

        if (!$existing) {
            throw new InvalidArgumentException(
                'The selected rate period does not exist.'
            );
        }

        /*
        * Before activating a period, make sure
        * it does not overlap another active period.
        */
        if ($isActive) {

            $this->validatePricingPeriodOverlap(
                $pricingPeriodID,
                (string)$existing['startTime'],
                (string)$existing['endTime'],
                $existing['days'] ?? []
            );
        }

        $stmt = $this->pdo->prepare("
            UPDATE pricing_periods
            SET isActive = ?
            WHERE pricingPeriodID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $pricingPeriodID
        ]);
    }

    public function getPricingPeriodCoverage(): array
    {
        $daySeconds =
            24 * 60 * 60;

        $weekSeconds =
            7 * $daySeconds;

        $intervals = [];

        $periods =
            $this->getPricingPeriods();

        foreach ($periods as $period) {

            if (
                (int)$period['isActive'] !== 1
            ) {
                continue;
            }

            $days =
                $this->normalisePricingDays(
                    explode(
                        ',',
                        (string)(
                            $period['daysCsv'] ?? ''
                        )
                    )
                );

            if (empty($days)) {
                continue;
            }

            $periodIntervals =
                $this->pricingPeriodWeekIntervals(
                    (string)$period['startTime'],
                    (string)$period['endTime'],
                    $days
                );

            foreach (
                $periodIntervals
                as $interval
            ) {
                $intervals[] =
                    $interval;
            }
        }

        /*
        * No active periods means the whole
        * week is uncovered.
        */
        if (empty($intervals)) {

            return [
                'isComplete' => false,
                'gaps' =>
                    $this->buildPricingCoverageGaps(
                        [
                            [
                                'start' => 0,
                                'end' => $weekSeconds
                            ]
                        ]
                    )
            ];
        }

        /*
        * Sort all intervals by their start time.
        */
        usort(
            $intervals,
            static function (
                array $a,
                array $b
            ): int {
                return
                    $a['start'] <=>
                    $b['start'];
            }
        );

        /*
        * Merge touching or overlapping intervals.
        *
        * Overlaps should normally already have
        * been prevented, but merging also makes
        * the coverage calculation robust.
        */
        $merged = [];

        foreach ($intervals as $interval) {

            if (empty($merged)) {

                $merged[] =
                    $interval;

                continue;
            }

            $lastIndex =
                count($merged) - 1;

            if (
                $interval['start'] <=
                $merged[$lastIndex]['end']
            ) {

                $merged[$lastIndex]['end'] =
                    max(
                        $merged[$lastIndex]['end'],
                        $interval['end']
                    );

                continue;
            }

            $merged[] =
                $interval;
        }

        /*
        * Find uncovered sections of the week.
        */
        $gaps = [];

        $cursor = 0;

        foreach ($merged as $interval) {

            if (
                $interval['start'] >
                $cursor
            ) {

                $gaps[] = [
                    'start' => $cursor,
                    'end' =>
                        $interval['start']
                ];
            }

            $cursor =
                max(
                    $cursor,
                    $interval['end']
                );
        }

        if ($cursor < $weekSeconds) {

            $gaps[] = [
                'start' => $cursor,
                'end' => $weekSeconds
            ];
        }

        return [
            'isComplete' =>
                empty($gaps),

            'gaps' =>
                $this->buildPricingCoverageGaps(
                    $gaps
                )
        ];
    }

    private function buildPricingCoverageGaps(
        array $gaps
    ): array {

        $daySeconds =
            24 * 60 * 60;

        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        $result = [];

        foreach ($gaps as $gap) {

            $cursor =
                (int)$gap['start'];

            $gapEnd =
                (int)$gap['end'];

            while ($cursor < $gapEnd) {

                $dayIndex =
                    intdiv(
                        $cursor,
                        $daySeconds
                    );

                /*
                * Safety guard.
                */
                if (
                    $dayIndex < 0 ||
                    $dayIndex > 6
                ) {
                    break;
                }

                $dayNumber =
                    $dayIndex + 1;

                $dayStart =
                    $dayIndex *
                    $daySeconds;

                $dayEnd =
                    $dayStart +
                    $daySeconds;

                $segmentEnd =
                    min(
                        $gapEnd,
                        $dayEnd
                    );

                $startWithinDay =
                    $cursor -
                    $dayStart;

                $endWithinDay =
                    $segmentEnd -
                    $dayStart;

                $result[] = [
                    'day' =>
                        $dayNumber,

                    'dayLabel' =>
                        $dayNames[$dayNumber],

                    'startTime' =>
                        $this
                            ->formatPricingCoverageTime(
                                $startWithinDay
                            ),

                    'endTime' =>
                        $this
                            ->formatPricingCoverageTime(
                                $endWithinDay
                            )
                ];

                $cursor =
                    $segmentEnd;
            }
        }

        return $result;
    }


    private function formatPricingCoverageTime(
        int $seconds
    ): string {

        /*
        * 24:00 is useful here to show the
        * end of a calendar day clearly.
        */
        if ($seconds >= 86400) {
            return '24:00';
        }

        if ($seconds < 0) {
            $seconds = 0;
        }

        $hours =
            intdiv(
                $seconds,
                3600
            );

        $minutes =
            intdiv(
                $seconds % 3600,
                60
            );

        return sprintf(
            '%02d:%02d',
            $hours,
            $minutes
        );
    }

    private function validatePricingPeriodOverlap(
        ?int $pricingPeriodID,
        string $startTime,
        string $endTime,
        array $days
    ): void {

        $existingPeriods =
            $this->getPricingPeriods();

        foreach ($existingPeriods as $period) {

            if (
                (int)$period['isActive'] !== 1
            ) {
                continue;
            }

            $existingID =
                (int)$period['pricingPeriodID'];

            if (
                $pricingPeriodID !== null &&
                $existingID === $pricingPeriodID
            ) {
                continue;
            }

            $existingDays =
                $this->normalisePricingDays(
                    explode(
                        ',',
                        (string)(
                            $period['daysCsv'] ?? ''
                        )
                    )
                );

            $newIntervals =
                $this->pricingPeriodWeekIntervals(
                    $startTime,
                    $endTime,
                    $days
                );

            $existingIntervals =
                $this->pricingPeriodWeekIntervals(
                    (string)$period['startTime'],
                    (string)$period['endTime'],
                    $existingDays
                );

            foreach ($newIntervals as $newInterval) {

                foreach (
                    $existingIntervals
                    as $existingInterval
                ) {

                    if (
                        $newInterval['start'] <
                            $existingInterval['end'] &&
                        $newInterval['end'] >
                            $existingInterval['start']
                    ) {

                        throw new InvalidArgumentException(
                            'This rate period overlaps with "' .
                            (string)$period['periodName'] .
                            '". Please choose different days or times.'
                        );
                    }
                }
            }
        }
    }


    private function pricingPeriodWeekIntervals(
        string $startTime,
        string $endTime,
        array $days
    ): array {

        $startSeconds =
            $this->pricingTimeToSeconds(
                $startTime
            );

        $endSeconds =
            $this->pricingTimeToSeconds(
                $endTime
            );

        $daySeconds =
            24 * 60 * 60;

        $weekSeconds =
            7 * $daySeconds;

        $intervals = [];

        foreach ($days as $day) {

            $dayIndex =
                (int)$day - 1;

            $start =
                ($dayIndex * $daySeconds) +
                $startSeconds;

            if ($startSeconds < $endSeconds) {

                $end =
                    ($dayIndex * $daySeconds) +
                    $endSeconds;

            } else {

                /*
                * Overnight period.
                *
                * Example:
                * Monday 22:00 -> Tuesday 07:00.
                */
                $end =
                    (($dayIndex + 1) * $daySeconds) +
                    $endSeconds;
            }

            /*
            * Split an interval that crosses the
            * Sunday -> Monday week boundary.
            */
            if ($end > $weekSeconds) {

                $intervals[] = [
                    'start' => $start,
                    'end' => $weekSeconds
                ];

                $intervals[] = [
                    'start' => 0,
                    'end' => $end - $weekSeconds
                ];

            } else {

                $intervals[] = [
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        return $intervals;
    }


    private function pricingTimeToSeconds(
        string $time
    ): int {

        $parts =
            array_map(
                'intval',
                explode(
                    ':',
                    $time
                )
            );

        return
            (($parts[0] ?? 0) * 3600) +
            (($parts[1] ?? 0) * 60) +
            ($parts[2] ?? 0);
    }


    private function normalisePricingTime(
        string $time,
        string $label
    ): string {
        $time =
            trim($time);

        if (
            !preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/',
                $time
            )
        ) {
            throw new InvalidArgumentException(
                $label . ' is invalid.'
            );
        }

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return $time;
    }


    private function normalisePricingDays(
        array $days
    ): array {
        $normalised = [];

        foreach ($days as $day) {

            if (
                is_array($day) ||
                is_object($day)
            ) {
                continue;
            }

            $dayNumber =
                (int)$day;

            if (
                $dayNumber >= 1 &&
                $dayNumber <= 7
            ) {
                $normalised[$dayNumber] =
                    $dayNumber;
            }
        }

        ksort($normalised);

        return array_values(
            $normalised
        );
    }

    public function getPassengerBands(): array
    {
        $sql = "
            SELECT
                passengerBandID,
                bandName,
                minPassengers,
                maxPassengers,
                isActive
            FROM passenger_bands
            WHERE serviceType = 'airport_transfer'
            ORDER BY
                maxPassengers ASC,
                passengerBandID ASC
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }


    // =========================================================
    // JOURNEY RULES
    // =========================================================

    public function getJourneyRules(): array
    {
        $sql = "
            SELECT
                ajr.airportRuleID,
                ajr.airportID,
                a.airportName,
                ajr.journeyType,
                ajr.blockMinutes,
                ajr.isActive
            FROM airport_journey_rules ajr

            JOIN airports a
                ON ajr.airportID = a.airportID

            ORDER BY
                a.airportName,
                ajr.journeyType
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJourneyRuleById(int $ruleID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ajr.airportRuleID,
                ajr.airportID,
                a.airportName,
                ajr.journeyType,
                ajr.blockMinutes,
                ajr.isActive
            FROM airport_journey_rules ajr

            JOIN airports a
                ON ajr.airportID = a.airportID

            WHERE ajr.airportRuleID = ?
            LIMIT 1
        ");

        $stmt->execute([$ruleID]);

        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rule ?: null;
    }

    public function saveJourneyRule(
        int $airportID,
        string $journeyType,
        int $blockMinutes,
        bool $isActive
    ): void {
        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if (!in_array($journeyType, ['pickup', 'dropoff'], true)) {
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }

        if ($blockMinutes < 1) {
            throw new InvalidArgumentException(
                'Block duration must be at least 1 minute.'
            );
        }

        $sql = "
            INSERT INTO airport_journey_rules
            (
                airportID,
                journeyType,
                blockMinutes,
                isActive
            )
            VALUES (?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE
                blockMinutes = VALUES(blockMinutes),
                isActive = VALUES(isActive)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $airportID,
            $journeyType,
            $blockMinutes,
            $isActive ? 1 : 0
        ]);
    }

    public function setJourneyRuleActive(
        int $ruleID,
        bool $isActive
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE airport_journey_rules
            SET isActive = ?
            WHERE airportRuleID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $ruleID
        ]);
    }


    // =========================================================
    // AIRPORT PRICING
    // =========================================================

    public function getAirportPriceMatrix(
        int $vehicleID,
        int $airportID,
        int $zoneID,
        string $journeyType
    ): array {

        /*
        |--------------------------------------------------------------------------
        | VALIDATE BASIC INPUT
        |--------------------------------------------------------------------------
        */

        if ($vehicleID < 1) {
            throw new InvalidArgumentException(
                'Vehicle is required.'
            );
        }

        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Zone is required.'
            );
        }

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
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD VEHICLE
        |--------------------------------------------------------------------------
        |
        | Archived vehicles are not valid for new airport pricing.
        |
        */

        $vehicleStmt =
            $this->pdo->prepare("
                SELECT
                    vehicleID,
                    vehicleName,
                    passengerCapacity,
                    isActive
                FROM vehicles
                WHERE vehicleID = ?
                AND isArchived = 0
                LIMIT 1
            ");

        $vehicleStmt->execute([
            $vehicleID
        ]);

        $vehicle =
            $vehicleStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$vehicle) {
            throw new InvalidArgumentException(
                'The selected vehicle does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD AIRPORT
        |--------------------------------------------------------------------------
        */

        $airportStmt =
            $this->pdo->prepare("
                SELECT
                    airportID,
                    airportName,
                    airportCode,
                    isActive
                FROM airports
                WHERE airportID = ?
                LIMIT 1
            ");

        $airportStmt->execute([
            $airportID
        ]);

        $airport =
            $airportStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$airport) {
            throw new InvalidArgumentException(
                'The selected airport does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD ZONE
        |--------------------------------------------------------------------------
        */

        $zoneStmt =
            $this->pdo->prepare("
                SELECT
                    zoneID,
                    zoneName,
                    description,
                    isActive
                FROM zones
                WHERE zoneID = ?
                LIMIT 1
            ");

        $zoneStmt->execute([
            $zoneID
        ]);

        $zone =
            $zoneStmt->fetch(
                PDO::FETCH_ASSOC
            );

        if (!$zone) {
            throw new InvalidArgumentException(
                'The selected zone does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PASSENGER BANDS
        |--------------------------------------------------------------------------
        |
        | Only active airport-transfer passenger bands are used as
        | columns in the current pricing matrix.
        |
        */

        $bandStmt =
            $this->pdo->query("
                SELECT
                    passengerBandID,
                    bandName,
                    minPassengers,
                    maxPassengers,
                    isActive
                FROM passenger_bands
                WHERE serviceType = 'airport_transfer'
                AND isActive = 1
                ORDER BY
                    maxPassengers ASC,
                    passengerBandID ASC
            ");

        $passengerBands =
            $bandStmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
        |--------------------------------------------------------------------------
        | RATE PERIODS
        |--------------------------------------------------------------------------
        |
        | Show:
        |
        | 1. all active Rate Periods;
        | 2. inactive Rate Periods that already have prices attached
        |    to this exact vehicle / airport / zone / journey.
        |
        | This means old pricing does not mysteriously disappear from
        | the admin screen just because its Rate Period was deactivated.
        |
        */

        $periodStmt =
            $this->pdo->prepare("
                SELECT
                    pp.pricingPeriodID,
                    pp.periodName,
                    pp.startTime,
                    pp.endTime,
                    pp.isActive,

                    GROUP_CONCAT(
                        ppd.dayOfWeek
                        ORDER BY ppd.dayOfWeek ASC
                        SEPARATOR ','
                    ) AS daysCsv,

                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM airport_pricing ap_existing
                            WHERE ap_existing.vehicleID = ?
                            AND ap_existing.airportID = ?
                            AND ap_existing.zoneID = ?
                            AND ap_existing.journeyType = ?
                            AND ap_existing.pricingPeriodID =
                                pp.pricingPeriodID
                        )
                        THEN 1
                        ELSE 0
                    END AS hasConfiguredPrices

                FROM pricing_periods pp

                LEFT JOIN pricing_period_days ppd
                    ON pp.pricingPeriodID =
                        ppd.pricingPeriodID

                WHERE
                    pp.isActive = 1

                    OR EXISTS (
                        SELECT 1
                        FROM airport_pricing ap_visible
                        WHERE ap_visible.vehicleID = ?
                        AND ap_visible.airportID = ?
                        AND ap_visible.zoneID = ?
                        AND ap_visible.journeyType = ?
                        AND ap_visible.pricingPeriodID =
                            pp.pricingPeriodID
                    )

                GROUP BY
                    pp.pricingPeriodID,
                    pp.periodName,
                    pp.startTime,
                    pp.endTime,
                    pp.isActive

                ORDER BY
                    pp.isActive DESC,
                    pp.startTime ASC,
                    pp.periodName ASC,
                    pp.pricingPeriodID ASC
            ");

        $periodStmt->execute([
            $vehicleID,
            $airportID,
            $zoneID,
            $journeyType,

            $vehicleID,
            $airportID,
            $zoneID,
            $journeyType
        ]);

        $pricingPeriods =
            $periodStmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
        |--------------------------------------------------------------------------
        | EXISTING PRICES
        |--------------------------------------------------------------------------
        */

        $priceStmt =
            $this->pdo->prepare("
                SELECT
                    airportPriceID,
                    pricingPeriodID,
                    passengerBandID,
                    basePrice,
                    isActive
                FROM airport_pricing
                WHERE vehicleID = ?
                AND airportID = ?
                AND zoneID = ?
                AND journeyType = ?
                ORDER BY
                    pricingPeriodID ASC,
                    passengerBandID ASC
            ");

        $priceStmt->execute([
            $vehicleID,
            $airportID,
            $zoneID,
            $journeyType
        ]);

        $priceRows =
            $priceStmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        /*
        |--------------------------------------------------------------------------
        | BUILD PRICE LOOKUP
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | $prices[3][2]
        |
        | means:
        |
        | Rate Period ID 3
        | Passenger Band ID 2
        |
        */

        $prices = [];

        foreach ($priceRows as $price) {

            $pricingPeriodID =
                (int)$price[
                    'pricingPeriodID'
                ];

            $passengerBandID =
                (int)$price[
                    'passengerBandID'
                ];

            if (
                !isset(
                    $prices[
                        $pricingPeriodID
                    ]
                )
            ) {
                $prices[
                    $pricingPeriodID
                ] = [];
            }

            $prices[
                $pricingPeriodID
            ][
                $passengerBandID
            ] = [
                'airportPriceID' =>
                    (int)$price[
                        'airportPriceID'
                    ],

                'basePrice' =>
                    (float)$price[
                        'basePrice'
                    ],

                'isActive' =>
                    (int)$price[
                        'isActive'
                    ] === 1
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK MATRIX COMPLETENESS
        |--------------------------------------------------------------------------
        |
        | Only ACTIVE Rate Periods and ACTIVE passenger bands matter
        | for customer-facing pricing.
        |
        | A missing price or an inactive price is considered incomplete.
        |
        */

        $missingCount = 0;

        foreach (
            $pricingPeriods
            as $period
        ) {

            if (
                (int)$period[
                    'isActive'
                ] !== 1
            ) {
                continue;
            }

            $pricingPeriodID =
                (int)$period[
                    'pricingPeriodID'
                ];

            foreach (
                $passengerBands
                as $band
            ) {

                $passengerBandID =
                    (int)$band[
                        'passengerBandID'
                    ];

                $price =
                    $prices[
                        $pricingPeriodID
                    ][
                        $passengerBandID
                    ] ?? null;

                if (
                    !$price ||
                    $price[
                        'isActive'
                    ] !== true
                ) {
                    $missingCount++;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN MATRIX DATA
        |--------------------------------------------------------------------------
        */

        return [
            'vehicle' =>
                $vehicle,

            'airport' =>
                $airport,

            'zone' =>
                $zone,

            'journeyType' =>
                $journeyType,

            'pricingPeriods' =>
                $pricingPeriods,

            'passengerBands' =>
                $passengerBands,

            'prices' =>
                $prices,

            'missingCount' =>
                $missingCount,

            'isComplete' =>
                $missingCount === 0
        ];
    }

    public function getAirportPrices(): array
    {
        $sql = "
            SELECT
                ap.airportPriceID,
                ap.vehicleID,
                ap.airportID,
                ap.zoneID,
                ap.pricingPeriodID,
                ap.passengerBandID,
                ap.journeyType,
                ap.basePrice,
                ap.isActive,

                v.vehicleName,
                a.airportName,
                z.zoneName,
                pp.periodName,
                pb.bandName

            FROM airport_pricing ap

            JOIN vehicles v
                ON ap.vehicleID = v.vehicleID

            JOIN airports a
                ON ap.airportID = a.airportID

            JOIN zones z
                ON ap.zoneID = z.zoneID

            JOIN pricing_periods pp
                ON ap.pricingPeriodID = pp.pricingPeriodID

            JOIN passenger_bands pb
                ON ap.passengerBandID = pb.passengerBandID

            ORDER BY
                a.airportName,
                z.zoneName,
                ap.journeyType,
                pp.pricingPeriodID,
                pb.maxPassengers,
                v.vehiclePriority,
                v.vehicleID
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAirportPriceById(int $priceID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM airport_pricing
            WHERE airportPriceID = ?
            LIMIT 1
        ");

        $stmt->execute([$priceID]);

        $price = $stmt->fetch(PDO::FETCH_ASSOC);

        return $price ?: null;
    }

    public function saveAirportPrice(
        int $vehicleID,
        int $airportID,
        int $zoneID,
        string $journeyType,
        int $pricingPeriodID,
        int $passengerBandID,
        float $basePrice,
        bool $isActive
    ): void {

        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($vehicleID < 1) {
            throw new InvalidArgumentException(
                'Vehicle is required.'
            );
        }

        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if ($zoneID < 1) {
            throw new InvalidArgumentException(
                'Zone is required.'
            );
        }

        if ($pricingPeriodID < 1) {
            throw new InvalidArgumentException(
                'Rate period is required.'
            );
        }

        if ($passengerBandID < 1) {
            throw new InvalidArgumentException(
                'Passenger band is required.'
            );
        }

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
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }

        if (
            !is_finite($basePrice) ||
            $basePrice < 0
        ) {
            throw new InvalidArgumentException(
                'Please enter a valid price.'
            );
        }

        /*
        * airport_pricing.basePrice is DECIMAL(10,2).
        */
        if ($basePrice > 99999999.99) {
            throw new InvalidArgumentException(
                'The price is too large.'
            );
        }

        $basePrice =
            round(
                $basePrice,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | VERIFY VEHICLE
        |--------------------------------------------------------------------------
        |
        | An inactive vehicle may still have prices configured in advance,
        | but an archived vehicle must not receive new pricing.
        |
        */

        $vehicleStmt =
            $this->pdo->prepare("
                SELECT vehicleID
                FROM vehicles
                WHERE vehicleID = ?
                AND isArchived = 0
                LIMIT 1
            ");

        $vehicleStmt->execute([
            $vehicleID
        ]);

        if (
            $vehicleStmt->fetchColumn() ===
            false
        ) {
            throw new InvalidArgumentException(
                'The selected vehicle is not available for pricing.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY AIRPORT
        |--------------------------------------------------------------------------
        |
        | We verify existence rather than requiring isActive = 1.
        | This allows prices to be prepared before an airport is made live.
        |
        */

        $airportStmt =
            $this->pdo->prepare("
                SELECT airportID
                FROM airports
                WHERE airportID = ?
                LIMIT 1
            ");

        $airportStmt->execute([
            $airportID
        ]);

        if (
            $airportStmt->fetchColumn() ===
            false
        ) {
            throw new InvalidArgumentException(
                'The selected airport does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY ZONE
        |--------------------------------------------------------------------------
        */

        $zoneStmt =
            $this->pdo->prepare("
                SELECT zoneID
                FROM zones
                WHERE zoneID = ?
                LIMIT 1
            ");

        $zoneStmt->execute([
            $zoneID
        ]);

        if (
            $zoneStmt->fetchColumn() ===
            false
        ) {
            throw new InvalidArgumentException(
                'The selected zone does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY RATE PERIOD
        |--------------------------------------------------------------------------
        |
        | Do not require the period to be active.
        |
        | Inactive historic periods may still have saved airport prices,
        | and the Airport Prices screen deliberately keeps those visible.
        |
        */

        $periodStmt =
            $this->pdo->prepare("
                SELECT pricingPeriodID
                FROM pricing_periods
                WHERE pricingPeriodID = ?
                LIMIT 1
            ");

        $periodStmt->execute([
            $pricingPeriodID
        ]);

        if (
            $periodStmt->fetchColumn() ===
            false
        ) {
            throw new InvalidArgumentException(
                'The selected rate period does not exist.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFY PASSENGER BAND
        |--------------------------------------------------------------------------
        |
        | The band must specifically belong to airport transfers.
        |
        */

        $bandStmt =
            $this->pdo->prepare("
                SELECT passengerBandID
                FROM passenger_bands
                WHERE passengerBandID = ?
                AND serviceType = 'airport_transfer'
                LIMIT 1
            ");

        $bandStmt->execute([
            $passengerBandID
        ]);

        if (
            $bandStmt->fetchColumn() ===
            false
        ) {
            throw new InvalidArgumentException(
                'The selected passenger band is not valid for airport transfers.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE PRICE
        |--------------------------------------------------------------------------
        |
        | The UNIQUE constraint on airport_pricing represents:
        |
        | vehicle
        | + airport
        | + zone
        | + journey type
        | + rate period
        | + passenger band
        |
        | If that combination already exists, update it.
        |
        */

        $sql = "
            INSERT INTO airport_pricing
            (
                vehicleID,
                airportID,
                zoneID,
                journeyType,
                pricingPeriodID,
                passengerBandID,
                basePrice,
                isActive
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE
                basePrice = VALUES(basePrice),
                isActive = VALUES(isActive)
        ";

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            $vehicleID,
            $airportID,
            $zoneID,
            $journeyType,
            $pricingPeriodID,
            $passengerBandID,
            $basePrice,
            $isActive ? 1 : 0
        ]);
    }

    public function setAirportPriceActive(
        int $priceID,
        bool $isActive
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE airport_pricing
            SET isActive = ?
            WHERE airportPriceID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $priceID
        ]);
    }


    // =========================================================
    // AIRPORT CHARGES
    // =========================================================

    public function getAirportCharges(): array
    {
        $sql = "
            SELECT
                ac.airportChargeID,
                ac.airportID,
                a.airportName,
                ac.pickupCharge,
                ac.dropoffCharge,
                ac.isActive
            FROM airport_charges ac

            JOIN airports a
                ON ac.airportID = a.airportID

            ORDER BY a.airportName
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAirportChargeById(int $chargeID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ac.airportChargeID,
                ac.airportID,
                a.airportName,
                ac.pickupCharge,
                ac.dropoffCharge,
                ac.isActive
            FROM airport_charges ac

            JOIN airports a
                ON ac.airportID = a.airportID

            WHERE ac.airportChargeID = ?
            LIMIT 1
        ");

        $stmt->execute([$chargeID]);

        $charge = $stmt->fetch(PDO::FETCH_ASSOC);

        return $charge ?: null;
    }

    public function saveAirportCharge(
        int $airportID,
        float $pickupCharge,
        float $dropoffCharge,
        bool $isActive
    ): void {
        if ($airportID < 1) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if ($pickupCharge < 0 || $dropoffCharge < 0) {
            throw new InvalidArgumentException(
                'Airport charges cannot be negative.'
            );
        }

        $sql = "
            INSERT INTO airport_charges
            (
                airportID,
                pickupCharge,
                dropoffCharge,
                isActive
            )
            VALUES (?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE
                pickupCharge = VALUES(pickupCharge),
                dropoffCharge = VALUES(dropoffCharge),
                isActive = VALUES(isActive)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $airportID,
            $pickupCharge,
            $dropoffCharge,
            $isActive ? 1 : 0
        ]);
    }

    public function setAirportChargeActive(
        int $chargeID,
        bool $isActive
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE airport_charges
            SET isActive = ?
            WHERE airportChargeID = ?
        ");

        $stmt->execute([
            $isActive ? 1 : 0,
            $chargeID
        ]);
    }
}