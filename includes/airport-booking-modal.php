<!-- Airport Booking Modal -->
<div class="modal fade" id="airportBookingModal" tabindex="-1" aria-labelledby="airportBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content booking-modal">

            <div class="modal-header">
                <div>
                    <p class="section-label mb-1">Airport Transfer</p>
                    <h2 class="modal-title h4 fw-bold" id="airportBookingModalLabel">Check airport booking details</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form action="/airport-booking-check.php" method="post" id="airportBookingForm">

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
                        <select class="form-select" name="airport" id="airportSelect" required>
                            <option value="">Please choose airport</option>
                            <option value="edinburgh">Edinburgh Airport</option>
                            <option value="glasgow">Glasgow Airport</option>
                            <option value="prestwick">Glasgow Prestwick Airport</option>
                            <option value="dundee">Dundee Airport</option>
                            <option value="newcastle">Newcastle Airport</option>
                            <option value="other">Other airport — request a quote</option>
                        </select>
                    </div>

                    <div class="alert alert-warning d-none" id="manualAirportMessage">
                        This airport needs manual confirmation. Please request a quote so we can check timing, distance and availability.
                    </div>

                    <div class="mt-4">
                        <h3 class="h5 fw-bold" id="addressSectionTitle">Journey address details</h3>
                        <p class="form-text mb-3" id="addressHelpText">
                            Enter the journey address details below.
                        </p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Postcode</label>
                            <input type="text" class="form-control text-uppercase" name="postcode" placeholder="Example: EH30 9PP" maxlength="10" required>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-dark w-100 rounded-pill" disabled>
                                Find Address
                            </button>
                        </div>
                    </div>

                    <div class="form-text mt-2">
                        Address lookup will be added later. For now, please type the address manually below.
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">House / flat number or name</label>
                            <input type="text" class="form-control" name="house_number" placeholder="Example: 103 or 1F1" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold">Street</label>
                            <input type="text" class="form-control" name="street" placeholder="Example: High Street" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Town / city</label>
                            <input type="text" class="form-control" name="town_city" placeholder="Example: South Queensferry" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Travel date</label>
                            <input type="date" class="form-control" name="travel_date" id="travelDate" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hour</label>
                            <select class="form-select" name="travel_hour" required>
                                <option value="">Hour</option>
                                <?php for ($h = 0; $h <= 23; $h++): ?>
                                    <option value="<?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>">
                                        <?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Minutes</label>
                            <select class="form-select" name="travel_minute" required>
                                <option value="">Minutes</option>
                                <?php for ($m = 0; $m <= 55; $m += 5): ?>
                                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>">
                                        <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-text mt-2" id="timeHelpText">
                        Choose the required time for your airport journey.
                    </div>

                    <div class="mt-4 d-none" id="flightNumberGroup">
                        <label class="form-label fw-bold">Flight number</label>
                        <input type="text" class="form-control text-uppercase" name="flight_number" placeholder="Example: BA145">
                        <div class="form-text">
                            For airport pickups, this helps us check arrival times where possible.
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Passengers</label>
                            <select class="form-select" name="passengers" id="passengerSelect" required>
                                <option value="">Please choose</option>
                                <option value="1">1 passenger</option>
                                <option value="2">2 passengers</option>
                                <option value="3">3 passengers</option>
                                <option value="4">4 passengers</option>
                                <option value="5">5 passengers</option>
                                <option value="6">6 passengers</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Large suitcases</label>
                            <select class="form-select" name="large_cases" id="largeCasesSelect" required>
                                <option value="">Please choose</option>
                                <option value="0">0</option>
                                <option value="1">1 large suitcase</option>
                                <option value="2">2 large suitcases</option>
                                <option value="3">3 large suitcases</option>
                                <option value="4">4 large suitcases</option>
                                <option value="5">5 large suitcases</option>
                                <option value="6">6 large suitcases</option>
                            </select>
                            <div class="form-text">
                                Checked luggage size, up to: 90 × 75 × 43 cm.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Small bags</label>
                            <select class="form-select" name="small_bags" id="smallBagsSelect" required>
                                <option value="">Please choose</option>
                                <option value="0">0</option>
                                <option value="1">1 small bag</option>
                                <option value="2">2 small bags</option>
                                <option value="3">3 small bags</option>
                                <option value="4">4 small bags</option>
                                <option value="5">5 small bags</option>
                                <option value="6">6 small bags</option>
                                <option value="7">7 small bags</option>
                                <option value="8">8 small bags</option>
                            </select>
                            <div class="form-text">
                                Cabin-size bag, up to: 55 × 45 × 25 cm.
                            </div>
                        </div>
                    </div>

                    <div class="form-text mt-2">
                        Some luggage combinations become unavailable depending on the number of passengers selected.
                    </div>

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" value="yes" id="oversizedLuggage" name="oversized_luggage">
                        <label class="form-check-label fw-bold" for="oversizedLuggage">
                            I have oversized luggage, a buggy, sports equipment, large boxes or unusual items
                        </label>
                    </div>

                    <div class="mt-4">
                        <label class="form-label fw-bold">Do you need an extra stop?</label>
                        <select class="form-select" name="extra_stop" id="extraStopSelect" required>
                            <option value="">Please choose</option>
                            <option value="no">No, direct airport transfer only</option>
                            <option value="yes">Yes, I need an extra stop</option>
                        </select>
                    </div>

                    <div class="alert alert-warning d-none mt-4" id="manualLuggageMessage">
                        This booking needs manual confirmation. Please request a quote if you have oversized luggage, a buggy, unusual items, or an extra stop, as this may affect vehicle space, journey time and price.
                    </div>

                    <div class="alert alert-info mt-4">
                        <strong>Next step:</strong> the completed system will check availability and estimated price before account login and deposit.
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                        <button type="submit" class="btn btn-brand btn-lg rounded-pill px-5" id="checkAvailabilityBtn">
                            Check Availability
                        </button>
                        <a href="/quote.php" class="btn btn-outline-dark btn-lg rounded-pill px-5">
                            Request a Quote Instead
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>