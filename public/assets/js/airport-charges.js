document.addEventListener('DOMContentLoaded', () => {

    const airportSelect =
        document.getElementById('airportID');

    const selectorForm =
        document.getElementById('airportChargeSelectorForm');

    const loadButton =
        document.getElementById('airportChargeLoadButton');


    /*
     * ---------------------------------------------------------
     * AUTO-LOAD AIRPORT
     * ---------------------------------------------------------
     *
     * JavaScript only improves the admin experience.
     *
     * The airport ID is still processed and validated by PHP.
     * If JavaScript is unavailable, the normal Load Charges
     * button remains available.
     */

    if (airportSelect && selectorForm) {

        if (loadButton) {
            loadButton.style.display = 'none';
        }

        airportSelect.addEventListener('change', () => {
            selectorForm.requestSubmit();
        });
    }


    /*
     * ---------------------------------------------------------
     * SCROLL TO SUCCESS MESSAGE AFTER SAVE
     * ---------------------------------------------------------
     */

    const params =
        new URLSearchParams(window.location.search);

    if (params.get('saved') !== '1') {
        return;
    }

    const successMessage =
        document.getElementById(
            'airportChargeSaveMessage'
        );

    if (!successMessage) {
        return;
    }

    successMessage.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });

});