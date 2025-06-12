-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 02:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flowershop`
--

-- --------------------------------------------------------

--
-- Table structure for table `klient`
--

CREATE TABLE `klient` (
  `Klient_ID` int(11) NOT NULL,
  `Nimi` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `extrainfo` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `klient`
--

INSERT INTO `klient` (`Klient_ID`, `Nimi`, `Email`, `telefon`, `extrainfo`) VALUES
(1, 'Ivan Ivanov', 'ivan@gmail.com', '35551234', 'Постоянный клиент'),
(2, 'Anna Petrova', 'anna@mail.com', '852552345', 'Хочет скидку'),
(3, 'Karl Karlov', 'karl@guru.guru', '554753456', 'Компания OÜ Kask'),
(4, 'Maria Maiorova', 'maria@tthk.ee', '583554567', ''),
(5, 'Daniel Loksa', 'daniel@example.com', '558455678', 'Доставка только по будням'),
(6, 'Test Isman', 'test@test.com', '+3725123456', 'Püsiklient'),
(7, 'Alpha Tamm', 'test2@test.com', '+3725345678', 'Püsiklient'),
(9, 'Deniel Kruusman', 'denielkruusman@example.com', '+3534534534', 'Uus klient');

--
-- Triggers `klient`
--
DELIMITER $$
CREATE TRIGGER `klient_delete` AFTER DELETE ON `klient` FOR EACH ROW BEGIN
  INSERT INTO Logi (Toiming, Andmed)
  VALUES (
    'KUSTUTAMINE',
    CONCAT(
      'Удалён клиент ID=', OLD.Klient_ID,
      ', Имя: ', OLD.Nimi,
      ', Email: ', OLD.Email
    )
  );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `logi_klient_lisamine` AFTER INSERT ON `klient` FOR EACH ROW BEGIN
  INSERT INTO `Logi` (`Toiming`, `Andmed`)
  VALUES (
    'LISAMINE',
    CONCAT('Добавлен клиент: ', NEW.Nimi, ', Email: ', NEW.Email, ', Телефон: ', NEW.telefon)
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `klienttellimused`
-- (See below for the actual view)
--
CREATE TABLE `klienttellimused` (
`Klient_Nimi` varchar(100)
,`Tellimus_ID` int(11)
,`Status` varchar(20)
,`Kuupaev` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `kohaletoimetamine`
--

CREATE TABLE `kohaletoimetamine` (
  `Kohaletoimetamine_ID` int(11) NOT NULL,
  `Tellimuseinfo_ID` int(11) NOT NULL,
  `Aadress` varchar(200) NOT NULL,
  `Kuupaev` date NOT NULL,
  `kellaeg` time NOT NULL,
  `Tootaja_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kohaletoimetamine`
--

INSERT INTO `kohaletoimetamine` (`Kohaletoimetamine_ID`, `Tellimuseinfo_ID`, `Aadress`, `Kuupaev`, `kellaeg`, `Tootaja_ID`) VALUES
(1, 1, 'Pärnu mnt 100', '2025-06-01', '12:00:00', 2),
(2, 2, 'Tartu mnt 55', '2025-06-02', '13:30:00', 4),
(3, 3, 'Narva mnt 33', '2025-06-03', '14:00:00', 2),
(4, 4, 'Viljandi mnt 22', '2025-06-04', '15:30:00', 4),
(5, 5, 'Tallinna mnt 88', '2025-06-05', '16:00:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `ladu`
--

CREATE TABLE `ladu` (
  `Ladu_ID` int(11) NOT NULL,
  `Linn` varchar(50) NOT NULL,
  `Aadress` varchar(100) NOT NULL,
  `Kontaktinfo` varchar(100) DEFAULT NULL,
  `LaduNimi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ladu`
--

INSERT INTO `ladu` (`Ladu_ID`, `Linn`, `Aadress`, `Kontaktinfo`, `LaduNimi`) VALUES
(1, 'Tallinn', 'Tartu mnt 10', 'info@ladu1.ee', 'Ladu1'),
(2, 'Tartu', 'Riia 20', 'info@ladu2.ee', 'Ladu2'),
(3, 'Pärnu', 'Ringi 5', 'info@ladu3.ee', 'Ladu3'),
(4, 'Narva', 'Puškini 3', 'info@ladu4.ee', 'Ladu4'),
(5, 'Viljandi', 'Lossi 8', 'info@ladu5.ee', 'Ladu5');

-- --------------------------------------------------------

--
-- Table structure for table `logi`
--

CREATE TABLE `logi` (
  `LogiID` int(11) NOT NULL,
  `Toiming` varchar(20) NOT NULL,
  `Aeg` datetime DEFAULT current_timestamp(),
  `Kasutaja` varchar(100) DEFAULT user(),
  `Andmed` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logi`
--

INSERT INTO `logi` (`LogiID`, `Toiming`, `Aeg`, `Kasutaja`, `Andmed`) VALUES
(1, 'LISAMINE', '2025-06-04 14:48:50', 'root@localhost', 'Добавлен клиент: Deniel Kruusman, Email: denielkruusman@example.com, Телефон: +3534534534'),
(2, 'LISAMINE', '2025-06-04 15:01:57', 'root@localhost', 'Добавлена позиция в заказ ID=6, Дата заказа: 2025-06-04 14:01:15, Товар ID=4, Кол-во=5, Цена за ед.: 2.50'),
(3, 'MUUTMINE', '2025-06-04 15:06:34', 'root@localhost', 'Изменён товар ID=2, Название: Tulip → Tulip (Holland), Цена: 5.50 → 5.56'),
(4, 'KUSTUTAMINE', '2025-06-04 15:09:17', 'root@localhost', 'Удалён клиент ID=8, Имя: Nicole Betta, Email: test3@test.com');

-- --------------------------------------------------------

--
-- Table structure for table `makse`
--

CREATE TABLE `makse` (
  `Makse_ID` int(11) NOT NULL,
  `Tellimus_ID` int(11) NOT NULL,
  `Summa` decimal(10,2) NOT NULL,
  `Makse_paev` datetime NOT NULL,
  `Makse_tüüp` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `makse`
--

INSERT INTO `makse` (`Makse_ID`, `Tellimus_ID`, `Summa`, `Makse_paev`, `Makse_tüüp`) VALUES
(1, 1, 50.00, '2025-06-01 11:00:00', 'Pangalingi'),
(2, 2, 75.50, '2025-06-02 13:00:00', 'Kaart'),
(3, 3, 40.00, '2025-06-03 10:00:00', 'Sularaha'),
(4, 4, 120.00, '2025-06-04 15:00:00', 'Kaart'),
(5, 5, 30.00, '2025-06-05 16:30:00', 'Pangalingi');

-- --------------------------------------------------------

--
-- Table structure for table `tellimus`
--

CREATE TABLE `tellimus` (
  `Tellimus_ID` int(11) NOT NULL,
  `Klient_ID` int(11) NOT NULL,
  `Status` varchar(20) NOT NULL,
  `Kuupaev` datetime NOT NULL,
  `Tootaja_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tellimus`
--

INSERT INTO `tellimus` (`Tellimus_ID`, `Klient_ID`, `Status`, `Kuupaev`, `Tootaja_ID`) VALUES
(1, 1, '80% tehtud', '2025-06-01 10:00:00', 1),
(2, 2, 'Kinnitatud', '2025-06-02 12:30:00', 2),
(3, 3, 'Töötlemisel', '2025-06-03 09:45:00', 3),
(4, 4, 'Toimetatud', '2025-06-04 14:20:00', 1),
(5, 5, 'Tühistatud', '2025-06-05 16:00:00', 2),
(6, 7, '50%', '2025-06-04 14:01:15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tellimuseinfo`
--

CREATE TABLE `tellimuseinfo` (
  `Tellimuseinfo_ID` int(11) NOT NULL,
  `Tellimus_ID` int(11) NOT NULL,
  `Toode_ID` int(11) NOT NULL,
  `Kogus` int(11) NOT NULL CHECK (`Kogus` > 0),
  `Ühiku_hind` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tellimuseinfo`
--

INSERT INTO `tellimuseinfo` (`Tellimuseinfo_ID`, `Tellimus_ID`, `Toode_ID`, `Kogus`, `Ühiku_hind`) VALUES
(1, 1, 1, 5, 10.00),
(2, 2, 2, 10, 5.50),
(3, 3, 3, 3, 7.25),
(4, 4, 4, 2, 15.75),
(5, 5, 5, 4, 6.00),
(6, 6, 4, 5, 2.50);

--
-- Triggers `tellimuseinfo`
--
DELIMITER $$
CREATE TRIGGER `logi_tellimuseinfo_lisamine` AFTER INSERT ON `tellimuseinfo` FOR EACH ROW BEGIN
  DECLARE tellimusKuupaev DATETIME;

  SELECT Kuupaev INTO tellimusKuupaev
  FROM Tellimus
  WHERE Tellimus_ID = NEW.Tellimus_ID;

  INSERT INTO Logi (Toiming, Andmed)
  VALUES (
    'LISAMINE',
    CONCAT(
      'Добавлена позиция в заказ ID=', NEW.Tellimus_ID,
      ', Дата заказа: ', tellimusKuupaev,
      ', Товар ID=', NEW.Toode_ID,
      ', Кол-во=', NEW.Kogus,
      ', Цена за ед.: ', NEW.Ühiku_hind
    )
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `tellimusetooted`
-- (See below for the actual view)
--
CREATE TABLE `tellimusetooted` (
`Tellimus_ID` int(11)
,`Toode_Nimi` varchar(100)
,`Kogus` int(11)
,`Ühiku_hind` decimal(10,2)
,`Kokku` decimal(20,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `toode`
--

CREATE TABLE `toode` (
  `Toode_ID` int(11) NOT NULL,
  `Nimetus` varchar(100) NOT NULL,
  `Hind` decimal(10,2) NOT NULL,
  `Kirjeldus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toode`
--

INSERT INTO `toode` (`Toode_ID`, `Nimetus`, `Hind`, `Kirjeldus`) VALUES
(1, 'Roos', 10.00, 'Punane roos'),
(2, 'Tulip (Holland)', 5.56, 'Kollane tulp'),
(3, 'Lily', 7.25, 'Valge liilia'),
(4, 'Orchid', 15.75, 'Eksootiline orhidee'),
(5, 'Sunflower', 6.00, 'Suur päevalill');

--
-- Triggers `toode`
--
DELIMITER $$
CREATE TRIGGER `Toode_Update` AFTER UPDATE ON `toode` FOR EACH ROW BEGIN
  INSERT INTO Logi (Toiming, Andmed)
  VALUES (
    'MUUTMINE',
    CONCAT(
      'Изменён товар ID=', NEW.Toode_ID,
      ', Название: ', OLD.Nimetus, ' → ', NEW.Nimetus,
      ', Цена: ', OLD.Hind, ' → ', NEW.Hind
    )
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `toodekategooria`
--

CREATE TABLE `toodekategooria` (
  `Kategooria_ID` int(11) NOT NULL,
  `Nimi` varchar(100) NOT NULL,
  `Kirjeldus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toodekategooria`
--

INSERT INTO `toodekategooria` (`Kategooria_ID`, `Nimi`, `Kirjeldus`) VALUES
(1, 'Lilled', 'Kõik lilled'),
(2, 'Eksootika', 'Eksootilised taimed'),
(3, 'Kevadlilled', 'Kevadhooaja lilled'),
(4, 'Sünnipäevad', 'Sünnipäevaks sobivad'),
(5, 'Romantika', 'Romantilised lilled');

-- --------------------------------------------------------

--
-- Table structure for table `toodeladu`
--

CREATE TABLE `toodeladu` (
  `ToodeLadu_ID` int(11) NOT NULL,
  `Toode_ID` int(11) NOT NULL,
  `Ladu_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toodeladu`
--

INSERT INTO `toodeladu` (`ToodeLadu_ID`, `Toode_ID`, `Ladu_ID`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 1),
(4, 4, 3),
(5, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `toode_kategooria`
--

CREATE TABLE `toode_kategooria` (
  `Toode_ID` int(11) NOT NULL,
  `Kategooria_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toode_kategooria`
--

INSERT INTO `toode_kategooria` (`Toode_ID`, `Kategooria_ID`) VALUES
(1, 1),
(1, 5),
(2, 1),
(3, 1),
(4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `töötaja`
--

CREATE TABLE `töötaja` (
  `Tootaja_ID` int(11) NOT NULL,
  `Nimi` varchar(50) NOT NULL,
  `roll` varchar(50) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `töötaja`
--

INSERT INTO `töötaja` (`Tootaja_ID`, `Nimi`, `roll`, `telefon`) VALUES
(1, 'Liis Lill', 'Florist', '5100011'),
(2, 'Mart Mets', 'Kuller', '5100022'),
(3, 'Kadri Kala', 'Florist', '5100033'),
(4, 'Jaan Jõgi', 'Kuller', '5100044'),
(5, 'Eve Ees', 'Admin', '5100055');

-- --------------------------------------------------------

--
-- Structure for view `klienttellimused`
--
DROP TABLE IF EXISTS `klienttellimused`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `klienttellimused`  AS SELECT `klient`.`Nimi` AS `Klient_Nimi`, `tellimus`.`Tellimus_ID` AS `Tellimus_ID`, `tellimus`.`Status` AS `Status`, `tellimus`.`Kuupaev` AS `Kuupaev` FROM (`klient` join `tellimus` on(`klient`.`Klient_ID` = `tellimus`.`Klient_ID`)) ORDER BY `tellimus`.`Kuupaev` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `tellimusetooted`
--
DROP TABLE IF EXISTS `tellimusetooted`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tellimusetooted`  AS SELECT `tellimuseinfo`.`Tellimus_ID` AS `Tellimus_ID`, `toode`.`Nimetus` AS `Toode_Nimi`, `tellimuseinfo`.`Kogus` AS `Kogus`, `tellimuseinfo`.`Ühiku_hind` AS `Ühiku_hind`, `tellimuseinfo`.`Kogus`* `tellimuseinfo`.`Ühiku_hind` AS `Kokku` FROM (`tellimuseinfo` join `toode` on(`tellimuseinfo`.`Toode_ID` = `toode`.`Toode_ID`)) ORDER BY `tellimuseinfo`.`Tellimus_ID` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klient`
--
ALTER TABLE `klient`
  ADD PRIMARY KEY (`Klient_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `kohaletoimetamine`
--
ALTER TABLE `kohaletoimetamine`
  ADD PRIMARY KEY (`Kohaletoimetamine_ID`),
  ADD KEY `Tellimuseinfo_ID` (`Tellimuseinfo_ID`),
  ADD KEY `Tootaja_ID` (`Tootaja_ID`);

--
-- Indexes for table `ladu`
--
ALTER TABLE `ladu`
  ADD PRIMARY KEY (`Ladu_ID`);

--
-- Indexes for table `logi`
--
ALTER TABLE `logi`
  ADD PRIMARY KEY (`LogiID`);

--
-- Indexes for table `makse`
--
ALTER TABLE `makse`
  ADD PRIMARY KEY (`Makse_ID`),
  ADD KEY `Tellimus_ID` (`Tellimus_ID`);

--
-- Indexes for table `tellimus`
--
ALTER TABLE `tellimus`
  ADD PRIMARY KEY (`Tellimus_ID`),
  ADD KEY `Klient_ID` (`Klient_ID`),
  ADD KEY `Tootaja_ID` (`Tootaja_ID`);

--
-- Indexes for table `tellimuseinfo`
--
ALTER TABLE `tellimuseinfo`
  ADD PRIMARY KEY (`Tellimuseinfo_ID`),
  ADD KEY `Tellimus_ID` (`Tellimus_ID`),
  ADD KEY `Toode_ID` (`Toode_ID`);

--
-- Indexes for table `toode`
--
ALTER TABLE `toode`
  ADD PRIMARY KEY (`Toode_ID`);

--
-- Indexes for table `toodekategooria`
--
ALTER TABLE `toodekategooria`
  ADD PRIMARY KEY (`Kategooria_ID`);

--
-- Indexes for table `toodeladu`
--
ALTER TABLE `toodeladu`
  ADD PRIMARY KEY (`ToodeLadu_ID`),
  ADD KEY `Toode_ID` (`Toode_ID`),
  ADD KEY `Ladu_ID` (`Ladu_ID`);

--
-- Indexes for table `toode_kategooria`
--
ALTER TABLE `toode_kategooria`
  ADD PRIMARY KEY (`Toode_ID`,`Kategooria_ID`),
  ADD KEY `Kategooria_ID` (`Kategooria_ID`);

--
-- Indexes for table `töötaja`
--
ALTER TABLE `töötaja`
  ADD PRIMARY KEY (`Tootaja_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `klient`
--
ALTER TABLE `klient`
  MODIFY `Klient_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kohaletoimetamine`
--
ALTER TABLE `kohaletoimetamine`
  MODIFY `Kohaletoimetamine_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ladu`
--
ALTER TABLE `ladu`
  MODIFY `Ladu_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `logi`
--
ALTER TABLE `logi`
  MODIFY `LogiID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `makse`
--
ALTER TABLE `makse`
  MODIFY `Makse_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tellimus`
--
ALTER TABLE `tellimus`
  MODIFY `Tellimus_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tellimuseinfo`
--
ALTER TABLE `tellimuseinfo`
  MODIFY `Tellimuseinfo_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `toode`
--
ALTER TABLE `toode`
  MODIFY `Toode_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `toodekategooria`
--
ALTER TABLE `toodekategooria`
  MODIFY `Kategooria_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `toodeladu`
--
ALTER TABLE `toodeladu`
  MODIFY `ToodeLadu_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `töötaja`
--
ALTER TABLE `töötaja`
  MODIFY `Tootaja_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kohaletoimetamine`
--
ALTER TABLE `kohaletoimetamine`
  ADD CONSTRAINT `kohaletoimetamine_ibfk_1` FOREIGN KEY (`Tellimuseinfo_ID`) REFERENCES `tellimuseinfo` (`Tellimuseinfo_ID`),
  ADD CONSTRAINT `kohaletoimetamine_ibfk_2` FOREIGN KEY (`Tootaja_ID`) REFERENCES `töötaja` (`Tootaja_ID`);

--
-- Constraints for table `makse`
--
ALTER TABLE `makse`
  ADD CONSTRAINT `makse_ibfk_1` FOREIGN KEY (`Tellimus_ID`) REFERENCES `tellimus` (`Tellimus_ID`);

--
-- Constraints for table `tellimus`
--
ALTER TABLE `tellimus`
  ADD CONSTRAINT `tellimus_ibfk_1` FOREIGN KEY (`Klient_ID`) REFERENCES `klient` (`Klient_ID`),
  ADD CONSTRAINT `tellimus_ibfk_2` FOREIGN KEY (`Tootaja_ID`) REFERENCES `töötaja` (`Tootaja_ID`);

--
-- Constraints for table `tellimuseinfo`
--
ALTER TABLE `tellimuseinfo`
  ADD CONSTRAINT `tellimuseinfo_ibfk_1` FOREIGN KEY (`Tellimus_ID`) REFERENCES `tellimus` (`Tellimus_ID`),
  ADD CONSTRAINT `tellimuseinfo_ibfk_3` FOREIGN KEY (`Toode_ID`) REFERENCES `toode` (`Toode_ID`);

--
-- Constraints for table `toodeladu`
--
ALTER TABLE `toodeladu`
  ADD CONSTRAINT `toodeladu_ibfk_1` FOREIGN KEY (`Toode_ID`) REFERENCES `toode` (`Toode_ID`),
  ADD CONSTRAINT `toodeladu_ibfk_2` FOREIGN KEY (`Ladu_ID`) REFERENCES `ladu` (`Ladu_ID`);

--
-- Constraints for table `toode_kategooria`
--
ALTER TABLE `toode_kategooria`
  ADD CONSTRAINT `toode_kategooria_ibfk_1` FOREIGN KEY (`Toode_ID`) REFERENCES `toode` (`Toode_ID`),
  ADD CONSTRAINT `toode_kategooria_ibfk_2` FOREIGN KEY (`Kategooria_ID`) REFERENCES `toodekategooria` (`Kategooria_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
