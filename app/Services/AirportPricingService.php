<?php

declare(strict_types=1);

class AirportPricingService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAirportBookingPrice(
        int $vehicleID,
        string $airportName,
        string $zoneName,
        string $journeyType,
        int $depositPercent = 20
    ): ?array {
        if ($vehicleID < 1) {
            throw new InvalidArgumentException('Invalid vehicle.');
        }

        if ($airportName === '') {
            throw new InvalidArgumentException('Airport is required.');
        }

        if ($zoneName === '') {
            throw new InvalidArgumentException('Pricing zone is required.');
        }

        if (!in_array($journeyType, ['pickup', 'dropoff'], true)) {
            throw new InvalidArgumentException('Invalid journey type.');
        }

        if ($depositPercent < 0 || $depositPercent > 100) {
            throw new InvalidArgumentException('Invalid deposit percentage.');
        }

        $priceSql = "
            SELECT basePrice
            FROM airport_pricing
            WHERE vehicleID = ?
              AND airportName = ?
              AND zoneName = ?
              AND journeyType = ?
              AND isActive = 1
            LIMIT 1
        ";

        $priceStmt = $this->pdo->prepare($priceSql);

        $priceStmt->execute([
            $vehicleID,
            $airportName,
            $zoneName,
            $journeyType
        ]);

        $priceRow = $priceStmt->fetch(PDO::FETCH_ASSOC);

        if (!$priceRow) {
            return null;
        }

        $basePrice = (float)$priceRow['basePrice'];

        $chargeSql = "
            SELECT pickupCharge, dropoffCharge
            FROM airport_charges
            WHERE airportName = ?
              AND isActive = 1
            LIMIT 1
        ";

        $chargeStmt = $this->pdo->prepare($chargeSql);
        $chargeStmt->execute([$airportName]);

        $chargeRow = $chargeStmt->fetch(PDO::FETCH_ASSOC);

        $airportCharge = 0.00;

        if ($chargeRow) {
            if ($journeyType === 'pickup') {
                $airportCharge = (float)$chargeRow['pickupCharge'];
            } else {
                $airportCharge = (float)$chargeRow['dropoffCharge'];
            }
        }

        $totalPrice = $basePrice + $airportCharge;

        $depositAmount = round(
            $totalPrice * ($depositPercent / 100),
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
            'remainingBalance' => $remainingBalance
        ];
    }
}