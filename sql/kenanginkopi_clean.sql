SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `kenanginkopi`;
USE `kenanginkopi`;

CREATE TABLE coffee (
  CoffeeID char(5) NOT NULL,
  CoffeeName varchar(50) NOT NULL,
  CoffeeDesc varchar(100) NOT NULL
); 

INSERT INTO coffee (`CoffeeID`, `CoffeeName`, `CoffeeDesc`) VALUES
('C0001', 'Latte', 'Coffee with milk'),
('C0002', 'Americano', 'Black coffee'),
('C0003', 'Cappuccino', 'Rich foam coffee');

CREATE TABLE store (
  StoreID char(5) NOT NULL,
  StoreName varchar(50) NOT NULL,
  StoreLocation varchar(100) NOT NULL
) ;

INSERT INTO store (`StoreID`, `StoreName`, `StoreLocation`) VALUES
('S0001', 'Kenangin Coffee - Jakarta', 'Jakarta Selatan'),
('S0002', 'Kenangin Coffee - Bandung', 'Bandung Kota');

CREATE TABLE storecoffee (
  StoreID char(5) NOT NULL,
  CoffeeID char(5) NOT NULL,
  Price decimal(8,2) NOT NULL
);

INSERT INTO `storecoffee` (`StoreID`, `CoffeeID`, `Price`) VALUES
('S0001', 'C0001', 25000.00),
('S0001', 'C0002', 20000.00),
('S0001', 'C0003', 27000.00),
('S0002', 'C0001', 25000.00),
('S0002', 'C0003', 27000.00);

CREATE TABLE transactions (
  TransactionID char(5) NOT NULL,
  UserID char(5) NOT NULL,
  StoreID char(5) NOT NULL,
  TransactionDate date NOT NULL,
  TotalPrice decimal(8,2) NOT NULL
);

CREATE TABLE transactiondetails (
  TransactionID char(5) NOT NULL,
  CoffeeID char(5) NOT NULL,
  Qty int(2) NOT NULL,
  Subtotal decimal(8,2) NOT NULL
) ;

CREATE TABLE users (
  UserID char(5) NOT NULL,
  UserFullName varchar(50) NOT NULL,
  UserName varchar(50) NOT NULL,
  UserEmail varchar(50) NOT NULL,
  UserPassword varchar(100) NOT NULL,
  UserRole char(6) NOT NULL DEFAULT 'User'
) ;

INSERT INTO users (`UserID`, `UserFullName`, `UserName`, `UserEmail`, `UserPassword`, `UserRole`) VALUES
('U0001', 'Rin Amanai', 'Rin', 'rin@gmail.com', '$2y$10$KVpkn6tD5YZsrIJJ1rry0uRgIANkhfz0n/EWOTqb6x3OjiL9E1r3u', 'Admin');

INSERT INTO users (`UserID`, `UserFullName`, `UserName`, `UserEmail`, `UserPassword`, `UserRole`) VALUES
('U0002','jojo','Jojo','Jojo@gmail.com','$2y$12$OkOvZ9rJo6Jy87TRLIg7h.xWMZTZP.qnWUUVHu64uQG313qMVK0pa','User');

INSERT INTO users (`UserID`, `UserFullName`, `UserName`, `UserEmail`, `UserPassword`, `UserRole`) VALUES
('U0003','Adam','Adam','Adam@gmail.com','$2y$12$ppIXdfG/CKyDdFRMnmU9cemHZr5tpYrTa.WsgmrBTfGlZYIej/92O','User');

DELETE FROM transactiondetails;
DELETE FROM transactions;


ALTER TABLE coffee
  ADD PRIMARY KEY (`CoffeeID`);

ALTER TABLE store
  ADD PRIMARY KEY (`StoreID`);

ALTER TABLE storecoffee
  ADD PRIMARY KEY (`StoreID`, `CoffeeID`),
  ADD KEY `CoffeeID` (`CoffeeID`);

ALTER TABLE transactions
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `StoreID` (`StoreID`);

ALTER TABLE transactiondetails
  ADD PRIMARY KEY (`TransactionID`, `CoffeeID`),
  ADD KEY `CoffeeID` (`CoffeeID`);

ALTER TABLE users
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `UserName` (`UserName`),
  ADD UNIQUE KEY `UserEmail` (`UserEmail`);

ALTER TABLE storecoffee
  ADD CONSTRAINT `storecoffee_ibfk_1` FOREIGN KEY (`StoreID`) REFERENCES `store` (`StoreID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `storecoffee_ibfk_2` FOREIGN KEY (`CoffeeID`) REFERENCES `coffee` (`CoffeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE transactions
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`StoreID`) REFERENCES `store` (`StoreID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE transactiondetails
  ADD CONSTRAINT `transactiondetails_ibfk_1` FOREIGN KEY (`TransactionID`) REFERENCES `transactions` (`TransactionID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactiondetails_ibfk_2` FOREIGN KEY (`CoffeeID`) REFERENCES `coffee` (`CoffeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
