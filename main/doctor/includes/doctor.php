<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/app.php';

const DOCTOR_STATUSES = [
    'Pending confirmation',
    'Confirmed',
    'Completed',
    'Cancelled',
];

function doctor_flash(string $type, string $message): void
{
    $_SESSION['doctor_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function doctor_get_flash(): ?array
{
    $flash = $_SESSION['doctor_flash'] ?? null;
    unset($_SESSION['doctor_flash']);

    return is_array($flash) ? $flash : null;
}

function doctor_render_shell_start(string $title): array
{
    $doctor = require_doctor();
    $flash = doctor_get_flash();
    ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($title) ?> | MediBridge AI Doctor</title>
    <link rel="stylesheet" href="../../style.css" />
    <script src="../app.js" defer></script>
  </head>
  <body class="admin-body">
    <nav class="navbar admin-navbar">
      <a href="index.php" class="nav-brand">
        <span class="brand-mark">DR</span>
        <span>Doctor Panel</span>
      </a>

      <ul class="nav-links">
        <li><a href="../index.php">Public Site</a></li>
        <li><a href="../logout.php" class="btn-login">Logout</a></li>
      </ul>

      <button
        class="hamburger"
        type="button"
        aria-label="Open menu"
        aria-expanded="false"
        data-menu-button
      >
        <span></span>
        <span></span>
        <span></span>
      </button>
    </nav>

    <main class="admin-shell">
      <aside class="admin-sidebar">
        <div class="admin-profile">
          <div class="profile-avatar"><?= e(initials($doctor['fullname'])) ?></div>
          <div>
            <strong><?= e($doctor['fullname']) ?></strong>
            <span><?= e($doctor['specialty']) ?></span>
          </div>
        </div>

        <nav class="admin-menu" aria-label="Doctor navigation">
          <a href="index.php" class="active">My Appointments</a>
        </nav>
      </aside>

      <section class="admin-content">
        <?php if ($flash): ?>
          <div class="form-alert <?= $flash['type'] === 'success' ? 'success' : '' ?>" style="display: block;" role="status">
            <?= e($flash['message']) ?>
          </div>
        <?php endif; ?>
    <?php

    return $doctor;
}

function doctor_render_shell_end(): void
{
    ?>
      </section>
    </main>
  </body>
</html>
    <?php
}

function doctor_format_datetime(?string $value): string
{
    if (!$value) {
        return 'Not available';
    }

    try {
        return (new DateTime($value))->format('M j, Y g:i A');
    } catch (Throwable) {
        return $value;
    }
}

function doctor_format_booking_datetime(string $date, string $time): string
{
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    if (!$dateTime) {
        return $date . ' ' . $time;
    }

    return $dateTime->format('M j, Y g:i A');
}
