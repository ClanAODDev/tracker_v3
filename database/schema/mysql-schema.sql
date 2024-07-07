/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `action_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `action_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` char(36) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `actionable_type` varchar(255) NOT NULL,
  `actionable_id` int(10) unsigned NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` int(10) unsigned DEFAULT NULL,
  `fields` text NOT NULL,
  `status` varchar(25) NOT NULL DEFAULT 'running',
  `exception` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `original` text DEFAULT NULL,
  `changes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `action_events_actionable_type_actionable_id_index` (`actionable_type`(191),`actionable_id`),
  KEY `action_events_batch_id_model_type_model_id_index` (`batch_id`,`model_type`(191),`model_id`),
  KEY `action_events_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL,
  `subject_type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_subject_id_index` (`subject_id`),
  KEY `activities_subject_type_index` (`subject_type`),
  KEY `activities_user_id_index` (`user_id`),
  KEY `activities_division_id_index` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `censuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `censuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` mediumint(8) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  `weekly_active_count` int(10) unsigned NOT NULL,
  `weekly_ts_count` int(11) NOT NULL DEFAULT 0,
  `weekly_voice_count` int(11) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `division_handle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `division_handle` (
  `division_id` int(10) unsigned NOT NULL,
  `handle_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`division_id`,`handle_id`),
  KEY `division_handle_division_id_index` (`division_id`),
  KEY `division_handle_handle_id_index` (`handle_id`),
  CONSTRAINT `division_handle_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `division_handle_handle_id_foreign` FOREIGN KEY (`handle_id`) REFERENCES `handles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `division_parttimer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `division_parttimer` (
  `division_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`division_id`,`member_id`),
  KEY `division_parttimer_division_id_index` (`division_id`),
  KEY `division_parttimer_member_id_index` (`member_id`),
  CONSTRAINT `division_parttimer_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `division_parttimer_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `handle_id` int(10) unsigned NOT NULL,
  `officer_role_id` int(11) DEFAULT NULL,
  `forum_app_id` int(11) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `structure` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shutdown_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisions_abbreviation_unique` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `handle_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `handle_member` (
  `handle_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`handle_id`,`member_id`),
  KEY `handle_member_handle_id_index` (`handle_id`),
  KEY `handle_member_member_id_index` (`member_id`),
  CONSTRAINT `handle_member_handle_id_foreign` FOREIGN KEY (`handle_id`) REFERENCES `handles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `handle_member_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `handles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `handles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_reserved_at_index` (`queue`,`reserved`,`reserved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `approver_id` int(10) unsigned DEFAULT NULL,
  `requester_id` int(10) unsigned NOT NULL,
  `reason` enum('military','medical','education','travel','other') NOT NULL DEFAULT 'other',
  `note_id` int(10) unsigned NOT NULL,
  `end_date` datetime NOT NULL,
  `extended` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leaves_member_id_unique` (`member_id`),
  KEY `leaves_approver_id_index` (`approver_id`),
  KEY `leaves_requester_id_index` (`requester_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `approver_id` int(10) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `canceller_id` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `hold_placed_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_requests_member_id_index` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `clan_id` mediumint(8) unsigned NOT NULL,
  `rank_id` tinyint(4) NOT NULL DEFAULT 1,
  `platoon_id` mediumint(9) NOT NULL,
  `squad_id` mediumint(9) NOT NULL,
  `position_id` tinyint(4) NOT NULL DEFAULT 1,
  `division_id` int(10) unsigned NOT NULL,
  `ts_unique_id` varchar(255) DEFAULT NULL,
  `discord` varchar(191) DEFAULT NULL,
  `last_voice_activity` datetime DEFAULT NULL,
  `last_voice_status` varchar(191) DEFAULT NULL,
  `discord_id` bigint(20) DEFAULT NULL,
  `flagged_for_inactivity` tinyint(1) NOT NULL,
  `posts` int(10) unsigned NOT NULL DEFAULT 0,
  `privacy_flag` tinyint(1) NOT NULL DEFAULT 0,
  `allow_pm` tinyint(1) NOT NULL DEFAULT 1,
  `join_date` timestamp NULL DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `last_ts_activity` datetime DEFAULT NULL,
  `last_promoted_at` datetime DEFAULT NULL,
  `last_trained_at` timestamp NULL DEFAULT NULL,
  `last_trained_by` int(11) DEFAULT NULL,
  `xo_at` timestamp NULL DEFAULT NULL,
  `co_at` timestamp NULL DEFAULT NULL,
  `recruiter_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `groups` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_clan_id_unique` (`clan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `body` text NOT NULL,
  `forum_thread_id` int(11) DEFAULT NULL,
  `member_id` mediumint(9) NOT NULL,
  `author_id` mediumint(9) NOT NULL,
  `type` enum('positive','negative','misc','sr_ldr') NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `platoons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platoons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `division_id` mediumint(9) NOT NULL,
  `leader_id` mediumint(9) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `order` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rank_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rank_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `rank_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ranks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `squads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `squads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `logo` varchar(191) DEFAULT NULL,
  `platoon_id` mediumint(9) NOT NULL,
  `leader_id` mediumint(9) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `gen_pop` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `auto_assign_to_id` int(11) DEFAULT NULL,
  `boilerplate` text DEFAULT NULL,
  `role_access` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`role_access`)),
  `display_order` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state` enum('new','assigned','resolved','rejected') NOT NULL DEFAULT 'new',
  `ticket_type_id` int(10) unsigned NOT NULL DEFAULT 1,
  `message_id` char(36) NOT NULL,
  `description` longtext NOT NULL,
  `caller_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role_id` tinyint(4) NOT NULL DEFAULT 1,
  `member_id` mediumint(9) NOT NULL,
  `settings` text NOT NULL,
  `developer` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_name_unique` (`name`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_member_id_unique` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2015_12_29_161233_create_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2015_12_30_025054_create_divisions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2015_12_30_082821_create_division_member_pivot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2015_12_30_083456_create_platoons_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2015_12_30_083543_create_squads_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2015_12_30_083625_create_ranks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2015_12_30_083720_create_positions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2016_03_16_160439_create_division_populations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2016_05_17_150808_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2016_05_22_024309_create_staff_sergeants_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2016_05_23_142407_create_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2016_05_30_120447_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2016_09_17_054913_ChangeEnabledDivisionToActiveDivision',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2016_09_17_060442_create_handles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2016_11_22_050303_create_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2016_11_23_031628_change_welcome_forum_to_welcome_area',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2016_11_23_040413_drop_structure_and_welcome_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2016_11_23_050606_divisions_uses_soft_deletes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2016_11_25_022759_create_censuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2016_11_25_174644_rename_staff_sergeants_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2016_11_26_002103_add_warhammer_wow_tf2_divisions',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2016_12_01_100341_add_activity_threshold_setting',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2016_12_10_203235_rename_last_forum_login_column',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2016_12_16_082909_add_gen_pop_to_squads',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2016_12_21_113036_add_soft_deletes_to_squads',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2016_12_25_232444_drop_locality_column_from_divisions',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2017_03_11_174914_add_colors_to_ranks',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2017_03_12_110429_add_name_to_squads',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2017_03_21_143857_create_handle_member_pivot_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2017_03_21_151410_create_division_handle_pivot_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2017_03_26_174009_add_last_logged_to_users',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2017_04_03_154853_add_handle_value_to_member_handles',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2017_04_14_104244_create_notes_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2017_04_19_212936_create_tags_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2017_05_09_103020_add_division_id_to_tags',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2017_05_16_144820_add_platoon_logo_to_edit_form',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2017_05_19_084550_add_handle_to_divisions',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2017_06_03_233447_add_handle_comments_for_placeholders',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2017_06_04_160821_add_post_count_to_members',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2017_06_06_203459_drop_visible_column_from_handles',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2017_06_14_221904_add_division_structure_to_divisions',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2017_06_19_220836_handle_url_can_be_null',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2017_06_20_081032_recruiter_id_can_be_null',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2017_06_20_203633_create_leaves_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2017_06_28_201450_create_division_parttimer_pivot_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2017_06_28_220150_drop_divisions_add_division_key_to_members',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2017_07_16_200149_add_flag_for_inactivity_to_members',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2017_08_08_175541_drop_sotdelete_column_from_leave',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2017_08_10_185406_add_teamspeak_datetime_to_members',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2017_08_12_085853_add_teamspeak_activity_to_census',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2017_08_12_123050_add_unique_id_to_members',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2017_08_22_155522_add_pending_status_to_member',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2017_10_08_172103_add_slug_to_tags_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2017_12_09_134202_create_fireteams_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2017_12_09_134229_create_fireteam_member_pivot_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2018_02_09_124537_drop_password_column_from_users',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2018_08_12_140513_create_member_requests_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2018_01_01_000000_create_action_events_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2018_09_12_202259_rebuild_staff_sergeant_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2018_09_22_143549_drop_tags_tables',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2018_10_21_120150_create_opt_in_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2018_12_16_124433_add_sgt_info_to_members',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2018_12_29_094201_update_last_promoted_column',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2019_02_04_105310_add_discord_name_to_members',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2019_07_07_111506_add_squad_logo',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2019_10_04_194902_create_tickets_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2019_10_04_213841_create_ticket_types_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2020_02_26_222816_add_processed_at_to_requests',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2019_05_10_000000_add_fields_to_action_events_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2019_11_29_225743_drop_vegas_optin_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2020_03_21_130955_add_officer_id_to_divisions',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2020_07_12_163136_add_hold_placed_at_column_to_member_requests',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2020_07_28_212123_add_shutdown_timestamp_to_divisions',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2020_10_11_122223_rename_staff_sergeants_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2020_10_18_143138_change_ticket_type_column',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2020_10_18_144619_create_ticket_comments_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2020_11_15_174622_add_ticket_boilerplate_column_to_ticket_types_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2020_11_17_110142_add_display_order_to_ticket_types_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2020_11_17_131641_add_rejected_to_ticket_types_enum',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2020_11_18_013001_create_failed_jobs_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2019_12_14_000001_create_personal_access_tokens_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2020_11_28_201211_add_role_access_to_ticket_types',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2021_06_10_080357_drop_staff_sergeants',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2021_07_03_132912_drop_oauth_tables_for_passport',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2021_07_05_095319_add_application_id_to_divisions_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2021_07_05_095336_add_privacy_flag_to_members_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2021_07_07_230419_add_slug_to_divisions_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2021_09_16_184407_add_allow_pm_column_to_members_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2021_12_31_150017_create_transfers_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2021_12_31_150144_create_rank_actions_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2023_01_03_134718_add_discord_id_to_members_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2023_05_17_143601_add_message_id_to_tickets_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2023_05_31_161731_add_auto_assign_to_column_to_ticket_types_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2024_03_21_083940_add_discord_member_data',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2024_04_07_184652_add_voice_census',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2024_05_06_141608_fix_failed_jobs_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2024_05_26_110404_add_indexes_to_members',49);
