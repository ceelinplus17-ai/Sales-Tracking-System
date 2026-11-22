-- Import this in phpMyAdmin (http://localhost/phpmyadmin) > Import
CREATE DATABASE IF NOT EXISTS yarnshop;
USE yarnshop;

CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS sales (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  customer_name  VARCHAR(100)  NOT NULL,
  product        VARCHAR(100)  NOT NULL,
  quantity       INT           NOT NULL,
  unit_price     DECIMAL(10,2) NOT NULL,
  payment_status ENUM('Paid','Pending') NOT NULL DEFAULT 'Paid',
  sale_date      DATE          NOT NULL
);

INSERT INTO sales (customer_name, product, quantity, unit_price, payment_status, sale_date) VALUES
('Lucy van Pelt',   'Chunky Merino Yarn',      3, 420.00, 'Paid',    '2026-11-18'),
('Linus Brown',     'Cotton Yarn Skein',       6, 150.00, 'Paid',    '2026-11-19'),
('Peppermint Patty','Amigurumi Starter Kit',   1, 680.00, 'Pending', '2026-11-20'),
('Sally Brown',     'Crochet Hook Set',        2, 350.00, 'Paid',    '2026-11-21'),
('Charlie Brown',   'Wool Blend Worsted',      4, 260.00, 'Pending', '2026-11-22');
