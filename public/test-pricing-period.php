<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/PricingPeriodService.php';

$service = new PricingPeriodService($pdo);

$timezone = new DateTimeZone('Europe/London');

$tests = [
    '2026-08-24 10:00:00', // Monday day
    '2026-08-24 23:00:00', // Monday night
    '2026-08-29 12:00:00', // Saturday day
    '2026-08-29 23:00:00', // Saturday night
    '2026-08-30 06:00:00', // Sunday early morning
    '2026-08-31 06:00:00', // Monday early morning
    '2026-08-31 07:00:00',
    '2026-08-24 22:00:00',
];

foreach ($tests as $test) {

    $date = new DateTimeImmutable(
        $test,
        $timezone
    );

    $period = $service->findPricingPeriod($date);

    echo htmlspecialchars($test);
    echo ' → ';
    echo htmlspecialchars(
        $period['periodName'] ?? 'NO MATCH'
    );
    echo '<br>';
}