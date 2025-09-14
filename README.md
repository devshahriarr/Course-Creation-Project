# Course Creator Laravel Project

This is a Laravel-based web application designed for creating and managing online courses, including categories, modules, and contents with support for text, images, videos, and links.

## Project Installation and Setup Process

To set up and run this project locally, follow these detailed steps:

### Prerequisites
- **PHP** (version 8.1 or higher recommended)
- **Composer** (for PHP dependency management)
- **Node.js** and **NPM** (if you plan to customize frontend assets)
- **MySQL** (or another database supported by Laravel)
- **Git** (for cloning the repository)

### Step-by-Step Installation

1. **Clone the Repository**:
   Clone this repository to your local machine using the following command:
   ```bash
   git clone https://github.com/devshahriarr/Course-Creation-Project.git
   cd your-repo-name
2. **Install PHP Dependencies:**:
    Ensure Composer is installed, then run:
    composer install
3. **Configure the Environment:**:
    Copy the example environment file to create your own
    Generate an application key for security
    Open the .env file and update the following settings with your local database credentials:
    
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
4. **Set Up the Database:**:
    Create a new database in your MySQL server (e.g., course_creator)
5. **Link Storage for File Uploads:**:
    php artisan storage:link
6. **Start the Development Server:**:
    php artisan serve

## Troubleshooting:

If you encounter errors, ensure all PHP extensions (e.g., pdo_mysql, mbstring, xml, fileinfo) are enabled in your php.ini file.
Check the .env file for correct database credentials if migrations fail.
Run php artisan config:clear and php artisan cache:clear if configuration issues persist.