USE ediventures_db;

-- =========================================================
-- TEST VEHICLE 2
-- Used for availability/failover testing.
-- =========================================================

INSERT IGNORE INTO vehicles
(
    vehicleID,
    vehicleName,
    passengerCapacity,
    isActive,
    isArchived,
    isPrimaryVehicle,
    minimumNoticeHours,
    vehiclePriority
)
VALUES
(
    2,
    'Test Car 2',
    6,
    1,
    0,
    0,
    1,
    2
);


-- =========================================================
-- TEST PRICING FOR VEHICLE 2
-- Deliberately higher than Car 1 so we can easily see
-- when the booking system has moved to Car 2.
-- =========================================================

INSERT IGNORE INTO airport_pricing
(
    vehicleID,
    airportName,
    zoneName,
    journeyType,
    basePrice
)
VALUES
(2, 'Edinburgh Airport', 'Zone A', 'pickup', 40.00),
(2, 'Edinburgh Airport', 'Zone A', 'dropoff', 40.00),
(2, 'Edinburgh Airport', 'Zone B', 'pickup', 50.00),
(2, 'Edinburgh Airport', 'Zone B', 'dropoff', 50.00),
(2, 'Glasgow Airport', 'Zone A', 'pickup', 105.00),
(2, 'Glasgow Airport', 'Zone A', 'dropoff', 105.00);

USE ediventures_db;

-- =========================================================
-- TEST CUSTOMERS
-- Password for all three accounts during development:
-- 0123456789
-- DO NOT use these accounts in production.
-- =========================================================

INSERT IGNORE INTO customers
(
    fullName,
    email,
    phone,
    passwordHash,
    isActive
)
VALUES
(
    'Test User One',
    'one@one.com',
    '07000000001',
    '$2y$10$89RT9Hys4D5KiEztb8S/uONUlTVVu8/6/mvTPV.X7Vo6Ka5PLfSna',
    1
),
(
    'Test User Two',
    'two@two.com',
    '07000000002',
    '$2y$10$89RT9Hys4D5KiEztb8S/uONUlTVVu8/6/mvTPV.X7Vo6Ka5PLfSna',
    1
),
(
    'Test User Three',
    'three@three.com',
    '07000000003',
    '$2y$10$89RT9Hys4D5KiEztb8S/uONUlTVVu8/6/mvTPV.X7Vo6Ka5PLfSna',
    1
);


-- Give all test accounts the normal customer role
INSERT IGNORE INTO customer_roles (customerID, roleID)
SELECT c.customerID, r.roleID
FROM customers c
JOIN roles r
WHERE c.email IN (
    'one@one.com',
    'two@two.com',
    'three@three.com'
)
AND r.roleName = 'customer';

INSERT IGNORE INTO airport_journey_rules
(airportName, journeyType, blockMinutes, isActive)
VALUES
('Edinburgh Airport', 'pickup', 120, 1),
('Edinburgh Airport', 'dropoff', 60, 1),
('Glasgow Airport', 'pickup', 180, 1),
('Glasgow Airport', 'dropoff', 180, 1);