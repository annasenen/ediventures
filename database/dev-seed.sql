USE ediventures_db;


-- =========================================================
-- PRIMARY DEVELOPMENT VEHICLE
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
    1,
    'Car 1',
    6,
    1,
    0,
    1,
    1,
    1
);


-- =========================================================
-- AIRPORT MASTER DATA
-- =========================================================

INSERT IGNORE INTO airports
(airportName, airportCode, isActive)
VALUES
('Edinburgh Airport', 'EDI', 1),
('Glasgow Airport', 'GLA', 1),
('Glasgow Prestwick Airport', 'PIK', 1),
('Dundee Airport', 'DND', 1),
('Newcastle Airport', 'NCL', 1);


-- =========================================================
-- ZONES
-- =========================================================

INSERT IGNORE INTO zones
(zoneName, description, isActive)
VALUES
('Zone A', 'Primary local automatic booking area', 1),
('Zone B', 'Secondary automatic booking area', 1);


-- =========================================================
-- ZONE POSTCODES
-- =========================================================

INSERT IGNORE INTO zone_postcodes
(zoneID, postcodePrefix, isActive)
SELECT zoneID, 'EH30', 1
FROM zones
WHERE zoneName = 'Zone A';

INSERT IGNORE INTO zone_postcodes
(zoneID, postcodePrefix, isActive)
SELECT zoneID, 'EH29', 1
FROM zones
WHERE zoneName = 'Zone A';

INSERT IGNORE INTO zone_postcodes
(zoneID, postcodePrefix, isActive)
SELECT zoneID, 'EH28', 1
FROM zones
WHERE zoneName = 'Zone A';

INSERT IGNORE INTO zone_postcodes
(zoneID, postcodePrefix, isActive)
SELECT zoneID, 'EH12', 1
FROM zones
WHERE zoneName = 'Zone B';

INSERT IGNORE INTO zone_postcodes
(zoneID, postcodePrefix, isActive)
SELECT zoneID, 'KY11', 1
FROM zones
WHERE zoneName = 'Zone B';


-- =========================================================
-- PRICING PERIODS
-- Temporary development defaults.
-- Admin will later control these values.
-- =========================================================

INSERT IGNORE INTO pricing_periods
(
    pricingPeriodID,
    periodName,
    startTime,
    endTime,
    priority,
    isActive
)
VALUES
(1, 'Weekday Day',   '07:00:00', '22:00:00', 10, 1),
(2, 'Weekday Night', '22:00:00', '07:00:00', 20, 1),
(3, 'Weekend Day',   '08:00:00', '20:00:00', 10, 1),
(4, 'Weekend Night', '20:00:00', '08:00:00', 20, 1);


-- Monday-Friday
INSERT IGNORE INTO pricing_period_days
(pricingPeriodID, dayOfWeek)
VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5);

-- Saturday-Sunday
INSERT IGNORE INTO pricing_period_days
(pricingPeriodID, dayOfWeek)
VALUES
(3, 6), (3, 7),
(4, 6), (4, 7);


-- =========================================================
-- AIRPORT PASSENGER BANDS
-- =========================================================

INSERT IGNORE INTO passenger_bands
(
    bandName,
    minPassengers,
    maxPassengers,
    serviceType,
    isActive
)
VALUES
('Up to 2 passengers', 1, 2, 'airport_transfer', 1),
('Up to 4 passengers', 3, 4, 'airport_transfer', 1),
('Up to 6 passengers', 5, 6, 'airport_transfer', 1);


-- =========================================================
-- AIRPORT JOURNEY BLOCKING RULES
-- =========================================================

INSERT IGNORE INTO airport_journey_rules
(airportName, journeyType, blockMinutes, isActive)
VALUES
('Edinburgh Airport', 'pickup', 120, 1),
('Edinburgh Airport', 'dropoff', 60, 1),
('Glasgow Airport', 'pickup', 180, 1),
('Glasgow Airport', 'dropoff', 180, 1);


-- =========================================================
-- AIRPORT CHARGES
-- Temporary development values.
-- Admin will control these later.
-- =========================================================

INSERT IGNORE INTO airport_charges
(
    airportName,
    pickupCharge,
    dropoffCharge,
    isActive
)
VALUES
('Edinburgh Airport', 8.50, 8.50, 1),
('Glasgow Airport', 0.00, 0.00, 1);


-- =========================================================
-- CAR 1 AIRPORT PRICING
--
-- We insert the Weekday Day base prices first.
-- =========================================================

INSERT IGNORE INTO airport_pricing
(
    vehicleID,
    airportID,
    zoneID,
    pricingPeriodID,
    passengerBandID,
    journeyType,
    basePrice,
    isActive
)
SELECT
    1,
    a.airportID,
    z.zoneID,
    pp.pricingPeriodID,
    pb.passengerBandID,
    prices.journeyType,
    prices.basePrice,
    1
FROM (
    SELECT 'Edinburgh Airport' AS airportName, 'Zone A' AS zoneName, 'pickup' AS journeyType, 35.00 AS basePrice
    UNION ALL
    SELECT 'Edinburgh Airport', 'Zone A', 'dropoff', 35.00
    UNION ALL
    SELECT 'Edinburgh Airport', 'Zone B', 'pickup', 45.00
    UNION ALL
    SELECT 'Edinburgh Airport', 'Zone B', 'dropoff', 45.00
    UNION ALL
    SELECT 'Glasgow Airport', 'Zone A', 'pickup', 95.00
    UNION ALL
    SELECT 'Glasgow Airport', 'Zone A', 'dropoff', 95.00
) prices

JOIN airports a
    ON a.airportName = prices.airportName

JOIN zones z
    ON z.zoneName = prices.zoneName

JOIN pricing_periods pp
    ON pp.periodName = 'Weekday Day'

JOIN passenger_bands pb
    ON pb.serviceType = 'airport_transfer';


-- =========================================================
-- CREATE TEMPORARY NIGHT / WEEKEND PRICES
--
-- These differences are ONLY development defaults.
-- Admin will later set exact prices.
-- =========================================================

-- Weekday Night = Weekday Day + £10

INSERT IGNORE INTO airport_pricing
(
    vehicleID,
    airportID,
    zoneID,
    pricingPeriodID,
    passengerBandID,
    journeyType,
    basePrice,
    isActive
)
SELECT
    ap.vehicleID,
    ap.airportID,
    ap.zoneID,
    night.pricingPeriodID,
    ap.passengerBandID,
    ap.journeyType,
    ap.basePrice + 10.00,
    ap.isActive
FROM airport_pricing ap

JOIN pricing_periods currentPeriod
    ON ap.pricingPeriodID = currentPeriod.pricingPeriodID

JOIN pricing_periods night
    ON night.periodName = 'Weekday Night'

WHERE ap.vehicleID = 1
  AND currentPeriod.periodName = 'Weekday Day';


-- Weekend Day = Weekday Day + £5

INSERT IGNORE INTO airport_pricing
(
    vehicleID,
    airportID,
    zoneID,
    pricingPeriodID,
    passengerBandID,
    journeyType,
    basePrice,
    isActive
)
SELECT
    ap.vehicleID,
    ap.airportID,
    ap.zoneID,
    weekendDay.pricingPeriodID,
    ap.passengerBandID,
    ap.journeyType,
    ap.basePrice + 5.00,
    ap.isActive
FROM airport_pricing ap

JOIN pricing_periods currentPeriod
    ON ap.pricingPeriodID = currentPeriod.pricingPeriodID

JOIN pricing_periods weekendDay
    ON weekendDay.periodName = 'Weekend Day'

WHERE ap.vehicleID = 1
  AND currentPeriod.periodName = 'Weekday Day';


-- Weekend Night = Weekday Day + £15

INSERT IGNORE INTO airport_pricing
(
    vehicleID,
    airportID,
    zoneID,
    pricingPeriodID,
    passengerBandID,
    journeyType,
    basePrice,
    isActive
)
SELECT
    ap.vehicleID,
    ap.airportID,
    ap.zoneID,
    weekendNight.pricingPeriodID,
    ap.passengerBandID,
    ap.journeyType,
    ap.basePrice + 15.00,
    ap.isActive
FROM airport_pricing ap

JOIN pricing_periods currentPeriod
    ON ap.pricingPeriodID = currentPeriod.pricingPeriodID

JOIN pricing_periods weekendNight
    ON weekendNight.periodName = 'Weekend Night'

WHERE ap.vehicleID = 1
  AND currentPeriod.periodName = 'Weekday Day';