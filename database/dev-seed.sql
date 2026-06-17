USE ediventures_db;

INSERT IGNORE INTO customer_roles (customerID, roleID)
SELECT c.customerID, r.roleID
FROM customers c
JOIN roles r
WHERE c.email = 'anza_25@yahoo.com'
AND r.roleName = 'admin';

INSERT IGNORE INTO vehicles
(vehicleID, vehicleName, passengerCapacity, isActive, isArchived, isPrimaryVehicle, minimumNoticeHours, vehiclePriority)
VALUES
(1, 'Car 1', 6, 1, 0, 1, 1, 1);

INSERT IGNORE INTO airport_pricing
(vehicleID, airportName, zoneName, journeyType, basePrice)
VALUES
(1, 'Edinburgh Airport', 'Zone A', 'pickup', 35.00),
(1, 'Edinburgh Airport', 'Zone A', 'dropoff', 35.00),
(1, 'Edinburgh Airport', 'Zone B', 'pickup', 45.00),
(1, 'Edinburgh Airport', 'Zone B', 'dropoff', 45.00),
(1, 'Glasgow Airport', 'Zone A', 'pickup', 95.00),
(1, 'Glasgow Airport', 'Zone A', 'dropoff', 95.00);