<?php

require_once __DIR__ . '/includes/app.php';

$user = require_login();
$errors = [];
$doctorOptions = array_map('doctor_display_name', public_doctors());
$visitTypes = ['Clinic visit', 'Online consultation', 'Follow-up'];
$selectedDoctor = '';

if (isset($_GET['doctor'])) {
    foreach ($doctorOptions as $option) {
        if (str_contains($option, $_GET['doctor'])) {
            $selectedDoctor = $option;
            break;
        }
    }
}

$form = [
    'patient_name' => $user['fullname'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'doctor' => $selectedDoctor,
    'date' => '',
    'time' => '',
    'visit_type' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'patient_name' => trim($_POST['patient_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'doctor' => trim($_POST['doctor'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'time' => trim($_POST['time'] ?? ''),
        'visit_type' => trim($_POST['visit_type'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ];

    if ($form['patient_name'] === '') {
        $errors[] = 'Patient name is required.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    $cleanPhone = preg_replace('/[^\d+]/', '', $form['phone']);
    if ($cleanPhone === '' || strlen($cleanPhone) < 7) {
        $errors[] = 'Enter a valid phone number.';
    }

    if (!in_array($form['doctor'], $doctorOptions, true)) {
        $errors[] = 'Choose a valid doctor.';
    }

    if ($form['date'] === '' || $form['date'] < date('Y-m-d')) {
        $errors[] = 'Choose today or a future appointment date.';
    }

    if ($form['time'] === '') {
        $errors[] = 'Choose a preferred time.';
    }

    if (!in_array($form['visit_type'], $visitTypes, true)) {
        $errors[] = 'Choose a valid visit type.';
    }

    if ($form['message'] === '') {
        $errors[] = 'Briefly describe the reason for visit.';
    }

    if (!$errors) {
        try {
            $bookingCode = 'MB-' . date('ymd') . '-' . random_int(1000, 9999);
            $stmt = $conn->prepare(
                "INSERT INTO appointments (
                    booking_code,
                    user_id,
                    patient_name,
                    email,
                    phone,
                    doctor,
                    appointment_date,
                    appointment_time,
                    visit_type,
                    reason
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $userId = (int) $user['id'];
            $stmt->bind_param(
                "sissssssss",
                $bookingCode,
                $userId,
                $form['patient_name'],
                $form['email'],
                $cleanPhone,
                $form['doctor'],
                $form['date'],
                $form['time'],
                $form['visit_type'],
                $form['message']
            );
            $stmt->execute();
            $stmt->close();

            header("Location: customer.php?booking=saved");
            exit;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $errors[] = 'Appointment could not be saved. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Book Appointment | MediBridge AI</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body data-page="appointment">
    <?php render_nav('appointment'); ?>

    <main class="page-shell">
      <section class="split-page">
        <div class="page-intro">
          <p class="eyebrow">Appointments</p>
          <h1>Request care with the right doctor</h1>
          <p>
            Share your preferred doctor, visit type, and reason for care. Your
            request is saved directly to the database and appears in My Bookings.
          </p>
          <div class="notice-box">
            <strong>Emergency notice</strong>
            This form is for routine and non-emergency care. If symptoms are
            urgent or life-threatening, contact emergency services immediately.
          </div>
        </div>

        <form class="form-container appointment-form" id="appointmentForm" method="post">
          <h2>Appointment details</h2>

          <?php if ($errors): ?>
            <div class="form-alert" style="display: block;" role="alert">
              <?= e(implode(' ', $errors)) ?>
            </div>
          <?php endif; ?>

          <div class="form-group">
            <label for="patient_name">Patient Name</label>
            <input
              id="patient_name"
              name="patient_name"
              type="text"
              value="<?= e($form['patient_name']) ?>"
              placeholder="Enter patient name"
              required
            />
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                id="email"
                name="email"
                type="email"
                value="<?= e($form['email']) ?>"
                placeholder="Enter email address"
                required
              />
            </div>
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                id="phone"
                name="phone"
                type="tel"
                value="<?= e($form['phone']) ?>"
                placeholder="Enter phone number"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="doctor">Preferred Doctor</label>
            <select id="doctor" name="doctor" required>
              <option value="">Choose a doctor</option>
              <?php foreach ($doctorOptions as $doctor): ?>
                <option value="<?= e($doctor) ?>" <?= $form['doctor'] === $doctor ? 'selected' : '' ?>>
                  <?= e($doctor) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="date">Preferred Date</label>
              <input
                id="date"
                name="date"
                type="date"
                min="<?= e(date('Y-m-d')) ?>"
                value="<?= e($form['date']) ?>"
                required
              />
            </div>
            <div class="form-group">
              <label for="time">Preferred Time</label>
              <input id="time" name="time" type="time" value="<?= e($form['time']) ?>" required />
            </div>
          </div>

          <div class="form-group">
            <label for="visit_type">Visit Type</label>
            <select id="visit_type" name="visit_type" required>
              <option value="">Choose visit type</option>
              <?php foreach ($visitTypes as $visitType): ?>
                <option value="<?= e($visitType) ?>" <?= $form['visit_type'] === $visitType ? 'selected' : '' ?>>
                  <?= e($visitType) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Reason for Visit</label>
            <textarea id="message" name="message" placeholder="Briefly describe the concern" required><?= e($form['message']) ?></textarea>
          </div>

          <button type="submit" class="btn-submit">Submit Appointment Request</button>
        </form>
      </section>
    </main>
  </body>
</html>
