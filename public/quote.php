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
                        Use this form for journeys that need manual confirmation, including extra stops,
                        unusual luggage, local rides, long-distance journeys and personalised Scotland tours.
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

                    <div class="mb-4">
                        <label class="form-label fw-bold">What do you need a quote for?</label>
                        <select class="form-select" name="quote_type" required>
                            <option value="">Please choose</option>
                            <option value="airport">Airport transfer</option>
                            <option value="local">Local journey</option>
                            <option value="long_distance">Long-distance ride</option>
                            <option value="tour">Scotland tour</option>
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
                            <label class="form-label fw-bold">Preferred date</label>
                            <input type="date" class="form-control" name="preferred_date" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Hour</label>
                            <select class="form-select" name="preferred_hour" required>
                                <option value="">Hour</option>
                                <?php for ($h = 0; $h <= 23; $h++): ?>
                                    <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>">
                                        <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Minutes</label>
                            <select class="form-select" name="preferred_minute" required>
                                <option value="">Minutes</option>
                                <?php for ($m = 0; $m <= 55; $m += 5): ?>
                                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>">
                                        <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pickup location</label>
                            <input type="text" class="form-control" name="pickup_location" placeholder="Address, postcode or area" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Destination</label>
                            <input type="text" class="form-control" name="destination" placeholder="Airport, address, town or tour destination" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Passengers</label>
                            <select class="form-select" name="passengers" required>
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
                            <select class="form-select" name="large_cases" required>
                                <option value="">Please choose</option>
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <option value="more">More / unsure</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Small bags</label>
                            <select class="form-select" name="small_bags" required>
                                <option value="">Please choose</option>
                                <?php for ($i = 0; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                                <option value="more">More / unsure</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Journey details</label>
                        <textarea class="form-control" name="journey_details" rows="5" placeholder="Please include extra stops, flight number, buggy, child seats, oversized luggage, tour ideas, waiting time or any special requests." required></textarea>
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