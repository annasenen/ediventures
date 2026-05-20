<?php
$pageTitle = "EdiVentures | Airport Transfers, Private Hire and Scotland Tours";
$metaDescription = "Family-run private hire transport from South Queensferry. Book airport transfers, local and long-distance journeys, and private tours across Scotland with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-75 gy-5">
                <div class="col-lg-6">
                    <p class="eyebrow mb-3">South Queensferry • Edinburgh • Scotland</p>

                    <h1 class="hero-title">
                        Airport Transfers, Private Hire and Scotland Tours
                    </h1>

                    <p class="hero-text">
                        EdiVentures is a family-run private hire business based in South Queensferry.
                        We offer reliable airport transfers, local and long-distance journeys, and private
                        tours across Scotland.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                        <a href="/quote.php" class="btn btn-brand btn-lg rounded-pill px-4">Get a Quote</a>
                        <a href="/scotland-tours.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Explore Tours</a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-image-card">
                        <img src="/assets/img/luxury-edinburgh-tour-vehicle-scotland.jpg"
                             alt="EdiVentures private hire vehicle for airport transfers and Scotland tours"
                             class="img-fluid rounded-4">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="trust-bar py-4">
        <div class="container">
            <div class="row text-center gy-3">
                <div class="col-6 col-lg-3">
                    <i class="fa-solid fa-plane-departure"></i>
                    <p>Airport Transfers</p>
                </div>
                <div class="col-6 col-lg-3">
                    <i class="fa-solid fa-car-side"></i>
                    <p>Private Hire Rides</p>
                </div>
                <div class="col-6 col-lg-3">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <p>Scotland Tours</p>
                </div>
                <div class="col-6 col-lg-3">
                    <i class="fa-solid fa-people-group"></i>
                    <p>Family-Run Service</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-5">
                    <p class="section-label">Welcome to EdiVentures</p>
                    <h2 class="section-title">Transport that works around your plans</h2>
                </div>
                <div class="col-lg-7">
                    <p class="lead">
                        Whether you need a pre-booked airport transfer, a local journey around South Queensferry
                        and Edinburgh, a longer-distance ride, or a personalised tour across Scotland, EdiVentures
                        provides a comfortable and reliable service with friendly communication.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label">Our Services</p>
                <h2 class="section-title">Choose the right service for your journey</h2>
            </div>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="service-card h-100">
                        <img src="/assets/img/vehicle-airport-transfer.png" alt="Airport transfer vehicle service in Scotland">
                        <div class="p-4">
                            <h3>Airport Transfers</h3>
                            <p>
                                Pre-booked airport transfers for Edinburgh, Glasgow and other Scottish airports,
                                with comfortable travel and clear communication before your journey.
                            </p>
                            <a href="/airport-transfers.php" class="text-link">Airport transfer options <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card h-100">
                        <img src="/assets/img/local-transfer-edinburgh.png" alt="Private hire journey in Edinburgh and South Queensferry">
                        <div class="p-4">
                            <h3>Local & Long-Distance Rides</h3>
                            <p>
                                Private hire journeys for local travel, events, appointments, business trips,
                                city journeys and longer-distance transport across Scotland.
                            </p>
                            <a href="/private-hire.php" class="text-link">Request a ride quote <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card h-100">
                        <img src="/assets/img/car-one-day-tour-scottish-highlands.png" alt="Private Scotland tour vehicle">
                        <div class="p-4">
                            <h3>Scotland Tours</h3>
                            <p>
                                Private tours across Scotland, including castles, coastal towns, scenic routes,
                                historic landmarks and personalised travel plans.
                            </p>
                            <a href="/scotland-tours.php" class="text-link">View Scotland tours <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-5 bg-dark text-white">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label text-brand">Online Booking Coming Soon</p>
                <h2 class="section-title">Simple online booking for airport transfers and tours</h2>
                <p class="section-intro mx-auto">
                    Our online booking system will allow customers to check available dates and times for selected
                    airport transfers and Scotland tours. For local journeys, long-distance rides and personalised
                    requests, customers can still contact us by form or WhatsApp.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="booking-card">
                        <i class="fa-solid fa-calendar-check"></i>
                        <h3>Check Availability</h3>
                        <p>Choose your airport transfer or tour and check available dates and times before booking.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="booking-card">
                        <i class="fa-solid fa-list-check"></i>
                        <h3>Confirm Details</h3>
                        <p>Enter your journey details, passenger numbers, luggage information and contact details.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="booking-card">
                        <i class="fa-solid fa-credit-card"></i>
                        <h3>Pay a Deposit</h3>
                        <p>Pay a secure online deposit to confirm your booking, with the remaining balance paid to the driver.</p>
                    </div>
                </div>

            </div>

            <div class="text-center mt-5">
                <a href="/availability.php" class="btn btn-brand btn-lg rounded-pill px-5">Ask About Availability</a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-5">
                    <p class="section-label">Private Tours</p>
                    <h2 class="section-title">Explore Scotland your way</h2>
                    <p>
                        Our tours are not limited to one location. We can help you explore historic landmarks,
                        scenic routes, coastal towns, castles and countryside locations across Scotland.
                    </p>
                    <a href="/build-your-tour.php" class="btn btn-brand rounded-pill px-4">Build Your Tour</a>
                </div>

                <div class="col-lg-7">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <div class="feature-box">
                                <i class="fa-solid fa-landmark"></i>
                                <h3>Historic Places</h3>
                                <p>Castles, old towns, monuments, heritage sites and famous Scottish landmarks.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="feature-box">
                                <i class="fa-solid fa-mountain-sun"></i>
                                <h3>Scenic Routes</h3>
                                <p>Lochs, countryside roads, coastal views and peaceful photo stops.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="feature-box">
                                <i class="fa-solid fa-map"></i>
                                <h3>Flexible Plans</h3>
                                <p>Choose a ready tour or request a personalised route based on your interests.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="feature-box">
                                <i class="fa-solid fa-car"></i>
                                <h3>Comfortable Travel</h3>
                                <p>Private vehicle travel without the pressure of large group tours.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label">How To Contact Us</p>
                <h2 class="section-title">Simple options for different journeys</h2>
            </div>

            <div class="row g-4 text-center">

                <div class="col-md-4">
                    <div class="step-box">
                        <span>1</span>
                        <h3>Airport Transfers</h3>
                        <p>Use the booking system when available, or request a quote with your flight, date, time and luggage details.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-box">
                        <span>2</span>
                        <h3>Local & Long Rides</h3>
                        <p>Send your pickup, destination, time, passengers and luggage by form or WhatsApp for a clear quote.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-box">
                        <span>3</span>
                        <h3>Scotland Tours</h3>
                        <p>Choose a planned tour or ask us to build a personalised tour around your interests.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="final-cta text-white text-center">
        <div class="container">
            <h2 class="fw-bold mb-3">Need a transfer, ride or Scotland tour?</h2>
            <p class="lead mb-4">
                Send us your journey details and we will reply with availability and a quote.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="/quote.php" class="btn btn-light btn-lg rounded-pill px-5">Get a Quote</a>
                <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-outline-light btn-lg rounded-pill px-5">WhatsApp Us</a>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>