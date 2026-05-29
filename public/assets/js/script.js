document.addEventListener('DOMContentLoaded', function () {
    console.log('EdiVentures website loaded');
});

document.addEventListener('DOMContentLoaded', function () {
    const journeyType = document.getElementById('journeyType');
    const addressSectionTitle = document.getElementById('addressSectionTitle');
    const addressHelpText = document.getElementById('addressHelpText');
    const timeHelpText = document.getElementById('timeHelpText');
    const flightNumberGroup = document.getElementById('flightNumberGroup');

    const airportSelect = document.getElementById('airportSelect');
    const manualAirportMessage = document.getElementById('manualAirportMessage');

    const travelDate = document.getElementById('travelDate');
    const travelHour = document.querySelector('select[name="travel_hour"]');
    const travelMinute = document.querySelector('select[name="travel_minute"]');

    const passengerSelect = document.getElementById('passengerSelect');
    const largeCasesSelect = document.getElementById('largeCasesSelect');
    const smallBagsSelect = document.getElementById('smallBagsSelect');
    const oversizedLuggage = document.getElementById('oversizedLuggage');
    const extraStopSelect = document.getElementById('extraStopSelect');
    const manualLuggageMessage = document.getElementById('manualLuggageMessage');

    function setMinimumDate() {
        if (!travelDate) return;

        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        travelDate.min = `${yyyy}-${mm}-${dd}`;
    }

    function isTodaySelected() {
        if (!travelDate || !travelDate.value) return false;

        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        return travelDate.value === `${yyyy}-${mm}-${dd}`;
    }

    function getMinimumNoticeMinutes() {
        if (!journeyType || !airportSelect) return 60;

        const type = journeyType.value;
        const airport = airportSelect.value;

        if (type === 'dropoff') {
            return 60; // temporary front-end rule until postcode zones are built
        }

        if (type === 'pickup') {
            if (airport === 'edinburgh') return 60;
            if (airport === 'glasgow') return 180;
            if (airport === 'prestwick') return 300;
            if (airport === 'dundee') return 300;
            if (airport === 'newcastle') return 720;
            return 720;
        }

        return 60;
    }

    function getMinimumAllowedDateTime() {
        const now = new Date();
        const minimumNotice = getMinimumNoticeMinutes();
        return new Date(now.getTime() + minimumNotice * 60000);
    }

    function updateTimeOptions() {
        if (!travelDate || !travelHour || !travelMinute) return;

        [...travelHour.options].forEach(option => option.disabled = false);
        [...travelMinute.options].forEach(option => option.disabled = false);

        const minimumDateTime = getMinimumAllowedDateTime();

        const yyyy = minimumDateTime.getFullYear();
        const mm = String(minimumDateTime.getMonth() + 1).padStart(2, '0');
        const dd = String(minimumDateTime.getDate()).padStart(2, '0');
        const minDateString = `${yyyy}-${mm}-${dd}`;

        travelDate.min = minDateString;

        if (!travelDate.value) return;

        if (travelDate.value < minDateString) {
            travelDate.value = '';
            travelHour.value = '';
            travelMinute.value = '';
            return;
        }

        if (travelDate.value !== minDateString) return;

        const minHour = minimumDateTime.getHours();
        const minMinute = minimumDateTime.getMinutes();

        [...travelHour.options].forEach(option => {
            if (option.value === '') return;
            const hourValue = parseInt(option.value, 10);
            option.disabled = hourValue < minHour;
        });

        if (travelHour.value && parseInt(travelHour.value, 10) === minHour) {
            [...travelMinute.options].forEach(option => {
                if (option.value === '') return;
                const minuteValue = parseInt(option.value, 10);
                option.disabled = minuteValue < minMinute;
            });
        }

        if (travelHour.value && parseInt(travelHour.value, 10) < minHour) {
            travelHour.value = '';
            travelMinute.value = '';
        }
    }

    function updateJourneyFields() {
        if (!journeyType || !flightNumberGroup) return;

        if (journeyType.value === 'dropoff') {
            if (addressSectionTitle) addressSectionTitle.textContent = 'Where should we pick you up?';
            if (addressHelpText) addressHelpText.textContent = 'Enter the address where the driver should collect you before travelling to the airport.';
            if (timeHelpText) timeHelpText.textContent = 'Choose your preferred pickup time.';
            flightNumberGroup.classList.add('d-none');
        } else if (journeyType.value === 'pickup') {
            if (addressSectionTitle) addressSectionTitle.textContent = 'Where should we take you after airport pickup?';
            if (addressHelpText) addressHelpText.textContent = 'Enter the address where you would like to be dropped off after arriving at the airport.';
            if (timeHelpText) timeHelpText.textContent = 'Choose your flight arrival time. Waiting and pickup timing will be considered later in the booking process.';
            flightNumberGroup.classList.remove('d-none');
        } else {
            if (addressSectionTitle) addressSectionTitle.textContent = 'Journey address details';
            if (addressHelpText) addressHelpText.textContent = 'Enter the journey address details below.';
            if (timeHelpText) timeHelpText.textContent = 'Choose the required time for your airport journey.';
            flightNumberGroup.classList.add('d-none');
        }
    }

    function updateAirportMessage() {
        if (!airportSelect || !manualAirportMessage) return;

        if (airportSelect.value === 'other') {
            manualAirportMessage.classList.remove('d-none');
        } else {
            manualAirportMessage.classList.add('d-none');
        }
    }

    function numberValue(selectElement) {
        return parseInt(selectElement.value || '0', 10);
    }

    function disableOptionsAbove(selectElement, maxValue) {
        [...selectElement.options].forEach(option => {
            if (option.value === '') return;
            option.disabled = parseInt(option.value, 10) > maxValue;
        });
    }

    function updateLuggageRules() {
        if (!passengerSelect || !largeCasesSelect || !smallBagsSelect || !manualLuggageMessage) return;

        const passengers = numberValue(passengerSelect);
        let largeCases = numberValue(largeCasesSelect);
        let smallBags = numberValue(smallBagsSelect);

        let maxLarge = 6;
        let maxSmall = 8;

        if (!passengers) {
            disableOptionsAbove(largeCasesSelect, 6);
            disableOptionsAbove(smallBagsSelect, 8);
            manualLuggageMessage.classList.add('d-none');
            return;
        }

        if (passengers >= 1 && passengers <= 4) {
            maxLarge = 6;

            if (largeCases === 6) maxSmall = 0;
            else if (largeCases === 5) maxSmall = 2;
            else if (largeCases === 4) maxSmall = 4;
            else if (largeCases === 3) maxSmall = 6;
            else maxSmall = 8;
        }

        if (passengers === 5) {
            maxLarge = 5;

            if (largeCases > 5) {
                largeCasesSelect.value = '5';
                largeCases = 5;
            }

            if (largeCases === 5) maxSmall = 0;
            else if (largeCases === 4) maxSmall = 2;
            else if (largeCases === 3) maxSmall = 4;
            else if (largeCases === 2) maxSmall = 6;
            else maxSmall = 8;
        }

        if (passengers === 6) {
            maxLarge = 4;

            if (largeCases > 4) {
                largeCasesSelect.value = '4';
                largeCases = 4;
            }

            if (largeCases === 4) maxSmall = 0;
            else if (largeCases === 3) maxSmall = 3;
            else if (largeCases === 2) maxSmall = 5;
            else if (largeCases === 1) maxSmall = 7;
            else maxSmall = 8;
        }

        disableOptionsAbove(largeCasesSelect, maxLarge);
        disableOptionsAbove(smallBagsSelect, maxSmall);

        if (smallBags > maxSmall) {
            smallBagsSelect.value = String(maxSmall);
        }

        const needsManualCheck =
            (oversizedLuggage && oversizedLuggage.checked) ||
            (extraStopSelect && extraStopSelect.value === 'yes');

        if (needsManualCheck) {
            manualLuggageMessage.classList.remove('d-none');
        } else {
            manualLuggageMessage.classList.add('d-none');
        }
    }

    if (journeyType) journeyType.addEventListener('change', updateJourneyFields);
    if (airportSelect) airportSelect.addEventListener('change', updateAirportMessage);
    if (travelDate) travelDate.addEventListener('change', updateTimeOptions);
    if (travelHour) travelHour.addEventListener('change', updateTimeOptions);
    if (travelMinute) travelMinute.addEventListener('change', updateTimeOptions);
    if (passengerSelect) passengerSelect.addEventListener('change', updateLuggageRules);
    if (largeCasesSelect) largeCasesSelect.addEventListener('change', updateLuggageRules);
    if (smallBagsSelect) smallBagsSelect.addEventListener('change', updateLuggageRules);
    if (oversizedLuggage) oversizedLuggage.addEventListener('change', updateLuggageRules);
    if (extraStopSelect) extraStopSelect.addEventListener('change', updateLuggageRules);

    setMinimumDate();
    updateTimeOptions();
    updateJourneyFields();
    updateAirportMessage();
    updateLuggageRules();
});