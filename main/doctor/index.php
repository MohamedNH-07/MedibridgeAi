<?php

require_once __DIR__ . '/includes/doctor.php';

$doctor = require_doctor();
$doctorName = doctor_display_name($doctor);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($appointmentId > 0 && in_array($status, DOCTOR_STATUSES, true)) {
        try {
            $stmt = $conn->prepare(
                "UPDATE appointments
                 SET status = ?
                 WHERE id = ? AND doctor = ?"
            );
            $stmt->bind_param("sis", $status, $appointmentId, $doctorName);
            $stmt->execute();
            $updated = $stmt->affected_rows;
            $stmt->close();

            if ($updated > 0) {
                doctor_flash('success', 'Appointment status updated.');
            } else {
                doctor_flash('error', 'Appointment was not assigned to this doctor.');
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            doctor_flash('error', 'Appointment status could not be updated.');
        }
    } else {
        doctor_flash('error', 'Choose a valid appointment status.');
    }

    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, booking_code, patient_name, email, phone, nic, appointment_date, appointment_time, visit_type, reason, status, created_at
     FROM appointments
     WHERE doctor = ?
     ORDER BY appointment_date DESC, appointment_time DESC"
);
$stmt->bind_param("s", $doctorName);
$stmt->execute();
$appointments = $stmt->get_result();

$allAppointments = [];
$upcoming = 0;
$pending = 0;
$completed = 0;
$today = date('Y-m-d');

while ($appointment = $appointments->fetch_assoc()) {
    if ($appointment['appointment_date'] >= $today && $appointment['status'] !== 'Completed') {
        $upcoming++;
    }

    if ($appointment['status'] === 'Pending confirmation') {
        $pending++;
    }

    if ($appointment['status'] === 'Completed') {
        $completed++;
    }

    $allAppointments[] = $appointment;
}
$stmt->close();

$doctor = doctor_render_shell_start('My Appointments');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Doctor workspace</p>
            <h1>My appointments</h1>
            <p>Viewing appointments assigned to <?= e($doctorName) ?>.</p>
          </div>
          <span class="badge"><?= e($doctor['available_days']) ?></span>
        </div>

        <div class="admin-metric-grid">
          <article class="admin-card metric-card">
            <span>Total Assigned</span>
            <strong><?= e(count($allAppointments)) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Upcoming</span>
            <strong><?= e($upcoming) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Pending</span>
            <strong><?= e($pending) ?></strong>
          </article>
          <article class="admin-card metric-card">
            <span>Completed</span>
            <strong><?= e($completed) ?></strong>
          </article>
        </div>

        <section class="admin-card">
          <div class="admin-card-header">
            <div>
              <p class="eyebrow">Care schedule</p>
              <h2>Assigned bookings</h2>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Booking</th>
                  <th>Patient</th>
                  <th>Visit</th>
                  <th>Reason</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($allAppointments): ?>
                  <?php foreach ($allAppointments as $appointment): ?>
                    <tr>
                      <td>
                        <strong><?= e($appointment['booking_code']) ?></strong>
                        <span><?= e(doctor_format_datetime($appointment['created_at'])) ?></span>
                      </td>
                      <td>
                        <strong><?= e($appointment['patient_name']) ?></strong>
                        <span><?= e($appointment['email']) ?></span>
                        <span><?= e($appointment['phone']) ?></span>
                        <span>NIC: <?= e($appointment['nic']) ?></span>
                      </td>
                      <td>
                        <strong><?= e(doctor_format_booking_datetime($appointment['appointment_date'], $appointment['appointment_time'])) ?></strong>
                        <span><?= e($appointment['visit_type']) ?></span>
                      </td>
                      <td><?= e($appointment['reason']) ?></td>
                      <td>
                        <form class="inline-form" method="post">
                          <input type="hidden" name="appointment_id" value="<?= e($appointment['id']) ?>" />
                          <select name="status" aria-label="Appointment status">
                            <?php foreach (DOCTOR_STATUSES as $status): ?>
                              <option value="<?= e($status) ?>" <?= $appointment['status'] === $status ? 'selected' : '' ?>>
                                <?= e($status) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn-clear">Save</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="5">No appointments assigned yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php doctor_render_shell_end(); ?>
