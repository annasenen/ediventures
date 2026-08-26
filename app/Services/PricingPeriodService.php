<?php

declare(strict_types=1);

class PricingPeriodService
{
    private PDO $pdo;
    private DateTimeZone $businessTimezone;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->businessTimezone = new DateTimeZone('Europe/London');
    }

    /**
     * Finds the active pricing period for a journey.
     *
     * The supplied journey time must represent the actual instant
     * of the journey. It is converted to Europe/London before
     * day/time pricing rules are evaluated.
     */
    public function findPricingPeriod(
        DateTimeInterface $journeyDateTime
    ): ?array {

        $localJourney = DateTimeImmutable::createFromInterface(
            $journeyDateTime
        )->setTimezone($this->businessTimezone);

        $dayOfWeek = (int)$localJourney->format('N');
        $time = $localJourney->format('H:i:s');

        /*
         * First check periods belonging to the current day.
         *
         * This covers:
         * - ordinary periods such as 07:00 -> 22:00
         * - the evening part of overnight periods such as
         *   22:00 -> 07:00
         */
        $period = $this->findForDayAndTime(
            $dayOfWeek,
            $time,
            false
        );

        if ($period !== null) {
            return $period;
        }

        /*
         * If nothing matched, check whether we are in the
         * after-midnight part of an overnight period that
         * started on the previous day.
         */
        $previousDay = $dayOfWeek === 1
            ? 7
            : $dayOfWeek - 1;

        return $this->findForDayAndTime(
            $previousDay,
            $time,
            true
        );
    }

    private function findForDayAndTime(
        int $dayOfWeek,
        string $time,
        bool $afterMidnightOnly
    ): ?array {

        if ($afterMidnightOnly) {

            $sql = "
                SELECT
                    pp.pricingPeriodID,
                    pp.periodName,
                    pp.startTime,
                    pp.endTime,
                    pp.priority
                FROM pricing_periods pp
                JOIN pricing_period_days ppd
                    ON pp.pricingPeriodID = ppd.pricingPeriodID
                WHERE pp.isActive = 1
                  AND ppd.dayOfWeek = ?
                  AND pp.startTime > pp.endTime
                  AND ? < pp.endTime
                ORDER BY pp.priority DESC
                LIMIT 1
            ";

        } else {

            $sql = "
                SELECT
                    pp.pricingPeriodID,
                    pp.periodName,
                    pp.startTime,
                    pp.endTime,
                    pp.priority
                FROM pricing_periods pp
                JOIN pricing_period_days ppd
                    ON pp.pricingPeriodID = ppd.pricingPeriodID
                WHERE pp.isActive = 1
                  AND ppd.dayOfWeek = ?
                  AND (
                        (
                            pp.startTime < pp.endTime
                            AND ? >= pp.startTime
                            AND ? < pp.endTime
                        )
                        OR
                        (
                            pp.startTime > pp.endTime
                            AND ? >= pp.startTime
                        )
                      )
                ORDER BY pp.priority DESC
                LIMIT 1
            ";
        }

        $stmt = $this->pdo->prepare($sql);

        if ($afterMidnightOnly) {
            $stmt->execute([
                $dayOfWeek,
                $time
            ]);
        } else {
            $stmt->execute([
                $dayOfWeek,
                $time,
                $time,
                $time
            ]);
        }

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;
    }
}