<?php
$pageTitle = "Request a Quote | EdiVentures";
$metaDescription = "Request a quote from EdiVentures for airport transfers, private hire journeys, long-distance rides, Scotland tours and personalised travel requests.";
$canonicalUrl = "https://www.ediventures.co.uk/quote";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero text-white">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Request a Quote</p>
                <h1 class="page-title">Tell us about your journey</h1>
                <p class="page-hero-text">
                    Use this form for airport transfers, local journeys, long-distance rides and personalised Scotland tours.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <form action="#" method="post" id="quoteForm">

                <h2 class="section-title mb-4">Quote request details</h2>

                <div class="quick-contact-box mb-4">
                    <h3 class="h5 fw-bold">Prefer a quick message?</h3>
                    <p class="mb-3">
                        You can complete the quote form below, or contact us directly. For urgent journeys, calling or WhatsApp may be quicker.
                    </p>

                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a href="tel:+447944267723" class="btn btn-brand rounded-pill px-4">
                            <i class="fa-solid fa-phone me-2"></i>Call Us
                        </a>

                        <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-success rounded-pill px-4" target="_blank">
                            <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp Us
                        </a>

                        <a href="https://www.facebook.com/people/EdiVentures/61561641614053/" class="btn btn-primary rounded-pill px-4" target="_blank">
                            <i class="fa-brands fa-facebook-messenger me-2"></i>Facebook Message
                        </a>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">What do you need a quote for?</label>
                    <select class="form-select" name="quote_type" id="quoteType" required>
                        <option value="">Please choose</option>
                        <option value="airport">Airport transfer</option>
                        <option value="local">Local journey</option>
                        <option value="long_distance">Long-distance ride</option>
                        <option value="tour">Enquiry about our tours</option>
                        <option value="custom_tour">Build your own tour</option>
                        <option value="other">Other enquiry</option>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Full name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone number</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold">Email address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Enquiry date</label>
                        <input type="date" class="form-control" name="preferred_date" id="quoteDate" required>
                    </div>

                    <div class="col-md-3 quote-time-field">
                        <label class="form-label fw-bold">Hour</label>
                        <select class="form-select" name="preferred_hour" id="quoteHour" required>
                            <option value="">Hour</option>
                            <?php for ($h = 0; $h <= 23; $h++): ?>
                                <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>">
                                    <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-3 quote-time-field">
                        <label class="form-label fw-bold">Minutes</label>
                        <select class="form-select" name="preferred_minute" id="quoteMinute" required>
                            <option value="">Minutes</option>
                            <?php for ($m = 0; $m <= 55; $m += 5): ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>">
                                    <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-text mt-2">
                    For urgent journeys, please call or WhatsApp us instead of using the form.
                </div>

                <!-- Tour Enquiry Fields -->
                <div id="tourEnquiryFields" class="quote-section d-none">

                    <div class="mt-4">
                        <label class="form-label fw-bold">Which tour are you enquiring about?</label>
                        <select class="form-select" name="tour_name">
                            <option value="">Please choose</option>
                            <option value="edinburgh_half_day">Edinburgh Half-Day Tour</option>
                            <option value="edinburgh_full_day">Edinburgh Full-Day Tour</option>
                            <option value="st_andrews_fife">St Andrews and Fife Tour</option>
                            <option value="kelpies_stirling">Kelpies and Stirling Tour</option>
                            <option value="rosslyn_chapel">Rosslyn Chapel Tour</option>
                            <option value="outlander">Outlander Locations Tour</option>
                            <option value="heritage">Scottish Heritage Tour</option>
                            <option value="not_sure">Not sure yet</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <h3 class="h5 fw-bold">Pickup location / hotel</h3>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Pickup postcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-uppercase" name="tour_pickup_postcode" placeholder="Example: EH1 1YZ">
                                    <button class="btn btn-outline-dark" type="button" disabled>
                                        Find Address
                                    </button>
                                </div>
                                <div class="form-text">Address lookup will be connected later.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Town / city</label>
                                <input type="text" class="form-control" name="tour_pickup_city" placeholder="Example: Edinburgh">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hotel / house / flat number</label>
                                <input type="text" class="form-control" name="tour_pickup_house" placeholder="Example: Hotel name or 1F1">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Street</label>
                                <input type="text" class="form-control" name="tour_pickup_street" placeholder="Example: Princes Street">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Your question or notes</label>
                        <textarea class="form-control"
                                name="tour_enquiry_notes"
                                rows="5"
                                placeholder="Ask about availability, price, pickup, duration, accessibility, family suitability or anything else."></textarea>
                    </div>

                </div>

                <!-- Build Your Own Tour Fields -->
                <div id="customTourFields" class="quote-section d-none">

                    <div class="mt-4">
                        <h3 class="h5 fw-bold">Pickup location / hotel</h3>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Pickup postcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-uppercase" name="custom_tour_pickup_postcode" placeholder="Example: EH1 1YZ">
                                    <button class="btn btn-outline-dark" type="button" disabled>
                                        Find Address
                                    </button>
                                </div>
                                <div class="form-text">Address lookup will be connected later.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Town / city</label>
                                <input type="text" class="form-control" name="custom_tour_pickup_city" placeholder="Example: Edinburgh">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Hotel / house / flat number</label>
                                <input type="text" class="form-control" name="custom_tour_pickup_house" placeholder="Example: Hotel name or 1F1">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Street</label>
                                <input type="text" class="form-control" name="custom_tour_pickup_street" placeholder="Example: Princes Street">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Preferred places or interests</label>
                        <textarea class="form-control"
                                name="custom_tour_interests"
                                rows="5"
                                placeholder="Tell us what you would like to see during your personalised Scotland tour. You may already have specific places in mind and would like a quote for a custom journey, for example castles, Highlands, coastal villages, Outlander locations, golf, scenic routes or family heritage locations."></textarea>
                    </div>

                </div>

                <!-- Local / Long-distance Journey Fields -->
                <div id="localJourneyFields" class="quote-section d-none">

                    <div class="row g-3 mt-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pickup postcode</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-uppercase" name="pickup_postcode" placeholder="Example: EH30 9PP">
                                <button class="btn btn-outline-dark" type="button" disabled>
                                    Find Address
                                </button>
                            </div>
                            <div class="form-text">
                                Address lookup will be connected later.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Town / city</label>
                            <input type="text" class="form-control" name="pickup_city" placeholder="Example: South Queensferry">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">House / flat number</label>
                            <input type="text" class="form-control" name="pickup_house" placeholder="Example: 12 or 1F1">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">Street</label>
                            <input type="text" class="form-control" name="pickup_street" placeholder="Example: High Street">
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="h5 fw-bold">Destination details</h3>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Destination postcode</label>
                            <div class="input-group">
                                <input type="text" class="form-control text-uppercase" name="destination_postcode" placeholder="Example: EH1 1YZ">
                                <button class="btn btn-outline-dark" type="button" disabled>
                                    Find Address
                                </button>
                            </div>
                            <div class="form-text">
                                Address lookup will be connected later.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Destination town / city</label>
                            <input type="text" class="form-control" name="destination_city" placeholder="Example: Edinburgh">
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Destination house / flat number</label>
                            <input type="text" class="form-control" name="destination_house" placeholder="Example: 12 or 1F1">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">Destination street</label>
                            <input type="text" class="form-control" name="destination_street" placeholder="Example: Princes Street">
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Passengers</label>
                            <select class="form-select" name="passengers">
                                <option value="">Please choose</option>
                                <option value="1">1 passenger</option>
                                <option value="2">2 passengers</option>
                                <option value="3">3 passengers</option>
                                <option value="4">4 passengers</option>
                                <option value="5">5 passengers</option>
                                <option value="6">6 passengers</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Journey details</label>
                        <textarea class="form-control" name="journey_details" rows="5" placeholder="Add extra stops, child seats, waiting time, luggage information or any other details."></textarea>
                    </div>

                </div>

                <!-- Airport Quote Fields -->
                <div id="airportQuoteFields" class="quote-section d-none">

                    <div class="mb-4 mt-4">
                        <label class="form-label fw-bold">Airport journey type</label>
                        <select class="form-select" name="airport_journey_type" id="airportQuoteJourneyType">
                            <option value="">Please choose</option>
                            <option value="dropoff">Airport drop-off</option>
                            <option value="pickup">Airport pickup</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Airport</label>
                        <select class="form-select" name="airport_name">
                            <option value="">Please choose airport</option>
                            <option value="edinburgh">Edinburgh Airport</option>
                            <option value="glasgow">Glasgow Airport</option>
                            <option value="prestwick">Glasgow Prestwick Airport</option>
                            <option value="dundee">Dundee Airport</option>
                            <option value="newcastle">Newcastle Airport</option>
                            <option value="other">Other airport</option>
                        </select>
                    </div>

                    <div class="mt-4 d-none" id="quoteFlightNumberGroup">
                        <label class="form-label fw-bold">Flight number</label>
                        <input type="text" class="form-control text-uppercase" name="flight_number" placeholder="Example: BA145">
                        <div class="form-text">
                            For airport pickups, this helps us check arrival times where possible. Flight API checking may be added later.
                        </div>
                    </div>

                    <div id="airportQuoteAddressFields" class="mt-4">

                        <h3 class="h5 fw-bold" id="airportQuoteAddressTitle">Journey address details</h3>
                        <p class="form-text mb-3" id="airportQuoteAddressHelp">
                            Choose airport pickup or drop-off first.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Postcode</label>

                                <div class="input-group">
                                    <input type="text"
                                        class="form-control text-uppercase"
                                        name="airport_quote_postcode"
                                        placeholder="Example: EH30 9PP">

                                    <button class="btn btn-outline-dark" type="button" disabled>
                                        Find Address
                                    </button>
                                </div>

                                <div class="form-text">
                                    Address lookup will be connected later.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Town / city</label>
                                <input type="text"
                                    class="form-control"
                                    name="airport_quote_city"
                                    placeholder="Example: South Queensferry">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">House / flat number</label>
                                <input type="text"
                                    class="form-control"
                                    name="airport_quote_house"
                                    placeholder="Example: 12 or 1F1">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Street</label>
                                <input type="text"
                                    class="form-control"
                                    name="airport_quote_street"
                                    placeholder="Example: High Street">
                            </div>
                        </div>

                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Passengers</label>
                            <select class="form-select" name="airport_passengers">
                                <option value="">Please choose</option>
                                <option value="1">1 passenger</option>
                                <option value="2">2 passengers</option>
                                <option value="3">3 passengers</option>
                                <option value="4">4 passengers</option>
                                <option value="5">5 passengers</option>
                                <option value="6">6 passengers</option>
                                <option value="more">More than 6 / please advise</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Large suitcases</label>
                            <select class="form-select" name="airport_large_cases">
                                <option value="">Please choose</option>
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <option value="more">More / unsure</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Small bags</label>
                            <select class="form-select" name="airport_small_bags">
                                <option value="">Please choose</option>
                                <?php for ($i = 0; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <option value="more">More / unsure</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Airport journey details</label>
                        <textarea class="form-control" name="airport_journey_details" rows="5" placeholder="Add extra stops, child seats, oversized luggage, buggy, waiting time, or anything else we should know."></textarea>
                    </div>

                </div>

                <div class="alert alert-info mt-4">
                    <strong>Please note:</strong> this form does not confirm a booking automatically. We will check availability and reply with a quote.
                </div>

                <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                    <button type="submit" class="btn btn-brand btn-lg rounded-pill px-5">
                        Send Quote Request
                    </button>

                    <a href="https://api.whatsapp.com/send?phone=447944267723" class="btn btn-outline-dark btn-lg rounded-pill px-5">
                        WhatsApp Us
                    </a>
                </div>

            </form>

        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>