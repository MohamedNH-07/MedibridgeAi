<?php

require_once __DIR__ . '/includes/admin.php';

if (admin_exists()) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullname === '' || !$email || $phone === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Complete all fields.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
    if ($cleanPhone === '' || strlen($cleanPhone) < 7) {
        $errors[] = 'Enter a valid phone number.';
    }

    if (!$errors) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $existingUser = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existingUser) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "UPDATE users
                     SET fullname = ?, phone = ?, password = ?, is_admin = 1
                     WHERE id = ?"
                );
                $existingId = (int) $existingUser['id'];
                $stmt->bind_param("sssi", $fullname, $cleanPhone, $hashedPassword, $existingId);
                $stmt->execute();
                $stmt->close();

                $_SESSION['user_id'] = $existingId;
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $isAdmin = 1;
                $stmt = $conn->prepare(
                    "INSERT INTO users (fullname, email, phone, password, is_admin)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssi", $fullname, $email, $cleanPhone, $hashedPassword, $isAdmin);
                $stmt->execute();
                $_SESSION['user_id'] = (int) $stmt->insert_id;
                $stmt->close();
            }

            session_regenerate_id(true);
            header("Location: index.php");
            exit;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $errors[] = 'Admin account could not be created.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Admin | MediBridge AI</title>
    <link rel="stylesheet" href="../../style.css" />
    <script src="../app.js" defer></script>
  </head>
  <body class="auth-page" data-page="auth">
    <main class="auth-shell">
      <section class="auth-grid" aria-label="Create first admin">
        <aside class="auth-proof">
          <div>
            <span>AD</span>
            <p class="eyebrow">Admin setup</p>
            <h2>Create the first staff administrator.</h2>
            <p>This screen is available only while no admin account exists.</p>
          </div>
          <ul>
            <li>Manage patient appointments</li>
            <li>Review users and support messages</li>
            <li>Monitor assistant conversation logs</li>
          </ul>
        </aside>

        <div class="auth-panel">
          <p class="eyebrow">First administrator</p>
          <h1>Create admin account</h1>
          <?php if ($errors): ?>
            <div class="form-alert" style="display: block;" role="alert">
              <?= e(implode(' ', $errors)) ?>
            </div>
          <?php endif; ?>

          <form action="setup.php" method="post">
            <div class="form-group">
              <label for="fullname">Full Name</label>
              <input id="fullname" name="fullname" type="text" placeholder="Admin name" required />
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="admin@example.com" required />
              </div>
              <div class="form-group">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="tel" placeholder="+94..." required />
              </div>
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" minlength="8" required />
              </div>
              <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" minlength="8" required />
              </div>
            </div>
            <button type="submit" class="btn-submit">Create Admin</button>
          </form>
        </div>
      </section>
    </main>
  </body>
</html>
