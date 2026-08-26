<?php

declare(strict_types=1);

class ZoneService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findZoneByPostcode(string $postcode): ?array
    {
        $cleanPostcode = strtoupper(
            preg_replace('/\s+/', '', trim($postcode))
        );

        if ($cleanPostcode === '') {
            throw new InvalidArgumentException(
                'Postcode is required.'
            );
        }

        $sql = "
            SELECT
                z.zoneID,
                z.zoneName,
                z.description
            FROM zone_postcodes zp
            JOIN zones z
                ON zp.zoneID = z.zoneID
            WHERE zp.isActive = 1
              AND z.isActive = 1
              AND ? LIKE CONCAT(zp.postcodePrefix, '%')
            ORDER BY
                CHAR_LENGTH(zp.postcodePrefix) DESC,
                z.zoneID ASC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cleanPostcode]);

        $zone = $stmt->fetch(PDO::FETCH_ASSOC);

        return $zone ?: null;
    }
}