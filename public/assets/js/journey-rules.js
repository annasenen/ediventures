document.addEventListener(
    'DOMContentLoaded',

    function () {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | AUTO-LOAD AIRPORT RULES
        |--------------------------------------------------------------------------
        */

        const selectorForm =
            document.getElementById(
                'journeyRuleSelectorForm'
            );

        const airportSelect =
            document.getElementById(
                'airportID'
            );

        const loadButton =
            document.getElementById(
                'journeyRuleLoadButton'
            );


        if (
            selectorForm &&
            airportSelect
        ) {

            if (loadButton) {
                loadButton.style.display = 'none';
            }

            airportSelect.addEventListener(
                'change',

                function () {
                    selectorForm.requestSubmit();
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE CONFIRMATION
        |--------------------------------------------------------------------------
        */

        const saveMessage =
            document.getElementById(
                'journeyRuleSaveMessage'
            );

        if (!saveMessage) {
            return;
        }

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

    }
);