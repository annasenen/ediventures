<?php
$pageTitle = "Build Your Own Private Scotland Tour | EdiVentures";
$metaDescription = "Create your own private Scotland tour with EdiVentures. Tell us where you would like to go, your pickup location, date, time and group size, and we will prepare a custom quote.";
$canonicalUrl = "https://www.ediventures.co.uk/build-your-tour.php";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero text-white"
    style="background: linear-gradient(rgba(0,0,0,0.68), rgba(0,0,0,0.68)), url('/assets/img/view-loch-lomond-one-day-tour.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Custom Private Tours</p>
                <h1 class="page-title">Build Your Own Scotland Tour</h1>
                <p class="page-hero-text">
                    Tell us where you would like to go, how much time you have, and what kind of places you enjoy.
                    We will prepare a private tour suggestion and quote for your journey.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <p class="section-label">How It Works</p>
            <h2 class="section-title">A simple way to plan your own tour</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-box">
                    <span>1</span>
                    <h3>Tell us your ideas</h3>
                    <p>Share your preferred places, date, pickup location and group size.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-box">
                    <span>2</span>
                    <h3>We check the route</h3>
                    <p>We review timing, distance, vehicle availability and what is realistic for the day.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-box">
                    <span>3</span>
                    <h3>Secure your booking</h3>
                    <p>Once agreed, your booking can be confirmed with a 20% deposit.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-light">
    <div class="container">
        <div class="row gy-4 align-items-start">

            <div class="col-lg-5">
                <p class="section-label">Tour Ideas</p>
                <h2 class="section-title">What kind of tour would you like?</h2>
                <p>
                    You can request castles, lochs, whisky stops, golf destinations, film locations,
                    scenic villages, family-friendly days out or a mix of different places.
                </p>

                <div class="custom-tour-box mt-3">
                    <h3>Popular custom tour ideas</h3>
                    <ul>
                        <li>Castles and Scottish history</li>
                        <li>Lochs, scenery and viewpoints</li>
                        <li>St Andrews and golf locations</li>
                        <li>Whisky or distillery stops</li>
                        <li>Outlander or film locations</li>
                        <li>Family-friendly sightseeing</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="booking-form-card">
                    <h2 class="section-title mb-3">Custom Tour Request</h2>

                    <form action="/quote.php" method="post">

                        <input type="hidden" name="quote_type" value="build_your_own_tour">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="passengers" class="form-label">Number of Passengers</label>
                                <select id="passengers" name="passengers" class="form-select">
                                    <option value="">Please select</option>
                                    <option value="1">1 passenger</option>
                                    <option value="2">2 passengers</option>
                                    <option value="3">3 passengers</option>
                                    <option value="4">4 passengers</option>
                                    <option value="5">5 passengers</option>
                                    <option value="6">6 passengers</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="tour_date" class="form-label">Preferred Date</label>
                                <input type="date" id="tour_date" name="tour_date" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Preferred Pickup Time</label>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <select name="pickup_hour" class="form-select">
                                            <option value="">Hour</option>
                                            <?php for ($hour = 6; $hour <= 23; $hour++): ?>
                                                <option value="<?= sprintf('%02d', $hour) ?>">
                                                    <?= sprintf('%02d', $hour) ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <div class="col-6">
                                        <select name="pickup_minute" class="form-select">
                                            <option value="">Minute</option>
                                            <option value="00">00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="tour_length" class="form-label">How much time do you have?</label>
                                <select id="tour_length" name="tour_length" class="form-select">
                                    <option value="">Please select</option>
                                    <option value="half_day">Half day</option>
                                    <option value="full_day">Full day</option>
                                    <option value="custom_hours">Specific number of hours</option>
                                    <option value="not_sure">Not sure yet</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="pickup_location" class="form-label">Pickup Location / Hotel</label>
                                <input type="text" id="pickup_location" name="pickup_location" class="form-control" placeholder="Hotel, address or area">
                            </div>

                            <div class="col-12">
                                <label for="places" class="form-label">Places you would like to visit</label>
                                <textarea id="places" name="places" class="form-control" rows="4" placeholder="Example: Stirling Castle, The Kelpies, Loch Lomond, St Andrews..."></textarea>
                            </div>

                            <div class="col-12">
                                <label for="interests" class="form-label">What are you interested in?</label>
                                <textarea id="interests" name="interests" class="form-control" rows="3" placeholder="History, castles, scenery, whisky, golf, family activities, photo stops..."></textarea>
                            </div>

                            <div class="col-12">
                                <label for="extra_details" class="form-label">Any extra details?</label>
                                <textarea id="extra_details" name="extra_details" class="form-control" rows="3" placeholder="Mobility needs, luggage, children, special occasion, lunch stop preferences..."></textarea>
                            </div>

                            <div class="col-12">
                                <div class="quick-contact-box">
                                    <strong>Please note:</strong>
                                    Custom tours are quoted individually. Once the route and availability are confirmed,
                                    the booking can be secured with a 20% deposit.
                                </div>
                            </div>

                            <div class="col-12 d-grid d-md-flex gap-3">
                                <button type="submit" class="btn btn-brand btn-lg rounded-pill px-4">
                                    Send Custom Tour Request
                                </button>

                                <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                                    WhatsApp Us
                                </a>
                            </div>

                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="py-4 bg-white">
    <div class="container text-center">
        <p class="section-label">Need Inspiration?</p>
        <h2 class="section-title">Not sure where to go?</h2>
        <p class="lead mx-auto" style="max-width: 760px;">
            Tell us your interests and we can suggest a realistic route based on your time, pickup location and group size.
        </p>

        <a href="/scotland-tours.php" class="btn btn-outline-dark rounded-pill px-4 mt-3">
            View Scotland Tours
        </a>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>