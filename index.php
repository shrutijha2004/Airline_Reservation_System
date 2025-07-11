<?php
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Airline Reservation System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-highlight {
            background-color: #e8f5e9;
        }
        .booking-highlight:hover {
            background-color: #c8e6c9;
        }
    </style>
</head>
<body>
<div class="overlay">
    <header>
        <h1>Airline Reservation System</h1>
        <div class="scrolling-text" data-text="✈️ Fly With Us Today!! ✈️"></div>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="book.php">Book</a>
        <a href="cancel.php">Cancel</a>
    </nav>

    <main>
        <h2>Available Flights</h2>
        <?php
        $result = mysqli_query($conn, "
            SELECT F.*, 
                   (F.TotalSeats - COUNT(S.SeatID)) AS SeatsAvailable
            FROM Flights F
            LEFT JOIN Seats S ON F.FlightID = S.FlightID AND S.IsBooked = TRUE
            GROUP BY F.FlightID
            HAVING SeatsAvailable > 0
            ORDER BY F.DepartureTime
        ");

        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-box'><table><tr>
                    <th>Flight ID</th><th>Airline</th><th>From</th><th>To</th>
                    <th>Departure</th><th>Arrival</th><th>Seats Left</th>
                  </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['FlightID']}</td>
                        <td>{$row['Airline']}</td>
                        <td>{$row['Origin']}</td>
                        <td>{$row['Destination']}</td>
                        <td>" . date('M j, Y g:i A', strtotime($row['DepartureTime'])) . "</td>
                        <td>" . date('M j, Y g:i A', strtotime($row['ArrivalTime'])) . "</td>
                        <td>{$row['SeatsAvailable']}</td>
                      </tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p>No flights available.</p>";
        }
        ?>

        <h2>Booking Summary</h2>
        <?php
        $query = "
            SELECT 
                C.FullName, 
                C.Email, 
                F.Airline, 
                F.Origin, 
                F.Destination, 
                F.DepartureTime, 
                B.BookingDate, 
                B.Status, 
                (SELECT SeatNumber FROM Seats WHERE BookingID = B.BookingID LIMIT 1) AS SeatNumber
            FROM Bookings B
            JOIN Customers C ON B.CustomerID = C.CustomerID
            JOIN Flights F ON B.FlightID = F.FlightID
            WHERE B.Status = 'Confirmed'
            ORDER BY B.BookingDate DESC
        ";

        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            echo "<div class='table-box'><table><tr>
                    <th>Name</th><th>Email</th><th>Airline</th><th>From</th><th>To</th>
                    <th>Departure</th><th>Seat</th><th>Status</th><th>Booked On</th>
                  </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr class='booking-highlight'>
                        <td>{$row['FullName']}</td>
                        <td>{$row['Email']}</td>
                        <td>{$row['Airline']}</td>
                        <td>{$row['Origin']}</td>
                        <td>{$row['Destination']}</td>
                        <td>" . date('M j, Y g:i A', strtotime($row['DepartureTime'])) . "</td>
                        <td>{$row['SeatNumber']}</td>
                        <td>{$row['Status']}</td>
                        <td>" . date('M j, Y g:i A', strtotime($row['BookingDate'])) . "</td>
                      </tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p>No bookings found.</p>";
        }
        ?>
    </main>
</div>
</body>
</html>