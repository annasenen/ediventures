document.addEventListener('DOMContentLoaded', function () {
    console.log('EdiVentures website loaded');
});

document.addEventListener('DOMContentLoaded', function () {
    const journeyType = document.getElementById('journeyType');
    const addressLabel = document.getElementById('addressLabel');
    const timeLabel = document.getElementById('timeLabel');

    if (journeyType && addressLabel && timeLabel) {
        journeyType.addEventListener('change', function () {
            if (journeyType.value === 'dropoff') {
                addressLabel.textContent = 'Pickup address';
                timeLabel.textContent = 'Preferred pickup time';
            } else if (journeyType.value === 'pickup') {
                addressLabel.textContent = 'Drop-off address';
                timeLabel.textContent = 'Flight arrival time';
            } else {
                addressLabel.textContent = 'Pickup or drop-off address';
                timeLabel.textContent = 'Pickup time / arrival time';
            }
        });
    }
});