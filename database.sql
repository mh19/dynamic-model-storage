
-- Adminer 4.3.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `props` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `items` (`id`, `props`) VALUES
(1,	'{\"age\": 44, \"name\": \"Test1\", \"lastname\": \"Tester\"}'),
(17,	'{\"age\": 15, \"name\": \"Petr\", \"email\": \"a@test.cz\", \"lastname\": \"Holik\"}'),
(21,	'{\"age\": 30, \"name\": \"Jiří\", \"lastname\": \"Votava\"}'),
(23,	'{\"age\": 44, \"name\": \"Jindřich\", \"lastname\": \"Svoboda\"}'),
(46,	'{\"age\": 25, \"name\": \"Marcel\", \"lastname\": \"Haim\"}'),
(47,	'{\"age\": 66, \"name\": \"Vladimír\", \"lastname\": \"Staněk\"}'),
(48,	'{\"age\": 22, \"name\": \"Klára\", \"lastname\": \"Nováková\", \"custom_property\": \"value\"}'),
(51,	'{\"age\": 88, \"name\": \"Ester\", \"lastname\": \"Bárová\"}');

-- 2017-03-22 21:15:44
