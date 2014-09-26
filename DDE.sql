-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 11, 2011 at 11:22 AM
-- Server version: 5.0.92
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nebmcupw_LINDEX`
--

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE IF NOT EXISTS `image` (
  `image_id` int(11) NOT NULL auto_increment,
  `project_id` int(11) NOT NULL,
  `caption` text collate utf8_unicode_ci NOT NULL,
  `display_order` tinyint(4) NOT NULL,
  `main` tinyint(4) NOT NULL default '0',
  `ori_file_name` varchar(256) collate utf8_unicode_ci NOT NULL,
  `file_name` varchar(256) collate utf8_unicode_ci NOT NULL,
  `width` tinyint(4) NOT NULL default '0',
  `height` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`image_id`),
  KEY `project_id` (`project_id`),
  FULLTEXT KEY `caption` (`caption`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL auto_increment,
  `title` varchar(256) collate utf8_unicode_ci default NULL,
  `description` text collate utf8_unicode_ci,
  `create_time` datetime NOT NULL,
  `display_order` tinyint(4) default NULL,
  `featured` tinyint(4) NOT NULL default '0',
  `published_on` datetime default NULL,
  PRIMARY KEY  (`project_id`),
  FULLTEXT KEY `title` (`title`,`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=36 ;
