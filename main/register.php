<?php

require_once __DIR__ . '/includes/app.php';

if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin/index.php");
        exit;
    }

    header("Location: customer.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || !$email || $phone === '' || $password === '' || $confirmPassword === '') {
        header("Location: register.php?error=missing");
        exit;
    }

    if (strlen($password) < 8) {
        header("Location: register.php?error=invalid");
        exit;
    }

    if ($password !== $confirmPassword) {
        header("Location: register.php?error=mismatch");
        exit;
    }

    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    if ($cleanPhone === '' || strlen($cleanPhone) < 7) {
        header("Location: register.php?error=invalid");
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existingUser) {
            header("Location: register.php?error=duplicate");
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (fullname, email, phone, password)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $fullname, $email, $cleanPhone, $hashedPassword);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['doctor_id']);
        header("Location: login.php?status=registered");
        exit;
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        header("Location: register.php?error=failed");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register | MediBridge AI</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body class="auth-page" data-page="auth">
    <?php render_nav('register'); ?>

    <main class="auth-shell">
      <section class="auth-grid" aria-label="Create MediBridge AI account">
        <aside class="auth-proof">
          <div>
            <span>AI</span>
            <p class="eyebrow">Start your care journey</p>
            <h2>Create a patient profile for faster appointment requests.</h2>
            <p>
              Your profile keeps contact details ready so each booking can move
              from concern to confirmation with fewer repeated steps.
            </p>
          </div>
          <ul>
            <li>Book doctors by specialty</li>
            <li>Prepare concerns with AI guidance</li>
            <li>Track requests in the patient dashboard</li>
          </ul>
        </aside>

        <div class="auth-panel">
          <p class="eyebrow">Create account</p>
          <h1>Register as a patient</h1>
          <div class="form-alert" id="formAlert" role="status"></div>
          <form action="register.php" method="post">
            <div class="form-group">
              <label for="fullname">Full Name</label>
              <input
                id="fullname"
                type="text"
                name="fullname"
                placeholder="Enter your full name"
                autocomplete="name"
                required
              />
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="email">Email Address</label>
                <input
                  id="email"
                  type="email"
                  name="email"
                  placeholder="Enter your email"
                  autocomplete="email"
                  required
                />
              </div>

              <div class="form-group">
                <label for="phone">Phone Number</label>
                <input
                  id="phone"
                  type="tel"
                  name="phone"
                  placeholder="Enter phone number"
                  autocomplete="tel"
                  required
                />
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="password">Password</label>
                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="Create password"
                  autocomplete="new-password"
                  minlength="8"
                  required
                />
              </div>

              <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                  id="confirm_password"
                  type="password"
                  name="confirm_password"
                  placeholder="Confirm password"
                  autocomplete="new-password"
                  minlength="8"
                  required
                />
              </div>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>

            <p class="form-footer">
              Already have an account?
              <a href="login.php">Login here</a>
            </p>
          </form>
        </div>
      </section>
    </main>
  </body>
</html>
