<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Colombo');

require_once __DIR__ . '/../../database/db.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    global $conn;
    $userId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT id, fullname, email, phone, is_admin
         FROM users
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $_SESSION = [];
        session_destroy();
        return null;
    }

    $user = $result;
    return $user;
}

function current_doctor(): ?array
{
    if (empty($_SESSION['doctor_id'])) {
        return null;
    }

    static $doctor = null;
    if ($doctor !== null) {
        return $doctor;
    }

    global $conn;
    $doctorId = (int) $_SESSION['doctor_id'];
    $stmt = $conn->prepare(
        "SELECT id, fullname, email, phone, specialty, available_days, is_active
         FROM doctors
         WHERE id = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result || (int) $result['is_active'] !== 1) {
        unset($_SESSION['doctor_id']);
        return null;
    }

    $doctor = $result;
    return $doctor;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_doctor_logged_in(): bool
{
    return current_doctor() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && (int) ($user['is_admin'] ?? 0) === 1;
}

function admin_exists(): bool
{
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE is_admin = 1");
    $row = $result->fetch_assoc();
    return (int) ($row['total'] ?? 0) > 0;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header("Location: login.php?error=auth");
        exit;
    }

    return $user;
}

function require_admin(): array
{
    if (!admin_exists()) {
        header("Location: setup.php");
        exit;
    }

    $user = current_user();
    if (!$user || (int) ($user['is_admin'] ?? 0) !== 1) {
        header("Location: login.php?error=auth");
        exit;
    }

    return $user;
}

function require_doctor(): array
{
    $doctor = current_doctor();
    if (!$doctor) {
        header("Location: login.php?error=auth");
        exit;
    }

    return $doctor;
}

function default_doctors(): array
{
    return [
        [
            'fullname' => 'Dr. Aisha Perera',
            'specialty' => 'General Physician',
            'available_days' => 'Mon, Wed, Fri',
            'tag' => 'Clinic and online',
        ],
        [
            'fullname' => 'Dr. Rajan Kumar',
            'specialty' => 'Cardiologist',
            'available_days' => 'Tue, Thu',
            'tag' => 'Referral friendly',
        ],
        [
            'fullname' => 'Dr. Sameera Dias',
            'specialty' => 'Dermatologist',
            'available_days' => 'Mon, Sat',
            'tag' => 'Skin and allergy',
        ],
        [
            'fullname' => 'Dr. Nimal Fernando',
            'specialty' => 'Pediatrician',
            'available_days' => 'Wed, Fri, Sun',
            'tag' => 'Child care',
        ],
    ];
}

function specialty_slug(string $specialty): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $specialty) ?? '');
    return trim($slug, '-') ?: 'doctor';
}

function doctor_display_name(array $doctor): string
{
    return $doctor['fullname'] . ' - ' . $doctor['specialty'];
}

function public_doctors(): array
{
    global $conn;
    $doctors = [];
    $result = $conn->query(
        "SELECT fullname, specialty, available_days
         FROM doctors
         WHERE is_active = 1
         ORDER BY fullname ASC"
    );

    while ($row = $result->fetch_assoc()) {
        $row['tag'] = 'Clinic and online';
        $doctors[] = $row;
    }

    return $doctors;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';

    foreach (array_slice(array_filter($parts), 0, 2) as $part) {
        $letters .= strtoupper(substr($part, 0, 1));
    }

    return $letters !== '' ? $letters : 'MB';
}

function render_nav(string $active = 'home'): void
{
    $loggedIn = is_logged_in();
    $doctorLoggedIn = is_doctor_logged_in();
    $homePrefix = $active === 'home' ? '' : 'index.php';
    ?>
    <nav class="navbar">
      <a href="index.php" class="nav-brand" aria-label="MediBridge AI home">
        <span class="brand-mark">MB</span>
        <span>MediBridge AI</span>
      </a>

      <ul class="nav-links">
        <li><a href="<?= e($homePrefix) ?>#home" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= e($homePrefix) ?>#services">Services</a></li>
        <li><a href="<?= e($homePrefix) ?>#doctors">Doctors</a></li>
        <li><a href="appointment.php" class="<?= $active === 'appointment' ? 'active' : '' ?>">Book Appointment</a></li>
        <?php if ($loggedIn && !is_admin()): ?>
          <li><a href="customer.php" class="<?= $active === 'customer' ? 'active' : '' ?>">My Bookings</a></li>
        <?php endif; ?>
        <li><a href="<?= e($homePrefix) ?>#about">About</a></li>
        <li><a href="<?= e($homePrefix) ?>#contact">Contact</a></li>
        <li><a href="chatbot.php" class="<?= $active === 'assistant' ? 'active' : '' ?>">AI Assistant</a></li>
        <?php if (is_admin()): ?>
          <li><a href="admin/index.php" class="btn-login">Admin</a></li>
          <li><a href="logout.php" class="btn-login">Logout</a></li>
        <?php elseif ($doctorLoggedIn): ?>
          <li><a href="doctor/index.php" class="btn-login">Doctor Panel</a></li>
          <li><a href="logout.php" class="btn-login">Logout</a></li>
        <?php elseif ($loggedIn): ?>
          <li><a href="logout.php" class="btn-login">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php" class="btn-login <?= $active === 'login' ? 'active' : '' ?>">Login</a></li>
          <li><a href="register.php" class="btn-register <?= $active === 'register' ? 'active' : '' ?>">Register</a></li>
        <?php endif; ?>
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
    <?php
}

function render_footer(): void
{
    ?>
    <footer class="footer">
      <div class="footer-inner">
        <div>
          <strong>MediBridge AI</strong>
          <p>Professional digital healthcare access for patients and clinics.</p>
        </div>
        <div>
          <a href="index.php#services">Services</a>
          <a href="index.php#doctors">Doctors</a>
          <a href="chatbot.php">AI Assistant</a>
          <a href="appointment.php">Book Care</a>
          <?php if (is_logged_in() && !is_admin()): ?>
            <a href="customer.php">My Bookings</a>
          <?php endif; ?>
          <?php if (is_admin()): ?>
            <a href="admin/index.php">Admin</a>
          <?php endif; ?>
          <?php if (is_doctor_logged_in()): ?>
            <a href="doctor/index.php">Doctor Panel</a>
          <?php endif; ?>
        </div>
        <p class="copyright">&copy; 2026 MediBridge AI. All rights reserved.</p>
      </div>
    </footer>
    <?php
}

function flash_message(string $name): ?string
{
    $value = $_SESSION[$name] ?? null;
    unset($_SESSION[$name]);
    return is_string($value) ? $value : null;
}
