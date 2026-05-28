<?php
$pageTitle = "Airport Transfers Scotland | EdiVentures Private Hire";
$metaDescription = "Book reliable airport transfers with EdiVentures from South Queensferry, Edinburgh and selected nearby areas. Comfortable private hire journeys for airport pickups and drop-offs.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-transfers";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

    <section class="page-hero airport-hero text-white">
        <div class="container">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-8">
                    <p class="eyebrow mb-3">Airport Transfers</p>
                    <h1 class="page-title">Airport Pickups and Drop-offs Across Scotland</h1>
                    <p class="page-hero-text">
                        Pre-booked airport transfers for departures and arrivals, including journeys from
                        South Queensferry, Edinburgh and selected nearby areas.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                        <a href="/airport-booking.php" class="btn btn-brand btn-lg rounded-pill px-4">Book Airport Transfer</a>
                        <a href="/quote.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Request a Quote</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-5">
                    <p class="section-label">Airport journeys</p>
                    <h2 class="section-title">Reliable airport transfers for departures and arrivals</h2>
                </div>
                <div class="col-lg-7">
                    <p class="lead">
                        EdiVentures provides pre-booked airport transfers for customers travelling to and from
                        Scottish airports. Whether you need an early morning airport drop-off or a pickup after
                        your flight lands, we aim to make the journey comfortable, clear and stress-free.
                    </p>
                    <p>
                        Online booking is available for selected airport routes. If your airport, postcode,
                        pickup point or journey type is not listed, you can request a quote and we will check the
                        route, timing and availability manually.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label">Choose Your Journey</p>
                <h2 class="section-title">Two simple airport transfer options</h2>
            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="service-card h-100">
                        <img src="/assets/img/vehicle-airport-transfer.png" alt="Private hire vehicle for airport drop-off in Scotland">
                        <div class="p-4">
                            <h3>Airport Drop-off</h3>
                            <p>
                                Travel from your pickup address to the airport with a pre-booked private hire journey.
                                Ideal for holidays, business trips, family travel and early morning departures.
                            </p>
                            <button type="button" class="btn btn-brand btn-lg rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#airportBookingModal">
                                Book Airport Transfer
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="service-card h-100">
                        <img src="/assets/img/passenger-plane-airport-transfer.jpg" alt="Airport pickup service for arriving passengers in Scotland">
                        <div class="p-4">
                            <h3>Airport Pickup</h3>
                            <p>
                                Arrange a pickup from the airport after your flight arrives. Please provide your
                                flight number so arrival times can be checked where possible.
                            </p>
                            <a href="/airport-booking.php?type=pickup" class="text-link">Book airport pickup <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-5 bg-dark text-white">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <p class="section-label text-brand">How Online Booking Works</p>
                    <h2 class="section-title">Book selected airport transfers online</h2>
                    <p>
                        The online booking system is designed for selected airport transfers where the route,
                        timing and travel area can be checked automatically. For journeys outside the standard
                        booking options, customers can request a quote instead.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="details-box">
                        <h3>Online booking will ask for:</h3>
                        <ul>
                            <li>Airport pickup or airport drop-off</li>
                            <li>Travel date and time</li>
                            <li>Pickup address or drop-off address</li>
                            <li>Pickup time or flight arrival time</li>
                            <li>Flight number, where available</li>
                            <li>Passenger number and luggage details</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label">Availability and Timing</p>
                <h2 class="section-title">Realistic journey time built into the booking</h2>
                <p class="lead mx-auto tour-intro">
                    Airport transfers need more than the pickup time alone. The booking system includes travel
                    time and buffer time.
                </p>
            </div>

            <div class="row g-4 text-center">

                <div class="col-md-4">
                    <div class="step-box">
                        <span>1</span>
                        <h3>Choose Journey</h3>
                        <p>Select airport pickup or drop-off, then enter your travel date and journey details.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-box">
                        <span>2</span>
                        <h3>Create Account</h3>
                        <p>Create or log in to your account so your booking details and confirmation can be saved securely.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-box">
                        <span>3</span>
                        <h3>Confirm with Deposit</h3>
                        <p>Confirm selected bookings with a secure online deposit, with the balance paid to the driver.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <p class="section-label">Not Listed?</p>
                    <h2 class="section-title">Need a different airport or pickup area?</h2>
                    <p>
                        If your airport, postcode, pickup area or journey details are not available in the online
                        booking options, please request a quote. This helps us check the distance, timing, luggage
                        space and availability properly before confirming.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="custom-tour-box">
                        <h3>Request a manual quote</h3>
                        <p>
                            Use the quote form or WhatsApp us with your date, time, pickup point, airport, passenger
                            number, luggage details and flight number if relevant.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="/quote.php" class="btn btn-brand rounded-pill px-4">Request a Quote</a>
                            <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-outline-dark rounded-pill px-4">WhatsApp Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../includes/airport-booking-modal.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>