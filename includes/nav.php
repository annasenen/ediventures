<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">
            <img src="/assets/img/ediventures-logo.png" alt="EdiVentures logo" class="site-logo">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="mainNavigation" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/airport-transfers.php">Airport Transfers</a></li>
                <li class="nav-item"><a class="nav-link" href="/private-hire.php">Private Hire</a></li>
                <li class="nav-item"><a class="nav-link" href="/scotland-tours.php">Scotland Tours</a></li>
                <li class="nav-item"><a class="nav-link" href="/build-your-tour.php">Build Your Tour</a></li>
                <?php if (isset($_SESSION["customerID"])): ?>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-brand rounded-pill px-4" href="/customer/dashboard.php">
                            My Account
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-dark rounded-pill px-4" href="/logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-brand rounded-pill px-4" href="/contact.php">
                            Get a Quote
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-dark rounded-pill px-4" href="/login.php">
                            Login
                        </a>
                    </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>