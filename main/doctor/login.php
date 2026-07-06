<?php

require_once __DIR__ . '/includes/doctor.php';

if (is_doctor_logged_in()) {
    header("Location: index.php");
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
            "SELECT id, fullname, email, password, is_active
             FROM doctors
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$doctor || (int) $doctor['is_active'] !== 1 || !password_verify($password, $doctor['password'])) {
            header("Location: login.php?error=credentials");
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['doctor_id'] = (int) $doctor['id'];
        unset($_SESSION['user_id']);

        header("Location: index.php");
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
    <title>Doctor Login | MediBridge AI</title>
    <link rel="stylesheet" href="../../style.css" />
    <script src="../app.js" defer></script>
  </head>
  <body class="auth-page" data-page="auth">
    <main class="auth-shell">
      <section class="auth-grid" aria-label="Doctor login">
        <aside class="auth-proof">
          <div>
            <span>DR</span>
            <p class="eyebrow">Doctor access</p>
            <h2>Review assigned appointments and update care status.</h2>
            <p>Doctor accounts are created by the MediBridge AI admin team.</p>
          </div>
          <ul>
            <li>Assigned booking list</li>
            <li>Patient appointment details</li>
            <li>Status updates for care coordination</li>
          </ul>
        </aside>

        <div class="auth-panel">
          <p class="eyebrow">Clinical login</p>
          <h1>Doctor panel</h1>
          <div class="form-alert" id="formAlert" role="status"></div>
          <form action="login.php" method="post">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input id="email" type="email" name="email" placeholder="Enter doctor email" required />
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input id="password" type="password" name="password" placeholder="Enter password" required />
            </div>
            <button type="submit" class="btn-submit">Login</button>
            <p class="form-footer">
              <a href="../index.php">Back to public site</a>
            </p>
          </form>
        </div>
      </section>
    </main>
  </body>
</html>
