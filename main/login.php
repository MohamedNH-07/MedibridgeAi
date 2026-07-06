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

if (is_doctor_logged_in()) {
    header("Location: doctor/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || trim($password) === '') {
        header("Location: login.php?error=missing");
        exit;
    }

    try {
        $stmt = $conn->prepare(
            "SELECT id, fullname, email, password, is_admin
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            unset($_SESSION['doctor_id']);

            if ((int) ($user['is_admin'] ?? 0) === 1) {
                header("Location: admin/index.php");
                exit;
            }

            header("Location: customer.php");
            exit;
        }

        $stmt = $conn->prepare(
            "SELECT id, fullname, email, password, is_active
             FROM doctors
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($doctor && (int) $doctor['is_active'] === 1 && password_verify($password, $doctor['password'])) {
            session_regenerate_id(true);
            $_SESSION['doctor_id'] = (int) $doctor['id'];
            unset($_SESSION['user_id']);

            header("Location: doctor/index.php");
            exit;
        }

        header("Location: login.php?error=credentials");
        exit;
    } catch (Throwable $exception) {
        error_log($exception->getMessage());
        header("Location: login.php?error=failed");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | MediBridge AI</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body class="auth-page" data-page="auth">
    <?php render_nav('login'); ?>

    <main class="auth-shell">
      <section class="auth-grid" aria-label="Login to MediBridge AI">
        <aside class="auth-proof">
          <div>
            <span>MB</span>
            <p class="eyebrow">Secure patient access</p>
            <h2>Continue your care journey with organized booking details.</h2>
            <p>
              Sign in to keep appointments, profile details, and future care
              requests connected to your account.
            </p>
          </div>
          <ul>
            <li>Appointment tracking from the database</li>
            <li>Private patient profile access</li>
            <li>Care notes and reminders</li>
          </ul>
        </aside>

        <div class="auth-panel">
          <p class="eyebrow">Welcome back</p>
          <h1>Login to your account</h1>
          <div class="form-alert" id="formAlert" role="status"></div>
          <form action="login.php" method="post">
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
              <label for="password">Password</label>
              <input
                id="password"
                type="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
              />
            </div>

            <button type="submit" class="btn-submit">Login</button>

            <p class="form-footer">
              Do not have an account?
              <a href="register.php">Register here</a>
            </p>
          </form>
        </div>
      </section>
    </main>
  </body>
</html>
