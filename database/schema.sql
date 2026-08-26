CREATE DATABASE IF NOT EXISTS ediventures_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ediventures_db;

DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS vehicle_blocks;
DROP TABLE IF EXISTS airport_charges;
DROP TABLE IF EXISTS airport_pricing;
DROP TABLE IF EXISTS passenger_bands;
DROP TABLE IF EXISTS pricing_period_days;
DROP TABLE IF EXISTS pricing_periods;
DROP TABLE IF EXISTS airport_journey_rules;
DROP TABLE IF EXISTS zone_postcodes;
DROP TABLE IF EXISTS zones;
DROP TABLE IF EXISTS airports;
DROP TABLE IF EXISTS airport_reference;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS custom_tour_requests;
DROP TABLE IF EXISTS customer_roles;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS customers;


CREATE TABLE customers (
    customerID INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE roles (
    roleID INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE customer_roles (
    customerRoleID INT AUTO_INCREMENT PRIMARY KEY,
    customerID INT NOT NULL,
    roleID INT NOT NULL,

    UNIQUE (customerID, roleID),

    FOREIGN KEY (customerID) REFERENCES customers(customerID)
        ON DELETE CASCADE,

    FOREIGN KEY (roleID) REFERENCES roles(roleID)
        ON DELETE CASCADE
);

INSERT INTO roles (roleName)
VALUES
('customer'),
('admin');


CREATE TABLE custom_tour_requests (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    customerID INT NOT NULL,

    preferredDate DATE NULL,
    pickupHour VARCHAR(2) NULL,
    pickupMinute VARCHAR(2) NULL,
    passengers INT NULL,
    tourLength VARCHAR(50) NULL,
    pickupLocation VARCHAR(255) NULL,

    places TEXT NULL,
    interests TEXT NULL,
    extraDetails TEXT NULL,

    status VARCHAR(30) DEFAULT 'pending',
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customerID) REFERENCES customers(customerID)
        ON DELETE CASCADE
);


CREATE TABLE vehicles (
    vehicleID INT AUTO_INCREMENT PRIMARY KEY,
    vehicleName VARCHAR(100) NOT NULL,
    passengerCapacity INT NOT NULL DEFAULT 6,
    isActive TINYINT(1) DEFAULT 1,
    isArchived TINYINT(1) DEFAULT 0,
    isPrimaryVehicle TINYINT(1) DEFAULT 0,
    minimumNoticeHours INT DEFAULT 1,
    vehiclePriority INT DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE airport_reference (
    airportReferenceID INT AUTO_INCREMENT PRIMARY KEY,
    airportName VARCHAR(150) NOT NULL,
    iataCode VARCHAR(3) NOT NULL UNIQUE,
    cityName VARCHAR(100) NULL,
    countryName VARCHAR(100) NOT NULL,
    isAvailable TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE airports (
    airportID INT AUTO_INCREMENT PRIMARY KEY,
    airportName VARCHAR(100) NOT NULL UNIQUE,
    airportCode VARCHAR(10) NULL UNIQUE,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE zones (
    zoneID INT AUTO_INCREMENT PRIMARY KEY,
    zoneName VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE zone_postcodes (
    zonePostcodeID INT AUTO_INCREMENT PRIMARY KEY,
    zoneID INT NOT NULL,
    postcodePrefix VARCHAR(12) NOT NULL,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (zoneID, postcodePrefix),

    FOREIGN KEY (zoneID) REFERENCES zones(zoneID)
        ON DELETE CASCADE
);

CREATE TABLE pricing_periods (
    pricingPeriodID INT AUTO_INCREMENT PRIMARY KEY,
    periodName VARCHAR(100) NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL,
    priority INT NOT NULL DEFAULT 10,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pricing_period_days (
    pricingPeriodDayID INT AUTO_INCREMENT PRIMARY KEY,
    pricingPeriodID INT NOT NULL,
    dayOfWeek TINYINT NOT NULL,

    UNIQUE (pricingPeriodID, dayOfWeek),

    FOREIGN KEY (pricingPeriodID)
        REFERENCES pricing_periods(pricingPeriodID)
        ON DELETE CASCADE
);

CREATE TABLE passenger_bands (
    passengerBandID INT AUTO_INCREMENT PRIMARY KEY,
    bandName VARCHAR(100) NOT NULL,
    minPassengers INT NOT NULL,
    maxPassengers INT NOT NULL,
    serviceType VARCHAR(30) NOT NULL,
    isActive TINYINT(1) DEFAULT 1,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (
        serviceType,
        minPassengers,
        maxPassengers
    )
);

CREATE TABLE airport_pricing (
    airportPriceID INT AUTO_INCREMENT PRIMARY KEY,

    vehicleID INT NOT NULL,
    airportID INT NOT NULL,
    zoneID INT NOT NULL,
    pricingPeriodID INT NOT NULL,
    passengerBandID INT NOT NULL,

    journeyType VARCHAR(30) NOT NULL,
    basePrice DECIMAL(10,2) NOT NULL,
    isActive TINYINT(1) DEFAULT 1,

    UNIQUE (
        vehicleID,
        airportID,
        zoneID,
        journeyType,
        pricingPeriodID,
        passengerBandID
    ),

    FOREIGN KEY (vehicleID)
        REFERENCES vehicles(vehicleID)
        ON DELETE CASCADE,

    FOREIGN KEY (airportID)
        REFERENCES airports(airportID),

    FOREIGN KEY (zoneID)
        REFERENCES zones(zoneID),

    FOREIGN KEY (pricingPeriodID)
        REFERENCES pricing_periods(pricingPeriodID),

    FOREIGN KEY (passengerBandID)
        REFERENCES passenger_bands(passengerBandID)
);

CREATE TABLE airport_charges (
    airportChargeID INT AUTO_INCREMENT PRIMARY KEY,
    airportID INT NOT NULL,

    pickupCharge DECIMAL(10,2) DEFAULT 0.00,
    dropoffCharge DECIMAL(10,2) DEFAULT 0.00,

    isActive TINYINT(1) DEFAULT 1,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (airportID),

    FOREIGN KEY (airportID)
        REFERENCES airports(airportID)
);

CREATE TABLE vehicle_blocks (
    blockID INT AUTO_INCREMENT PRIMARY KEY,
    vehicleID INT NOT NULL,
    blockStart DATETIME NOT NULL,
    blockEnd DATETIME NOT NULL,
    reason VARCHAR(255) NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (vehicleID) REFERENCES vehicles(vehicleID)
        ON DELETE CASCADE
);

CREATE TABLE bookings (
    bookingID INT AUTO_INCREMENT PRIMARY KEY,
    customerID INT NOT NULL,
    vehicleID INT NULL,

    bookingType VARCHAR(50) NOT NULL,
    journeyType VARCHAR(50) NULL,

    airportName VARCHAR(100) NULL,
    zoneName VARCHAR(50) NULL,

    pickupAddress VARCHAR(255) NULL,
    dropoffAddress VARCHAR(255) NULL,
    flightNumber VARCHAR(50) NULL,

    passengers INT NULL,
    luggageDetails TEXT NULL,

    journeyStart DATETIME NOT NULL,
    journeyEnd DATETIME NOT NULL,

    basePrice DECIMAL(10,2) DEFAULT 0.00,
    airportCharge DECIMAL(10,2) DEFAULT 0.00,
    totalPrice DECIMAL(10,2) DEFAULT 0.00,
    depositPercent INT DEFAULT 20,
    depositAmount DECIMAL(10,2) DEFAULT 0.00,

    status VARCHAR(30) DEFAULT 'pending',
    holdExpiresAt DATETIME NULL,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customerID) REFERENCES customers(customerID)
        ON DELETE CASCADE,

    FOREIGN KEY (vehicleID) REFERENCES vehicles(vehicleID)
        ON DELETE SET NULL
);

CREATE TABLE airport_journey_rules (
    airportRuleID INT AUTO_INCREMENT PRIMARY KEY,
    airportID INT NOT NULL,

    journeyType VARCHAR(30) NOT NULL,
    blockMinutes INT NOT NULL,

    isActive TINYINT(1) DEFAULT 1,

    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (
        airportID,
        journeyType
    ),

    FOREIGN KEY (airportID)
        REFERENCES airports(airportID)
);