<?php

declare(strict_types=1);

class PaymentService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPendingBookingForCustomer(
        int $bookingID,
        int $customerID
    ): ?array {
        if ($bookingID < 1 || $customerID < 1) {
            return null;
        }

        $sql = "
            SELECT
                b.*,
                v.vehicleName
            FROM bookings b
            LEFT JOIN vehicles v
                ON b.vehicleID = v.vehicleID
            WHERE b.bookingID = ?
              AND b.customerID = ?
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $bookingID,
            $customerID
        ]);

        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        return $booking ?: null;
    }

    public function cancelPendingBooking(
        int $bookingID,
        int $customerID
    ): bool {
        if ($bookingID < 1 || $customerID < 1) {
            throw new InvalidArgumentException(
                'Invalid booking.'
            );
        }

        $sql = "
            UPDATE bookings
            SET
                status = 'cancelled',
                holdExpiresAt = NULL
            WHERE bookingID = ?
              AND customerID = ?
              AND status = 'pending_payment'
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $bookingID,
            $customerID
        ]);

        return $stmt->rowCount() === 1;
    }

    public function confirmPayment(
        int $bookingID,
        int $customerID
    ): bool {
        if ($bookingID < 1 || $customerID < 1) {
            throw new InvalidArgumentException(
                'Invalid booking.'
            );
        }

        $sql = "
            UPDATE bookings
            SET
                status = 'confirmed',
                holdExpiresAt = NULL
            WHERE bookingID = ?
              AND customerID = ?
              AND status = 'pending_payment'
              AND holdExpiresAt IS NOT NULL
              AND holdExpiresAt > NOW()
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $bookingID,
            $customerID
        ]);

        return $stmt->rowCount() === 1;
    }
}