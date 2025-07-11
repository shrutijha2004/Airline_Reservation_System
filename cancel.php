error_reporting(E_ALL);
ini_set('display_errors', 1);
<?php
session_start();
include 'db.php';

$message = "";
$flights = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['fetch'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='error'>❌ Please enter a valid email address</div>";
    } else {
        $email = $conn->real_escape_string($email);
        $query = "SELECT B.BookingID, B.FlightID, F.Airline, F.Origin, F.Destination
                  FROM Bookings B
                  JOIN Customers C ON B.CustomerID = C.CustomerID
                  JOIN Flights F ON B.FlightID = F.FlightID
                  WHERE C.Email = '$email' AND B.Status = 'Confirmed'";
        
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $flights[] = $row;
        }

        if (empty($flights)) {
            $message = "<div class='error'>❌ No active bookings found for this email</div>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel'])) {
    $conn->begin_transaction();
    try {
        $email = $conn->real_escape_string(trim($_POST['email']));
        $flightID = (int)$_POST['flight_id'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $flightID <= 0) {
            throw new Exception("Invalid input data");
        }

        // Get and lock booking
        $query = "SELECT B.BookingID 
                  FROM Bookings B
                  JOIN Customers C ON B.CustomerID = C.CustomerID
                  WHERE C.Email = '$email' AND B.FlightID = $flightID AND B.Status = 'Confirmed' FOR UPDATE";
        
        $result = $conn->query($query);
        if ($row = $result->fetch_assoc()) {
            $bookingID = $row['BookingID'];

            // Free the seat
            $conn->query("UPDATE Seats SET IsBooked = FALSE, BookingID = NULL WHERE BookingID = $bookingID");
            
            // Cancel booking
            $conn->query("UPDATE Bookings SET Status = 'Cancelled' WHERE BookingID = $bookingID");
            
            $conn->commit();
            $message = "<div class='success'>✅ Booking canceled successfully! A confirmation has been sent to your email.</div>";
        } else {
            throw new Exception("No active booking found");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cancel Booking</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success { color: green; padding: 10px; background: #e6ffe6; border-radius: 4px; }
        .error { color: red; padding: 10px; background: #ffebeb; border-radius: 4px; }
        select, input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="overlay">
    <header>
        <h1>Airline Reservation System</h1>
        <div class="scrolling-text" data-text="Welcome to Airline Reservation System"></div>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="book.php">Book</a>
        <a href="cancel.php">Cancel</a>
    </nav>

    <main>
        <div class="table-box">
            <h2>Cancel Booking</h2>
            <?= $message ?>

            <form method="POST">
                <label>Email:</label>
                <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>

                <?php if (!isset($_POST['fetch']) || empty($flights)): ?>
                    <input type="submit" name="fetch" value="Find My Bookings">
                <?php endif; ?>

                <?php if (!empty($flights)): ?>
                    <label>Select Booking to Cancel:</label>
                    <select name="flight_id" required>
                        <option value="">-- Select Booking --</option>
                        <?php foreach ($flights as $flight): ?>
                            <option value="<?= $flight['FlightID'] ?>">
                                <?= htmlspecialchars("{$flight['Airline']} - {$flight['Origin']} to {$flight['Destination']}") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
                    <input type="submit" name="cancel" value="Cancel Booking">
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>
</body>
</html>