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

        public function getZones(): array
        {
            $sql = "
                SELECT
                    zoneID,
                    zoneName,
                    description,
                    isActive
                FROM zones
                ORDER BY zoneName
            ";

            return $this->pdo
                ->query($sql)
                ->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getPricingPeriods(): array
        {
            $sql = "
                SELECT
                    pricingPeriodID,
                    periodName,
                    startTime,
                    endTime,
                    priority,
                    isActive
                FROM pricing_periods
                ORDER BY
                    priority ASC,
                    pricingPeriodID ASC
            ";

            return $this->pdo
                ->query($sql)
                ->fetchAll(PDO::FETCH_ASSOC);
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
                'Pricing period is required.'
            );
        }

        if ($passengerBandID < 1) {
            throw new InvalidArgumentException(
                'Passenger band is required.'
            );
        }

        if (!in_array($journeyType, ['pickup', 'dropoff'], true)) {
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }

        if ($basePrice < 0) {
            throw new InvalidArgumentException(
                'Price cannot be negative.'
            );
        }

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

        $stmt = $this->pdo->prepare($sql);

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