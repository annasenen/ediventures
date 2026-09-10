<?php

require_once __DIR__ . '/../../includes/admin-auth.php';

$pageTitle = "Airport Settings | EdiVentures Admin";
$metaDescription = "Manage airport booking settings.";
$canonicalUrl = "https://www.ediventures.co.uk/admin/airport-settings.php";

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/nav.php';

?>

<main>

<section
    class="account-header text-white"
    style="background: linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)), url('/assets/img/passenger-plane-airport-transfer.jpg') center/cover no-repeat;"
>
    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <p class="eyebrow mb-3">
                    Admin Area
                </p>

                <h1 class="page-title">
                    Airport Settings
                </h1>

                <p class="page-hero-text">
                    Manage airports, booking areas, pricing periods,
                    journey prices and airport-specific rules.
                </p>

            </div>

        </div>

    </div>
</section>


<section class="py-5 bg-light">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-5">

            <div>
                <h2 class="section-title mb-1">
                    Booking Configuration
                </h2>

                <p class="text-muted mb-0">
                    Choose the area you want to manage.
                </p>
            </div>

            <a
                href="/admin/dashboard.php"
                class="btn btn-outline-dark rounded-pill px-4"
            >
                Back to Admin Dashboard
            </a>

        </div>


        <div class="row g-4">


            <!-- AIRPORTS -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-plane-departure"></i>

                    <h3>
                        Airports
                    </h3>

                    <p>
                        Add airports, change airport details and control
                        which airports are available for online booking.
                    </p>

                    <a
                        href="/admin/airports.php"
                        class="text-link"
                    >
                        Manage Airports
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>

                </div>

            </div>


            <!-- ZONES -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-map-location-dot"></i>

                    <h3>
                        Zones
                    </h3>

                    <p>
                        Manage booking areas and decide which postcode
                        prefixes belong to each pricing zone.
                    </p>

                    <a
                        href="/admin/zones.php"
                        class="text-link"
                    >
                        Manage Zones
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>

                </div>

            </div>


            <!-- RATE PERIODS -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-clock"></i>

                    <h3>
                        Rate Periods
                    </h3>

                    <p>
                        Control weekday, night and weekend pricing hours,
                        including which days each rate applies to.
                    </p>

                    <a
                        href="/admin/rate-periods.php"
                        class="text-link"
                    >
                        Manage Rate Periods
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>

                </div>

            </div>


            <!-- AIRPORT PRICES -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-sterling-sign"></i>

                    <h3>
                        Airport Prices
                    </h3>

                    <p>
                        Manage prices by vehicle, airport, zone,
                        passenger group and rate period.
                    </p>

                    <a
                        href="/admin/airport-prices.php"
                        class="text-link"
                    >
                        Manage Airport Prices
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>

                </div>

            </div>


            <!-- AIRPORT CHARGES -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-receipt"></i>

                    <h3>
                        Airport Charges
                    </h3>

                    <p>
                        Manage airport pickup and drop-off access charges
                        separately from the journey price.
                    </p>

                    <a
                        href="/admin/airport-charges.php"
                        class="text-link"
                    >
                        Manage Airport Charges
                        <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>

                </div>

            </div>


            <!-- JOURNEY RULES -->

            <div class="col-md-6 col-xl-4">

                <div class="feature-box h-100">

                    <i class="fa-solid fa-hourglass-half"></i>

                    <h3>
                        Journey Rules
                    </h3>

                    <p>
                        Control how long each airport pickup or drop-off
                        reserves the assigned vehicle.
                    </p>

                    <a
                        href="#"
                        class="text-link"
                    >
                        Coming soon
                    </a>

                </div>

            </div>


        </div>

    </div>

</section>

</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>