<?php

require_once __DIR__ . '/includes/admin.php';

if (!admin_exists()) {
    header("Location: setup.php");
    exit;
}

if (is_admin()) {
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
            "SELECT id, fullname, email, password, is_admin
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || (int) $user['is_admin'] !== 1 || !password_verify($password, $user['password'])) {
            header("Location: login.php?error=credentials");
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        unset($_SESSION['doctor_id']);

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
    <title>Admin Login | MediBridge AI</title>
    <link rel="stylesheet" href="../../style.css" />
    <script src="../app.js" defer></script>
  </head>
  <body class="auth-page" data-page="auth">
    <main class="auth-shell">
      <section class="auth-grid" aria-label="Admin login">
        <aside class="auth-proof">
          <div>
            <span>AD</span>
            <p class="eyebrow">Admin access</p>
            <h2>Manage MediBridge AI operations securely.</h2>
            <p>Only staff accounts marked as admin can access this area.</p>
          </div>
          <ul>
            <li>Appointment status control</li>
            <li>User and message review</li>
            <li>Operational dashboard metrics</li>
          </ul>
        </aside>

        <div class="auth-panel">
          <p class="eyebrow">Staff login</p>
          <h1>Admin panel</h1>
          <div class="form-alert" id="formAlert" role="status"></div>
          <form action="login.php" method="post">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input id="email" type="email" name="email" placeholder="Enter admin email" required />
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
