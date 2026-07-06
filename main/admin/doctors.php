<?php

require_once __DIR__ . '/includes/admin.php';

require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
        $specialty = trim($_POST['specialty'] ?? '');
        $availableDays = trim($_POST['available_days'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($fullname === '' || !$email || $phone === '' || $specialty === '' || $availableDays === '' || $password === '') {
            $errors[] = 'Complete all doctor account fields.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Doctor password must be at least 8 characters.';
        }

        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
        if ($cleanPhone === '' || strlen($cleanPhone) < 7) {
            $errors[] = 'Enter a valid doctor phone number.';
        }

        if (!$errors) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "INSERT INTO doctors (fullname, email, phone, specialty, available_days, password)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssss", $fullname, $email, $cleanPhone, $specialty, $availableDays, $hashedPassword);
                $stmt->execute();
                $stmt->close();
                admin_flash('success', 'Doctor account created.');
                header("Location: doctors.php");
                exit;
            } catch (Throwable $exception) {
                error_log($exception->getMessage());
                $errors[] = 'Doctor account could not be created. The email may already exist.';
            }
        }
    }

    if ($action === 'status') {
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $isActive = (int) ($_POST['is_active'] ?? 0);

        if ($doctorId > 0 && in_array($isActive, [0, 1], true)) {
            try {
                $stmt = $conn->prepare("UPDATE doctors SET is_active = ? WHERE id = ?");
                $stmt->bind_param("ii", $isActive, $doctorId);
                $stmt->execute();
                $stmt->close();
                admin_flash('success', 'Doctor status updated.');
            } catch (Throwable $exception) {
                error_log($exception->getMessage());
                admin_flash('error', 'Doctor status could not be updated.');
            }
        }

        header("Location: doctors.php");
        exit;
    }

    if ($action === 'password') {
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        if ($doctorId > 0 && strlen($password) >= 8) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE doctors SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $doctorId);
                $stmt->execute();
                $stmt->close();
                admin_flash('success', 'Doctor password updated.');
            } catch (Throwable $exception) {
                error_log($exception->getMessage());
                admin_flash('error', 'Doctor password could not be updated.');
            }
        } else {
            admin_flash('error', 'Password must be at least 8 characters.');
        }

        header("Location: doctors.php");
        exit;
    }
}

$doctors = $conn->query(
    "SELECT d.*, COUNT(a.id) AS appointment_count
     FROM doctors d
     LEFT JOIN appointments a ON a.doctor = CONCAT(d.fullname, ' - ', d.specialty)
     GROUP BY d.id, d.fullname, d.email, d.phone, d.specialty, d.available_days, d.password, d.is_active, d.created_at
     ORDER BY d.created_at DESC, d.fullname ASC"
);

admin_render_shell_start('Doctors', 'doctors');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Clinical team</p>
            <h1>Doctors</h1>
            <p>Create doctor login accounts and control whether each doctor appears in public booking.</p>
          </div>
        </div>

        <section class="admin-card">
          <div class="admin-card-header">
            <div>
              <p class="eyebrow">New account</p>
              <h2>Create doctor login</h2>
            </div>
          </div>

          <?php if ($errors): ?>
            <div class="form-alert" style="display: block;" role="alert">
              <?= e(implode(' ', $errors)) ?>
            </div>
          <?php endif; ?>

          <form class="admin-form-grid" method="post">
            <input type="hidden" name="action" value="create" />
            <div class="form-group">
              <label for="fullname">Doctor Name</label>
              <input id="fullname" name="fullname" type="text" placeholder="Dr. Full Name" required />
            </div>
            <div class="form-group">
              <label for="specialty">Specialty</label>
              <input id="specialty" name="specialty" type="text" placeholder="Cardiologist" required />
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="doctor@example.com" required />
            </div>
            <div class="form-group">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" placeholder="+94..." required />
            </div>
            <div class="form-group">
              <label for="available_days">Available Days</label>
              <input id="available_days" name="available_days" type="text" placeholder="Mon, Wed, Fri" required />
            </div>
            <div class="form-group">
              <label for="password">Temporary Password</label>
              <input id="password" name="password" type="password" minlength="8" required />
            </div>
            <button type="submit" class="btn-submit">Create Doctor</button>
          </form>
        </section>

        <section class="admin-card">
          <div class="admin-card-header">
            <div>
              <p class="eyebrow">Directory</p>
              <h2>Doctor accounts</h2>
            </div>
          </div>

          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Doctor</th>
                  <th>Contact</th>
                  <th>Availability</th>
                  <th>Appointments</th>
                  <th>Status</th>
                  <th>Password</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($doctors->num_rows): ?>
                  <?php while ($doctor = $doctors->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <strong><?= e($doctor['fullname']) ?></strong>
                        <span><?= e($doctor['specialty']) ?></span>
                      </td>
                      <td>
                        <span><?= e($doctor['email']) ?></span>
                        <span><?= e($doctor['phone']) ?></span>
                      </td>
                      <td><?= e($doctor['available_days']) ?></td>
                      <td><?= e($doctor['appointment_count']) ?></td>
                      <td>
                        <form class="inline-form" method="post">
                          <input type="hidden" name="action" value="status" />
                          <input type="hidden" name="doctor_id" value="<?= e($doctor['id']) ?>" />
                          <select name="is_active" aria-label="Doctor status">
                            <option value="1" <?= (int) $doctor['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (int) $doctor['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                          </select>
                          <button type="submit" class="btn-clear">Save</button>
                        </form>
                      </td>
                      <td>
                        <form class="inline-form" method="post">
                          <input type="hidden" name="action" value="password" />
                          <input type="hidden" name="doctor_id" value="<?= e($doctor['id']) ?>" />
                          <input name="password" type="password" placeholder="New password" minlength="8" required />
                          <button type="submit" class="btn-clear">Reset</button>
                        </form>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="6">No doctor accounts yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php admin_render_shell_end(); ?>
