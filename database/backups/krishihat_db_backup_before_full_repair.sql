-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: krishihat_db
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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'সবজি','sbji',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(2,'শস্য','ssz',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(3,'fol','fol',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(4,'ফল','fl',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(5,'Fruits','fruits',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(6,'Vegetables','vegetables',NULL,NULL,'2026-10-16 22:16:07','2026-10-16 22:16:07'),(7,'ধান্য ও দানা শস্য','grains-cereals',NULL,'🌾','2026-10-20 16:53:20','2026-10-20 16:53:20'),(8,'কন্দ ও আলু জাতীয়','tubers-potatoes',NULL,'🥔','2026-10-20 16:53:20','2026-10-20 16:53:20'),(9,'মসলা জাতীয় ফসল','spices',NULL,'🧅','2026-10-20 16:53:20','2026-10-20 16:53:20'),(10,'ডাল ও তৈলবীজ','pulses-oilseeds',NULL,'🫘','2026-10-20 16:53:20','2026-10-20 16:53:20');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversation_messages`
--

DROP TABLE IF EXISTS `conversation_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversation_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `body` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_messages_conversation_id_foreign` (`conversation_id`),
  KEY `conversation_messages_sender_id_foreign` (`sender_id`),
  CONSTRAINT `conversation_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversation_messages`
--

LOCK TABLES `conversation_messages` WRITE;
/*!40000 ALTER TABLE `conversation_messages` DISABLE KEYS */;
INSERT INTO `conversation_messages` VALUES (1,2,10,'hi',NULL,'2026-10-16 20:23:03','2026-10-16 20:23:03'),(2,2,10,'halo',NULL,'2026-10-16 20:23:50','2026-10-16 20:23:50'),(3,2,10,'aaf',NULL,'2026-10-16 20:23:59','2026-10-16 20:23:59'),(4,2,9,'HELLO BROTHER',NULL,'2026-10-16 20:31:12','2026-10-16 20:31:12'),(5,2,9,'KI KHOBOOOR',NULL,'2026-10-16 20:31:27','2026-10-16 20:31:27'),(6,2,9,'HI',NULL,'2026-10-16 20:31:47','2026-10-16 20:31:47'),(7,2,9,'123456',NULL,'2026-10-16 20:32:25','2026-10-16 20:32:25'),(8,3,11,'HI',NULL,'2026-10-16 20:39:46','2026-10-16 20:39:46'),(9,4,9,'HII FARDAUS',NULL,'2026-10-16 20:44:05','2026-10-16 20:44:05'),(10,4,11,'HI ROHOM ALI',NULL,'2026-10-16 20:44:24','2026-10-16 20:44:24'),(11,4,9,'KEMON ASO',NULL,'2026-10-16 20:44:40','2026-10-16 20:44:40'),(12,4,11,'VALO TMI',NULL,'2026-10-16 20:44:53','2026-10-16 20:44:53'),(13,4,11,'VALO TMI',NULL,'2026-10-16 20:44:53','2026-10-16 20:44:53'),(14,4,11,NULL,'chat-attachments/10jiBYyDIk9f9sHtGjZTxe7Go4Cyu1fXtNt5ATdn.jpg','2026-10-16 20:46:43','2026-10-16 20:46:43'),(15,2,9,'HELLO',NULL,'2026-10-16 20:55:46','2026-10-16 20:55:46'),(16,3,11,'HI',NULL,'2026-10-16 20:56:07','2026-10-16 20:56:07'),(17,3,11,'123456789',NULL,'2026-10-16 20:56:20','2026-10-16 20:56:20'),(18,3,9,'NJKL',NULL,'2026-10-16 20:56:33','2026-10-16 20:56:33'),(19,3,11,'JHKLP',NULL,'2026-10-16 20:56:42','2026-10-16 20:56:42'),(20,3,9,'555555555555555555555555555555555',NULL,'2026-10-16 20:56:53','2026-10-16 20:56:53'),(21,3,9,NULL,'chat-attachments/H1O9S6Q8A9NfOwxD92uPXHAUisCBiI2i9IPlQO2C.jpg','2026-10-16 20:57:02','2026-10-16 20:57:02'),(22,3,11,'KI RE',NULL,'2026-10-16 20:57:25','2026-10-16 20:57:25'),(23,3,9,NULL,'chat-attachments/myidj3aX4KXxurO16cj834epg2tkvx5jTM5ZQbjG.jpg','2026-10-16 21:07:40','2026-10-16 21:07:40'),(24,5,15,'সালাম মোখলেছুর ভাই। ব্রি ধান ২৮-এর আর্দ্রতা কত শতাংশ আছে এবং কালকে কারওয়ান বাজারে ডেলিভারি দিতে পারবেন কি না?',NULL,'2026-10-18 16:54:46','2026-10-20 16:54:46'),(25,5,12,'ওয়ালাইকুমুস সালাম করিম ভাই। মেশিনে মাপা হয়েছে, আর্দ্রতা ১৪% এর নিচে আছে। কাল দুপুরেই ট্রাক ঢাকার উদ্দেশ্যে রওনা দিবে।',NULL,'2026-10-18 17:54:46','2026-10-20 16:54:46');
/*!40000 ALTER TABLE `conversation_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `offer_id` bigint(20) unsigned DEFAULT NULL,
  `context_key` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversations_context_key_unique` (`context_key`),
  KEY `conversations_product_id_foreign` (`product_id`),
  KEY `conversations_offer_id_foreign` (`offer_id`),
  KEY `conversations_buyer_id_updated_at_index` (`buyer_id`,`updated_at`),
  KEY `conversations_farmer_id_updated_at_index` (`farmer_id`,`updated_at`),
  CONSTRAINT `conversations_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`),
  CONSTRAINT `conversations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,8,7,9,NULL,'product:9:buyer:8','2026-10-16 20:08:12','2026-10-16 20:08:12'),(2,10,9,11,NULL,'product:11:buyer:10','2026-10-16 20:22:56','2026-10-16 20:55:46'),(3,11,9,11,NULL,'product:11:buyer:11','2026-10-16 20:39:40','2026-10-16 21:07:40'),(4,11,9,11,2,'offer:2','2026-10-16 20:43:53','2026-10-16 20:46:43'),(5,15,12,12,NULL,'product:12:buyer:15','2026-10-20 16:54:46','2026-10-20 16:54:46');
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_14_081037_create_products_table',1),(5,'2026_10_14_082632_add_product_columns_to_products_table',1),(6,'2026_10_16_055830_add_role_to_users_table',2),(7,'2026_10_16_072430_add_farmer_id_to_products_table',3),(8,'2026_10_16_074531_fix_missing_farmer_id_on_products_table',4),(9,'2026_10_16_083241_change_image_column_to_text_on_products_table',5),(10,'2026_10_16_084418_create_orders_table',6),(11,'2026_10_16_084425_create_order_items_table',6),(12,'2026_10_17_000001_create_offers_table',7),(13,'2026_10_17_000002_create_offer_events_table',7),(14,'2026_10_17_000003_add_workflow_storage',8),(15,'2026_10_17_120000_create_categories_and_link_products',9),(16,'2026_10_21_041500_add_location_fields_to_products_table',10),(17,'2026_10_21_044500_add_tracking_to_orders_and_create_order_tracking_events_table',11);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('06825eec-1656-4a5f-88d1-eab8664486c9','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New price offer for \\u09b2\\u09be\\u0989\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/farmer\\/dashboard#farmer-offers\"}',NULL,'2026-10-16 20:42:23','2026-10-16 20:42:23'),('08724134-e0d2-49f0-9fe7-a41272109711','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:57:25','2026-10-16 20:57:25'),('1abf3c30-c5ac-43ac-ae70-3835ab2f60ea','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:23:59','2026-10-16 20:23:59'),('306cf10d-227b-424d-9fa2-e58c82b84d79','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:56:42','2026-10-16 20:56:42'),('3338b350-8adf-412b-adf9-9a35be8095e7','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}','2026-10-16 23:42:13','2026-10-16 20:56:33','2026-10-16 23:42:13'),('4132fc77-87dd-433e-92db-14abb76f344f','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}',NULL,'2026-10-16 20:44:40','2026-10-16 20:44:40'),('438b5a8e-8cdb-44a1-a8a2-8fc09d575204','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:57:02','2026-10-16 20:57:02'),('4d5f4288-da6b-417a-9d3e-827cf5ee8fcf','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"The buyer responded to your counter-offer for \\u09b2\\u09be\\u0989\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/farmer\\/dashboard#farmer-offers\"}','2026-10-16 20:43:46','2026-10-16 20:43:23','2026-10-16 20:43:46'),('5974ae7b-0eda-4a3b-b53e-3171b5c9181d','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}','2026-10-16 20:56:26','2026-10-16 20:56:07','2026-10-16 20:56:26'),('64667067-4b59-4ea5-ae3c-aa1438230034','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:56:20','2026-10-16 20:56:20'),('650cd312-22e6-4fae-bccd-441b4d51c193','App\\Notifications\\WorkflowNotification','App\\Models\\User',10,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:31:27','2026-10-16 20:31:27'),('74873aaa-50ca-4891-949b-e03a8493131b','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}','2026-10-16 20:44:13','2026-10-16 20:44:05','2026-10-16 20:44:13'),('780ad207-c818-4a53-8ff3-5ac35f358ee9','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:56:53','2026-10-16 20:56:53'),('a696d594-87b1-470d-8bb1-034fcc527eaf','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 20:39:46','2026-10-16 20:39:46'),('ae8cfe5d-7d4a-470a-b72f-38c7ccd59f2f','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New price offer for \\u09b2\\u09be\\u0989\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/farmer\\/dashboard#farmer-offers\"}',NULL,'2026-10-16 20:39:37','2026-10-16 20:39:37'),('b9edb7e8-98f6-44fb-9370-7a8e70fd970f','App\\Notifications\\WorkflowNotification','App\\Models\\User',10,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:55:46','2026-10-16 20:55:46'),('bd3d2a39-9706-47dc-90b2-3f2f50939f72','App\\Notifications\\WorkflowNotification','App\\Models\\User',10,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:31:47','2026-10-16 20:31:47'),('c14401dc-183f-4cd1-b699-3f2bd9b9abab','App\\Notifications\\WorkflowNotification','App\\Models\\User',10,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:31:12','2026-10-16 20:31:12'),('c40348c9-46b6-49d6-9f01-1c8e40757278','App\\Notifications\\WorkflowNotification','App\\Models\\User',10,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}',NULL,'2026-10-16 20:32:25','2026-10-16 20:32:25'),('c5f9f34b-38eb-475f-9fa2-7a5bf16c3cca','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}',NULL,'2026-10-16 20:44:53','2026-10-16 20:44:53'),('d9e990be-6b7a-4d44-9fba-8eeae7fd6aaa','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}',NULL,'2026-10-16 20:44:53','2026-10-16 20:44:53'),('db7222b7-2eae-424c-a788-e33aa427b780','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}',NULL,'2026-10-16 20:46:43','2026-10-16 20:46:43'),('e942348b-e6c1-48a3-babc-8ca75f695b59','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"New message from Rohom Ali\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/3\"}',NULL,'2026-10-16 21:07:40','2026-10-16 21:07:40'),('e99c7110-baac-4444-9211-433462e312e5','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}','2026-10-16 20:43:33','2026-10-16 20:23:50','2026-10-16 20:43:33'),('eacce78e-f0b0-4855-be1c-6d65c461bb31','App\\Notifications\\WorkflowNotification','App\\Models\\User',11,'{\"message\":\"The farmer responded to your offer for \\u09b2\\u09be\\u0989\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/buyer\\/dashboard#my-offers\"}','2026-10-16 20:43:14','2026-10-16 20:42:51','2026-10-16 20:43:14'),('fb48165e-e7cb-4fa3-85fb-c71b5cf21929','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/4\"}',NULL,'2026-10-16 20:44:24','2026-10-16 20:44:24'),('fd2b6322-5001-4460-915a-34b95b3a4184','App\\Notifications\\WorkflowNotification','App\\Models\\User',9,'{\"message\":\"New message from Fardaus Hossain\",\"url\":\"http:\\/\\/127.0.0.1:8090\\/conversations\\/2\"}','2026-10-16 20:31:03','2026-10-16 20:23:03','2026-10-16 20:31:03');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_events`
--

DROP TABLE IF EXISTS `offer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `actor_role` varchar(20) NOT NULL,
  `action` varchar(30) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offer_events_actor_id_foreign` (`actor_id`),
  KEY `offer_events_offer_id_created_at_index` (`offer_id`,`created_at`),
  CONSTRAINT `offer_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `offer_events_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_events`
--

LOCK TABLES `offer_events` WRITE;
/*!40000 ALTER TABLE `offer_events` DISABLE KEYS */;
INSERT INTO `offer_events` VALUES (1,1,8,'buyer','offered',30.00,'2026-10-16 19:46:42','2026-10-16 19:46:42'),(2,2,11,'buyer','offered',40.00,'2026-10-16 20:39:37','2026-10-16 20:39:37'),(3,3,11,'buyer','offered',40.00,'2026-10-16 20:42:23','2026-10-16 20:42:23'),(4,2,9,'farmer','countered',45.00,'2026-10-16 20:42:51','2026-10-16 20:42:51'),(5,2,11,'buyer','counter_accepted',45.00,'2026-10-16 20:43:23','2026-10-16 20:43:23'),(6,4,15,'buyer','created',34.00,'2026-10-17 16:53:20','2026-10-20 16:53:20'),(7,4,12,'farmer','countered',35.50,'2026-10-18 18:53:20','2026-10-20 16:53:20'),(8,4,15,'buyer','accepted',35.00,'2026-10-18 20:53:20','2026-10-20 16:53:20'),(9,5,16,'buyer','created',25.00,'2026-10-19 16:53:20','2026-10-20 16:53:20'),(10,5,13,'farmer','countered',26.50,'2026-10-20 08:53:20','2026-10-20 16:53:20');
/*!40000 ALTER TABLE `offer_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `initial_price` decimal(10,2) NOT NULL,
  `current_price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offers_product_id_status_index` (`product_id`,`status`),
  KEY `offers_buyer_id_created_at_index` (`buyer_id`,`created_at`),
  KEY `offers_farmer_id_created_at_index` (`farmer_id`,`created_at`),
  CONSTRAINT `offers_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offers_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offers_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (1,6,8,2,30.00,30.00,6,'pending','2026-10-16 19:46:42','2026-10-16 19:46:42'),(2,11,11,9,40.00,45.00,1,'accepted','2026-10-16 20:39:37','2026-10-16 20:43:23'),(3,11,11,9,40.00,40.00,1,'pending','2026-10-16 20:42:23','2026-10-16 20:42:23'),(4,12,15,12,34.00,35.00,500,'accepted','2026-10-20 16:53:20','2026-10-20 16:53:20'),(5,13,16,13,25.00,26.50,300,'countered','2026-10-20 16:53:20','2026-10-20 16:53:20');
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `farmer_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_farmer_id_foreign` (`farmer_id`),
  CONSTRAINT `order_items_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,2,1,40.00,40.00,'2026-10-16 03:04:45','2026-10-16 03:04:45'),(2,2,1,2,1,40.00,40.00,'2026-10-16 16:45:24','2026-10-16 16:45:24'),(3,3,12,12,500,35.00,17500.00,'2026-10-20 16:53:20','2026-10-20 16:53:20'),(4,4,13,13,200,28.00,5600.00,'2026-10-20 16:53:20','2026-10-20 16:53:20'),(5,5,14,14,150,65.00,9750.00,'2026-10-20 16:53:20','2026-10-20 16:53:20');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_tracking_events`
--

DROP TABLE IF EXISTS `order_tracking_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_tracking_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_tracking_events_actor_id_foreign` (`actor_id`),
  KEY `order_tracking_events_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_tracking_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_tracking_events_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tracking_events`
--

LOCK TABLES `order_tracking_events` WRITE;
/*!40000 ALTER TABLE `order_tracking_events` DISABLE KEYS */;
INSERT INTO `order_tracking_events` VALUES (1,3,'shipped','অর্ডার গ্রহণ করা হয়েছে (Order Placed)','অর্ডার সফলভাবে ডাটাবেজে রেকর্ড করা হয়েছে।','প্লট ৪৪, কারওয়ান বাজার পাইকারি আড়ত, ঢাকা-১২১৫',NULL,NULL,NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20'),(2,4,'delivered','অর্ডার গ্রহণ করা হয়েছে (Order Placed)','অর্ডার সফলভাবে ডাটাবেজে রেকর্ড করা হয়েছে।','খাতুনগঞ্জ পাইকারি বাজার, চট্টগ্রাম',NULL,NULL,NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20'),(3,5,'confirmed','অর্ডার গ্রহণ করা হয়েছে (Order Placed)','অর্ডার সফলভাবে ডাটাবেজে রেকর্ড করা হয়েছে।','সাতমাথা পাইকারি বাজার, বগুড়া',NULL,NULL,NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20');
/*!40000 ALTER TABLE `order_tracking_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `tracking_code` varchar(50) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `phone` varchar(30) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `driver_phone` varchar(30) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `current_location_text` varchar(255) DEFAULT NULL,
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `estimated_delivery_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `accepted_offer_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_accepted_offer_id_unique` (`accepted_offer_id`),
  UNIQUE KEY `orders_tracking_code_unique` (`tracking_code`),
  KEY `orders_buyer_id_foreign` (`buyer_id`),
  CONSTRAINT `orders_accepted_offer_id_foreign` FOREIGN KEY (`accepted_offer_id`) REFERENCES `offers` (`id`),
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,6,40.00,'pending',NULL,'epz ashulia dhak a','01999530710',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-16 03:04:45','2026-10-16 03:04:45',NULL),(2,1,40.00,'pending',NULL,'sffbvfs','01999530710',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-16 16:45:24','2026-10-16 16:45:24',NULL),(3,15,17500.00,'shipped','KH-TRK-DEMO01','প্লট ৪৪, কারওয়ান বাজার পাইকারি আড়ত, ঢাকা-১২১৫','01700-777888','মো. রফিকুল ইসলাম (Rafiqul Islam)','01800-123456','ঢাকা-মেট্রো-ট-১১-২০৯১','সিরাজগঞ্জ হাইওয়ে যমুনা সেতু পশ্চিম গোলচত্বর',24.39860000,89.75480000,'2026-10-20 22:53:20','2026-10-20 16:53:20','2026-10-20 16:53:20',4),(4,15,5600.00,'delivered','KH-TRK-DEMO02','খাতুনগঞ্জ পাইকারি বাজার, চট্টগ্রাম','01700-777888','মো. কামাল উদ্দিন (Kamal Uddin)','01800-456789','দিনাজপুর-ট-১২-৮৮৭১','খাতুনগঞ্জ পাইকারি আড়ত, চট্টগ্রাম',22.33840000,91.83890000,'2026-10-20 14:53:20','2026-10-20 16:53:20','2026-10-20 16:53:20',NULL),(5,16,9750.00,'confirmed','KH-TRK-DEMO03','সাতমাথা পাইকারি বাজার, বগুড়া','01800-999000',NULL,NULL,NULL,'তাহেরপুর কৃষক সেবামঞ্চ, রাজশাহী',24.41670000,88.83330000,'2026-10-22 16:53:20','2026-10-20 16:53:20','2026-10-20 16:53:20',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_photos`
--

DROP TABLE IF EXISTS `product_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_photos_product_id_position_index` (`product_id`,`position`),
  CONSTRAINT `product_photos_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_photos`
--

LOCK TABLES `product_photos` WRITE;
/*!40000 ALTER TABLE `product_photos` DISABLE KEYS */;
INSERT INTO `product_photos` VALUES (1,11,'products/X2vzIG56vSjkL4aG6SL7ZL6xEzHi6djT0lWJ5YEs.jpg',0,'2026-10-16 20:13:01','2026-10-16 20:13:01');
/*!40000 ALTER TABLE `product_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `upazila` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(255) NOT NULL DEFAULT 'kg',
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `image` text DEFAULT NULL,
  `farmer_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_farmer_id_foreign` (`farmer_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_district_index` (`district`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_farmer_id_foreign` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'2026-10-15 23:23:06','2026-10-16 16:45:24','টাটকা টমেটো','সবজি',1,NULL,NULL,NULL,NULL,'খামার থেকে সংগ্রহ করা টাটকা টমেটো।',40.00,'কেজি',48,NULL,2),(2,'2026-10-15 23:23:06','2026-10-16 01:48:21','আলু','সবজি',1,NULL,NULL,NULL,NULL,'তাজা দেশি আলু।',35.00,'কেজি',100,NULL,2),(3,'2026-10-15 23:23:06','2026-10-16 01:48:21','পেঁয়াজ','সবজি',1,NULL,NULL,NULL,NULL,'দেশি পেঁয়াজ।',60.00,'কেজি',80,NULL,2),(4,'2026-10-15 23:23:06','2026-10-16 01:48:21','সুগন্ধি চাল','শস্য',2,NULL,NULL,NULL,NULL,'দৈনন্দিন রান্নার জন্য চাল।',75.00,'কেজি',100,NULL,2),(5,'2026-10-15 23:23:06','2026-10-16 01:48:21','তাজা বেগুন','সবজি',1,NULL,NULL,NULL,NULL,'তাজা বেগুন, বাজারজাত করার উপযোগী।',45.00,'কেজি',40,NULL,2),(6,'2026-10-15 23:23:06','2026-10-16 01:48:21','কাঁচা মরিচ','সবজি',1,NULL,NULL,NULL,NULL,'তাজা কাঁচা মরিচ।',80.00,'কেজি',30,NULL,2),(7,'2026-10-16 02:32:05','2026-10-16 02:32:05','পেয়ারা','fol',3,NULL,NULL,NULL,NULL,'asd',10.00,'কেজি',12620,NULL,5),(8,'2026-10-16 02:34:58','2026-10-16 02:34:58','পেয়ারা','ফল',4,NULL,NULL,NULL,NULL,'পেয়ারা হলো Myrtaceae পরিবারভুক্ত Psidium guajava প্রজাতির একটি পুষ্টিকর ও জনপ্রিয় ট্রপিক্যাল ফল।\r\n\r\nপুষ্টিগুণ (প্রতি ১০০ গ্রামে)\r\n\r\n• শক্তি: ৬৮–৭৬ কিলোক্যালোরি\r\n• ভিটামিন সি: প্রায় ২২৮.৩ মিলিগ্রাম (কমলার চেয়ে প্রায় ৩–৪ গুণ বেশি)\r\n• শর্করা: ১৪.৩২ গ্রাম\r\n• আঁশ (ফাইবার): ৫.৪ গ্রাম\r\n• পটাশিয়াম: ৪১৭ মিলিগ্রাম\r\n\r\nপ্রধান উপকারিতা\r\n\r\n• রোগ প্রতিরোধ ক্ষমতা: ভিটামিন সি সমৃদ্ধ হওয়ায় এটি রোগ প্রতিরোধ ক্ষমতা বাড়ায় এবং সর্দি-কাশি কমাতে সাহায্য করে।\r\n• হজম ও কোষ্ঠকাঠিন্য: প্রচুর পরিমাণে ডায়েটারি ফাইবার থাকায় এটি হজমশক্তি বাড়ায় এবং কোষ্ঠকাঠিন্য দূর করে।\r\n• ডায়াবেটিস ও রক্তচাপ নিয়ন্ত্রণ: পেয়ারার গ্লাইসেমিক সূচক কম হওয়ায় এটি রক্তে শর্করা এবং উচ্চ রক্তচাপ নিয়ন্ত্রণে সহায়তা করে।',10.00,'কেজি',12620,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRYXHwPvL9sOkCMEk9rFMxjqACy9ZWYlundCnoiVc3COA&s=10',5),(9,'2026-10-16 18:59:57','2026-10-16 18:59:57','কাঁচা পেঁপে','Fruits',5,NULL,NULL,NULL,NULL,'bl',50.00,'kg',15556,'https://www.setarafarm.com/upload/product/1561901708909.jpg',7),(11,'2026-10-16 20:13:01','2026-10-16 20:13:01','লাউ','Vegetables',6,NULL,NULL,NULL,NULL,'Bottle Gourd (Lau) Long – 1 Piece\r\nলাউ (Bottle Gourd) বাংলাদেশের একটি জনপ্রিয় সবজি। এতে রয়েছে ভিটামিন C, ভিটামিন B, পটাশিয়াম, ক্যালসিয়াম ও আঁশ যা হজমশক্তি বৃদ্ধি, ওজন কমানো ও শরীর ঠাণ্ডা রাখতে সহায়ক।\r\n\r\nএকটি দীর্ঘ লাউ (± ১–১.৫ কেজি) তরকারি, ভর্তা, ডাল ও মাছের সঙ্গে রান্নার জন্য আদর্শ।\r\n\r\n✅ উপকারিতা ও ফিচারস:\r\n🔹 ১টি ফ্রেশ লম্বা লাউ\r\n🔹 হজমশক্তি বাড়ায় ও শরীর ঠাণ্ডা রাখে\r\n🔹 কোলেস্টেরল কমাতে সহায়ক\r\n🔹 ওজন নিয়ন্ত্রণে কার্যকর\r\n🔹 ভর্তা, তরকারি, ভাজি, মাছ ও মাংসের সঙ্গে আদর্শ\r\n\r\n✅ Nutritional Info (Per 100g):\r\n\r\nEnergy: 14 kcal\r\n\r\nCarbohydrates: 3.4 g\r\n\r\nFiber: 0.6 g\r\n\r\nVitamin C: 10 mg\r\n\r\nCalcium: 26 mg\r\n\r\nPotassium: 150 mg',50.00,'piece',85,NULL,9),(12,'2026-10-20 16:53:20','2026-10-20 16:56:35','সোনালী ব্রি ধান ২৮ (BRRI Dhan 28 Paddy - Demo)','ধান্য ও দানা শস্য',7,'Bogura','Sherpur',24.67380000,89.41840000,'বগুড়ার শেরপুর খামার থেকে সরাসরি সংগৃহীত ১৪% আর্দ্রতা-পরীক্ষিত সোনালী ব্রি ধান ২৮। পাইকারি চালকল ও বড় আড়তের জন্য আদর্শ।',36.50,'kg',2500,NULL,12),(13,'2026-10-20 16:53:20','2026-10-20 16:56:35','ডায়মন্ড কোল্ড স্টোরেজ আলু (Diamant Potato - Demo)','কন্দ ও আলু জাতীয়',8,'Dinajpur','Birganj',25.85690000,88.65750000,'দিনাজপুরের বীরগঞ্জের আধুনিক হিমাগারে সংরক্ষিত ডায়মন্ড জাতের বাছাইকৃত গোল আলু। সমআকৃতির ও দাগহীন।',28.00,'kg',1800,NULL,13),(14,'2026-10-20 16:53:20','2026-10-20 16:56:35','তাহেরপুরী দেশি পেঁয়াজ (Taherpuri Onion - Demo)','মসলা জাতীয় ফসল',9,'Rajshahi','Taherpur',24.41670000,88.83330000,'রাজশাহীর বিখ্যাত তাহেরপুরী কড়া ঝাঁঝালো শুকনো দেশি পেঁয়াজ। পাতলা খোসা ও দীর্ঘ সংরক্ষণক্ষমতা সম্পন্ন।',65.00,'kg',1200,NULL,14),(15,'2026-10-20 16:53:20','2026-10-20 16:56:35','রাজশাহীর ফজলি আম (Fazli Mango - Demo)','Fruits',5,'Rajshahi','Bagha',24.18330000,88.83330000,'বাঘা অঞ্চলের নিজস্ব বাগান থেকে গাছপাকা সুমিষ্ট ফজলি আম। কোনো ক্ষতিকারক রাসায়নিক বা কার্বাইডমুক্ত।',85.00,'kg',800,NULL,14),(16,'2026-10-20 16:53:20','2026-10-20 16:56:35','রংপুরের খাঁটি সরিষা বীজ (Mustard Seed - Demo)','ডাল ও তৈলবীজ',10,'Rangpur','Mithapukur',25.57860000,89.28280000,'রংপুরের মিঠাপুকুর হতে আহরিত তেল নিষ্কাশনযোগ্য দেশি মাঘী সরিষা। উচ্চ তেল উৎপাদনশীল জাত।',95.00,'kg',600,NULL,12),(17,'2026-10-20 16:53:20','2026-10-20 16:56:35','যশোরের গোল বেগুন (Purple Brinjal - Demo)','Vegetables',6,'Jashore','Sharsha',23.08330000,88.88330000,'যশোরের শার্শা থেকে সংগৃহীত সতেজ ও চকচকে বেগুনি গোল বেগুন। কীটনাশক অবশেষমুক্ত।',35.00,'kg',450,NULL,13);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'buyer',
  `profile_photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Fardaus Hossain','hfarsaus345@gmail.com',NULL,'$2y$12$knX7Iqk02ChUlMJ7U5HKo..Ig.qAuQBFUUGZpq1/qSnwLebhV3/ju',NULL,'2026-10-15 23:41:10','2026-10-15 23:41:10','buyer',NULL),(2,'Fardaus Hossain','hfarsaus34678@gmail.com',NULL,'$2y$12$AzzP2eXHQY.y6A7jYXJ9bOpbxLe1MhWbJYRuWixUxHPXXu/aLfB6W',NULL,'2026-10-15 23:44:11','2026-10-16 01:08:15','farmer',NULL),(3,'Test Buyer','buyer.test@example.com',NULL,'$2y$12$JzVo8YCGzjHKnjKPu5T/BuEy1UZI9.388KpYwh/9brYL.dYytO00m',NULL,'2026-10-16 00:26:02','2026-10-16 00:26:02','buyer',NULL),(4,'Fardaus Hossain','hfarsaus34589@gmail.com',NULL,'$2y$12$4Wv6wBw6wuDelUSHix/.NOE9KGPbllnly6/rtWC64P9JRuF0qmwda',NULL,'2026-10-16 01:17:10','2026-10-16 01:17:10','farmer',NULL),(5,'acaef','afafef@gmail.com',NULL,'$2y$12$10DMoC4c8qV6HCmY9h4uLeEybjqWZLIbGR652jrc86XK0grBGZBEa',NULL,'2026-10-16 02:25:25','2026-10-16 02:25:25','farmer',NULL),(6,'Fardaus Hossain','hfarsaus3455666@gmail.com',NULL,'$2y$12$8cvImNI392U28fFNEpVO0.EaDB0pkWl4xZ/aQANpUC7qTq4pWzULG',NULL,'2026-10-16 02:59:39','2026-10-16 02:59:39','buyer',NULL),(7,'ergerg','hfarsaus345ggggdf@gmail.com',NULL,'$2y$12$VqxX05aoLVlZ1p2qOj5wDeAXq9ZND4cdeNn/l0whlhLkpXkzbRuQK',NULL,'2026-10-16 18:56:08','2026-10-16 18:56:08','farmer',NULL),(8,'Fardaus Hossain','hfarsaus345666@gmail.com',NULL,'$2y$12$2IVTKH.MhgtkTqGjla2Xd.yal4kyoSqG91njXRv3dGX0NFpUOEb6K',NULL,'2026-10-16 19:34:30','2026-10-16 19:34:30','buyer',NULL),(9,'Rohom Ali','hfarsaus3455555@gmail.com',NULL,'$2y$12$FfKxlVZUEPkYWoFtwCGf1.wEnJwkVcx/e5e14IcFiHIbkOtxN3GbK',NULL,'2026-10-16 20:10:17','2026-10-16 20:10:17','farmer',NULL),(10,'Fardaus Hossain','hfarsaus3@gmail.com',NULL,'$2y$12$tdJGk9gMgOcZuiMffChndutw3HGtNrjpa9UhQG8ryH8jqd4bBfiD2',NULL,'2026-10-16 20:22:42','2026-10-16 20:22:42','buyer',NULL),(11,'Fardaus Hossain','hfarsaus34574775875@gmail.com',NULL,'$2y$12$kjT5hGqDON2mW6z1dpPgr.RsBcA4nl7eURm1TCBgf5qr4lf4sjgDC',NULL,'2026-10-16 20:39:21','2026-10-16 20:39:21','buyer',NULL),(12,'মো. মোখলেছুর রহমান (Mokbul Farmer - Demo)','farmer.mokbul@demo.krishihat.test',NULL,'$2y$12$c5zN4Rkgym6ZyrUt0EBXJeZEMa3jKbRl1WLcuSAT.J1cr6WZu9am.',NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20','farmer',NULL),(13,'হাফেজ আব্দুর রহিম (Rahim Farmer - Demo)','farmer.rahim@demo.krishihat.test',NULL,'$2y$12$c5zN4Rkgym6ZyrUt0EBXJeZEMa3jKbRl1WLcuSAT.J1cr6WZu9am.',NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20','farmer',NULL),(14,'মো. সুলেমান আলী (Solaiman Farmer - Demo)','farmer.solaiman@demo.krishihat.test',NULL,'$2y$12$c5zN4Rkgym6ZyrUt0EBXJeZEMa3jKbRl1WLcuSAT.J1cr6WZu9am.',NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20','farmer',NULL),(15,'করিম ট্রেডার্স (Karim Wholesale Buyer - Demo)','buyer.karim@demo.krishihat.test',NULL,'$2y$12$c5zN4Rkgym6ZyrUt0EBXJeZEMa3jKbRl1WLcuSAT.J1cr6WZu9am.',NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20','buyer',NULL),(16,'আড়তদার আনিসুর রহমান (Anisur Agro Buyer - Demo)','buyer.anisur@demo.krishihat.test',NULL,'$2y$12$c5zN4Rkgym6ZyrUt0EBXJeZEMa3jKbRl1WLcuSAT.J1cr6WZu9am.',NULL,'2026-10-20 16:53:20','2026-10-20 16:53:20','buyer',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-21  5:05:15
