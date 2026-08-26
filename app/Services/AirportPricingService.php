<?php

declare(strict_types=1);

class AirportPricingService
{
    private PDO $pdo;
    private PricingPeriodService $pricingPeriodService;

    public function __construct(
        PDO $pdo,
        PricingPeriodService $pricingPeriodService
    ) {
        $this->pdo = $pdo;
        $this->pricingPeriodService = $pricingPeriodService;
    }

    public function getAirportBookingPrice(
        int $vehicleID,
        string $airportName,
        string $zoneName,
        string $journeyType,
        DateTimeInterface $journeyDateTime,
        int $passengers,
        int $depositPercent = 20
    ): ?array {
        if ($vehicleID < 1) {
            throw new InvalidArgumentException(
                'Invalid vehicle.'
            );
        }

        if ($passengers < 1) {
            throw new InvalidArgumentException(
                'Passenger count must be at least one.'
            );
        }

        $airportName = trim($airportName);
        $zoneName = trim($zoneName);

        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if ($zoneName === '') {
            throw new InvalidArgumentException(
                'Pricing zone is required.'
            );
        }

        if (
            !in_array(
                $journeyType,
                ['pickup', 'dropoff'],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }

        if (
            $depositPercent < 0 ||
            $depositPercent > 100
        ) {
            throw new InvalidArgumentException(
                'Invalid deposit percentage.'
            );
        }

        /*
         * Determine the pricing period from the journey's
         * UK local day and time.
         */
        $pricingPeriod =
            $this->pricingPeriodService
                ->findPricingPeriod(
                    $journeyDateTime
                );

        if (!$pricingPeriod) {
            return null;
        }

        $pricingPeriodID =
            (int)$pricingPeriod['pricingPeriodID'];

        /*
         * Determine which passenger pricing band applies.
         *
         * Example:
         * 1-2 passengers -> Up to 2
         * 3-4 passengers -> Up to 4
         * 5-6 passengers -> Up to 6
         */
        $bandSql = "
            SELECT
                passengerBandID,
                bandName,
                minPassengers,
                maxPassengers
            FROM passenger_bands
            WHERE serviceType = 'airport_transfer'
              AND isActive = 1
              AND ? BETWEEN minPassengers AND maxPassengers
            ORDER BY
                maxPassengers ASC,
                passengerBandID ASC
            LIMIT 1
        ";

        $bandStmt =
            $this->pdo->prepare($bandSql);

        $bandStmt->execute([
            $passengers
        ]);

        $passengerBand =
            $bandStmt->fetch(PDO::FETCH_ASSOC);

        if (!$passengerBand) {
            return null;
        }

        $passengerBandID =
            (int)$passengerBand['passengerBandID'];

        /*
         * Final airport price is determined by:
         *
         * vehicle
         * + airport
         * + zone
         * + journey type
         * + pricing period
         * + passenger band
         */
        $priceSql = "
            SELECT
                ap.basePrice,
                ap.airportID,
                ap.zoneID,
                ap.pricingPeriodID,
                ap.passengerBandID
            FROM airport_pricing ap

            JOIN airports a
                ON ap.airportID = a.airportID

            JOIN zones z
                ON ap.zoneID = z.zoneID

            WHERE ap.vehicleID = ?
              AND a.airportName = ?
              AND a.isActive = 1
              AND z.zoneName = ?
              AND z.isActive = 1
              AND ap.journeyType = ?
              AND ap.pricingPeriodID = ?
              AND ap.passengerBandID = ?
              AND ap.isActive = 1

            LIMIT 1
        ";

        $priceStmt =
            $this->pdo->prepare($priceSql);

        $priceStmt->execute([
            $vehicleID,
            $airportName,
            $zoneName,
            $journeyType,
            $pricingPeriodID,
            $passengerBandID
        ]);

        $priceRow =
            $priceStmt->fetch(PDO::FETCH_ASSOC);

        if (!$priceRow) {
            return null;
        }

        $basePrice =
            (float)$priceRow['basePrice'];

        /*
         * Airport charges are currently still linked
         * by airport name.
         *
         * We will migrate them to airportID separately.
         */
        $chargeSql = "
            SELECT
                ac.pickupCharge,
                ac.dropoffCharge
            FROM airport_charges ac

            JOIN airports a
                ON ac.airportID = a.airportID

            WHERE a.airportName = ?
            AND a.isActive = 1
            AND ac.isActive = 1

            LIMIT 1
        ";

        $chargeStmt =
            $this->pdo->prepare($chargeSql);

        $chargeStmt->execute([
            $airportName
        ]);

        $chargeRow =
            $chargeStmt->fetch(PDO::FETCH_ASSOC);

        $airportCharge = 0.00;

        if ($chargeRow) {
            $airportCharge =
                $journeyType === 'pickup'
                    ? (float)$chargeRow['pickupCharge']
                    : (float)$chargeRow['dropoffCharge'];
        }

        $totalPrice =
            $basePrice + $airportCharge;

        $depositAmount = round(
            $totalPrice *
            ($depositPercent / 100),
            2
        );

        $remainingBalance = round(
            $totalPrice - $depositAmount,
            2
        );

        return [
            'basePrice' => $basePrice,
            'airportCharge' => $airportCharge,
            'totalPrice' => $totalPrice,

            'depositPercent' => $depositPercent,
            'depositAmount' => $depositAmount,
            'remainingBalance' => $remainingBalance,

            'airportID' =>
                (int)$priceRow['airportID'],

            'zoneID' =>
                (int)$priceRow['zoneID'],

            'pricingPeriodID' =>
                (int)$priceRow['pricingPeriodID'],

            'pricingPeriodName' =>
                $pricingPeriod['periodName'],

            'passengerBandID' =>
                (int)$priceRow['passengerBandID'],

            'passengerBandName' =>
                $passengerBand['bandName']
        ];
    }
}