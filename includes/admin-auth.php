<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION["customerID"])) {
    header("Location: /login.php");
    exit;
}

$customerID = $_SESSION["customerID"];

$sql = "SELECT r.roleName
        FROM customer_roles cr
        JOIN roles r ON cr.roleID = r.roleID
        WHERE cr.customerID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$customerID]);

$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array("admin", $roles, true)) {
    header("Location: /customer/dashboard.php");
    exit;
}