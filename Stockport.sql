-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 03:03 PM
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
-- Database: `stockport`
--

-- --------------------------------------------------------

--
-- Table structure for table `billofmaterials`
--

CREATE TABLE `billofmaterials` (
  `BOMID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `MaterialID` int(11) NOT NULL,
  `QuantityRequired` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carriers`
--

CREATE TABLE `carriers` (
  `CarrierID` int(11) NOT NULL,
  `CarrierName` varchar(100) NOT NULL,
  `ContactPerson` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customerorders`
--

CREATE TABLE `customerorders` (
  `CustomerOrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `OrderDate` datetime NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `Status` enum('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `CustomerName` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EmployeeID` int(11) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Role` varchar(45) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `employeeEmail` varchar(100) DEFAULT NULL,
  `employeePassword` varchar(255) NOT NULL,
  `HireDate` date NOT NULL,
  `Status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`EmployeeID`, `FirstName`, `LastName`, `Role`, `Phone`, `employeeEmail`, `employeePassword`, `HireDate`, `Status`) VALUES
(1, 'Kim Jensen', 'Yebes', 'Admin', '09123456789', 'kimjensenyebes@gmail.com', '$2y$10$zgByliOTroett3FUGuvTbeK1mv0ERICQ.67kgsFql.xOXc6af8Cmm', '2025-02-17', 'Active'),
(2, 'Christian Earl', 'Tapit', 'Employee', '09123456789', 'christianearltapit@gmail.com', '$2y$10$lwK9pau8gk4j5L2uOOwpWe3yUM.poSe8KhuTw1My7b5QU0rdgvNyO', '2025-02-25', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `LocationID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 0,
  `LastUpdatedDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderDetailID` int(11) NOT NULL,
  `CustomerOrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productionorders`
--

CREATE TABLE `productionorders` (
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `EmployeeID` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Status` enum('Planned','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  `QuantityOrdered` int(11) NOT NULL,
  `QuantityProduced` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `productionorders`
--

INSERT INTO `productionorders` (`OrderID`, `ProductID`, `EmployeeID`, `StartDate`, `EndDate`, `Status`, `QuantityOrdered`, `QuantityProduced`) VALUES
(1, 1, 1, '2025-02-25 00:00:00', '2025-02-07 00:00:00', 'Completed', 192, 0),
(2, 1, 1, '2025-02-25 00:00:00', '2025-02-06 00:00:00', 'Completed', 1120128, 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(45) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL,
  `ProductionCost` decimal(10,2) DEFAULT NULL,
  `SellingPrice` decimal(10,2) DEFAULT NULL,
  `LocationID` int(11) DEFAULT NULL,
  `MaterialID` int(11) DEFAULT NULL,
  `product_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `Category`, `Weight`, `ProductionCost`, `SellingPrice`, `LocationID`, `MaterialID`, `product_img`) VALUES
(1, 'Food Can', 'Can', 10.00, 10.00, 15.00, 1, 1, 'century_tuna_can.jpg'),
(2, 'Biscuit Tin', 'Tin', 10.00, 20.00, 25.00, 1, 1, 'biscuit_tin.jpg'),
(3, 'Paint can', 'Can', 10.00, 20.00, 25.00, 1, 1, 'paint_can.jpg'),
(4, 'Baking Mold', 'Mold', 30.00, 35.00, 40.00, 1, 1, 'baking_mold.jpg'),
(5, 'Oil Drum', 'Drum', 30.00, 2500.00, 3000.00, 2, 2, 'oil_drum.jpg'),
(6, 'Fuel Tank', 'Tank', 50.00, 1000.00, 1500.00, 2, 2, 'fuel_tank.jpg'),
(7, 'Coin Bank/Safe', 'Safe', 20.00, 700.00, 1000.00, 2, 2, 'coin_bank.jpg'),
(8, 'Beverage can', 'Can', 10.00, 50.00, 100.00, 2, 3, 'beverage_can.jpg'),
(9, 'Food Tray', 'Tray', 50.00, 100.00, 200.00, 2, 3, 'food_tray.jpg'),
(10, 'Aerosol Can', 'Can', 20.00, 100.00, 200.00, 2, 3, 'aerosol_can.jpg'),
(11, 'Storage Bin', 'Bin', 200.00, 5000.00, 10000.00, 1, 4, 'storage_bin.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `products_warehouse`
--

CREATE TABLE `products_warehouse` (
  `productLocationID` int(11) NOT NULL,
  `productWarehouse` varchar(45) NOT NULL,
  `Section` varchar(45) NOT NULL,
  `Capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products_warehouse`
--

INSERT INTO `products_warehouse` (`productLocationID`, `productWarehouse`, `Section`, `Capacity`) VALUES
(1, 'San Pedro City', 'Metal Storage', 10000),
(2, 'Taguig City', 'Metal Storage', 10000);

-- --------------------------------------------------------

--
-- Table structure for table `rawmaterials`
--

CREATE TABLE `rawmaterials` (
  `MaterialID` int(11) NOT NULL,
  `MaterialName` varchar(100) NOT NULL,
  `SupplierID` int(11) DEFAULT NULL,
  `QuantityInStock` int(11) DEFAULT NULL,
  `UnitCost` decimal(10,2) DEFAULT NULL,
  `LastRestockedDate` date DEFAULT NULL,
  `MinimumStock` int(11) DEFAULT NULL,
  `raw_warehouse` varchar(255) NOT NULL,
  `raw_material_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `rawmaterials`
--

INSERT INTO `rawmaterials` (`MaterialID`, `MaterialName`, `SupplierID`, `QuantityInStock`, `UnitCost`, `LastRestockedDate`, `MinimumStock`, `raw_warehouse`, `raw_material_img`) VALUES
(1, 'TinPlate', 1, 4165, 1500.00, '2025-02-18', 5000, 'Paranaque City', 'tinplate.jpg'),
(2, 'Steel', 2, 10000, 1500.00, '2025-02-18', 5000, 'Makati City', 'steel.jpg'),
(3, 'Aluminum', 3, 10000, 1500.00, '2025-02-18', 5000, 'Caloocan City', 'aluminum.jpg'),
(4, 'Stainless Steel', 4, 10000, 1500.00, '2025-02-18', 5000, 'Quezon City', 'stainlesssteel.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `ShipmentID` int(11) NOT NULL,
  `CustomerOrderID` int(11) NOT NULL,
  `CarrierID` int(11) NOT NULL,
  `ShipmentDate` datetime DEFAULT NULL,
  `TrackingNumber` varchar(100) DEFAULT NULL,
  `Status` enum('Pending','In Transit','Delivered','Failed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL,
  `SupplierName` varchar(100) NOT NULL,
  `ContactPerson` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`SupplierID`, `SupplierName`, `ContactPerson`, `Phone`, `Email`, `Address`) VALUES
(1, 'TinPlate Supplier', 'Christian Earl Tapit', '09123456789', 'christianearltapit@gmail.com', 'Block 1 Lot 2 Normal City'),
(2, 'Steel Plate Supplier', 'Kim Jensen Yebes', '09123456789', 'kimjensenyebes@gmail.com', 'Block 1 Lot 2 Normal Street'),
(3, 'Aluminum Supplier', 'Axel Jilian Bumatay', '09123456789', 'axeljilianbumatay@gmail.com', 'Block 1 Lot 2 Normal Steet'),
(4, 'Stainless Steel', 'Aly Sacay', '09123456789', 'alysacay@gmail.com', 'Block 1 Lot 2 Normal Street');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billofmaterials`
--
ALTER TABLE `billofmaterials`
  ADD PRIMARY KEY (`BOMID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `MaterialID` (`MaterialID`);

--
-- Indexes for table `carriers`
--
ALTER TABLE `carriers`
  ADD PRIMARY KEY (`CarrierID`);

--
-- Indexes for table `customerorders`
--
ALTER TABLE `customerorders`
  ADD PRIMARY KEY (`CustomerOrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`InventoryID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderDetailID`),
  ADD KEY `CustomerOrderID` (`CustomerOrderID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `productionorders`
--
ALTER TABLE `productionorders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `fk_product_location` (`LocationID`),
  ADD KEY `fk_product_material` (`MaterialID`);

--
-- Indexes for table `products_warehouse`
--
ALTER TABLE `products_warehouse`
  ADD PRIMARY KEY (`productLocationID`);

--
-- Indexes for table `rawmaterials`
--
ALTER TABLE `rawmaterials`
  ADD PRIMARY KEY (`MaterialID`),
  ADD KEY `idx_supplier` (`SupplierID`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`ShipmentID`),
  ADD KEY `CustomerOrderID` (`CustomerOrderID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billofmaterials`
--
ALTER TABLE `billofmaterials`
  MODIFY `BOMID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carriers`
--
ALTER TABLE `carriers`
  MODIFY `CarrierID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerorders`
--
ALTER TABLE `customerorders`
  MODIFY `CustomerOrderID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `InventoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `OrderDetailID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productionorders`
--
ALTER TABLE `productionorders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products_warehouse`
--
ALTER TABLE `products_warehouse`
  MODIFY `productLocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rawmaterials`
--
ALTER TABLE `rawmaterials`
  MODIFY `MaterialID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `ShipmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billofmaterials`
--
ALTER TABLE `billofmaterials`
  ADD CONSTRAINT `billofmaterials_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`),
  ADD CONSTRAINT `billofmaterials_ibfk_2` FOREIGN KEY (`MaterialID`) REFERENCES `rawmaterials` (`MaterialID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_location` FOREIGN KEY (`LocationID`) REFERENCES `products_warehouse` (`productLocationID`),
  ADD CONSTRAINT `fk_product_material` FOREIGN KEY (`MaterialID`) REFERENCES `rawmaterials` (`MaterialID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
