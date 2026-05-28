<?php
$pageTitle = "Book Airport Transfer | EdiVentures";
$metaDescription = "Book selected airport pickups and drop-offs with EdiVentures.";
$canonicalUrl = "https://www.ediventures.co.uk/airport-booking";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>
    <section class="page-hero airport-hero text-white">
        <div class="container">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-8">
                    <p class="eyebrow mb-3">Airport Booking</p>
                    <h1 class="page-title">Book an Airport Pickup or Drop-off</h1>
                    <p class="page-hero-text">
                        Start your airport transfer booking below.
                    </p>

                    <button type="button" class="btn btn-brand btn-lg rounded-pill px-5 mt-3" data-bs-toggle="modal" data-bs-target="#airportBookingModal">
                        Start Airport Booking
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/airport-booking-modal.php'; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>