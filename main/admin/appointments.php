<?php

require_once __DIR__ . '/includes/admin.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($appointmentId > 0 && in_array($status, ADMIN_STATUSES, true)) {
        try {
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $appointmentId);
            $stmt->execute();
            $stmt->close();
            admin_flash('success', 'Appointment status updated.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            admin_flash('error', 'Appointment status could not be updated.');
        }
    } else {
        admin_flash('error', 'Choose a valid appointment status.');
    }

    header("Location: appointments.php");
    exit;
}

$statusFilter = trim($_GET['status'] ?? '');
$allowedFilter = in_array($statusFilter, ADMIN_STATUSES, true);

if ($allowedFilter) {
    $stmt = $conn->prepare(
        "SELECT a.*, u.fullname AS account_name, u.email AS account_email
         FROM appointments a
         LEFT JOIN users u ON a.user_id = u.id
         WHERE a.status = ?
         ORDER BY a.appointment_date DESC, a.appointment_time DESC"
    );
    $stmt->bind_param("s", $statusFilter);
    $stmt->execute();
    $appointments = $stmt->get_result();
} else {
    $appointments = $conn->query(
        "SELECT a.*, u.fullname AS account_name, u.email AS account_email
         FROM appointments a
         LEFT JOIN users u ON a.user_id = u.id
         ORDER BY a.appointment_date DESC, a.appointment_time DESC"
    );
}

admin_render_shell_start('Appointments', 'appointments');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Care operations</p>
            <h1>Appointments</h1>
            <p>Review every booking request and update its confirmation status.</p>
          </div>
          <form class="admin-filter" method="get">
            <select name="status" aria-label="Filter by status">
              <option value="">All statuses</option>
              <?php foreach (ADMIN_STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                  <?= e($status) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-clear">Filter</button>
          </form>
        </div>

        <section class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Booking</th>
                  <th>Patient</th>
                  <th>Appointment</th>
                  <th>Reason</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($appointments->num_rows): ?>
                  <?php while ($appointment = $appointments->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <strong><?= e($appointment['booking_code']) ?></strong>
                        <span><?= e(admin_format_datetime($appointment['created_at'])) ?></span>
                      </td>
                      <td>
                        <strong><?= e($appointment['patient_name']) ?></strong>
                        <span><?= e($appointment['email']) ?></span>
                        <span><?= e($appointment['phone']) ?></span>
                      </td>
                      <td>
                        <strong><?= e($appointment['doctor']) ?></strong>
                        <span><?= e(admin_format_booking_datetime($appointment['appointment_date'], $appointment['appointment_time'])) ?></span>
                        <span><?= e($appointment['visit_type']) ?></span>
                      </td>
                      <td><?= e($appointment['reason']) ?></td>
                      <td>
                        <form class="inline-form" method="post">
                          <input type="hidden" name="appointment_id" value="<?= e($appointment['id']) ?>" />
                          <select name="status" aria-label="Appointment status">
                            <?php foreach (ADMIN_STATUSES as $status): ?>
                              <option value="<?= e($status) ?>" <?= $appointment['status'] === $status ? 'selected' : '' ?>>
                                <?= e($status) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn-clear">Save</button>
                        </form>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="5">No appointments found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php
if (isset($stmt)) {
    $stmt->close();
}
admin_render_shell_end();
?>
