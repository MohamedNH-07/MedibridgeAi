<?php

require_once __DIR__ . '/includes/admin.php';

admin_render_shell_start('Dashboard', 'dashboard');

$totalUsers = admin_count('users', 'is_admin = 0');
$totalAdmins = admin_count('users', 'is_admin = 1');
$totalDoctors = admin_count('doctors');
$totalAppointments = admin_count('appointments');
$pendingAppointments = admin_count('appointments', "status = 'Pending confirmation'");
$contactMessages = admin_count('contact_messages');
$unreadMessages = admin_count('contact_messages', 'is_read = 0');
$assistantLogs = admin_count('assistant_logs');

$recentAppointments = $conn->query(
    "SELECT a.booking_code, a.patient_name, a.doctor, a.appointment_date, a.appointment_time, a.status, u.email AS account_email
     FROM appointments a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC
     LIMIT 6"
);

$recentMessages = $conn->query(
    "SELECT fullname, email, message, is_read, created_at
     FROM contact_messages
     ORDER BY created_at DESC
     LIMIT 4"
);
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Operations</p>
            <h1>Admin dashboard</h1>
            <p>Monitor bookings, patients, contact messages, and assistant activity from one database-backed workspace.</p>
          </div>
          <a href="appointments.php" class="btn-primary">Review Appointments</a>
        </div>

        <div class="admin-metric-grid">
          <article class="admin-card metric-card">
            <span>Patient Users</span>
            <strong><?= e($totalUsers) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Admins</span>
            <strong><?= e($totalAdmins) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Doctors</span>
            <strong><?= e($totalDoctors) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Appointments</span>
            <strong><?= e($totalAppointments) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Pending</span>
            <strong><?= e($pendingAppointments) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Messages</span>
            <strong><?= e($contactMessages) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>AI Logs</span>
            <strong><?= e($assistantLogs) ?></strong>
          </article>
        </div>

        <div class="admin-two-column">
          <section class="admin-card">
            <div class="admin-card-header">
              <div>
                <p class="eyebrow">Recent</p>
                <h2>Latest appointments</h2>
              </div>
              <a href="appointments.php" class="btn-clear">View All</a>
            </div>
            <div class="admin-table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Booking</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($recentAppointments->num_rows): ?>
                    <?php while ($appointment = $recentAppointments->fetch_assoc()): ?>
                      <tr>
                        <td><?= e($appointment['booking_code']) ?></td>
                        <td>
                          <strong><?= e($appointment['patient_name']) ?></strong>
                          <span><?= e(admin_format_booking_datetime($appointment['appointment_date'], $appointment['appointment_time'])) ?></span>
                        </td>
                        <td><?= e($appointment['doctor']) ?></td>
                        <td>
                          <span class="status-pill <?= e(admin_status_class($appointment['status'])) ?>">
                            <?= e($appointment['status']) ?>
                          </span>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="4">No appointments yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>

          <section class="admin-card">
            <div class="admin-card-header">
              <div>
                <p class="eyebrow">Support</p>
                <h2>Contact inbox</h2>
              </div>
              <span class="badge"><?= e($unreadMessages) ?> unread</span>
            </div>
            <div class="admin-feed">
              <?php if ($recentMessages->num_rows): ?>
                <?php while ($message = $recentMessages->fetch_assoc()): ?>
                  <article>
                    <strong><?= e($message['fullname']) ?></strong>
                    <span><?= e($message['email']) ?> - <?= e(admin_format_datetime($message['created_at'])) ?></span>
                    <p><?= e($message['message']) ?></p>
                  </article>
                <?php endwhile; ?>
              <?php else: ?>
                <p>No contact messages yet.</p>
              <?php endif; ?>
            </div>
          </section>
        </div>
<?php admin_render_shell_end(); ?>
