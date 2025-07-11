-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 10, 2025 at 07:26 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `airline_DB`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `availableflights`
-- (See below for the actual view)
--
CREATE TABLE `availableflights` (
`Airline` varchar(50)
,`ArrivalTime` datetime
,`DepartureTime` datetime
,`Destination` varchar(50)
,`FlightID` int
,`Origin` varchar(50)
,`SeatsAvailable` bigint
);

-- --------------------------------------------------------

--
-- Table structure for table `Bookings`
--

CREATE TABLE `Bookings` (
  `BookingID` int NOT NULL,
  `CustomerID` int DEFAULT NULL,
  `FlightID` int DEFAULT NULL,
  `BookingDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Status` enum('Confirmed','Cancelled') DEFAULT 'Confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Bookings`
--

INSERT INTO `Bookings` (`BookingID`, `CustomerID`, `FlightID`, `BookingDate`, `Status`) VALUES
(1, 1, 1, '2025-07-11 00:17:08', 'Confirmed'),
(2, 2, 1, '2025-07-11 00:17:08', 'Confirmed'),
(3, 3, 2, '2025-07-11 00:17:08', 'Confirmed'),
(4, 4, 3, '2025-07-11 00:17:08', 'Confirmed'),
(5, 5, 4, '2025-07-11 00:17:08', 'Confirmed'),
(6, 6, 5, '2025-07-11 00:17:08', 'Confirmed'),
(7, 7, 3, '2025-07-11 00:17:38', 'Cancelled');

--
-- Triggers `Bookings`
--
DELIMITER $$
CREATE TRIGGER `AfterBookingCancel` AFTER UPDATE ON `Bookings` FOR EACH ROW BEGIN
    IF NEW.Status = 'Cancelled' AND OLD.Status = 'Confirmed' THEN
        UPDATE Seats
        SET IsBooked = FALSE, BookingID = NULL
        WHERE BookingID = NEW.BookingID;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `BeforeBookingInsert` BEFORE INSERT ON `Bookings` FOR EACH ROW BEGIN
    DECLARE available_seats INT;
    DECLARE total_seats INT;
    
    -- Check available seats
    SELECT COUNT(*) INTO available_seats
    FROM Seats
    WHERE FlightID = NEW.FlightID AND IsBooked = FALSE;
    
    -- Get total seats for the flight
    SELECT TotalSeats INTO total_seats
    FROM Flights
    WHERE FlightID = NEW.FlightID;
    
    -- If no seats available, prevent the booking
    IF available_seats = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No available seats for this flight';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Customers`
--

CREATE TABLE `Customers` (
  `CustomerID` int NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `PassportNo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Customers`
--

INSERT INTO `Customers` (`CustomerID`, `FullName`, `Email`, `Phone`, `PassportNo`) VALUES
(1, 'Shrushti Sharma', 'shrushti@gmail.com', '9876543210', 'P123456'),
(2, 'Rahul Mehta', 'rahul@yahoo.com', '9123456789', 'P654321'),
(3, 'Neha Verma', 'neha@onmicrosoft.com', '9811122233', 'P345678'),
(4, 'Vikram Desai', 'vikram@gmail.com', '9898989898', 'P876543'),
(5, 'Anjali Gupta', 'anjali@gmail.com', '9000012345', 'P567890'),
(6, 'Siddharth Rao', 'sidrao@yahoo.com', '9834567890', 'P111111'),
(7, 'Sid', 'Sidfun@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Flights`
--

CREATE TABLE `Flights` (
  `FlightID` int NOT NULL,
  `Airline` varchar(50) DEFAULT NULL,
  `Origin` varchar(50) DEFAULT NULL,
  `Destination` varchar(50) DEFAULT NULL,
  `DepartureTime` datetime DEFAULT NULL,
  `ArrivalTime` datetime DEFAULT NULL,
  `TotalSeats` int DEFAULT NULL
) ;

--
-- Dumping data for table `Flights`
--

INSERT INTO `Flights` (`FlightID`, `Airline`, `Origin`, `Destination`, `DepartureTime`, `ArrivalTime`, `TotalSeats`) VALUES
(1, 'IndiGo', 'Delhi', 'Mumbai', '2025-07-15 10:00:00', '2025-07-15 12:00:00', 5),
(2, 'Air India', 'Delhi', 'Bangalore', '2025-07-16 09:00:00', '2025-07-16 11:30:00', 4),
(3, 'SpiceJet', 'Mumbai', 'Chennai', '2025-07-17 14:00:00', '2025-07-17 16:15:00', 3),
(4, 'Vistara', 'Delhi', 'Kolkata', '2025-07-18 08:00:00', '2025-07-18 10:45:00', 4),
(5, 'AirAsia', 'Hyderabad', 'Delhi', '2025-07-19 07:30:00', '2025-07-19 09:30:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Seats`
--

CREATE TABLE `Seats` (
  `SeatID` int NOT NULL,
  `FlightID` int NOT NULL,
  `SeatNumber` varchar(5) NOT NULL,
  `IsBooked` tinyint(1) DEFAULT '0',
  `BookingID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Seats`
--

INSERT INTO `Seats` (`SeatID`, `FlightID`, `SeatNumber`, `IsBooked`, `BookingID`) VALUES
(1, 1, '1A', 1, 1),
(2, 1, '1B', 1, 2),
(3, 1, '1C', 0, NULL),
(4, 1, '2A', 0, NULL),
(5, 1, '2B', 0, NULL),
(6, 1, '3A', 0, NULL),
(7, 1, '3B', 0, NULL),
(8, 2, '1A', 1, 3),
(9, 2, '1B', 0, NULL),
(10, 2, '2A', 0, NULL),
(11, 2, '2B', 0, NULL),
(12, 3, '1A', 1, 4),
(13, 3, '1B', 0, NULL),
(14, 3, '1C', 0, NULL),
(15, 4, '1A', 1, 5),
(16, 4, '1B', 0, NULL),
(17, 4, '2A', 0, NULL),
(18, 4, '2B', 0, NULL),
(19, 5, '1A', 1, 6),
(20, 5, '1B', 0, NULL);

-- --------------------------------------------------------

--
-- Structure for view `availableflights`
--
DROP TABLE IF EXISTS `availableflights`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `availableflights`  AS SELECT `F`.`FlightID` AS `FlightID`, `F`.`Airline` AS `Airline`, `F`.`Origin` AS `Origin`, `F`.`Destination` AS `Destination`, `F`.`DepartureTime` AS `DepartureTime`, `F`.`ArrivalTime` AS `ArrivalTime`, (`F`.`TotalSeats` - count(`S`.`SeatID`)) AS `SeatsAvailable` FROM (`flights` `F` left join `seats` `S` on(((`F`.`FlightID` = `S`.`FlightID`) and (`S`.`IsBooked` = true)))) GROUP BY `F`.`FlightID` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Bookings`
--
ALTER TABLE `Bookings`
  ADD PRIMARY KEY (`BookingID`),
  ADD UNIQUE KEY `unique_booking` (`CustomerID`,`FlightID`,`Status`),
  ADD KEY `FlightID` (`FlightID`);

--
-- Indexes for table `Customers`
--
ALTER TABLE `Customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `PassportNo` (`PassportNo`);

--
-- Indexes for table `Flights`
--
ALTER TABLE `Flights`
  ADD PRIMARY KEY (`FlightID`);

--
-- Indexes for table `Seats`
--
ALTER TABLE `Seats`
  ADD PRIMARY KEY (`SeatID`),
  ADD UNIQUE KEY `unique_seat` (`FlightID`,`SeatNumber`),
  ADD KEY `BookingID` (`BookingID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Bookings`
--
ALTER TABLE `Bookings`
  MODIFY `BookingID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Customers`
--
ALTER TABLE `Customers`
  MODIFY `CustomerID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Flights`
--
ALTER TABLE `Flights`
  MODIFY `FlightID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Seats`
--
ALTER TABLE `Seats`
  MODIFY `SeatID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Bookings`
--
ALTER TABLE `Bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `Customers` (`CustomerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`FlightID`) REFERENCES `Flights` (`FlightID`) ON DELETE CASCADE;

--
-- Constraints for table `Seats`
--
ALTER TABLE `Seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`FlightID`) REFERENCES `Flights` (`FlightID`) ON DELETE CASCADE,
  ADD CONSTRAINT `seats_ibfk_2` FOREIGN KEY (`BookingID`) REFERENCES `Bookings` (`BookingID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
