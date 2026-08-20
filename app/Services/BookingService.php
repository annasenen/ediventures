<?php

declare(strict_types=1);

class BookingService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createAirportBooking(
        int $customerID,
        array $booking
    ): int {
        if ($customerID < 1) {
            throw new InvalidArgumentException('Invalid customer.');
        }

        if (empty($booking['vehicle_id'])) {
            throw new InvalidArgumentException('Vehicle is required.');
        }

        if (empty($booking['journey_start']) || empty($booking['journey_end'])) {
            throw new InvalidArgumentException('Journey times are required.');
        }

        if (empty($booking['journey_type'])) {
            throw new InvalidArgumentException('Journey type is required.');
        }

        if (empty($booking['airport_name'])) {
            throw new InvalidArgumentException('Airport is required.');
        }

        $customerAddress =
            trim($booking['house_number'] ?? '') . ', ' .
            trim($booking['street'] ?? '') . ', ' .
            trim($booking['town_city'] ?? '') . ', ' .
            trim($booking['postcode'] ?? '');

        if ($booking['journey_type'] === 'pickup') {
            $pickupAddress = $booking['airport_name'];
            $dropoffAddress = $customerAddress;
        } else {
            $pickupAddress = $customerAddress;
            $dropoffAddress = $booking['airport_name'];
        }

        $luggageDetails =
            ($booking['large_cases'] ?? 0) . ' large suitcase(s), ' .
            ($booking['small_bags'] ?? 0) . ' small bag(s)';

        $holdExpiresAt = (new DateTimeImmutable())
            ->modify('+15 minutes')
            ->format('Y-m-d H:i:s');

        $sql = "
            INSERT INTO bookings (
                customerID,
                vehicleID,
                bookingType,
                journeyType,
                airportName,
                zoneName,
                pickupAddress,
                dropoffAddress,
                flightNumber,
                passengers,
                luggageDetails,
                journeyStart,
                journeyEnd,
                basePrice,
                airportCharge,
                totalPrice,
                depositPercent,
                depositAmount,
                holdExpiresAt,
                status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $customerID,
            (int)$booking['vehicle_id'],
            'airport_transfer',
            $booking['journey_type'],
            $booking['airport_name'],
            $booking['zone_name'] ?? null,
            $pickupAddress,
            $dropoffAddress,
            $booking['flight_number'] ?? null,
            (int)($booking['passengers'] ?? 0),
            $luggageDetails,
            $booking['journey_start'],
            $booking['journey_end'],
            (float)($booking['base_price'] ?? 0),
            (float)($booking['airport_charge'] ?? 0),
            (float)($booking['total_price'] ?? 0),
            20,
            (float)($booking['deposit_amount'] ?? 0),
            $holdExpiresAt,
            'pending_payment'
        ]);

        return (int)$this->pdo->lastInsertId();
    }
}