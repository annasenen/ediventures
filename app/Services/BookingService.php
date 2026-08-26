<?php

declare(strict_types=1);

class BookingService
{
    private PDO $pdo;
    private AirportPricingService $pricingService;

    public function __construct(
        PDO $pdo,
        AirportPricingService $pricingService
    ) {
        $this->pdo = $pdo;
        $this->pricingService = $pricingService;
    }

    public function createAirportBooking(
        int $customerID,
        array $booking
    ): array {
        if ($customerID < 1) {
            throw new InvalidArgumentException('Invalid customer.');
        }

        if (empty($booking['vehicle_id'])) {
            throw new InvalidArgumentException('Vehicle is required.');
        }

        if (
            empty($booking['journey_start']) ||
            empty($booking['journey_end'])
        ) {
            throw new InvalidArgumentException(
                'Journey times are required.'
            );
        }

        if (empty($booking['journey_type'])) {
            throw new InvalidArgumentException(
                'Journey type is required.'
            );
        }

        if (empty($booking['airport_name'])) {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if (empty($booking['zone_name'])) {
            throw new InvalidArgumentException(
                'Pricing zone is required.'
            );
        }

        $quotedVehicleID = (int)$booking['vehicle_id'];
        $passengers = (int)($booking['passengers'] ?? 0);

        if ($passengers < 1) {
            throw new InvalidArgumentException(
                'Passenger count must be at least one.'
            );
        }

        $journeyStart = new DateTimeImmutable(
            $booking['journey_start'],
            new DateTimeZone('UTC')
        );

        $journeyEnd = new DateTimeImmutable(
            $booking['journey_end'],
            new DateTimeZone('UTC')
        );

        if ($journeyEnd <= $journeyStart) {
            throw new InvalidArgumentException(
                'Journey end time must be later than journey start time.'
            );
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

        try {
            $this->pdo->beginTransaction();

            /*
             * Lock all suitable active vehicles in a deterministic order.
             *
             * This prevents two simultaneous booking requests from both
             * securing the same vehicle.
             */
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
                ORDER BY vehicleID ASC
                FOR UPDATE
            ";

            $vehicleStmt = $this->pdo->prepare($vehicleSql);
            $vehicleStmt->execute([$passengers]);

            $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$vehicles) {
                throw new RuntimeException(
                    'Sorry, no suitable vehicle is currently available for this journey.'
                );
            }

            /*
             * Once the rows are safely locked, try:
             *
             * 1. Originally quoted vehicle first
             * 2. Primary vehicle
             * 3. Smallest suitable capacity
             * 4. Admin priority
             * 5. Vehicle ID
             */
            usort(
                $vehicles,
                function (array $a, array $b) use ($quotedVehicleID): int {
                    $aQuoted =
                        (int)$a['vehicleID'] === $quotedVehicleID ? 1 : 0;

                    $bQuoted =
                        (int)$b['vehicleID'] === $quotedVehicleID ? 1 : 0;

                    if ($aQuoted !== $bQuoted) {
                        return $bQuoted <=> $aQuoted;
                    }

                    $primaryCompare =
                        (int)$b['isPrimaryVehicle']
                        <=>
                        (int)$a['isPrimaryVehicle'];

                    if ($primaryCompare !== 0) {
                        return $primaryCompare;
                    }

                    $capacityCompare =
                        (int)$a['passengerCapacity']
                        <=>
                        (int)$b['passengerCapacity'];

                    if ($capacityCompare !== 0) {
                        return $capacityCompare;
                    }

                    $priorityCompare =
                        (int)$a['vehiclePriority']
                        <=>
                        (int)$b['vehiclePriority'];

                    if ($priorityCompare !== 0) {
                        return $priorityCompare;
                    }

                    return
                        (int)$a['vehicleID']
                        <=>
                        (int)$b['vehicleID'];
                }
            );

            $selectedVehicle = null;
            $selectedPricing = null;

            $now = new DateTimeImmutable(
                'now',
                new DateTimeZone('UTC')
            );

            foreach ($vehicles as $vehicle) {
                $vehicleID = (int)$vehicle['vehicleID'];

                /*
                 * Check this vehicle's minimum notice period.
                 */
                $minimumNoticeHours =
                    (int)$vehicle['minimumNoticeHours'];

                $earliestAllowed = $now->modify(
                    '+' . $minimumNoticeHours . ' hours'
                );

                if ($journeyStart < $earliestAllowed) {
                    continue;
                }

                /*
                 * Check manual vehicle blocks.
                 */
                $blockSql = "
                    SELECT blockID
                    FROM vehicle_blocks
                    WHERE vehicleID = ?
                      AND blockStart < ?
                      AND blockEnd > ?
                    LIMIT 1
                ";

                $blockStmt = $this->pdo->prepare($blockSql);

                $blockStmt->execute([
                    $vehicleID,
                    $journeyEnd->format('Y-m-d H:i:s'),
                    $journeyStart->format('Y-m-d H:i:s')
                ]);

                if ($blockStmt->fetchColumn() !== false) {
                    continue;
                }

                /*
                 * Check confirmed bookings and active payment holds.
                 */
                $conflictSql = "
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

                $conflictStmt =
                    $this->pdo->prepare($conflictSql);

                $conflictStmt->execute([
                    $vehicleID,
                    $journeyEnd->format('Y-m-d H:i:s'),
                    $journeyStart->format('Y-m-d H:i:s')
                ]);

                if ($conflictStmt->fetchColumn() !== false) {
                    continue;
                }

                /*
                 * Recalculate price for this exact vehicle.
                 *
                 * Never trust the old browser/session price at finalisation.
                 */
                $pricing =
                    $this->pricingService->getAirportBookingPrice(
                        $vehicleID,
                        $booking['airport_name'],
                        $booking['zone_name'],
                        $booking['journey_type'],
                        $journeyStart,
                        $passengers,
                        20
                    );

                /*
                 * Vehicle without configured online pricing cannot
                 * be automatically booked.
                 */
                if (!$pricing) {
                    continue;
                }

                $selectedVehicle = $vehicle;
                $selectedPricing = $pricing;

                break;
            }

            if (!$selectedVehicle || !$selectedPricing) {
                throw new RuntimeException(
                    'This journey is temporarily unavailable. Another customer may be completing a booking. Please try again shortly or choose another time.'
                );
            }

            $selectedVehicleID =
                (int)$selectedVehicle['vehicleID'];

            /*
             * Compare current server-calculated price with the price
             * the customer previously reviewed.
             */
            $oldTotalCents = (int)round(
                (float)($booking['total_price'] ?? 0) * 100
            );

            $newTotalCents = (int)round(
                (float)$selectedPricing['totalPrice'] * 100
            );

            $priceChanged =
                $oldTotalCents !== $newTotalCents;

            /*
             * If the price changed, do not silently create a hold at
             * a price the customer has not approved.
             */
            if ($priceChanged) {
                $updatedBooking = $booking;

                $updatedBooking['vehicle_id'] =
                    $selectedVehicleID;

                $updatedBooking['base_price'] =
                    $selectedPricing['basePrice'];

                $updatedBooking['airport_charge'] =
                    $selectedPricing['airportCharge'];

                $updatedBooking['total_price'] =
                    $selectedPricing['totalPrice'];

                $updatedBooking['deposit_amount'] =
                    $selectedPricing['depositAmount'];

                $updatedBooking['remaining_balance'] =
                    $selectedPricing['remainingBalance'];

                /*
                 * Nothing has been inserted yet.
                 * Release the transaction/locks and let the customer
                 * review the revised price.
                 */
                $this->pdo->commit();

                return [
                    'status' => 'price_changed',
                    'booking' => $updatedBooking
                ];
            }

            /*
             * Price is still accepted.
             * Create a fresh 15-minute hold.
             */
            $holdExpiresAt = $now
                ->modify('+15 minutes')
                ->format('Y-m-d H:i:s');

            $insertSql = "
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
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ";

            $insertStmt =
                $this->pdo->prepare($insertSql);

            $insertStmt->execute([
                $customerID,
                $selectedVehicleID,
                'airport_transfer',
                $booking['journey_type'],
                $booking['airport_name'],
                $booking['zone_name'],
                $pickupAddress,
                $dropoffAddress,
                $booking['flight_number'] ?? null,
                $passengers,
                $luggageDetails,
                $journeyStart->format('Y-m-d H:i:s'),
                $journeyEnd->format('Y-m-d H:i:s'),
                $selectedPricing['basePrice'],
                $selectedPricing['airportCharge'],
                $selectedPricing['totalPrice'],
                $selectedPricing['depositPercent'],
                $selectedPricing['depositAmount'],
                $holdExpiresAt,
                'pending_payment'
            ]);

            $bookingID =
                (int)$this->pdo->lastInsertId();

            $this->pdo->commit();

            return [
                'status' => 'created',
                'bookingID' => $bookingID
            ];

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}