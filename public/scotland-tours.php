<?php
$pageTitle = "Scotland Tours from Edinburgh | Private Tours with EdiVentures";
$metaDescription = "Explore private Scotland tours with EdiVentures, including Edinburgh, St Andrews, Stirling, Outlander locations, Rosslyn Chapel and personalised Scottish tours.";
$canonicalUrl = "https://www.ediventures.co.uk/scotland-tours";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';

$tours = [
    [
        "title" => "Morning Half-Day Tour of Edinburgh",
        "image" => "edinburgh-castle.jpg",
        "duration" => "9:00 AM – 1:00 PM",
        "price" => "From £180",
        "description" => "Start your day with a private driving tour through Edinburgh, exploring historic landmarks, viewpoints and hidden gems across Scotland’s capital.",
        "link" => "/tour-details.php?tour=edinburgh-morning-half-day"
    ],
    [
        "title" => "Afternoon Half-Day Tour of Edinburgh",
        "image" => "calton-hill-edinburgh.jpg",
        "duration" => "2:00 PM – 6:00 PM",
        "price" => "From £180",
        "description" => "Enjoy Edinburgh in the afternoon light with a comfortable private tour through the city’s history, culture and scenic viewpoints.",
        "link" => "/tour-details.php?tour=edinburgh-afternoon-half-day"
    ],
    [
        "title" => "Full-Day Grand Tour of Edinburgh",
        "image" => "edinburgh-assembly-hall.jpg",
        "duration" => "9:00 AM – 6:00 PM",
        "price" => "From £350",
        "description" => "A full-day private tour of Edinburgh, ideal for visitors who want to experience the city’s highlights without excessive walking.",
        "link" => "/tour-details.php?tour=edinburgh-full-day"
    ],
    [
        "title" => "Edinburgh, Kelpies and Stirling Tour",
        "image" => "kelpies-sculptures-scotland.jpg",
        "duration" => "9:00 AM – 6:00 PM",
        "price" => "From £380",
        "description" => "A full-day journey combining Edinburgh, the iconic Kelpies and the historic beauty of Stirling.",
        "link" => "/tour-details.php?tour=edinburgh-kelpies-stirling"
    ],
    [
        "title" => "St Andrews and the Kingdom of Fife",
        "image" => "st-andrews-cathedral-ruins-fife.jpg",
        "duration" => "9:00 AM – 6:00 PM",
        "price" => "From £420",
        "description" => "Discover the Fife coast, historic St Andrews, coastal villages and beautiful Scottish scenery.",
        "link" => "/tour-details.php?tour=st-andrews-fife"
    ],
    [
        "title" => "Outlander Time Travel Experience",
        "image" => "blackness-castle-outlander-tour.jpg",
        "duration" => "9:00 AM – 6:00 PM",
        "price" => "From £470",
        "description" => "Visit famous Outlander-inspired locations and historic Scottish places connected with the atmosphere of the series.",
        "link" => "/tour-details.php?tour=outlander-scotland"
    ],
    [
        "title" => "Edinburgh and Rosslyn Chapel Tour",
        "image" => "rosslyn-chapel-edinburgh-tour.jpg",
        "duration" => "9:00 AM – 1:30 PM",
        "price" => "From £200",
        "description" => "Explore Edinburgh and the mysterious Rosslyn Chapel in a private tour blending city heritage with historic intrigue.",
        "link" => "/tour-details.php?tour=edinburgh-rosslyn-chapel"
    ],
    [
        "title" => "Personalised Scottish Heritage Tour",
        "image" => "scottish-castle-ancestral-tour.jpg",
        "duration" => "Custom",
        "price" => "Quote required",
        "description" => "A personalised route for guests interested in ancestry, castles, clan history, Scottish heritage or family connections.",
        "link" => "/build-your-tour.php"
    ],
    [
        "title" => "Build Your Own Scottish Adventure",
        "image" => "ediventures-tour-vehicle-st-andrews-scotland.jpg",
        "duration" => "Custom",
        "price" => "Quote required",
        "description" => "Tell us what you would love to see and we will help shape a private tour around your interests, time and route.",
        "link" => "/build-your-tour.php"
    ],
];
?>

<main>

    <section class="page-hero text-white">
        <div class="container">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-8">
                    <p class="eyebrow mb-3">Private Scotland Tours</p>
                    <h1 class="page-title">Explore Scotland with EdiVentures</h1>
                    <p class="page-hero-text">
                        Discover Edinburgh, historic castles, coastal towns, Outlander locations, St Andrews,
                        Stirling and personalised routes across Scotland with a comfortable private tour.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                        <a href="/availability.php" class="btn btn-brand btn-lg rounded-pill px-4">Check Availability</a>
                        <a href="/build-your-tour.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Build Your Tour</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-label">Tour Options</p>
                <h2 class="section-title">Private tours for different interests</h2>
                <p class="lead mx-auto tour-intro">
                    Choose one of our planned Scotland tours or request a personalised route. Online booking
                    and deposits will be added later for selected tours.
                </p>
            </div>

            <div class="row g-4">
                <?php foreach ($tours as $tour): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="tour-card h-100">
                            <img src="/assets/img/<?= htmlspecialchars($tour['image']) ?>"
                                 alt="<?= htmlspecialchars($tour['title']) ?>"
                                 class="tour-card-img">

                            <div class="tour-card-body">
                                <h3><?= htmlspecialchars($tour['title']) ?></h3>

                                <div class="tour-meta">
                                    <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($tour['duration']) ?></span>
                                    <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($tour['price']) ?></span>
                                </div>

                                <p><?= htmlspecialchars($tour['description']) ?></p>

                                <div class="d-flex flex-column flex-sm-row gap-2 mt-auto">
                                    <a href="<?= htmlspecialchars($tour['link']) ?>" class="btn btn-brand rounded-pill">
                                        Learn More
                                    </a>
                                    <a href="/availability.php" class="btn btn-outline-dark rounded-pill">
                                        Check Availability
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <p class="section-label">Personalised Routes</p>
                    <h2 class="section-title">Not sure which tour is right for you?</h2>
                    <p>
                        If you have a place in mind, family history to explore, or a special interest such as
                        castles, coastal towns, golf, Outlander filming locations or scenic routes, we can help
                        you shape a personalised private tour.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="custom-tour-box">
                        <h3>Build your own tour</h3>
                        <p>
                            Tell us your preferred date, starting point, interests, group size and how long you
                            would like to travel for. We will review your request and prepare a suitable route or quote.
                        </p>
                        <a href="/build-your-tour.php" class="btn btn-brand rounded-pill px-4">Start Planning</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>