<?php
session_start();

$pageTitle = "Create Customer Account | EdiVentures";
$metaDescription = "Create an EdiVentures customer account to manage tour bookings, custom tour requests, quotes and deposit payments.";
$canonicalUrl = "https://www.ediventures.co.uk/register.php";

require_once __DIR__ . '/../config/database.php';

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["fullName"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";

    if ($fullName === "") {
        $errors[] = "Full name is required.";
    }

    if ($email === "") {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($phone === "") {
        $errors[] = "Phone number is required.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $checkSql = "SELECT customerID FROM customers WHERE email = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $errors[] = "An account with this email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insertSql = "INSERT INTO customers (fullName, email, phone, passwordHash)
                          VALUES (?, ?, ?, ?)";

            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                $fullName,
                $email,
                $phone,
                $passwordHash
            ]);

            if (isset($_GET["redirect"]) && $_GET["redirect"] === "airport-booking-confirm") {
                header("Location: /login.php?redirect=airport-booking-confirm");
                exit;
            }

            $success = "Account created successfully. You can now log in.";
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
                <h1 class="page-title">Create Your EdiVentures Account</h1>
                <p class="page-hero-text">
                    Create an account to manage your tour bookings, custom tour requests, quotes and deposit payments.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="booking-form-card mx-auto">

            <h2 class="section-title mb-3">Register</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>

                    <div class="mt-3">
                        <a href="<?= isset($_GET['redirect']) && $_GET['redirect'] === 'airport-booking-confirm' ? '/login.php?redirect=airport-booking-confirm' : '/login.php' ?>" class="btn btn-brand rounded-pill px-4">
                            Go to Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="fullName" class="form-label">Full Name *</label>
                        <input type="text" id="fullName" name="fullName" class="form-control"
                               value="<?= htmlspecialchars($_POST['fullName'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 8 characters.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label">Confirm Password *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-brand btn-lg rounded-pill px-4">
                            Create Account
                        </button>
                    </div>

                    <div class="col-12">
                        <p class="mb-0">
                            Already have an account?
                            <a href="<?= isset($_GET['redirect']) && $_GET['redirect'] === 'airport-booking-confirm' ? '/login.php?redirect=airport-booking-confirm' : '/login.php' ?>" class="text-link">Log in here</a>
                        </p>
                    </div>

                </div>

            </form>

        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>