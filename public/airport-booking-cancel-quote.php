<?php

session_start();

unset($_SESSION["pending_airport_booking"]);
unset($_SESSION["airport_booking_draft"]);
unset($_SESSION["after_login_redirect"]);

header("Location: /airport-transfers.php");
exit;