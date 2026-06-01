<?php
$pageTitle = "Private Hire South Queensferry | Local and Long-Distance Rides";
$metaDescription = "Private hire journeys from South Queensferry, including local rides, Edinburgh trips, events, appointments and long-distance travel across Scotland.";
$canonicalUrl = "https://www.ediventures.co.uk/private-hire";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero text-white">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Private Hire Journeys</p>
                <h1 class="page-title">Local and Long-Distance Rides</h1>
                <p class="page-hero-text">
                    Pre-booked private hire journeys from South Queensferry, Edinburgh and nearby areas, including local trips,
                    Edinburgh journeys, events, appointments and longer-distance travel.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <a href="/quote.php" class="btn btn-brand btn-lg rounded-pill px-4">Request a Quote</a>
                    <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-outline-light btn-lg rounded-pill px-4">WhatsApp Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-5">
                <p class="section-label">Local travel</p>
                <h2 class="section-title">Reliable journeys for everyday travel</h2>
            </div>
            <div class="col-lg-7">
                <p class="lead">
                    EdiVentures supports local customers with comfortable private hire journeys for short and longer trips.
                    Whether you need transport to Edinburgh, a local appointment, an evening event or a longer journey,
                    you can request a quote and we will check availability.
                </p>
                <p>
                    All journeys must be pre-booked. For trips outside our usual area, unusual luggage, extra stops or larger
                    groups, please use the quote form so we can confirm the details properly.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label">Journey Types</p>
            <h2 class="section-title">Private hire options</h2>
        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center h-100">
                    <i class="fa-solid fa-location-dot"></i>
                    <h3>Local Journeys</h3>
                    <p>Short local journeys from South Queensferry, Edinburgh and nearby areas, subject to availability.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center h-100">
                    <i class="fa-solid fa-city"></i>
                    <h3>Edinburgh Trips</h3>
                    <p>Pre-booked journeys into Edinburgh for restaurants, hotels, events, appointments and days out.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center h-100">
                    <i class="fa-solid fa-route"></i>
                    <h3>Long-Distance Rides</h3>
                    <p>Longer journeys across Scotland can be quoted manually based on distance, time and availability.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <p class="section-label text-brand">Request a Quote</p>
                <h2 class="section-title">Send us your journey details</h2>
                <p>
                    For private hire journeys, pricing can depend on pickup point, destination, travel time, passenger
                    numbers, luggage, waiting time and extra stops. The quote form helps us understand the journey before
                    confirming availability.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="details-box">
                    <h3>Please include:</h3>
                    <ul>
                        <li>Pickup address or postcode</li>
                        <li>Destination address or area</li>
                        <li>Date and preferred time</li>
                        <li>Number of passengers</li>
                        <li>Any luggage, buggy or extra stops</li>
                        <li>Any waiting time or special requests</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <p class="section-label">Important</p>
                <h2 class="section-title">Pre-booked private hire only</h2>
                <p>
                    EdiVentures provides pre-booked private hire journeys. Some journeys may require manual confirmation,
                    especially if they include extra stops, longer distances, unusual luggage or travel outside our usual
                    operating area.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="custom-tour-box">
                    <h3>Need a journey quote?</h3>
                    <p>
                        Use the quote form for local rides, long-distance journeys and any private hire request that needs
                        manual confirmation.
                    </p>
                    <div class="journey-action-grid">

                        <a href="/quote.php"
                        class="btn btn-brand rounded-pill">
                            Request a Quote
                        </a>

                        <a href="https://api.whatsapp.com/send?phone=447944267723"
                        class="btn btn-success rounded-pill"
                        target="_blank">
                            <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp
                        </a>

                        <a href="tel:+447944267723"
                        class="btn btn-outline-dark rounded-pill">
                            Call Us
                        </a>

                        <a href="https://www.facebook.com/people/EdiVentures/61561641614053/"
                        class="btn btn-outline-primary rounded-pill"
                        target="_blank">
                            <i class="fa-brands fa-facebook-messenger me-2"></i>Messenger
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>