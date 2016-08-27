-- Adminer 4.2.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `checkouts`;
CREATE TABLE `checkouts` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `invoice` varchar(255) DEFAULT 'UNKNOWN',
  `product` varchar(255) NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL DEFAULT '1',
  `discount` int(11) NOT NULL DEFAULT '0',
  `price` float NOT NULL DEFAULT '0',
  `colour` varchar(255) NOT NULL DEFAULT 'UNKNOWN',
  `size` varchar(255) NOT NULL DEFAULT 'UNKNOWN',
  `weight` float NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `checkouts_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) CHARACTER SET utf8 NOT NULL,
  `shipping` varchar(255) NOT NULL DEFAULT 'selfpickup',
  `billplz` varchar(255) DEFAULT 'UNKNOWN',
  `billing` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `total_amount` int(11) NOT NULL DEFAULT '0',
  `total_price` float NOT NULL DEFAULT '0',
  `total_weight` float NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `collector` varchar(255) DEFAULT 'UNKNOWN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `jwts`;
CREATE TABLE `jwts` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `audience` varchar(255) NOT NULL,
  `os` varchar(255) NOT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `jwts_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `detail` text NOT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `price` float NOT NULL DEFAULT '0',
  `discount` int(11) NOT NULL DEFAULT '0',
  `colour` varchar(255) NOT NULL DEFAULT 'UNKNOWN',
  `size` varchar(255) NOT NULL DEFAULT 'UNKNOWN',
  `weight` float NOT NULL DEFAULT '0',
  `category` varchar(255) NOT NULL DEFAULT '',
  `brand` varchar(255) NOT NULL DEFAULT '',
  `photo_1` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `photo_2` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `photo_3` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `brand` (`brand`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `shippings`;
CREATE TABLE `shippings` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `invoice` varchar(2555) NOT NULL DEFAULT '',
  `recipient` varchar(255) NOT NULL DEFAULT '',
  `first_address` varchar(255) NOT NULL DEFAULT '',
  `second_address` varchar(255) DEFAULT '',
  `poscode` int(11) NOT NULL DEFAULT '0',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(255) NOT NULL DEFAULT '',
  `cost` float NOT NULL DEFAULT '0',
  `serial` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `role` varchar(255) NOT NULL DEFAULT 'MEMBER',
  `status` varchar(255) NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
('2ce3b3b5-6011-4955-b9b2-db50c7c72647',	'arma7x',	'arma7x@live.com',	'$2y$10$4cA8xbR40jO.bhr7OuureuMH1xAPW.n6sLAdzhLGwmfiE0kJ/7muK',	'ADMIN',	'ACTIVE',	'2016-08-02 14:22:31',	'2016-08-22 08:03:13');

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `product` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 2016-08-27 07:44:34
