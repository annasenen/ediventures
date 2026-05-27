<?php
$pageTitle = "Book Airport Transfer | EdiVentures";
$metaDescription = "Book selected airport pickups and drop-offs with EdiVentures. Enter your airport, date, time, passengers and luggage details.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-booking";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero airport-hero text-white">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Airport Booking</p>
                <h1 class="page-title">Book an Airport Pickup or Drop-off</h1>
                <p class="page-hero-text">
                    Use this form for selected airport transfers. If your airport or journey is not listed, please request a quote instead.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <form action="#" method="post">

                <h2 class="section-title mb-4">Airport transfer details</h2>

                <div class="mb-4">
                    <label class="form-label fw-bold">Journey type</label>
                    <select class="form-select" name="journey_type" id="journeyType" required>
                        <option value="">Please choose</option>
                        <option value="dropoff">Airport drop-off</option>
                        <option value="pickup">Airport pickup</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Airport</label>
                    <select class="form-select" name="airport" required>
                        <option value="">Please choose airport</option>
                        <option value="edinburgh">Edinburgh Airport</option>
                        <option value="glasgow">Glasgow Airport</option>
                        <option value="prestwick">Prestwick Airport</option>
                        <option value="dundee">Dundee Airport</option>
                        <option value="aberdeen">Aberdeen Airport</option>
                        <option value="inverness">Inverness Airport</option>
                        <option value="other">Other airport — request a quote</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold" id="addressLabel">Pickup or drop-off address</label>
                        <input type="text" class="form-control" name="address" placeholder="Example: 10 High Street, South Queensferry" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Postcode</label>
                        <input type="text" class="form-control text-uppercase" name="postcode" placeholder="EH30..." required>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Travel date</label>
                        <input type="date" class="form-control" name="travel_date" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold" id="timeLabel">Pickup time / arrival time</label>
                        <input type="time" class="form-control" name="travel_time" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label fw-bold">Flight number, where available</label>
                    <input type="text" class="form-control text-uppercase" name="flight_number" placeholder="Example: BA145">
                    <div class="form-text">
                        For airport pickups, this helps us check arrival times where possible.
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Number of passengers</label>
                        <select class="form-select" name="passengers" required>
                            <option value="">Please choose</option>
                            <option value="1">1 passenger</option>
                            <option value="2">2 passengers</option>
                            <option value="3">3 passengers</option>
                            <option value="4">4 passengers</option>
                            <option value="5">5 passengers</option>
                            <option value="6">6 passengers</option>
                        </select>
                        <div class="form-text">
                            Prices and luggage space may depend on whether the booking is for up to 4 passengers or 5–6 passengers.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Luggage details</label>
                        <select class="form-select" name="luggage" required>
                            <option value="">Please choose</option>
                            <option value="small">Small bags only</option>
                            <option value="medium">Medium luggage</option>
                            <option value="large">Large suitcases</option>
                            <option value="mixed">Mixed luggage / buggy / special items</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label fw-bold">Additional notes</label>
                    <textarea class="form-control" name="notes" rows="4" placeholder="Add child seats, large luggage, extra stops or special requests here."></textarea>
                </div>

                <div class="alert alert-info mt-4">
                    <strong>Next step:</strong> this form will later check availability and show an estimated price. 
                    If the journey cannot be booked online, you will be guided to request a quote.
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <button type="submit" class="btn btn-brand btn-lg rounded-pill px-5">
                        Check Availability
                    </button>
                    <a href="/quote.php" class="btn btn-outline-dark btn-lg rounded-pill px-5">
                        Request a Quote Instead
                    </a>
                </div>

            </form>

        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>