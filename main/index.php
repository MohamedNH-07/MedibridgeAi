<?php

require_once __DIR__ . '/includes/app.php';

$user = current_user();
$contactState = $_GET['contact'] ?? '';
$contactMessage = match ($contactState) {
    'saved' => 'Message sent. The support team will follow up soon.',
    'failed' => 'Message could not be sent. Please try again.',
    default => ''
};
$contactClass = $contactState === 'saved' ? 'form-alert success' : 'form-alert';
$doctors = public_doctors();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="MediBridge AI helps patients book appointments, prepare for consultations, and manage care details online."
    />
    <title>MediBridge AI | Digital Healthcare Access</title>
    <link rel="stylesheet" href="../style.css" />
    <script src="app.js" defer></script>
  </head>
  <body data-page="home">
    <?php render_nav('home'); ?>

    <main>
      <section class="hero-wrap" id="home">
        <div class="hero">
          <div class="hero-copy">
            <p class="eyebrow">Digital healthcare access</p>
            <h1>Smarter care starts with <span>one trusted bridge.</span></h1>
            <p>
              MediBridge AI helps patients prepare symptoms, book verified
              doctors, and keep appointment details organized from a single
              secure care experience.
            </p>
            <div class="hero-actions">
              <a href="appointment.php" class="btn-primary">Book Appointment</a>
              <a href="chatbot.php" class="btn-secondary">Use AI Assistant</a>
            </div>
            <div class="trust-strip" aria-label="Platform highlights">
              <div class="trust-item">
                <strong>24/7</strong>
                <span>Patient guidance</span>
              </div>
              <div class="trust-item">
                <strong>50+</strong>
                <span>Verified doctors</span>
              </div>
              <div class="trust-item">
                <strong>10K+</strong>
                <span>Care journeys</span>
              </div>
            </div>
          </div>

          <div class="hero-visual" aria-label="Doctor using MediBridge AI">
            <div class="care-status" aria-hidden="true">
              <span>AI triage ready</span>
              <span>Appointments open</span>
              <span>Records organized</span>
            </div>
            <img
              src="../images/doctor ilustration.png"
              alt="Doctor representing MediBridge AI digital care"
            />
          </div>
        </div>
      </section>

      <section class="section is-white" aria-labelledby="features-title">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">Patient centered platform</p>
            <h2 id="features-title">A clear path from concern to confirmed care</h2>
            <p>
              Each tool supports a real step in the care journey, from first
              symptom notes to follow-up reminders.
            </p>
          </div>

          <div class="feature-grid">
            <article class="feature-card">
              <span class="icon">AI</span>
              <h3>Symptom Preparation</h3>
              <p>Capture the concern, duration, and warning signs before you book.</p>
            </article>
            <article class="feature-card">
              <span class="icon">DR</span>
              <h3>Doctor Matching</h3>
              <p>Search by specialty and choose doctors with visible availability.</p>
            </article>
            <article class="feature-card">
              <span class="icon">AP</span>
              <h3>Fast Booking</h3>
              <p>Submit visit requests with preferred date, time, and care type.</p>
            </article>
            <article class="feature-card">
              <span class="icon">ID</span>
              <h3>Patient Dashboard</h3>
              <p>Review appointments, profile details, and care notes after login.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="section is-soft" id="services">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">Services</p>
            <h2>Built for everyday healthcare needs</h2>
            <p>
              MediBridge AI is designed for routine and non-emergency care,
              giving patients practical tools before and after they meet a
              doctor.
            </p>
          </div>

          <div class="service-grid">
            <article class="service-card">
              <span>OC</span>
              <h3>Online Consultation</h3>
              <p>Request remote care for suitable non-emergency concerns.</p>
            </article>
            <article class="service-card">
              <span>AI</span>
              <h3>AI Health Assistant</h3>
              <p>Receive general guidance and know when to seek urgent help.</p>
            </article>
            <article class="service-card">
              <span>RX</span>
              <h3>Prescription Notes</h3>
              <p>Keep instructions and follow-up reminders close to your visit.</p>
            </article>
            <article class="service-card">
              <span>FU</span>
              <h3>Follow-up Support</h3>
              <p>Track continuing care steps after your appointment request.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="section workflow-band">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">How it works</p>
            <h2>Four focused steps from symptoms to appointment</h2>
          </div>

          <div class="timeline">
            <article>
              <strong>1</strong>
              <h3>Create your profile</h3>
              <p>Register basic contact details so bookings stay organized.</p>
            </article>
            <article>
              <strong>2</strong>
              <h3>Prepare your concern</h3>
              <p>Use the assistant to summarize symptoms and warning signs.</p>
            </article>
            <article>
              <strong>3</strong>
              <h3>Select a doctor</h3>
              <p>Choose by specialty, availability, and preferred visit type.</p>
            </article>
            <article>
              <strong>4</strong>
              <h3>Track your booking</h3>
              <p>Review pending appointments and care notes in the dashboard.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="section doctors" id="doctors">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">Medical team</p>
            <h2>Find the right doctor faster</h2>
            <p>Search the available doctors by name or filter by specialty.</p>
          </div>

          <div class="doctor-tools">
            <div class="search-section">
              <input
                type="text"
                id="searchInput"
                placeholder="Search doctor name or specialty"
                aria-label="Search doctors"
              />
            </div>
            <select id="specialtyFilter" aria-label="Filter doctors by specialty">
              <option value="all">All specialties</option>
              <?php foreach (array_unique(array_column($doctors, 'specialty')) as $specialty): ?>
                <option value="<?= e(specialty_slug($specialty)) ?>"><?= e($specialty) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="doctor-cards" id="doctorList">
            <?php foreach ($doctors as $doctor): ?>
              <article class="doctor-card" data-specialty="<?= e(specialty_slug($doctor['specialty'])) ?>">
                <header>
                  <div class="doctor-avatar"><?= e(initials($doctor['fullname'])) ?></div>
                  <div>
                    <h3><?= e($doctor['fullname']) ?></h3>
                    <p><?= e($doctor['specialty']) ?></p>
                  </div>
                </header>
                <div class="doctor-meta">
                  <span><?= e($doctor['available_days']) ?></span>
                  <span><?= e($doctor['tag'] ?? 'Clinic and online') ?></span>
                </div>
                <a href="appointment.php?doctor=<?= e(urlencode($doctor['fullname'])) ?>" class="btn-book">Book Now</a>
              </article>
            <?php endforeach; ?>
          </div>
          <p class="empty-state" id="emptyState">No doctors found. Try another name or specialty.</p>
        </div>
      </section>

      <section class="section is-soft" id="about">
        <div class="section-inner">
          <div class="insight-grid">
            <article class="insight-card primary">
              <p class="eyebrow">About MediBridge AI</p>
              <h3>A practical care bridge for patients and clinics</h3>
              <p>
                MediBridge AI reduces friction around appointment requests,
                symptom preparation, and patient communication. It gives
                patients structure before they meet a doctor and gives clinics
                clearer information to work with.
              </p>
              <ul class="about-list">
                <li>General guidance for routine health concerns</li>
                <li>Doctor discovery across common specialties</li>
                <li>Appointment details stored in the database for follow-up</li>
              </ul>
            </article>

            <article class="insight-card">
              <p class="eyebrow">Safety note</p>
              <h3>Designed to support care, not replace doctors</h3>
              <p>
                The assistant provides general information only. It does not
                diagnose, prescribe treatment, or replace emergency medical
                services.
              </p>
              <a href="chatbot.php" class="btn-secondary">Start Symptom Check</a>
            </article>
          </div>
        </div>
      </section>

      <section class="section is-white faq">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">FAQ</p>
            <h2>Common questions</h2>
          </div>

          <div class="faq-grid">
            <details>
              <summary>Can the AI assistant diagnose me?</summary>
              <p>No. It gives general guidance and helps you prepare for a qualified medical consultation.</p>
            </details>
            <details>
              <summary>Do I need an account to book?</summary>
              <p>Yes. Login is required so each appointment can be saved securely to your booking history.</p>
            </details>
            <details>
              <summary>Is MediBridge AI for emergencies?</summary>
              <p>No. For urgent or life-threatening symptoms, contact emergency services immediately.</p>
            </details>
          </div>
        </div>
      </section>

      <section class="section is-soft" id="contact">
        <div class="section-inner">
          <div class="section-heading center">
            <p class="eyebrow">Contact</p>
            <h2>Need help coordinating care?</h2>
            <p>
              Contact the MediBridge AI support team for help with accounts,
              appointments, or platform questions.
            </p>
          </div>
        </div>

        <div class="contact-container">
          <div class="contact-info">
            <div class="contact-details">
              <p><strong>Email:</strong> support@medibridgeai.com</p>
              <p><strong>Phone:</strong> +94 77 123 4567</p>
              <p><strong>Location:</strong> Colombo, Sri Lanka</p>
            </div>
          </div>

          <form class="contact-form" action="contact.php" method="post">
            <?php if ($contactMessage !== ''): ?>
              <div class="<?= e($contactClass) ?>" style="display: block;" role="status">
                <?= e($contactMessage) ?>
              </div>
            <?php endif; ?>
            <label>
              Full Name
              <input
                type="text"
                name="fullname"
                placeholder="Enter your name"
                value="<?= e($user['fullname'] ?? '') ?>"
                required
              />
            </label>
            <label>
              Email Address
              <input
                type="email"
                name="email"
                placeholder="Enter your email"
                value="<?= e($user['email'] ?? '') ?>"
                required
              />
            </label>
            <label>
              Message
              <textarea name="message" placeholder="How can we help?" required></textarea>
            </label>
            <button type="submit" class="btn-submit">Send Message</button>
          </form>
        </div>
      </section>
    </main>

    <?php render_footer(); ?>
  </body>
</html>
