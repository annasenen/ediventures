<?php

declare(strict_types=1);

class AirportJourneyRuleService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getBlockMinutes(
        string $airportName,
        string $journeyType
    ): ?int {
        if ($airportName === '') {
            throw new InvalidArgumentException(
                'Airport is required.'
            );
        }

        if (!in_array($journeyType, ['pickup', 'dropoff'], true)) {
            throw new InvalidArgumentException(
                'Invalid journey type.'
            );
        }

        $sql = "
            SELECT blockMinutes
            FROM airport_journey_rules
            WHERE airportName = ?
              AND journeyType = ?
              AND isActive = 1
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $airportName,
            $journeyType
        ]);

        $blockMinutes = $stmt->fetchColumn();

        if ($blockMinutes === false) {
            return null;
        }

        return (int)$blockMinutes;
    }

    public function calculateJourneyEnd(
        DateTimeInterface $journeyStart,
        string $airportName,
        string $journeyType
    ): ?DateTimeImmutable {
        $blockMinutes = $this->getBlockMinutes(
            $airportName,
            $journeyType
        );

        if ($blockMinutes === null) {
            return null;
        }

        return DateTimeImmutable::createFromInterface(
            $journeyStart
        )->modify(
            '+' . $blockMinutes . ' minutes'
        );
    }
}