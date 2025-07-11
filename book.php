<?php
session_start();
include 'db.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = "";
$availableSeats = [];
$flights = [];
$selectedFlight = null;

// Generate CSRF token
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Success message
if (isset($_GET['success'])) {
    $message = "<div class='success'>✅ Booking successful!</div>";
}

// Fetch all flights
$flightRes = $conn->query("SELECT FlightID, Airline, Origin, Destination FROM Flights");
while ($row = $flightRes->fetch_assoc()) {
    $flights[] = $row;
}

// Fetch available seats for selected flight
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['flight_id']) && !isset($_POST['submit'])) {
    $flightID = (int)$_POST['flight_id'];
    $selectedFlight = $flightID;
    $seatQuery = $conn->query("SELECT SeatNumber FROM Seats WHERE FlightID = $flightID AND IsBooked = FALSE");
    while ($row = $seatQuery->fetch_assoc()) {
        $availableSeats[] = $row['SeatNumber'];
    }
}

// Handle booking
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        die("CSRF token validation failed");
    }

    $conn->begin_transaction();
    try {
        $name = trim($_POST['fullname']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $flightID = (int)$_POST['flight_id'];
        $seatNumber = isset($_POST['seat_number']) ? trim($_POST['seat_number']) : '';

        if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($seatNumber)) {
            throw new Exception("❌ Please fill all fields correctly.");
        }

        // Check for existing booking for this user and flight
        $check = $conn->query("
            SELECT B.BookingID 
            FROM Bookings B
            JOIN Customers C ON B.CustomerID = C.CustomerID
            WHERE C.Email = '".$conn->real_escape_string($email)."' 
            AND B.FlightID = $flightID 
            AND B.Status = 'Confirmed'
        ");
        
        if ($check->num_rows > 0) {
            throw new Exception("❌ You already have a booking for this flight.");
        }

        // Check if seat is still available
        $seatCheck = $conn->query("
            SELECT * FROM Seats 
            WHERE FlightID = $flightID 
            AND SeatNumber = '".$conn->real_escape_string($seatNumber)."' 
            AND IsBooked = FALSE
        ");
        
        if ($seatCheck->num_rows === 0) {
            throw new Exception("❌ Selected seat is no longer available.");
        }

        // Insert or get customer
        $stmt = $conn->prepare("
            INSERT INTO Customers (FullName, Email) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE CustomerID = LAST_INSERT_ID(CustomerID)
        ");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $customerID = $stmt->insert_id;

        // Insert booking
        $conn->query("INSERT INTO Bookings (CustomerID, FlightID) VALUES ($customerID, $flightID)");
        $bookingID = $conn->insert_id;

        // Update seat status
        $conn->query("
            UPDATE Seats 
            SET IsBooked = TRUE, BookingID = $bookingID 
            WHERE FlightID = $flightID 
            AND SeatNumber = '".$conn->real_escape_string($seatNumber)."'
        ");

        $conn->commit();
        header("Location: book.php?success=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='error'>" . htmlspecialchars($e->getMessage()) . "</div>";
        $selectedFlight = $flightID;
        // Re-fetch available seats
        $seatQuery = $conn->query("SELECT SeatNumber FROM Seats WHERE FlightID = $flightID AND IsBooked = FALSE");
        while ($row = $seatQuery->fetch_assoc()) {
            $availableSeats[] = $row['SeatNumber'];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Flight</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success { color: green; margin-bottom: 15px; }
        .error { color: red; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        select, input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: white;
            box-sizing: border-box;
        }
        select {
            appearance: none;
            height: 40px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .seat-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .seat-option {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .seat-option:hover {
            background-color: #f0f0f0;
        }
        .seat-option.selected {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .seat-label {
            margin-bottom: 8px;
            display: block;
        }
    </style>
    <script>
        function selectSeat(seatNumber) {
            // Remove selected class from all seats
            document.querySelectorAll('.seat-option').forEach(seat => {
                seat.classList.remove('selected');
            });
            
            // Add selected class to clicked seat
            event.target.classList.add('selected');
            
            // Update hidden input
            document.querySelector('input[name="seat_number"]').value = seatNumber;
        }
    </script>
</head>
<body>
<div class="overlay">
    <header>
        <h1>Airline Reservation System</h1>
        <div class="scrolling-text" data-text="✈️ Book Your Flight Today! ✈️"></div>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="book.php">Book</a>
        <a href="cancel.php">Cancel</a>
    </nav>

    <main>
        <div class="table-box">
            <h2>Book a Flight</h2>
            <?= $message ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                <input type="hidden" name="seat_number" id="seat_number" value="">

                <div class="form-group">
                    <label for="flight_id">Select Flight:</label>
                    <select name="flight_id" id="flight_id" onchange="this.form.submit()" required>
                        <option value="">-- Select Flight --</option>
                        <?php foreach ($flights as $flight): ?>
                            <option value="<?= $flight['FlightID'] ?>" 
                                <?= (isset($selectedFlight) && $selectedFlight == $flight['FlightID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars("{$flight['Airline']} - {$flight['Origin']} to {$flight['Destination']}") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($availableSeats)): ?>
                    <div class="form-group">
                        <span class="seat-label">Available Seats:</span>
                        <div class="seat-container">
                            <?php foreach ($availableSeats as $seat): ?>
                                <div class="seat-option" onclick="selectSeat('<?= htmlspecialchars($seat) ?>')">
                                    <?= htmlspecialchars($seat) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full Name:</label>
                        <input type="text" name="fullname" id="fullname" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <input type="submit" name="submit" value="Book Flight">
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>
</body>
</html>