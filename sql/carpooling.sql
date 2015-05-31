-- MySQL dump 10.13  Distrib 5.6.24, for osx10.9 (x86_64)
--
-- Host: localhost    Database: tarea3grupo23
-- ------------------------------------------------------
-- Server version	5.6.21

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `tarea3grupo23`
--

/*!40000 DROP DATABASE IF EXISTS `tarea3grupo23`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tarea3grupo23` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `tarea3grupo23`;

--
-- Table structure for table `journey`
--

DROP TABLE IF EXISTS `journey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(32) NOT NULL,
  `route` varchar(255) NOT NULL,
  `time` time(1) NOT NULL,
  `seats` tinyint(4) NOT NULL,
  `creation_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_journey_user_idx` (`user_id`),
  CONSTRAINT `fk_journey_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journey`
--

LOCK TABLES `journey` WRITE;
/*!40000 ALTER TABLE `journey` DISABLE KEYS */;
INSERT INTO `journey` VALUES (1,'user','Beauchef por Bilbao','07:45:00.0',3,'2015-05-14 22:24:18'),(2,'user2','Beauchef por Irarrazaval','07:50:00.0',4,'2015-05-14 22:45:33');
/*!40000 ALTER TABLE `journey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journey_day`
--

DROP TABLE IF EXISTS `journey_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journey_day` (
  `journey_id` int(11) NOT NULL,
  `day` tinyint(4) NOT NULL,
  PRIMARY KEY (`day`,`journey_id`),
  KEY `fk_journey_days_journey1_idx` (`journey_id`),
  CONSTRAINT `fk_journey_days_journey1` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journey_day`
--

LOCK TABLES `journey_day` WRITE;
/*!40000 ALTER TABLE `journey_day` DISABLE KEYS */;
INSERT INTO `journey_day` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(2,1),(2,2),(2,4),(2,5);
/*!40000 ALTER TABLE `journey_day` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journey_music_style`
--

DROP TABLE IF EXISTS `journey_music_style`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journey_music_style` (
  `journey_id` int(11) NOT NULL,
  `music_style_id` varchar(16) NOT NULL,
  PRIMARY KEY (`journey_id`,`music_style_id`),
  KEY `fk_journey_has_music_style_journey1_idx` (`journey_id`),
  CONSTRAINT `fk_journey_has_music_style_journey1` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journey_music_style`
--

LOCK TABLES `journey_music_style` WRITE;
/*!40000 ALTER TABLE `journey_music_style` DISABLE KEYS */;
INSERT INTO `journey_music_style` VALUES (1,'metal'),(1,'rock'),(2,'electronic'),(2,'funk'),(2,'hiphop'),(2,'metal'),(2,'pop'),(2,'rock');
/*!40000 ALTER TABLE `journey_music_style` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journey_passenger`
--

DROP TABLE IF EXISTS `journey_passenger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journey_passenger` (
  `journey_id` int(11) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `join_datetime` datetime NOT NULL,
  PRIMARY KEY (`journey_id`,`user_id`),
  KEY `fk_journey_has_user_user1_idx` (`user_id`),
  KEY `fk_journey_has_user_journey1_idx` (`journey_id`),
  CONSTRAINT `fk_journey_has_user_journey1` FOREIGN KEY (`journey_id`) REFERENCES `journey` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_journey_has_user_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journey_passenger`
--

LOCK TABLES `journey_passenger` WRITE;
/*!40000 ALTER TABLE `journey_passenger` DISABLE KEYS */;
INSERT INTO `journey_passenger` VALUES (1,'user2','2015-05-14 22:45:53'),(2,'user','2015-05-14 22:45:44');
/*!40000 ALTER TABLE `journey_passenger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` varchar(32) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(64) NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `dob` date NOT NULL,
  `sex` char(1) NOT NULL,
  `register_datetime` datetime NOT NULL,
  `avatar` varchar(45) DEFAULT NULL,
  `about` varchar(140) DEFAULT NULL,
  `last_login_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('user','afeebf697ba6fc217e041b1d95c8c8ccd7403ba7','user@user.com','+5691234567890','Juan','Perez','1990-01-01','M','2015-05-14 22:21:12','user.png','Texto de prueba de user','2015-05-14 22:31:50'),('user2','81de223b7791432efbc93f2cf11310e8f912f7f5','user2@user2.com','+5681234567890','Juana','Perez','1989-12-12','F','2015-05-14 22:23:01','user2.png','Texto de prueba de user2','2015-05-14 22:31:40');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-05-14 22:54:55
