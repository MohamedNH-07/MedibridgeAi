# MediBridge AI Beginner Guide

This project is a simple PHP and MySQL healthcare booking app.

## Main Folders

- `database/db.php` connects to MySQL and creates the needed tables.
- `main/index.php` is the public home page.
- `main/login.php` and `main/register.php` handle patient login and registration.
- `main/appointment.php` saves a new appointment.
- `main/customer.php` shows a patient's bookings.
- `main/admin/` contains admin pages.
- `main/doctor/` contains doctor pages.
- `main/includes/app.php` contains common helper functions used by many pages.
- `style.css` controls the design.
- `main/app.js` controls small browser actions like menu, search, and chatbot messages.

## Common PHP Flow

Most pages follow this pattern:

1. Include the shared helper file with `require_once`.
2. Check login or permissions if the page is private.
3. Read form data from `$_POST` or URL data from `$_GET`.
4. Validate the data.
5. Use prepared SQL statements to read or save database records.
6. Show the HTML page.

## Important Helper Functions

- `e($value)` safely prints text inside HTML.
- `current_user()` gets the logged-in patient or admin.
- `current_doctor()` gets the logged-in doctor.
- `require_login()` blocks pages that need a patient login.
- `require_admin()` blocks pages that need an admin login.
- `require_doctor()` blocks pages that need a doctor login.
- `public_doctors()` loads active doctors for the public site.
- `render_nav()` prints the top navigation bar.
- `render_footer()` prints the footer.

## Database Notes

The app uses these main tables:

- `users` for patients and admins.
- `doctors` for doctor accounts.
- `appointments` for bookings. Each appointment stores the patient's NIC number.
- `contact_messages` for contact form messages.
- `assistant_logs` for chatbot logs.

Prepared statements are used when saving or loading form data. This keeps SQL safer and avoids putting user input directly into a query.
