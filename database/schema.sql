CREATE DATABASE IF NOT EXISTS ediventures_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ediventures_db;

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

DROP TABLE IF EXISTS custom_tour_requests;

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
    minimumNoticeHours INT DEFAULT 0
);

CREATE TABLE airport_pricing (
    airportPriceID INT AUTO_INCREMENT PRIMARY KEY,
    airportName VARCHAR(100) NOT NULL,
    zoneName VARCHAR(50) NOT NULL,
    journeyType VARCHAR(30) NOT NULL,
    basePrice DECIMAL(10,2) NOT NULL,
    isActive TINYINT(1) DEFAULT 1
);

CREATE TABLE airport_charges (
    airportChargeID INT AUTO_INCREMENT PRIMARY KEY,
    airportName VARCHAR(100) NOT NULL,
    pickupCharge DECIMAL(10,2) DEFAULT 0.00,
    dropoffCharge DECIMAL(10,2) DEFAULT 0.00,
    isActive TINYINT(1) DEFAULT 1,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
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
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customerID) REFERENCES customers(customerID)
        ON DELETE CASCADE,

    FOREIGN KEY (vehicleID) REFERENCES vehicles(vehicleID)
        ON DELETE SET NULL
);