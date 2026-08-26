<!-- =========================================================
     AIRPORT BOOKING MODAL
========================================================= -->

<div
    class="modal fade"
    id="airportBookingModal"
    tabindex="-1"
    aria-labelledby="airportBookingModalLabel"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down"
    >

        <div class="modal-content airport-booking-modal">


            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div class="modal-header airport-booking-modal__header">

                <div>

                    <p class="section-label mb-1">
                        Airport Transfer
                    </p>

                    <h2
                        class="modal-title"
                        id="airportBookingModalLabel"
                    >
                        Check airport booking details
                    </h2>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>



            <!-- =====================================================
                 BODY
            ====================================================== -->

            <div class="modal-body airport-booking-modal__body">

                <form
                    action="/airport-booking-check.php"
                    method="post"
                    id="airportBookingForm"
                >


                    <!-- =================================================
                         JOURNEY
                    ================================================== -->

                    <section class="airport-booking-section">

                        <div class="airport-booking-section__heading">

                            <h3>
                                Journey details
                            </h3>

                            <p>
                                Choose the type of airport journey and airport.
                            </p>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="journeyType"
                                >
                                    Journey type
                                </label>

                                <select
                                    class="form-select"
                                    name="journey_type"
                                    id="journeyType"
                                    required
                                >

                                    <option value="">
                                        Please choose
                                    </option>

                                    <option value="dropoff">
                                        Airport drop-off
                                    </option>

                                    <option value="pickup">
                                        Airport pickup
                                    </option>

                                </select>

                            </div>



                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="airportSelect"
                                >
                                    Airport
                                </label>

                                <select
                                    class="form-select"
                                    name="airport"
                                    id="airportSelect"
                                    required
                                >

                                    <option value="">
                                        Please choose airport
                                    </option>

                                    <option value="edinburgh">
                                        Edinburgh Airport
                                    </option>

                                    <option value="glasgow">
                                        Glasgow Airport
                                    </option>

                                    <option value="prestwick">
                                        Glasgow Prestwick Airport
                                    </option>

                                    <option value="dundee">
                                        Dundee Airport
                                    </option>

                                    <option value="newcastle">
                                        Newcastle Airport
                                    </option>

                                    <option value="other">
                                        Other airport — request a quote
                                    </option>

                                </select>

                            </div>


                        </div>


                        <div
                            class="airport-booking-notice airport-booking-notice--warning d-none mt-3"
                            id="manualAirportMessage"
                        >

                            <i class="fa-solid fa-circle-info"></i>

                            <span>
                                This airport needs manual confirmation.
                                Please request a quote so we can check timing,
                                distance and availability.
                            </span>

                        </div>

                    </section>



                    <!-- =================================================
                         ADDRESS
                    ================================================== -->

                    <section class="airport-booking-section">

                        <div class="airport-booking-section__heading">

                            <h3 id="addressSectionTitle">
                                Journey address details
                            </h3>

                            <p id="addressHelpText">
                                Enter the journey address details below.
                            </p>

                        </div>


                        <!-- POSTCODE + FUTURE ADDRESS LOOKUP -->

                        <div class="row g-3">

                            <div class="col-md-8">

                                <label
                                    class="form-label"
                                    for="airportPostcode"
                                >
                                    Postcode
                                </label>

                                <input
                                    type="text"
                                    class="form-control text-uppercase"
                                    name="postcode"
                                    id="airportPostcode"
                                    placeholder="Example: EH30 9PP"
                                    maxlength="10"
                                    autocomplete="postal-code"
                                    required
                                >

                            </div>


                            <div class="col-md-4 d-flex align-items-end">

                                <button
                                    type="button"
                                    class="btn btn-outline-dark w-100 airport-address-search-button"
                                    id="airportAddressSearchButton"
                                    disabled
                                >
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>
                                    Find Address
                                </button>

                            </div>

                        </div>


                        <div class="form-text airport-address-api-note">

                            <i class="fa-solid fa-location-dot me-1"></i>

                            Address lookup will be connected later.
                            For now, please enter the address manually.

                        </div>


                        <!-- RESERVED FOR FUTURE API RESULTS -->

                        <div
                            class="d-none mt-3"
                            id="airportAddressResults"
                        >

                            <label
                                class="form-label"
                                for="airportAddressSelect"
                            >
                                Select address
                            </label>

                            <select
                                class="form-select"
                                id="airportAddressSelect"
                            >

                                <option value="">
                                    Please choose an address
                                </option>

                            </select>

                        </div>



                        <!-- MANUAL ADDRESS -->

                        <div class="row g-3 mt-1">


                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="airportHouseNumber"
                                >
                                    House / flat number or name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="house_number"
                                    id="airportHouseNumber"
                                    placeholder="Example: 103 or 1F1"
                                    autocomplete="address-line1"
                                    required
                                >

                            </div>



                            <div class="col-md-8">

                                <label
                                    class="form-label"
                                    for="airportStreet"
                                >
                                    Street
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="street"
                                    id="airportStreet"
                                    placeholder="Example: High Street"
                                    autocomplete="address-line2"
                                    required
                                >

                            </div>



                            <div class="col-12">

                                <label
                                    class="form-label"
                                    for="airportTownCity"
                                >
                                    Town / city
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="town_city"
                                    id="airportTownCity"
                                    placeholder="Example: South Queensferry"
                                    autocomplete="address-level2"
                                    required
                                >

                            </div>


                        </div>

                    </section>



                    <!-- =================================================
                         DATE / TIME
                    ================================================== -->

                    <section class="airport-booking-section">

                        <div class="airport-booking-section__heading">

                            <h3>
                                Date and time
                            </h3>

                            <p id="timeHelpText">
                                Choose the required time for your airport journey.
                            </p>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-6">

                                <label
                                    class="form-label"
                                    for="travelDate"
                                >
                                    Travel date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="travel_date"
                                    id="travelDate"
                                    required
                                >

                            </div>



                            <div class="col-6 col-md-3">

                                <label class="form-label">
                                    Hour
                                </label>

                                <select
                                    class="form-select"
                                    name="travel_hour"
                                    required
                                >

                                    <option value="">
                                        Hour
                                    </option>

                                    <?php for ($h = 0; $h <= 23; $h++): ?>

                                        <option
                                            value="<?= str_pad(
                                                $h,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>"
                                        >
                                            <?= str_pad(
                                                $h,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>
                                        </option>

                                    <?php endfor; ?>

                                </select>

                            </div>



                            <div class="col-6 col-md-3">

                                <label class="form-label">
                                    Minutes
                                </label>

                                <select
                                    class="form-select"
                                    name="travel_minute"
                                    required
                                >

                                    <option value="">
                                        Minutes
                                    </option>

                                    <?php for ($m = 0; $m <= 55; $m += 5): ?>

                                        <option
                                            value="<?= str_pad(
                                                $m,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>"
                                        >
                                            <?= str_pad(
                                                $m,
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            ) ?>
                                        </option>

                                    <?php endfor; ?>

                                </select>

                            </div>


                        </div>



                        <!-- FLIGHT NUMBER -->

                        <div
                            class="mt-3 d-none"
                            id="flightNumberGroup"
                        >

                            <label class="form-label">
                                Flight number
                            </label>

                            <input
                                type="text"
                                class="form-control text-uppercase"
                                name="flight_number"
                                placeholder="Example: BA145"
                            >

                            <div class="form-text">
                                For airport pickups, this helps us check
                                arrival times where possible.
                            </div>

                        </div>

                    </section>



                    <!-- =================================================
                         PASSENGERS / LUGGAGE
                    ================================================== -->

                    <section class="airport-booking-section">

                        <div class="airport-booking-section__heading">

                            <h3>
                                Passengers and luggage
                            </h3>

                            <p>
                                Tell us how many passengers and bags are travelling.
                            </p>

                        </div>


                        <div class="row g-3">


                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="passengerSelect"
                                >
                                    Passengers
                                </label>

                                <select
                                    class="form-select"
                                    name="passengers"
                                    id="passengerSelect"
                                    required
                                >

                                    <option value="">
                                        Please choose
                                    </option>

                                    <option value="1">
                                        1 passenger
                                    </option>

                                    <option value="2">
                                        2 passengers
                                    </option>

                                    <option value="3">
                                        3 passengers
                                    </option>

                                    <option value="4">
                                        4 passengers
                                    </option>

                                    <option value="5">
                                        5 passengers
                                    </option>

                                    <option value="6">
                                        6 passengers
                                    </option>

                                </select>

                            </div>



                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="largeCasesSelect"
                                >
                                    Large suitcases
                                </label>

                                <select
                                    class="form-select"
                                    name="large_cases"
                                    id="largeCasesSelect"
                                    required
                                >

                                    <option value="">
                                        Please choose
                                    </option>

                                    <option value="0">0</option>

                                    <option value="1">
                                        1 large suitcase
                                    </option>

                                    <option value="2">
                                        2 large suitcases
                                    </option>

                                    <option value="3">
                                        3 large suitcases
                                    </option>

                                    <option value="4">
                                        4 large suitcases
                                    </option>

                                    <option value="5">
                                        5 large suitcases
                                    </option>

                                    <option value="6">
                                        6 large suitcases
                                    </option>

                                </select>

                                <div class="form-text">
                                    Checked luggage size,
                                    up to: 90 × 75 × 43 cm.
                                </div>

                            </div>



                            <div class="col-md-4">

                                <label
                                    class="form-label"
                                    for="smallBagsSelect"
                                >
                                    Small bags
                                </label>

                                <select
                                    class="form-select"
                                    name="small_bags"
                                    id="smallBagsSelect"
                                    required
                                >

                                    <option value="">
                                        Please choose
                                    </option>

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
                                    Cabin-size bag,
                                    up to: 55 × 45 × 25 cm.
                                </div>

                            </div>


                        </div>


                        <div class="form-text mt-2">

                            Some luggage combinations become unavailable
                            depending on the number of passengers selected.

                        </div>



                        <!-- OVERSIZED / UNUSUAL LUGGAGE -->

                        <div class="airport-booking-option mt-4">

                            <div class="airport-booking-option__copy">

                                <strong>
                                    Oversized or unusual luggage
                                </strong>

                                <span>
                                    Buggy, sports equipment, large boxes
                                    or other unusual items.
                                </span>

                            </div>


                            <div class="form-check form-switch m-0">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="yes"
                                    id="oversizedLuggage"
                                    name="oversized_luggage"
                                >

                            </div>

                        </div>



                        <!-- EXTRA STOP -->

                        <div class="airport-booking-option mt-3">

                            <div class="airport-booking-option__copy">

                                <strong>
                                    Do you need an extra stop?
                                </strong>

                                <span>
                                    Standard online booking is for a direct
                                    airport transfer.
                                </span>

                            </div>


                            <div class="airport-extra-stop-choice">


                                <label>

                                    <input
                                        type="radio"
                                        name="extra_stop"
                                        value="no"
                                        checked
                                        required
                                    >

                                    <span>
                                        No
                                    </span>

                                </label>


                                <label>

                                    <input
                                        type="radio"
                                        name="extra_stop"
                                        value="yes"
                                        required
                                    >

                                    <span>
                                        Yes
                                    </span>

                                </label>


                            </div>

                        </div>



                        <!-- MANUAL CONFIRMATION MESSAGE -->

                        <div
                            class="airport-booking-notice airport-booking-notice--warning d-none mt-3"
                            id="manualLuggageMessage"
                        >

                            <i class="fa-solid fa-circle-info"></i>

                            <span>

                                This booking needs manual confirmation.
                                Please request a quote if you have oversized
                                luggage, a buggy, unusual items, or an extra
                                stop, as this may affect vehicle space,
                                journey time and price.

                            </span>

                        </div>

                    </section>



                    <!-- =================================================
                         ACTIONS
                    ================================================== -->

                    <div class="airport-booking-actions">


                        <div class="airport-booking-actions__note">

                            <i class="fa-solid fa-circle-info"></i>

                            <span>
                                Next, we will check availability and estimated
                                price before account login and deposit.
                            </span>

                        </div>



                        <div class="airport-booking-actions__buttons">


                            <a
                                href="/quote.php"
                                class="btn btn-outline-dark"
                            >
                                Request a Quote Instead
                            </a>


                            <button
                                type="submit"
                                class="btn btn-brand"
                                id="checkAvailabilityBtn"
                            >
                                Check Availability
                            </button>


                        </div>


                    </div>


                </form>

            </div>

        </div>

    </div>

</div>