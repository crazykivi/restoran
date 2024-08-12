﻿--
-- Script was generated by Devart dbForge Studio 2022 for MySQL, Version 9.1.21.0
-- Product home page: http://www.devart.com/dbforge/mysql/studio
-- Script date 14.05.2024 0:15:56
-- Server version: 10.4.28
-- Client version: 4.1
--

-- 
-- Disable foreign keys
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Set SQL mode
-- 
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 
-- Set character set the client will use to send SQL statements to the server
--
SET NAMES 'utf8';

--
-- Set default database
--
USE restoran;

--
-- Drop table `feedbacks`
--
DROP TABLE IF EXISTS feedbacks;

--
-- Drop table `users`
--
DROP TABLE IF EXISTS users;

--
-- Drop event `event`
--
DROP EVENT IF EXISTS event;

--
-- Drop table `reservations`
--
DROP TABLE IF EXISTS reservations;

--
-- Set default database
--
USE restoran;

--
-- Create table `reservations`
--
CREATE TABLE reservations (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  phone varchar(14) NOT NULL,
  date datetime NOT NULL,
  guests int(11) NOT NULL,
  result varchar(255) NOT NULL DEFAULT 'Оставлена заявка',
  position varchar(255) NOT NULL DEFAULT 'Активна',
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 6,
AVG_ROW_LENGTH = 8192,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

DELIMITER $$

--
-- Create event `event`
--
CREATE 
	DEFINER = 'root'@'localhost'
EVENT event
	ON SCHEDULE EVERY '1' DAY
	STARTS '2024-05-10 22:15:58'
	DO 
BEGIN
  UPDATE reservations
  SET position = 'Не активно'
  WHERE date < CURDATE() AND position = 'Активна';
END
$$

ALTER 
	DEFINER = 'root'@'localhost'
EVENT event
	ENABLE
$$

DELIMITER ;

--
-- Create table `users`
--
CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 2,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

--
-- Create index `username` on table `users`
--
ALTER TABLE users
ADD UNIQUE INDEX username (username);

--
-- Create table `feedbacks`
--
CREATE TABLE feedbacks (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  rating int(2) NOT NULL,
  comment text NOT NULL,
  time_feedback timestamp NOT NULL DEFAULT current_timestamp,
  status varchar(50) NOT NULL DEFAULT 'На рассмотрении',
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 3,
AVG_ROW_LENGTH = 16384,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_general_ci;

-- 
-- Dumping data for table users
--
INSERT INTO users VALUES
(1, 'test-user', '$2y$10$HbKNotuDfi92eMOwb9FfL.7AmrndkoixO60ay2GZGguGM2TYEhEci', '2024-05-10 19:32:44');

-- 
-- Dumping data for table reservations
--
INSERT INTO reservations VALUES
(1, 'Никита', '+79999999999', '2024-05-10 17:47:00', 3, 'Не ответил', 'Не активно'),
(2, 'Никита', '+79999999999', '2024-05-24 17:18:00', 6, 'Оставлена заявка', 'Активна'),
(3, 'Василий', '+74213144124', '2024-05-12 18:30:00', 10, 'Отмена', 'Активна'),
(4, 'Василий', '+79999999999', '2024-05-12 22:30:00', 6, 'Оставлена заявка', 'Активна'),
(5, 'Никита', '+79994949494', '2024-05-10 00:25:00', 3, 'Оставлена заявка', 'Не активно');

-- 
-- Dumping data for table feedbacks
--
INSERT INTO feedbacks VALUES
(1, 'Николая П.', 5, 'Посетив ресторан "Симфония вкусов", я ощутил настоящий вкус Севера. Каждое блюдо было настоящим произведением искусства, которое не только радовало взгляд, но и баловало вкусовые рецепторы. Чувствуется, что шеф-повар вкладывает душу в свои творения, используя только свежие и качественные ингредиенты.\r\n\r\nИнтерьер ресторана полностью соответствует его концепции: элементы декора и дизайна наполнены характером и атмосферой холодного, но удивительно прекрасного Севера. Персонал был вежлив и внимателен, обслуживание оставило только приятные впечатления.\r\n\r\nОсобенно запомнилось блюдо под названием "Северное сияние" — палитра вкусов, представленная на тарелке, перенесла меня в мир северной магии и красоты. Это место идеально подходит как для романтического ужина, так и для деловых встреч.\r\n\r\nС уверенностью могу рекомендовать "Симфонию вкусов" каждому, кто хочет прикоснуться к северной кухне и испытать незабываемые впечатления.', '2024-05-13 20:47:59', 'Одобрено'),
(2, 'Василий', 4, 'В', '2024-05-13 21:14:04', 'Одобрено');

-- 
-- Restore previous SQL mode
-- 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

-- 
-- Enable foreign keys
-- 
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;