# Campus Grid: The Central Nervous System of Student Life

🔗 **[Click Here to View the Live Demo](https://campus-grid.kesug.com/?i=2)** 🔗

**Campus Grid** is a premium, web-based campus utility platform designed to digitalize the college experience. It replaces outdated physical notice boards and fragmented WhatsApp groups with a centralized, searchable, and interactive digital ecosystem.

Built by students for students, this platform streamlines information accessibility, resource sharing, and item recovery.

---

## 🚀 Key Features

*   **Digital Notice Board**: A centralized, real-time hub for official campus announcements and academic notices.
*   **Visual Lost & Found**: A searchable database where students can upload images and descriptions of misplaced or recovered items.
*   **Campus Marketplace**: A secure local environment for students to buy and sell textbooks, gear, and other resources.
*   **Code Snippet Hub**: A dedicated section for IT students to share programming solutions and solve bugs together.
*   **Command Node (Admin Panel)**: A full-featured dashboard for administrators to moderate posts, manage user permissions, and track network statistics.
*   **Premium Interactive UI**: Features a dynamic neural-grid background, glassmorphism design, and a responsive masonry feed.

---

## 🛠️ Technology Stack

*   **Frontend**: HTML5, CSS3 (Modern Flexbox/Grid), JavaScript (ES6+).
*   **Backend**: PHP 8.x.
*   **Database**: MySQL.
*   **Animation**: GSAP (GreenSock Animation Platform).
*   **Server Environment**: XAMPP / Apache.

---

## 🔧 Installation & Setup

### 1. Database Setup
*   Create a MySQL database named `campus_grid`.
*   Import the provided SQL schema file to generate the `users` and `posts` tables.

### 2. Configuration
*   Clone the repository to your local `htdocs` or `www` directory.
*   Rename `db.ex.php` to `db.php` and update it with your local credentials:
    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "campus_grid";
    ```

### 3. Permissions
*   Ensure the `uploads/` and `Avtar/` folders have write permissions for image handling.

---

## 📜 Academic Context

Developed as a final year project for the **Bachelor of Computer Applications (BCA)** degree.

*   **Institution**: Bharati Vidyapeeth Deemed University, AKIMSS, Solapur.
*   **Developer**: Varun Salgare.
*   **Academic Year**: 2025-26.

---

## ⚖️ License
This project is for educational purposes. Feel free to use and modify it for your own campus community.