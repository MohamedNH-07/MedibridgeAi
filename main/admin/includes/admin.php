<?php

require_once __DIR__ . '/../../includes/app.php';

const ADMIN_STATUSES = [
    'Pending confirmation',
    'Confirmed',
    'Completed',
    'Cancelled',
];

function admin_flash($type, $message)
{
    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function admin_get_flash()
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    if (is_array($flash)) {
        return $flash;
    }

    return null;
}

function admin_render_shell_start($title, $active = 'dashboard')
{
    $admin = require_admin();
    $flash = admin_get_flash();
    ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($title) ?> | MediBridge AI Admin</title>
    <link rel="stylesheet" href="../../style.css" />
    <script src="../app.js" defer></script>
  </head>
  <body class="admin-body">
    <nav class="navbar admin-navbar">
      <a href="index.php" class="nav-brand">
        <span class="brand-mark">MB</span>
        <span>Admin Panel</span>
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
          <div class="profile-avatar"><?= e(initials($admin['fullname'])) ?></div>
          <div>
            <strong><?= e($admin['fullname']) ?></strong>
            <span><?= e($admin['email']) ?></span>
          </div>
        </div>

        <nav class="admin-menu" aria-label="Admin navigation">
          <a href="index.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
          <a href="appointments.php" class="<?= $active === 'appointments' ? 'active' : '' ?>">Appointments</a>
          <a href="doctors.php" class="<?= $active === 'doctors' ? 'active' : '' ?>">Doctors</a>
          <a href="users.php" class="<?= $active === 'users' ? 'active' : '' ?>">Users</a>
          <a href="messages.php" class="<?= $active === 'messages' ? 'active' : '' ?>">Messages</a>
          <a href="assistant.php" class="<?= $active === 'assistant' ? 'active' : '' ?>">AI Logs</a>
        </nav>
      </aside>

      <section class="admin-content">
        <?php if ($flash): ?>
          <div class="form-alert <?= $flash['type'] === 'success' ? 'success' : '' ?>" style="display: block;" role="status">
            <?= e($flash['message']) ?>
          </div>
        <?php endif; ?>
    <?php
}

function admin_render_shell_end()
{
    ?>
      </section>
    </main>
  </body>
</html>
    <?php
}

function admin_count($table, $where = '')
{
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM {$table}";
    if ($where !== '') {
        $sql .= " WHERE {$where}";
    }

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    return (int) ($row['total'] ?? 0);
}

function admin_format_datetime($value)
{
    if (!$value) {
        return 'Not available';
    }

    try {
        $dateTime = new DateTime($value);
        return $dateTime->format('M j, Y g:i A');
    } catch (Throwable $exception) {
        return $value;
    }
}

function admin_format_booking_datetime($date, $time)
{
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    if (!$dateTime) {
        return $date . ' ' . $time;
    }

    return $dateTime->format('M j, Y g:i A');
}

function admin_status_class($status)
{
    if ($status === 'Confirmed') {
        return 'status-confirmed';
    }

    if ($status === 'Completed') {
        return 'status-completed';
    }

    if ($status === 'Cancelled') {
        return 'status-cancelled';
    }

    return 'status-pending';
}
