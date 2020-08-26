-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 08, 2013 at 04:06 PM
-- Server version: 5.5.31
-- PHP Version: 5.4.4-14+deb7u3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `steellist`
--

-- --------------------------------------------------------

--
-- Table structure for table `credit_cards`
--

CREATE TABLE IF NOT EXISTS `credit_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_on_card` text CHARACTER SET utf8 NOT NULL,
  `type` text CHARACTER SET utf8 NOT NULL,
  `card_num` text CHARACTER SET utf8 NOT NULL,
  `safe_num` text CHARACTER SET utf8 NOT NULL,
  `exp_mon` int(11) NOT NULL,
  `exp_year` int(11) NOT NULL,
  `sec_code` int(11) NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `credit_cards`
--

INSERT INTO `credit_cards` (`id`, `name_on_card`, `type`, `card_num`, `safe_num`, `exp_mon`, `exp_year`, `sec_code`, `member_id`) VALUES
(7, 'omt', 'American Express', '378282246310005', '********0005', 1, 2013, 123, 31);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE IF NOT EXISTS `inventory` (
  `OutD` int(10) unsigned NOT NULL,
  `InD` int(10) unsigned NOT NULL,
  `WT` int(10) unsigned NOT NULL,
  `Length` int(10) unsigned NOT NULL,
  `Grade` text NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`OutD`, `InD`, `WT`, `Length`, `Grade`, `id`) VALUES
(100, 0, 100, 15000, 'Alloy', 1),
(200, 100, 100, 8000, 'Carbon', 2),
(500, 100, 400, 10000, 'Alloy', 3),
(500, 300, 200, 22000, 'Carbon', 4),
(1000, 600, 400, 8000, 'Alloy', 5),
(500, 200, 300, 1200, 'Stainless', 6);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` text NOT NULL,
  `time` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `user`, `time`) VALUES
(27, 'omt', '1375983299'),
(28, 'omt', '1375986578'),
(29, 'omt', '1375990922'),
(30, 'omt', '1375990928'),
(31, 'omt', '1375990932'),
(32, 'omt', '1375991010'),
(33, 'omt', '1375991827'),
(34, 'omt', '1375992331'),
(35, 'omt', '1375992336'),
(36, 'omt', '1375992407'),
(37, 'omt', '1375992419'),
(38, 'omt', '1375992760');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE IF NOT EXISTS `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `phone` text,
  `last_name` text NOT NULL,
  `first_name` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `company` text NOT NULL,
  `last_update` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `email`, `phone`, `last_name`, `first_name`, `username`, `password`, `company`, `last_update`) VALUES
(31, 'support@steellist.com', '(713)263-4968', 'Smith', 'Zachary', 'omt', '80a55e9a9c89b0d494b55ff20c34dd00618ae6dd', 'Steel List', '1375993258');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE IF NOT EXISTS `purchase_order` (
  `piece_id` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_time` text NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `credit_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `piece_id` (`piece_id`),
  KEY `credit_id` (`credit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD CONSTRAINT `credit_cards_ibfk_3` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_10` FOREIGN KEY (`credit_id`) REFERENCES `credit_cards` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_8` FOREIGN KEY (`customer_id`) REFERENCES `members` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_9` FOREIGN KEY (`piece_id`) REFERENCES `inventory` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
