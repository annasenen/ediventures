<?php
session_start();

$pageTitle = "Customer Login | EdiVentures";
$metaDescription = "Log in to your EdiVentures customer account to manage bookings, custom tour requests, quotes and payments.";
$canonicalUrl = "https://www.ediventures.co.uk/login.php";

require_once __DIR__ . '/../config/database.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "") {
        $errors[] = "Email address is required.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $sql = "SELECT customerID, fullName, email, passwordHash, isActive 
                FROM customers 
                WHERE email = ? 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            $errors[] = "Invalid email or password.";
        } elseif ((int)$customer["isActive"] !== 1) {
            $errors[] = "This account is currently inactive.";
        } elseif (!password_verify($password, $customer["passwordHash"])) {
            $errors[] = "Invalid email or password.";
        } else {
            $_SESSION["customerID"] = $customer["customerID"];
            $_SESSION["customerName"] = $customer["fullName"];
            $_SESSION["customerEmail"] = $customer["email"];

            header("Location: /customer/dashboard.php");
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main>

<section class="page-hero text-white"
    style="background: linear-gradient(rgba(0,0,0,0.70), rgba(0,0,0,0.70)), url('/assets/img/background-image.jpg') center/cover no-repeat;">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8">
                <p class="eyebrow mb-3">Customer Account</p>
                <h1 class="page-title">Log In to Your Account</h1>
                <p class="page-hero-text">
                    Access your EdiVentures account to manage bookings, custom tour requests, quotes and deposit payments.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <h2 class="section-title mb-3">Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">

                <div class="row g-3">

                    <div class="col-12">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-brand btn-lg rounded-pill px-4">
                            Log In
                        </button>
                    </div>

                    <div class="col-12">
                        <p class="mb-0">
                            Do not have an account yet?
                            <a href="/register.php" class="text-link">Create one here</a>
                        </p>
                    </div>

                </div>

            </form>

        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>