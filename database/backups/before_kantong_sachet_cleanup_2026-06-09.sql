-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kantong_jamu
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
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `worker_fee` int(11) NOT NULL DEFAULT 0,
  `minimum_stock` decimal(12,3) NOT NULL DEFAULT 0.000,
  `warning_stock` decimal(12,3) NOT NULL DEFAULT 20.000,
  `price_per_unit` decimal(12,3) NOT NULL DEFAULT 0.000,
  `selling_price` decimal(12,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (5,'KJ-042','Kantong Yellow Helow','STOCK JADI','pcs',101.000,0,0.000,20.000,1168.200,1553.706,'2026-06-01 10:16:30','2026-06-09 14:13:51'),(6,'KJ-043','Kantong Reddish Wish','STOCK JADI','pcs',301.000,250,0.000,20.000,1196.000,1591.000,'2026-06-01 10:16:30','2026-06-09 14:50:03'),(7,'KJ-044','Kantong Simple Purple','STOCK JADI','pcs',259.000,0,0.000,20.000,997.349,1326.474,'2026-06-01 10:16:30','2026-06-09 14:39:08'),(8,'KJ-045','Sachet Yellow Helow','STOCK JADI','pcs',125.000,0,0.000,20.000,1723.200,2291.856,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(9,'KJ-046','Sachet Reddish Wish','STOCK JADI','pcs',72.000,0,0.000,20.000,1552.349,2064.624,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(10,'KJ-047','Sachet Simple Purple','STOCK JADI','pcs',66.000,0,0.000,20.000,1751.005,2328.836,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(11,'KJ-048','Pouch Yellow Helow','STOCK JADI','pcs',12.000,0,0.000,20.000,18724.800,24903.984,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(12,'KJ-049','Pouch Reddish Wish','STOCK JADI','pcs',12.000,0,0.000,20.000,16332.882,21722.733,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(13,'KJ-050','Pouch Simple Purple','STOCK JADI','pcs',12.000,0,0.000,20.000,19114.068,25421.710,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(14,'KJ-051','Pouch Mix and Match','STOCK JADI','pcs',10.000,0,0.000,20.000,19191.000,25524.030,'2026-06-01 10:16:30','2026-06-09 14:13:58'),(15,'KJ-052','Wedang Uwuh','STOCK JADI','pcs',30.000,0,0.000,20.000,3150.000,4189.500,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(16,'KJ-053','Madu Uray Sachet','STOCK JADI','pcs',10.000,0,0.000,20.000,2125.000,2826.250,'2026-06-01 10:16:30','2026-06-01 10:33:26'),(17,'KJ-054','Box Beauty Journey','STOCK JADI','pcs',9.000,5000,0.000,20.000,13866.000,18442.000,'2026-06-01 10:16:30','2026-06-09 14:48:04'),(18,'KJ-055','Box Healthy Journey','STOCK JADI','pcs',3.000,5000,0.000,20.000,15062.000,20032.000,'2026-06-01 10:16:30','2026-06-09 14:48:06'),(19,'KJ-056','Box Serenity Journey','STOCK JADI','pcs',9.000,5000,0.000,20.000,15257.000,20291.000,'2026-06-01 10:16:30','2026-06-09 14:48:07'),(20,'KJ-057','Box All Day Journey','STOCK JADI','pcs',4.000,5000,0.000,20.000,13053.000,17360.000,'2026-06-01 10:16:30','2026-06-09 14:47:06'),(21,'KJ-058','Hampers','STOCK JADI','pcs',0.000,5000,0.000,20.000,83185.000,110637.000,'2026-06-01 10:16:30','2026-06-09 14:48:09');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_of_materials`
--

DROP TABLE IF EXISTS `bill_of_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_of_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `qty_needed` decimal(12,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_of_materials_product_id_foreign` (`product_id`),
  KEY `bill_of_materials_material_id_foreign` (`material_id`),
  CONSTRAINT `bill_of_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_of_materials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_of_materials`
--

LOCK TABLES `bill_of_materials` WRITE;
/*!40000 ALTER TABLE `bill_of_materials` DISABLE KEYS */;
INSERT INTO `bill_of_materials` VALUES (57,5,14,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(58,8,14,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(59,11,14,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(60,5,41,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(61,8,41,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(62,11,41,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(63,5,35,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(64,8,35,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(65,11,35,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(66,5,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(67,8,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(68,11,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(69,7,31,0.800,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(70,10,31,0.800,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(71,13,31,0.800,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(72,7,38,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(73,10,38,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(74,13,38,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(75,7,41,1.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(76,10,41,1.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(77,13,41,1.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(78,7,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(79,10,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(80,13,36,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(81,7,37,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(82,10,37,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(83,13,37,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(84,7,21,0.250,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(85,10,21,0.250,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(86,13,21,0.250,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(87,6,31,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(88,9,31,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(89,12,31,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(90,6,10,1.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(91,9,10,1.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(92,12,10,1.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(93,6,39,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(94,9,39,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(95,12,39,2.000,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(96,6,40,2.750,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(97,9,40,2.750,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(98,12,40,2.750,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(99,6,36,0.500,'2026-06-01 10:33:26','2026-06-01 10:33:26'),(100,9,36,0.500,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(101,12,36,0.500,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(102,6,33,0.250,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(103,9,33,0.250,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(104,12,33,0.250,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(105,15,12,1.000,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(106,15,16,1.000,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(107,15,41,3.000,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(108,15,17,2.500,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(109,15,38,0.250,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(110,15,19,3.000,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(111,15,14,3.000,'2026-06-01 10:33:27','2026-06-01 10:33:27'),(112,15,13,14.000,'2026-06-01 10:33:27','2026-06-01 10:33:27');
/*!40000 ALTER TABLE `bill_of_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_activities`
--

DROP TABLE IF EXISTS `work_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'pcs',
  `fee_per_unit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `adds_finished_stock` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_activities_product_id_activity_name_unique` (`product_id`,`activity_name`),
  CONSTRAINT `work_activities_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_activities`
--

LOCK TABLES `work_activities` WRITE;
/*!40000 ALTER TABLE `work_activities` DISABLE KEYS */;
INSERT INTO `work_activities` VALUES (1,5,'Packing Kantong','pcs',120.00,1,1,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(2,6,'Packing Kantong','pcs',120.00,1,1,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(3,7,'Packing Kantong','pcs',120.00,1,1,'2026-06-09 04:59:20','2026-06-09 04:59:20');
/*!40000 ALTER TABLE `work_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_activity_materials`
--

DROP TABLE IF EXISTS `work_activity_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_activity_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `work_activity_id` bigint(20) unsigned NOT NULL,
  `material_id` bigint(20) unsigned NOT NULL,
  `qty_needed` decimal(12,3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_activity_materials_work_activity_id_material_id_unique` (`work_activity_id`,`material_id`),
  KEY `work_activity_materials_material_id_foreign` (`material_id`),
  CONSTRAINT `work_activity_materials_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `work_activity_materials_work_activity_id_foreign` FOREIGN KEY (`work_activity_id`) REFERENCES `work_activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_activity_materials`
--

LOCK TABLES `work_activity_materials` WRITE;
/*!40000 ALTER TABLE `work_activity_materials` DISABLE KEYS */;
INSERT INTO `work_activity_materials` VALUES (1,1,45,1.000,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(2,1,59,1.000,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(3,1,44,1.000,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(4,2,45,1.000,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(5,2,60,1.000,'2026-06-09 04:59:19','2026-06-09 04:59:19'),(6,2,42,1.000,'2026-06-09 04:59:20','2026-06-09 04:59:20'),(7,3,45,1.000,'2026-06-09 04:59:20','2026-06-09 04:59:20'),(8,3,61,1.000,'2026-06-09 04:59:20','2026-06-09 04:59:20'),(9,3,43,1.000,'2026-06-09 04:59:20','2026-06-09 04:59:20');
/*!40000 ALTER TABLE `work_activity_materials` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-09 22:11:18
