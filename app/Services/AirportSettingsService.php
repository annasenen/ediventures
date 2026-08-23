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
    // VEHICLES
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


    // =========================================================
    // JOURNEY RULES
    // =========================================================

    public function getJourneyRules(): array
    {
        $sql = "
            SELECT *
            FROM airport_journey_rules
            ORDER BY airportName, journeyType
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJourneyRuleById(int $ruleID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM airport_journey_rules
            WHERE airportRuleID = ?
            LIMIT 1
        ");

        $stmt->execute([$ruleID]);

        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rule ?: null;
    }

    public function saveJourneyRule(
        string $airportName,
        string $journeyType,
        int $blockMinutes,
        bool $isActive
    ): void {
        $airportName = trim($airportName);

        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport name is required.'
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
                airportName,
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
            $airportName,
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
                ap.*,
                v.vehicleName
            FROM airport_pricing ap
            JOIN vehicles v
                ON ap.vehicleID = v.vehicleID
            ORDER BY
                ap.airportName,
                ap.zoneName,
                ap.journeyType,
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
        string $airportName,
        string $zoneName,
        string $journeyType,
        float $basePrice,
        bool $isActive
    ): void {
        $airportName = trim($airportName);
        $zoneName = trim($zoneName);

        if ($vehicleID < 1) {
            throw new InvalidArgumentException(
                'Vehicle is required.'
            );
        }

        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport name is required.'
            );
        }

        if ($zoneName === '') {
            throw new InvalidArgumentException(
                'Zone is required.'
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
                airportName,
                zoneName,
                journeyType,
                basePrice,
                isActive
            )
            VALUES (?, ?, ?, ?, ?, ?)

            ON DUPLICATE KEY UPDATE
                basePrice = VALUES(basePrice),
                isActive = VALUES(isActive)
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $vehicleID,
            $airportName,
            $zoneName,
            $journeyType,
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
            SELECT *
            FROM airport_charges
            ORDER BY airportName
        ";

        return $this->pdo
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAirportChargeById(int $chargeID): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM airport_charges
            WHERE airportChargeID = ?
            LIMIT 1
        ");

        $stmt->execute([$chargeID]);

        $charge = $stmt->fetch(PDO::FETCH_ASSOC);

        return $charge ?: null;
    }

    public function saveAirportCharge(
        string $airportName,
        float $pickupCharge,
        float $dropoffCharge,
        bool $isActive
    ): void {
        $airportName = trim($airportName);

        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport name is required.'
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
                airportName,
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
            $airportName,
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