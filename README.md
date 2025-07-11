# ✈️ Airline Reservation System

A fully functional Airline Reservation System built with **PHP**, **MySQL**, **HTML**, and **CSS**.  
Made with a lot of debugging, midnight frustration, and eventual excitement! 😵‍💫 → 🎉

---

## 🚀 Features

- 🔍 **Flight Listings** with real-time available seats  
- ✅ **Booking System** with email validation (no more double bookings 😤)  
- ❌ **Cancellations** with dropdowns for ease  
- 🖥️ **Clean UI** with a scrolling marquee and airplane vibe  
- 💾 **Database-backed** seat and flight management  
- 🛡️ Secure with **CSRF token** and email checks

---

## 💻 Tech Stack

- **Frontend:** HTML, CSS  
- **Backend:** PHP  
- **Database:** MySQL  
- **Server:** MAMP (for local development)

---

## 📂 Folder Structure

Airline-reservation-system/

├── index.php      (Flight listing & summary)

├── book.php       (Flight booking logic)

├── cancel.php     (Cancel bookings)

├── db.php         (DB connection (update credentials!))

├── style.css      (Styling)

├── airline_db.sql (Database export file)

├── README.md

---


## 🛠️ Setup Instructions

1. **Clone this repository.**

2. **Import the database**

 -Open phpMyAdmin

 -Create a new database named airline_db

 -Import the file airline_db.sql

3. **Configure database**

 -In db.php, update your database credentials if needed.

4. **Run Locally**

 -Place the folder inside your MAMP/XAMPP htdocs directory.

 -Start Apache & MySQL.

 -Open your browser and visit:
   http://localhost/(your-folder-name)/

# 😅 What I Learned (the hard way)

 -> SQL constraints are very real and will humble you

 -> Preventing duplicate bookings needs actual logic, not vibes

 -> "Undefined index: seat_number" at midnight = character development

 -> Booking once is hard, preventing twice is harder 😂

# 📸 Screenshots

<img width="1424" height="656" alt="Screenshot 2025-07-11 at 11 55 57 AM" src="https://github.com/user-attachments/assets/d7825868-68d8-40a5-aa0b-1fa476c7ec15" />
<img width="1424" height="656" alt="Screenshot 2025-07-11 at 11 56 09 AM" src="https://github.com/user-attachments/assets/f479432f-5b14-4769-b40c-aed3b1c48de1" />

<img width="1424" height="656" alt="Screenshot 2025-07-11 at 11 56 21 AM" src="https://github.com/user-attachments/assets/1a4649c5-7aaf-48ad-9949-352a8d590e09" />

<img width="1424" height="656" alt="Screenshot 2025-07-11 at 11 57 04 AM" src="https://github.com/user-attachments/assets/4f1be50d-5a21-4fa6-8f8f-ed4f62c914ff" />




