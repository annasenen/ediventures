document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | SAVE CONFIRMATION
        |--------------------------------------------------------------------------
        */

        const saveMessage =
            document.getElementById(
                'airportPriceSaveMessage'
            );


        if (saveMessage) {

            saveMessage.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            saveMessage.setAttribute(
                'tabindex',
                '-1'
            );

            saveMessage.focus({
                preventScroll: true
            });

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD PRICES
        |--------------------------------------------------------------------------
        |
        | If the admin deliberately pressed "Load Prices",
        | move directly to the selected pricing matrix.
        |
        */

        const params =
            new URLSearchParams(
                window.location.search
            );


        if (
            params.get('loaded') !== '1'
        ) {
            return;
        }


        const matrixSection =
            document.getElementById(
                'airportPriceMatrixSection'
            );


        if (!matrixSection) {
            return;
        }


        matrixSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

    }
);