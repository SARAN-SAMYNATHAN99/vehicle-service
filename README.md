# 🚗 GearShift - Full-Stack Vehicle Service Booking System
**Web Technology (WT) College Assignment / Mini-Project**

---

## 📌 Project Overview

**GearShift** is a complete, full-stack, responsive Vehicle Service Booking and Fleet Management web application designed specifically for automotive service centers. The system enables vehicle owners (customers) to register their vehicles (cars, motorcycles, SUVs), browse specialized maintenance packages, schedule appointments with preferred time slots, and monitor real-time repair progress.

It also features a separate **Administrator Command Center** allowing workshop managers to supervise bookings, assign ASE-certified mechanics, manage service pricing, track active bay workloads, and view customer fleet records.

Built with **HTML5, CSS3, Vanilla JavaScript, PHP 8+ (PDO), and MySQL**, without external frontend libraries or frameworks (such as React, Angular, Vue, or Bootstrap), adhering strictly to academic Web Technology curriculum standards.

---

## 🚀 Key Features

### 👤 Customer Portal
1. **Automotive Landing Page (`index.html`)**
   - High-performance automotive theme with deep charcoal and racing amber accents.
   - Dynamic hero banner with key trust statistics (15,000+ vehicles, 4.9 rating).
   - Live service packages showcase with direct "Book Now" shortcuts.
   - "Why Choose Us" 4-pillar value proposition and 3-step interactive booking explanation.
   - Contact footer with operating hours, service location, and quick links.

2. **User Authentication & Session Management (`login.html`, `register.html`)**
   - Client-side validation: email regex, phone length (≥ 7 digits), name length, password matching, password length (≥ 6 chars).
   - Server-side validation: uniqueness check on email, prepared statement insertion, BCrypt password hashing (`password_hash`).
   - Session state tracking (`backend/auth/me.php`) with auto-redirects and persistent state across pages.
   - 1-click **Quick Demo Fill buttons** for effortless testing.

3. **Customer Dashboard (`dashboard.html`)**
   - Personalized welcome greeting with authenticated user's name.
   - 4 live metric widgets: Total Vehicles, Upcoming Bookings, Completed Services, and Pending Confirmations.
   - Quick action shortcuts: "+ Add Vehicle", "⚡ Book Service", "📋 View All Bookings".
   - Recent service orders feed with color-coded status badges.

4. **Vehicle Garage Management (`vehicles.html`)**
   - Complete CRUD operations: Add, View, Edit, and Delete vehicles.
   - Vehicle fields: Registration Number, Type (Car, Bike, SUV, Truck, Other), Brand/Make, Model, Year, and Fuel Type (Petrol, Diesel, Electric, Hybrid, CNG).
   - Ownership isolation: Customers can strictly only view/modify their own vehicles.
   - Cascade safety: Database foreign keys automatically maintain data integrity.

5. **Services Catalog (`services.html`)**
   - Real-time client-side live search filter across all service titles and descriptions.
   - 8 standard specialized automotive services:
     - *General Service* ($99.00 | 3 Hours)
     - *Oil Change* ($49.00 | 1 Hour)
     - *Brake Service* ($79.00 | 2 Hours)
     - *Tyre Service* ($39.00 | 1.5 Hours)
     - *AC Service* ($89.00 | 2.5 Hours)
     - *Engine Check* ($120.00 | 3 Hours)
     - *Battery Replacement* ($110.00 | 1 Hour)
     - *Wheel Alignment* ($45.00 | 1.5 Hours)
   - "Book This Service" CTA passing `?service_id=X` directly into the booking page.

6. **Interactive Appointment Booking (`booking.html`)**
   - Dropdown of user's registered vehicles (with an alert and shortcut if no vehicle is registered yet).
   - Service selection with dynamic price and duration calculations.
   - Date picker configured with minimum selectable date set to `today` (disabling past dates).
   - Available time slots (09:00 AM, 10:30 AM, 12:00 PM, 02:00 PM, 03:30 PM, 05:00 PM).
   - Additional customer notes / vehicle symptom descriptions.
   - Live **Order Summary Card** updating dynamically before submission.

7. **Live Booking History & Tracking (`my-bookings.html`)**
   - Comprehensive appointment list showing Booking ID, Vehicle, Package, Schedule, Assigned Mechanic, Price, and Status.
   - Filter by status tabs: `All`, `Pending`, `Confirmed`, `In Service`, `Completed`, `Cancelled`.
   - Real-time search across ID, plate number, and service package.
   - **Interactive Modal Details**: visual status stepper (`Pending` ➔ `Confirmed` ➔ `In Service` ➔ `Completed`), mechanic contact info, and notes.
   - **Cancellation Guard**: Cancellation is permitted **only** when the booking is in `Pending` or `Confirmed` status. In Service or Completed bookings cannot be cancelled.

---

### 🔒 Administrator Control Center (`admin/`)
1. **Admin Login Gateway (`admin/index.html`)**
   - Dedicated admin portal with 1-click default credential autofill.
   - Role-based redirection and server-side authorization validation.

2. **Executive Overview Dashboard (`admin/dashboard.html`)**
   - Real-time workshop KPIs:
     - Total Registered Customers
     - Total Vehicles in Fleet
     - Total Service Bookings
     - Pending Bookings needing review
     - Completed Services
     - Active Service Packages
     - Mechanic Team Size
     - Total Completed Revenue ($ USD)
   - Recent service appointments feed.

3. **Bookings & Dispatch Center (`admin/bookings.html`)**
   - Master list of all customer bookings across the business.
   - Status filtering (`Pending`, `Confirmed`, `In Service`, `Completed`, `Cancelled`).
   - Multi-field search (Customer name, email, phone, vehicle plate, booking ID).
   - In-table **Mechanic Assignment** dropdown: assign or reassign any technician on the fly (auto-promotes status to Confirmed).
   - In-table **Status Transition** dropdown: immediately update booking stage.
   - Full booking details modal.

4. **Service Packages Management (`admin/services.html`)**
   - Add new service packages with title, description, price, and duration.
   - Edit existing service pricing or specifications.
   - Delete obsolete service packages.

5. **Mechanics Team Management (`admin/mechanics.html`)**
   - Add, edit, and delete workshop mechanics.
   - Fields: Full Name, Contact Phone, Specialization, and Bay Availability (`Available`, `Busy`, `On Leave`).

6. **Customer Directory & Fleet Portfolio (`admin/customers.html`)**
   - Searchable customer directory showing contact info, vehicle count, and booking count.
   - **Customer Portfolio Modal**: displays a customer's registered vehicle list alongside their complete service appointment history.

---

## 🛠️ Technology Stack

| Layer | Technologies Used | Description |
|---|---|---|
| **Frontend** | HTML5, CSS3, Vanilla JS | Custom responsive design system, Flexbox, CSS Grid, Modals, Toasts. Zero third-party frameworks. |
| **Backend** | PHP 8+ | REST-style modular endpoints, PDO Prepared Statements, Session Authentication, BCrypt. |
| **Database** | MySQL / MariaDB (XAMPP) | Relational schema with foreign keys, indexes, cascades, and sample data. |
| **Web Server** | Apache (XAMPP) | Works natively in Apache `htdocs` or standard PHP built-in server. |

---

## 📁 Project Folder Structure

```
vehicle/
├── index.html                      # Customer Landing Page
├── login.html                      # Customer / Universal Login Page
├── register.html                   # Customer Registration Page
├── dashboard.html                  # Customer Dashboard & KPI Summary
├── vehicles.html                   # Customer Vehicle Fleet CRUD
├── services.html                   # Services Catalog & Live Search
├── booking.html                    # Appointment Booking Form & Dynamic Summary
├── my-bookings.html                # Booking History, Status Timeline & Cancellation
├── setup.php                       # 1-Click Database Setup & Diagnostics Wizard
├── database.sql                    # Complete Database Schema, Relationships & Seed Data
├── README.md                       # Complete College Assignment Documentation
│
├── css/
│   ├── style.css                   # Core Design System, Variables, Components, Responsive
│   └── dashboard.css               # Customer & Admin Dashboard Layouts & Timelines
│
├── js/
│   ├── api.js                      # Centralized Fetch API Wrapper, Toasts & Modal Engine
│   ├── auth.js                     # Authentication, Route Guards & Session Handlers
│   ├── vehicles.js                 # Customer Vehicle CRUD & Modal Controllers
│   ├── services.js                 # Services Catalog Render & Live Search
│   ├── booking.js                  # Booking Flow, Slot Selection & Dynamic Estimator
│   ├── my-bookings.js              # Booking History, Details Modal & Cancellation Logic
│   ├── admin.js                    # Admin Operations (Stats, Dispatch, Mechanics, Services)
│   └── script.js                   # Navigation Toggle, Global UI & Dynamic Elements
│
├── backend/
│   ├── config/
│   │   ├── database.php            # PDO Database Connection Singleton
│   │   ├── response.php            # Standardized JSON Response & Input Sanitizer
│   │   └── auth_check.php          # Session Guard & Admin/Customer Role Verifiers
│   │
│   ├── auth/
│   │   ├── register.php            # User Registration API (POST)
│   │   ├── login.php               # User Authentication & Role Redirect (POST)
│   │   ├── logout.php              # Session Destruction API (POST)
│   │   └── me.php                  # Active Session Status API (GET)
│   │
│   ├── vehicles/
│   │   ├── list.php                # List Current User's Vehicles (GET)
│   │   ├── get.php                 # Retrieve Single Vehicle (GET)
│   │   ├── add.php                 # Add New Vehicle (POST)
│   │   ├── update.php              # Update Existing Vehicle (POST)
│   │   └── delete.php              # Delete Vehicle (POST)
│   │
│   ├── services/
│   │   ├── list.php                # Public / Customer Services List (GET)
│   │   ├── get.php                 # Get Service Details (GET)
│   │   ├── add.php                 # Admin Add Service (POST)
│   │   ├── update.php              # Admin Update Service (POST)
│   │   └── delete.php              # Admin Delete Service (POST)
│   │
│   ├── mechanics/
│   │   ├── list.php                # List Mechanics / Available (GET)
│   │   ├── get.php                 # Get Mechanic Details (GET)
│   │   ├── add.php                 # Admin Add Mechanic (POST)
│   │   ├── update.php              # Admin Update Mechanic (POST)
│   │   └── delete.php              # Admin Delete Mechanic (POST)
│   │
│   ├── bookings/
│   │   ├── create.php              # Customer Create Booking (POST)
│   │   ├── my_bookings.php         # Customer Booking History with Joins (GET)
│   │   ├── get.php                 # Get Booking Details (GET)
│   │   ├── cancel.php              # Customer Cancel Booking [Pending/Confirmed] (POST)
│   │   ├── list_all.php            # Admin Filtered Booking List (GET)
│   │   ├── update_status.php       # Admin Update Status & Bay (POST)
│   │   └── assign_mechanic.php     # Admin Assign Mechanic (POST)
│   │
│   └── admin/
│       ├── stats.php               # Admin Dashboard Aggregate KPIs & Recent Feed (GET)
│       ├── customers.php           # Admin Customers Directory with Vehicle/Booking Counts (GET)
│       └── customer_details.php    # Admin Customer Portfolio with Garage & History (GET)
│
└── admin/
    ├── index.html                  # Admin Login Portal
    ├── dashboard.html              # Admin Dashboard with Live Statistics
    ├── bookings.html               # Admin Bookings & Mechanic Dispatch Center
    ├── services.html               # Admin Services Catalog Management
    ├── mechanics.html              # Admin Mechanics Team Management
    └── customers.html              # Admin Customer Fleet & Portfolio Directory
```

---

## 💻 Installation & Setup Guide (XAMPP)

Follow these steps to set up and run the project on Windows using XAMPP:

### Step 1: Install & Start XAMPP
1. Download and install **XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/) (ensure PHP 8.0+ is included).
2. Open the **XAMPP Control Panel**.
3. Click **Start** for both **Apache** and **MySQL**. Verify that both modules display a green status.

### Step 2: Place Project in `htdocs`
1. Copy or move the entire `vehicle` project folder into your XAMPP web root:
   ```
   C:\xampp\htdocs\vehicle
   ```
2. Your project path will now be: `C:\xampp\htdocs\vehicle\index.html`.

### Step 3: Initialize the MySQL Database (Two Options)

#### Option A: 1-Click Browser Setup Wizard (Recommended & Easiest)
1. Open your web browser (Chrome, Edge, Firefox).
2. Navigate to:
   ```
   http://localhost/vehicle/setup.php
   ```
3. The setup wizard will check your PHP version and PDO extensions.
4. Click the blue **🚀 Initialize Database Now** button.
5. It will automatically create the database `vehicle_service_db`, create all 5 tables with foreign keys, and seed all sample services, mechanics, admin, and demo customer data!

#### Option B: Manual Import via phpMyAdmin
1. In your browser, open: `http://localhost/phpmyadmin/`.
2. Click **New** in the left sidebar and name the database:
   ```sql
   vehicle_service_db
   ```
   (Collation: `utf8mb4_unicode_ci` or standard `utf8mb4_general_ci`).
3. Select `vehicle_service_db`, click the **Import** tab at the top.
4. Click **Choose File** and select `C:\xampp\htdocs\vehicle\database.sql`.
5. Scroll down and click **Import** (or **Go**).

---

## 🔑 Default Login Credentials

The database comes pre-seeded with ready-to-test accounts:

| Portal | Email Address | Password | Role | Features |
|---|---|---|---|---|
| **Admin Portal** | `admin@gearshift.com` | `Admin@123` | Administrator | Full Workshop Management, Stats, Dispatch, Mechanics, Services |
| **Customer Portal** | `john@example.com` | `Password@123` | Customer | Vehicle Fleet, Book Appointments, Track Live Status |
| **Demo Customer 2** | `emily@example.com` | `Password@123` | Customer | Pre-seeded with Hybrid SUV |
| **Demo Customer 3** | `michael@example.com` | `Password@123` | Customer | Pre-seeded with Tesla EV |

*(Note: On both `login.html` and `admin/index.html`, you can also click the **⚡ Quick Demo Logins** buttons to instantly fill credentials with 1 click!)*

---

## 🌐 How to Run & Test the Application

### 1. Customer Workflow
1. Navigate to `http://localhost/vehicle/index.html`.
2. Click **Login** or navigate to `http://localhost/vehicle/login.html`.
3. Click **👤 Demo Customer** to autofill credentials and click **Sign In**.
4. You will be redirected to the **Customer Dashboard** (`dashboard.html`):
   - Review total registered vehicles and active bookings.
5. Click **My Vehicles** (`vehicles.html`):
   - Add a new vehicle (e.g., Car, Honda Civic, 2022, Petrol).
   - Edit an existing vehicle or test the delete confirmation.
6. Click **Book Service** (`booking.html`):
   - Choose your vehicle from the dropdown.
   - Choose a service package (e.g. *Brake Service* or *General Service*).
   - Observe how the **Estimated Total** and **Duration** dynamically update in the Live Summary Card!
   - Pick an appointment date and time slot.
   - Submit the booking.
7. You are automatically taken to **My Bookings** (`my-bookings.html`):
   - Click **Details** to see the 4-step progress stepper (`Pending` ➔ `Confirmed` ➔ `In Service` ➔ `Completed`).
   - Click **Cancel** on a Pending or Confirmed booking to test cancellation with an optional reason.
8. Click **Logout** from the sidebar or header.

### 2. Admin Workflow
1. Navigate to `http://localhost/vehicle/admin/` (or click *Admin Login* in footer).
2. Click **Fill Default Admin** (`admin@gearshift.com` / `Admin@123`) and submit.
3. You will arrive at the **Admin Command Center** (`admin/dashboard.html`):
   - View live workshop metrics: total customers, vehicles, revenue, pending reviews.
4. Click **Bookings & Dispatch** (`admin/bookings.html`):
   - Filter bookings by `Pending`, `Confirmed`, `In Service`, `Completed`, or `Cancelled`.
   - Use the search bar to find bookings by customer name or vehicle number.
   - Use the **Assign Mechanic** dropdown to assign a technician (e.g., Robert Miller). Notice the status updates to `Confirmed` automatically!
   - Change a booking status to `In Service` or `Completed`.
5. Click **Customers & Fleet** (`admin/customers.html`):
   - Search customers.
   - Click **View Portfolio** to open a modal displaying all vehicles registered to that customer and their full booking history.
6. Click **Service Packages** (`admin/services.html`):
   - Add a new custom service (e.g., "Ceramic Coating Detail" - $199.00 - 4 Hours).
   - Edit or delete packages.
7. Click **Mechanics Team** (`admin/mechanics.html`):
   - Add a new technician with specialization (e.g., "Transmission Specialist") and set availability to `Available`.

---

## 🔒 Security & Code Quality Implementations

1. **Prepared Statements**: All database operations (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) use PDO prepared statements with parameter binding (`:param`), completely eliminating SQL Injection vulnerabilities.
2. **Password Hashing**: Passwords are never stored in plaintext. They are encrypted using PHP's native `password_hash($password, PASSWORD_BCRYPT)` and validated using timing-attack safe `password_verify($password, $hash)`.
3. **Role-Based Access Control (RBAC)**:
   - All administrative endpoints (`backend/admin/*`, `backend/services/add.php`, etc.) check `$_SESSION['user']['role'] === 'admin'`, responding with `403 Forbidden` if unauthorized.
   - Customer endpoints check `$_SESSION['user']['id']`, ensuring users can never read, modify, or delete another user's vehicles or bookings.
4. **XSS Protection**: User input is sanitized using `htmlspecialchars(strip_tags(...), ENT_QUOTES, 'UTF-8')`.
5. **Session Security**: Sensitive fields (such as password hashes) are stripped prior to session storage.
6. **Cancellation Constraints**: Enforced both on the client and server: customers can only cancel bookings with `Pending` or `Confirmed` statuses.

---

## 🎓 College Assignment / Viva Presentation Highlights

If your professor or examiner asks:
- **"Where are the database relationships defined?"**  
  Point them to `database.sql` and the `FOREIGN KEY` constraints linking `vehicles.user_id ➔ users.id`, `bookings.user_id ➔ users.id`, `bookings.vehicle_id ➔ vehicles.id`, `bookings.service_id ➔ services.id`, and `bookings.mechanic_id ➔ mechanics.id`.
- **"How does the frontend communicate with PHP without page reloads?"**  
  Explain `js/api.js` and asynchronous `fetch()` API calls returning REST-style JSON payloads parsed from PHP's `sendResponse()` helper.
- **"How are past appointment dates prevented?"**  
  Show `js/booking.js` where `dateInput.min` is set to `new Date().toISOString().split('T')[0]`, and `backend/bookings/create.php` where `$bookingDate < date('Y-m-d')` is validated server-side.
- **"Why is no frontend framework used?"**  
  Explain that semantic HTML5, pure CSS3 variables and Flexbox/Grid layouts, and modular vanilla JavaScript were used to demonstrate core Web Technology mastery.

---

**Developed for Web Technology (WT) College Academic Evaluation.**
All rights reserved © 2026 GearShift Automotive System.
