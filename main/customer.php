<?php

require_once __DIR__ . '/includes/app.php';

$user = require_login();
$appointments = [];
$stmt = $conn->prepare(
    "SELECT booking_code, doctor, appointment_date, appointment_time, visit_type, reason, status, created_at
     FROM appointments
     WHERE user_id = ?
     ORDER BY appointment_date DESC, appointment_time DESC, created_at DESC"
);
$userId = (int) $user['id'];
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

$today = date('Y-m-d');
$upcomingCount = 0;
$pendingCount = 0;
$completedCount = 0;

foreach ($appointments as $appointment) {
    if ($appointment['appointment_date'] >= $today && strcasecmp($appointment['status'], 'Completed') !== 0) {
        $upcomingCount++;
    }

    if (str_contains(strtolower($appointment['status']), 'pending')) {
        $pendingCount++;
    }

    if (strcasecmp($appointment['status'], 'Completed') === 0) {
        $completedCount++;
    }
}

function format_booking_date(string $date, string $time): string
{
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time);
    if (!$dateTime) {
        return $date;
    }

    return $dateTime->format('D, M j, g:i A');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Bookings | MediBridge AI</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body>
    <?php render_nav('customer'); ?>

    <main class="customer-shell">
      <section class="customer-hero">
        <div>
          <p class="eyebrow">Patient workspace</p>
          <h1>Your appointments, profile, and care notes in one place</h1>
          <p>
            Track requests from the database, keep patient contact details ready,
            and continue care with the assistant when symptoms change.
          </p>
        </div>
        <div class="customer-actions">
          <a href="appointment.php" class="btn-primary">New Booking</a>
          <a href="chatbot.php" class="btn-secondary">Ask AI Assistant</a>
        </div>
      </section>

      <section class="dashboard-grid" aria-label="Patient dashboard">
        <aside class="profile-panel">
          <div class="profile-avatar"><?= e(initials($user['fullname'])) ?></div>
          <h2><?= e($user['fullname']) ?></h2>
          <p><?= e($user['email']) ?></p>
          <div class="profile-list">
            <span>Phone</span>
            <strong><?= e($user['phone']) ?></strong>
            <span>Patient ID</span>
            <strong>MB-<?= e(initials($user['fullname'])) ?>-<?= e($user['id']) ?></strong>
            <span>Preferred care</span>
            <strong>Online and clinic visits</strong>
          </div>
        </aside>

        <div class="dashboard-main">
          <?php if (($_GET['booking'] ?? '') === 'saved'): ?>
            <div class="success-banner" style="display: block;">
              Appointment request saved to the database. Your booking is visible below.
            </div>
          <?php endif; ?>

          <div class="metric-row">
            <article class="metric-card">
              <span>Upcoming</span>
              <strong><?= e($upcomingCount) ?></strong>
            </article>
            <article class="metric-card">
              <span>Pending</span>
              <strong><?= e($pendingCount) ?></strong>
            </article>
            <article class="metric-card">
              <span>Completed</span>
              <strong><?= e($completedCount) ?></strong>
            </article>
          </div>

          <section class="booking-panel">
            <div class="booking-header">
              <div>
                <p class="eyebrow">Appointments</p>
                <h2>Booking requests</h2>
              </div>
            </div>

            <?php if ($appointments): ?>
              <div class="booking-list">
                <?php foreach ($appointments as $appointment): ?>
                  <article class="booking-card">
                    <div class="booking-date">
                      <strong><?= e(format_booking_date($appointment['appointment_date'], $appointment['appointment_time'])) ?></strong>
                      <span><?= e($appointment['visit_type']) ?></span>
                    </div>
                    <div class="booking-detail">
                      <h3><?= e($appointment['doctor']) ?></h3>
                      <p><?= e($appointment['reason']) ?></p>
                      <span>Booking ID: <?= e($appointment['booking_code']) ?></span>
                    </div>
                    <div class="booking-status"><?= e($appointment['status']) ?></div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="empty-bookings" style="display: grid;">
                <h3>No bookings yet</h3>
                <p>
                  Book a doctor appointment and it will appear here from the
                  database with status, doctor, date, time, and visit details.
                </p>
                <a href="appointment.php" class="btn-primary">Book Appointment</a>
              </div>
            <?php endif; ?>
          </section>

          <section class="care-columns">
            <article class="care-card">
              <p class="eyebrow">Records</p>
              <h3>Recent care notes</h3>
              <ul>
                <li>Appointments are saved under your patient account</li>
                <li>Booking status updates can be tracked here</li>
                <li>Follow-up requests stay connected to your profile</li>
              </ul>
            </article>
            <article class="care-card">
              <p class="eyebrow">Clinic updates</p>
              <h3>Before your visit</h3>
              <ul>
                <li>Arrive 10 minutes before clinic visits</li>
                <li>Bring previous reports for specialist care</li>
                <li>Online visit links are shared after confirmation</li>
              </ul>
            </article>
          </section>
        </div>
      </section>
    </main>

    <?php render_footer(); ?>
  </body>
</html>
