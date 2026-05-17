# ZETA CREDIT - Loan Management System

ZETA CREDIT is a modern, web-based Loan Management System built with PHP and MySQL. It features a responsive UI, client registration, loan application processing, and an integrated M-Pesa STK Push flow for processing fee payments.

## Features
- **Client Management:** Register and manage client details.
- **Loan Applications:** Apply for different loan products with automated interest calculations.
- **M-Pesa Integration:** Fully integrated Daraja API for automated processing fee payments via STK Push.
- **Admin Dashboard:** (To be implemented) Track loans, approvals, and disbursements.

## Tech Stack
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP 8+, PDO
- Database: MySQL / MariaDB
- APIs: Safaricom Daraja API

## Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone https://github.com/sammy001-svg/Loan-system.git
   cd Loan-system
   ```

2. **Database Configuration:**
   - Import the `database/schema.sql` file into your MySQL database.
   - Update `includes/config.php` with your database credentials.

3. **M-Pesa Configuration:**
   - Open `includes/config.php`.
   - Set your `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, `MPESA_SHORTCODE`, and `MPESA_PASSKEY`.
   - For local STK Push testing, you might need to run Ngrok to expose the `mpesa_callback.php` endpoint to the internet.

4. **Run the Application:**
   - Use XAMPP/WAMP or PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
   - Open your browser and navigate to `http://localhost:8000/index.php`.

## License
MIT License
