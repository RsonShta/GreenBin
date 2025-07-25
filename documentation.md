 GreenBin Nepal - Documentation

 1. Project Overview

GreenBin Nepal is a web-based application designed to facilitate waste management and reporting. It allows users to report garbage-related issues, which can then be managed and addressed by administrators. The system includes different roles with specific permissions, ensuring a structured and efficient workflow for waste management.

 2. Features

 2.1 User Features

   User Registration and Login: Users can create an account and log in to the system.
   Submit Reports: Authenticated users can submit reports about garbage, including location details.
   View Reports: Users can view the reports they have submitted.
   Profile Management: Users can view and edit their profile information.
   Password Reset: Users can request a password reset if they forget their password.

 2.2 Admin Features

   Admin Login: Administrators have a separate login panel.
   Dashboard: Admins can view statistics and an overview of the reports on their dashboard.
   Manage Reports: Admins can view, edit, and delete reports submitted by users.
   Update Report Status: Admins can update the status of a report (e.g., "pending," "in progress," "resolved").
   View Report Details: Admins can view detailed information for each report.

 2.3 Super Admin Features

   Super Admin Login: A dedicated login for the super administrator.
   User Management: Super admins can add, delete, and manage users.
   Role Management: Super admins can update the roles of users (e.g., promote a user to an admin).

 3. Technologies Used

   Backend: PHP
   Frontend: HTML, CSS, JavaScript
   Database: MySQL (assumed, as it's commonly used with XAMPP)
   Web Server: Apache (via XAMPP)

 4. Project Structure

The project is organized into the following main directories:

   `backend/`: Contains all the server-side PHP scripts.
       `admin/`: Scripts specific to admin functionalities.
       `classes/`: PHP classes for database connections, user management, etc.
       `includes/`: Reusable PHP files, such as database connection setup and authentication checks.
       `superAdmin/`: Scripts for super admin functionalities.
   `frontend/`: Contains the client-side code.
       `admin/`: JavaScript files for the admin dashboard.
       `dashboard/`: CSS and JavaScript for the user dashboard.
       `home/`: CSS and JavaScript for the home page.
       `img/`: Image assets used in the project.
       `login/`: CSS and JavaScript for the login page.
       `register/`: CSS and JavaScript for the registration page.
       `superAdmin/`: JavaScript files for the super admin panel.
   `pages/`: Contains the main PHP files that render the HTML pages.
       `includes/`: Header and footer files that are included in the pages.
   `uploads/`: Directory for file uploads (e.g., images for reports).

 5. Installation and Setup

To run this project locally, you will need to have XAMPP installed.

 5.1 Prerequisites

   [XAMPP](https://www.apachefriends.org/index.html) (which includes Apache, MySQL, and PHP)

 5.2 Setup Steps

1.  Clone the repository:
    ```bash
    git clone https://github.com/RsonShta/GreenBin.git
    ```
2.  Move the project to XAMPP's `htdocs` directory:
       Place the `GreenBin` folder inside the `htdocs` directory of your XAMPP installation (e.g., `c:/xampp/htdocs/GreenBin`).
3.  Start Apache and MySQL:
       Open the XAMPP Control Panel and start the Apache and MySQL modules.
4.  Create the database:
       Open your web browser and go to `http://localhost/phpmyadmin`.
       Create a new database named `GreenBin_Nepal`.
       Note: You will need to import the database schema. If a `.sql` file is not available, you may need to create the tables manually based on the PHP code.
5.  Configure the database connection:
       Open `backend/classes/Database.php` and ensure the database connection settings are correct for your local environment.
6.  Database Schema:
       Below is the required database schema for the project.

    `users` table:
    ```sql
    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `email_id` varchar(100) NOT NULL,
      `password_hash` varchar(255) NOT NULL,
      `phone_number` varchar(15) NOT NULL,
      `country` varchar(50) DEFAULT 'NP',
      `role` enum('user','admin','superadmin') DEFAULT 'user',
      `profile_photo` varchar(255) DEFAULT 'default.jpg',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `email_id` (`email_id`),
      UNIQUE KEY `phone_number` (`phone_number`)
    );
    ```

    `reports` table:
    ```sql
    CREATE TABLE `reports` (
      `report_id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text NOT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `location` varchar(255) NOT NULL,
      `status` varchar(50) NOT NULL DEFAULT 'pending',
      `date` date NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`report_id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    );
    ```

    `password_resets` table:
    ```sql
    CREATE TABLE `password_resets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(100) NOT NULL,
      `token` varchar(255) NOT NULL,
      `expires` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    );
    ```
7.  Access the application:
       Open your web browser and navigate to `http://localhost/GreenBin`.

 7. API Endpoints

The backend provides several API endpoints to support the frontend functionalities.

 6.1 Authentication

   `POST /backend/login.php`: User login.
   `POST /backend/register.php`: User registration.
   `GET /backend/logout.php`: User logout.
   `POST /backend/requestPasswordReset.php`: Request a password reset.
   `POST /backend/updatePassword.php`: Update user password.

 6.2 Reports

   `POST /backend/reportSubmit.php`: Submit a new report.
   `GET /backend/getReports.php`: Get all reports for the logged-in user.
   `GET /backend/getReport.php`: Get a single report by its ID.
   `POST /backend/editReport.php`: Edit an existing report.
   `POST /backend/deleteReport.php`: Delete a report.

 6.3 Admin

   `POST /backend/admin/login.php`: Admin login.
   `GET /backend/admin/getReports.php`: Get all reports for the admin.
   `GET /backend/admin/getReportDetails.php`: Get details of a specific report.
   `POST /backend/admin/updateReportStatus.php`: Update the status of a report.

 6.4 Super Admin

   `POST /backend/superAdmin/superAdminLogin.php`: Super admin login.
   `POST /backend/superAdmin/addUser.php`: Add a new user.
   `POST /backend/superAdmin/deleteUser.php`: Delete a user.
   `POST /backend/superAdmin/updateRole.php`: Update a user's role.


