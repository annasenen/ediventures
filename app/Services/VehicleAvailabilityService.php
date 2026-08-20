<?php

declare(strict_types=1);

class VehicleAvailabilityService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Find the best available vehicle for a requested journey.
     *
     * Selection order:
     * 1. Primary vehicle first
     * 2. Smallest suitable passenger capacity
     * 3. Admin-controlled priority
     * 4. Vehicle ID as final tie-breaker
     */
    public function findAvailableVehicle(
        DateTimeInterface $journeyStart,
        DateTimeInterface $journeyEnd,
        int $passengers
    ): ?array {
        if ($passengers < 1) {
            throw new InvalidArgumentException(
                'Passenger count must be at least one.'
            );
        }

        if ($journeyEnd <= $journeyStart) {
            throw new InvalidArgumentException(
                'Journey end time must be later than journey start time.'
            );
        }

        $vehicleSql = "
            SELECT
                vehicleID,
                vehicleName,
                passengerCapacity,
                minimumNoticeHours,
                vehiclePriority,
                isPrimaryVehicle
            FROM vehicles
            WHERE isActive = 1
              AND isArchived = 0
              AND passengerCapacity >= ?
            ORDER BY
                isPrimaryVehicle DESC,
                passengerCapacity ASC,
                vehiclePriority ASC,
                vehicleID ASC
        ";

        $vehicleStmt = $this->pdo->prepare($vehicleSql);
        $vehicleStmt->execute([$passengers]);

        $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

        $now = new DateTimeImmutable();
        $journeyStartString = $journeyStart->format('Y-m-d H:i:s');
        $journeyEndString = $journeyEnd->format('Y-m-d H:i:s');

        foreach ($vehicles as $vehicle) {
            $minimumNoticeHours = (int)$vehicle['minimumNoticeHours'];

            $earliestAllowed = $now->modify(
                '+' . $minimumNoticeHours . ' hours'
            );

            if ($journeyStart < $earliestAllowed) {
                continue;
            }

            if ($this->hasManualBlock(
                (int)$vehicle['vehicleID'],
                $journeyStartString,
                $journeyEndString
            )) {
                continue;
            }

            if ($this->hasBookingConflict(
                (int)$vehicle['vehicleID'],
                $journeyStartString,
                $journeyEndString
            )) {
                continue;
            }

            return $vehicle;
        }

        return null;
    }

    private function hasManualBlock(
        int $vehicleID,
        string $journeyStart,
        string $journeyEnd
    ): bool {
        $sql = "
            SELECT blockID
            FROM vehicle_blocks
            WHERE vehicleID = ?
              AND blockStart < ?
              AND blockEnd > ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $vehicleID,
            $journeyEnd,
            $journeyStart
        ]);

        return $stmt->fetchColumn() !== false;
    }

    private function hasBookingConflict(
        int $vehicleID,
        string $journeyStart,
        string $journeyEnd
    ): bool {
        $sql = "
            SELECT bookingID
            FROM bookings
            WHERE vehicleID = ?
            AND (
                    status = 'confirmed'
                    OR status = 'pending'
                    OR (
                        status = 'pending_payment'
                        AND holdExpiresAt IS NOT NULL
                        AND holdExpiresAt > NOW()
                    )
                )
            AND journeyStart < ?
            AND journeyEnd > ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $vehicleID,
            $journeyEnd,
            $journeyStart
        ]);

        return $stmt->fetchColumn() !== false;
    }
}