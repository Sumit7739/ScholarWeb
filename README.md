# ScholarWeb
---
**ScholarWeb** is a web application designed to simplify the management and access of classroom materials for students. It centralizes class notes, tasks, homework, and other important resources in one place, ensuring students stay organized and up-to-date with their academic responsibilities.

## Features

- **Class Notes**: Access detailed notes for each class, organized by subject and date.
- **Task Management**: Keep track of homework and assignments with due dates and descriptions.
- **Resource Sharing**: Upload and download reference materials and files related to coursework.
- **Notifications**: Receive reminders for upcoming deadlines and new material uploads.
- **User Registration**: Each student has a personalized account to access their notes and tasks.
- **Search Functionality**: Quickly find notes or tasks using keywords or filters.

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Deployment**: Apache server (e.g., XAMPP, WAMP, or web hosting services)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/scholarweb.git
   ```

2. Set up the database:
   - Create a MySQL database named `scholarweb`.
   - Import the provided SQL file (`/database/scholarweb.sql`) into your MySQL database.

3. Configure the database connection:
   - Open `config.php` and update the database credentials:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'your_db_username');
     define('DB_PASSWORD', 'your_db_password');
     define('DB_NAME', 'scholarweb');
     ```

4. Deploy the app on a local server (XAMPP, WAMP, or LAMP):
   - Place the cloned project folder in your server’s root directory (e.g., `htdocs` for XAMPP).
   - Start the Apache and MySQL services.
   - Access the app via your browser at `http://localhost/scholarweb`.

## Contributing

1. Fork the repository.
2. Create a new branch:
   ```bash
   git checkout -b feature-name
   ```
3. Make your changes and commit them:
   ```bash
   git commit -m 'Add some feature'
   ```
4. Push the changes to your branch:
   ```bash
   git push origin feature-name
   ```
5. Open a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
