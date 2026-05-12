CREATE DATABASE  IF NOT EXISTS `registry_engine` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `registry_engine`;
-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 192.168.3.15    Database: registry_engine
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_warnings`
--

DROP TABLE IF EXISTS `audit_warnings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_warnings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `ipaddress` varchar(45) NOT NULL,
  `geo_id` int unsigned NOT NULL,
  `log` mediumtext NOT NULL,
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `user_obj_idx` (`user_id`),
  KEY `geo_location_idx` (`geo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_ip`
--

DROP TABLE IF EXISTS `geo_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_ip` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(45) NOT NULL,
  `location` varchar(45) NOT NULL,
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `ipaddress_UNIQUE` (`ipaddress`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `iconcaptcha_attempts`
--

DROP TABLE IF EXISTS `iconcaptcha_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iconcaptcha_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varbinary(16) NOT NULL,
  `attempts` int NOT NULL,
  `timeout_until` datetime DEFAULT NULL,
  `valid_until` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iconcaptcha_attempts_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `iconcaptcha_challenges`
--

DROP TABLE IF EXISTS `iconcaptcha_challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iconcaptcha_challenges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `challenge_id` varchar(36) NOT NULL,
  `widget_id` varchar(36) NOT NULL,
  `puzzle` text NOT NULL,
  `ip_address` varbinary(16) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sessions_widget_challenge` (`challenge_id`,`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subscribe_id` int unsigned NOT NULL COMMENT 'reference to the subscription transaction details',
  `user_id` int unsigned NOT NULL COMMENT 'reference to the user table',
  `tax_id` varchar(45) NOT NULL COMMENT 'Taxpayer Identification Number (Tax ID)',
  `vat_special` int NOT NULL DEFAULT '1' COMMENT 'is VAT Special Invoice? zero means VAT Ordinary Invoice',
  `title` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL COMMENT 'e-mail address or mailing address',
  `phone_number` varchar(20) NOT NULL,
  `bank_name` varchar(45) NOT NULL DEFAULT '-',
  `bank_account` varchar(45) NOT NULL DEFAULT '-',
  `add_time` datetime NOT NULL,
  `note` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `user_source_idx` (`user_id`),
  KEY `subscription_package_idx` (`subscribe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_transaction`
--

DROP TABLE IF EXISTS `invoice_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_transaction` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int unsigned NOT NULL,
  `subscription_id` int unsigned NOT NULL,
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `subscription_idx` (`subscription_id`),
  KEY `invoice_id_idx` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Multiple orders can be merged for invoice issuance.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_view`
--

DROP TABLE IF EXISTS `page_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_view` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) NOT NULL,
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `ipaddress` varchar(45) DEFAULT NULL,
  `geo_id` int unsigned NOT NULL,
  `user_agent` varchar(4096) DEFAULT NULL,
  `resource` varchar(45) DEFAULT NULL,
  `identifier` varchar(255) NOT NULL,
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `geo_location_idx` (`geo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=418 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(45) NOT NULL DEFAULT 'general',
  `q` varchar(1024) NOT NULL,
  `hashcode` char(32) NOT NULL,
  `session_id` varchar(45) NOT NULL DEFAULT '-',
  `user_id` int unsigned NOT NULL,
  `ipaddress` varchar(64) NOT NULL,
  `geo` int unsigned NOT NULL DEFAULT '0',
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `user_obj_idx` (`user_id`),
  KEY `hash_index` (`hashcode`),
  KEY `geo_location_idx` (`geo`),
  KEY `top_term_idx` (`q`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_hits`
--

DROP TABLE IF EXISTS `search_hits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_hits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `symbol_id` int unsigned NOT NULL,
  `term` varchar(1024) NOT NULL,
  `hashcode` char(32) NOT NULL,
  `type_id` int unsigned NOT NULL,
  `hits` int unsigned NOT NULL DEFAULT '0',
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `hash_index` (`hashcode`),
  FULLTEXT KEY `search_text` (`term`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subscription`
--

DROP TABLE IF EXISTS `subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `package` varchar(45) NOT NULL,
  `amount` decimal(10,0) unsigned NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `note` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `user_action_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'user name, could be in any chars',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'password_hash result',
  `affiliation` varchar(255) NOT NULL DEFAULT '-' COMMENT 'institution',
  `banned` int unsigned NOT NULL DEFAULT '0' COMMENT 'has been banned from access the database resource?',
  `activated` int NOT NULL DEFAULT '0' COMMENT 'verified of the user email?',
  `activate_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The activation time of the email account is mainly used to calculate the time difference, in order to implement an annual verification and activation mechanism for the account''s email address.',
  `add_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create time of this user account',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `unique_account` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_rate_limits`
--

DROP TABLE IF EXISTS `user_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_rate_limits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(32) NOT NULL,
  `ipaddress` varchar(45) NOT NULL,
  `resource` varchar(255) NOT NULL,
  `visit_time` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `idx_user_resource_time` (`user_hash`,`resource`,`visit_time`)
) ENGINE=InnoDB AUTO_INCREMENT=843 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'registry_engine'
--

--
-- Dumping routines for database 'registry_engine'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-13  4:48:07
