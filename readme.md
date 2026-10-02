# MediBridge AI – Healthcare Appointment Management System

MediBridge AI is a full-stack healthcare management web application built with **PHP and MySQL**. It lets patients find doctors, book appointments online and get basic symptom guidance from a chat assistant, while doctors and administrators manage bookings through their own secure panels.

## Features

### Patient Portal
- Patient registration and secure login
- Browse available doctors with live search and specialty filter
- Book appointments (clinic visit, online consultation or follow-up) with date, time and reason for visit
- Automatic booking code for every appointment (e.g. `MB-260716-4821`)
- "My bookings" page showing appointment history and status
- Contact form to send messages to the clinic

### AI Health Assistant (Chatbot)
- Symptom chat interface with quick-prompt buttons (fever, chest pain, skin rash, headache)
- Rule-based guidance for common symptoms
- Emergency keyword detection (e.g. chest pain, difficulty breathing) that advises seeking urgent care
- Chat history is logged to the database for administrator review
- Medical safety notice: the assistant gives general guidance only and is not a diagnosis tool

### Doctor Panel
- Separate doctor login
- View appointments assigned to the logged-in doctor
- Update appointment status

### Admin Panel
- First-run setup page to create the initial administrator
- Dashboard with latest appointments and contact inbox
- Manage all appointments and filter by status
- Create and manage doctor accounts
- View registered users
- Read contact messages
- Review assistant chat logs

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP (sessions, prepared statements) |
| Database | MySQL (MySQLi) |
| Frontend | HTML5, CSS3, JavaScript |
| Server (local) | XAMPP / WAMP / any Apache + PHP + MySQL stack |

## Security Practices

- Passwords hashed with `password_hash()` and verified with `password_verify()`
- SQL injection protection through prepared statements
- Output escaped with `htmlspecialchars()` to prevent XSS
- Session ID regenerated on login
- Role-based access control for patients, doctors and administrators
- Server-side validation of all form input, including phone, email and Sri Lankan NIC format

## Database

The app uses five tables:

| Table | Purpose |
|-------|---------|
| `users` | Patient and admin accounts |
| `doctors` | Doctor accounts, specialty and available days |
| `appointments` | Bookings, including patient NIC, visit type and status |
| `contact_messages` | Messages from the contact form |
| `assistant_logs` | Chatbot conversation logs |

The full schema is in `database/schema.sql`. The tables are also created automatically the first time the app connects (`database/db.php`).

## Project Structure

```
MedibridgeAi/
├── database/
│   ├── db.php              # MySQL connection and automatic table setup
│   └── schema.sql          # Database schema
├── images/                 # Site images
├── main/
│   ├── admin/              # Admin panel pages
│   ├── doctor/             # Doctor panel pages
│   ├── includes/app.php    # Shared helper functions
│   ├── index.php           # Home page
│   ├── login.php / register.php / logout.php
│   ├── appointment.php     # Book an appointment
│   ├── customer.php        # Patient bookings
│   ├── chatbot.php         # AI health assistant page
│   ├── assistant_log.php   # Saves chat logs
│   ├── contact.php
│   └── app.js              # Menu, search/filter and chatbot logic
├── style.css
└── BEGINNER_GUIDE.md
```

## Getting Started

### Prerequisites
- PHP 7.4 or later
- MySQL or MariaDB
- Apache (XAMPP or WAMP recommended)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/MohamedNH-07/MedibridgeAi.git
   ```
2. **Move the folder** into your web server directory (for XAMPP: `C:\xampp\htdocs\`).
3. **Start Apache and MySQL** from the XAMPP control panel.
4. **Check the database settings** in `database/db.php` (defaults: host `localhost`, user `root`, empty password, database `medibridgeai`). The database and tables are created automatically on first load. You can also import `database/schema.sql` manually.
5. **Open the app** in your browser:
   - Patient site: `http://localhost/MedibridgeAi/main/`
   - Admin setup (first run only): `http://localhost/MedibridgeAi/main/admin/setup.php`
   - Admin login: `http://localhost/MedibridgeAi/main/admin/login.php`
   - Doctor login: `http://localhost/MedibridgeAi/main/doctor/login.php`

### Sample Data

Four sample doctors (General Physician, Cardiologist, Dermatologist, Pediatrician) are added automatically on first run with a default password. **Change these passwords before using the system anywhere other than a local test environment.**

## Appointment Workflow

1. Patient registers, logs in and selects a doctor.
2. Patient submits the booking form and receives a booking code.
3. The appointment is created with the status **Pending confirmation**.
4. The doctor or admin reviews it and updates the status.
5. The patient sees the updated status under "My bookings".

## Limitations and Future Improvements

- The assistant currently uses keyword-based rules. It could be extended with a real AI/LLM API.
- Add email or SMS appointment notifications.
- Add CSRF tokens to all forms.
- Prevent double-booking of the same doctor and time slot.
- Move database credentials into an environment configuration file.

## Author

**Mohamed Neeshan**
HNDIT Student, Sri Lanka Institute of Advanced Technological Education (SLIATE)
GitHub: [MohamedNH-07](https://github.com/MohamedNH-07)
