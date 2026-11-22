-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: bd-lenguajesprog
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `bd-lenguajesprog`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `bd-lenguajesprog` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `bd-lenguajesprog`;

--
-- Table structure for table `frameworks`
--

DROP TABLE IF EXISTS `frameworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frameworks` (
  `id_framework` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `id_lenguaje` int(11) NOT NULL,
  PRIMARY KEY (`id_framework`),
  UNIQUE KEY `lenguaje_id` (`id_lenguaje`),
  CONSTRAINT `fk_frameworks_lenguaje` FOREIGN KEY (`id_lenguaje`) REFERENCES `lenguajes` (`id_lenguaje`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `frameworks`
--

LOCK TABLES `frameworks` WRITE;
/*!40000 ALTER TABLE `frameworks` DISABLE KEYS */;
INSERT INTO `frameworks` VALUES (1,'Actix Web','2017-01-01','Backend Web','Framework web de alto rendimiento basado en el modelo de actores.',9);
/*!40000 ALTER TABLE `frameworks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lenguajes`
--

DROP TABLE IF EXISTS `lenguajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lenguajes` (
  `id_lenguaje` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `creador` varchar(150) NOT NULL,
  `tipado` varchar(100) NOT NULL,
  `uso_principal` varchar(200) NOT NULL,
  PRIMARY KEY (`id_lenguaje`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lenguajes`
--

LOCK TABLES `lenguajes` WRITE;
/*!40000 ALTER TABLE `lenguajes` DISABLE KEYS */;
INSERT INTO `lenguajes` VALUES (2,'C#','2000-01-01','Anders Hejlsberg','Estático y fuerte','Aplicaciones de escritorio, servicios web y videojuegos con Unity'),(3,'C++','1985-01-01','Bjarne Stroustrup','Estático y fuerte','Software de alto rendimiento y videojuegos'),(4,'Go','2009-01-01','Robert Griesemer, Rob Pike y Ken Thompson','Estático y fuerte','Sistemas distribuidos y servicios en la nube'),(5,'JavaScript','1995-01-01','Brendan Eich','Dinámico y débil','Desarrollo web frontend y backend'),(6,'Kotlin','2011-01-01','JetBrains','Estático y fuerte','Desarrollo de aplicaciones Android'),(7,'PHP','1995-01-01','Rasmus Lerdorf','Dinámico y débil','Desarrollo web del lado del servidor'),(8,'Ruby','1995-01-01','Yukihiro Matsumoto','Dinámico y fuerte','Desarrollo web con Ruby on Rails'),(9,'Rust','2010-01-01','Graydon Hoare','Estático y fuerte','Sistemas de bajo nivel con seguridad de memoria'),(10,'lumbie','1999-02-02','yahir lopez','finamico','webs');
/*!40000 ALTER TABLE `lenguajes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-22 14:55:36
