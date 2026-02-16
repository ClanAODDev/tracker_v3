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
  `name` tinyint(3) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_subject_id_index` (`subject_id`),
  KEY `activities_subject_type_index` (`subject_type`),
  KEY `activities_user_id_index` (`user_id`),
  KEY `activities_division_id_index` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` mediumint(8) unsigned NOT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `reminded_by_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_reminders_member_id_created_at_index` (`member_id`,`created_at`),
  KEY `activity_reminders_division_id_created_at_index` (`division_id`,`created_at`),
  KEY `activity_reminders_reminded_by_id_created_at_index` (`reminded_by_id`,`created_at`),
  CONSTRAINT `activity_reminders_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_reminders_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`clan_id`) ON DELETE CASCADE,
  CONSTRAINT `activity_reminders_reminded_by_id_foreign` FOREIGN KEY (`reminded_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `award_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `award_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `reason` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `award_member_award_id_index` (`award_id`),
  KEY `award_member_member_id_index` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `awards` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 100,
  `division_id` int(11) DEFAULT NULL,
  `prerequisite_award_id` bigint(20) unsigned DEFAULT NULL,
  `tiered_group_name` varchar(191) DEFAULT NULL,
  `tiered_group_description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'For awards given during certain periods of time',
  `allow_request` tinyint(1) NOT NULL DEFAULT 0,
  `repeatable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `instructions` varchar(191) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `awards_prerequisite_award_id_foreign` (`prerequisite_award_id`),
  CONSTRAINT `awards_prerequisite_award_id_foreign` FOREIGN KEY (`prerequisite_award_id`) REFERENCES `awards` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  PRIMARY KEY (`id`),
  KEY `censuses_division_id_index` (`division_id`),
  KEY `censuses_division_id_created_at_index` (`division_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comment_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comment_reactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL,
  `reactor_type` varchar(191) NOT NULL,
  `reactor_id` bigint(20) unsigned NOT NULL,
  `reaction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_reactions_comment_id_foreign` (`comment_id`),
  KEY `comment_reactions_reactor_type_reactor_id_index` (`reactor_type`,`reactor_id`),
  CONSTRAINT `comment_reactions_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comment_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comment_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(191) NOT NULL,
  `subscribable_id` bigint(20) unsigned NOT NULL,
  `subscriber_type` varchar(191) NOT NULL,
  `subscriber_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commentions_subscriptions_unique` (`subscribable_type`,`subscribable_id`,`subscriber_type`,`subscriber_id`),
  KEY `comment_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`),
  KEY `comment_subscriptions_subscriber_type_subscriber_id_index` (`subscriber_type`,`subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_type` varchar(191) NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `commentable_type` varchar(191) NOT NULL,
  `commentable_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_author_type_author_id_index` (`author_type`,`author_id`),
  KEY `comments_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `division_application_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `division_application_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(10) unsigned NOT NULL,
  `type` varchar(191) NOT NULL,
  `label` varchar(191) NOT NULL,
  `helper_text` varchar(191) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `required` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_application_fields_division_id_foreign` (`division_id`),
  CONSTRAINT `division_application_fields_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `division_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `division_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `division_id` int(10) unsigned NOT NULL,
  `responses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`responses`)),
  `recruited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `division_applications_user_id_foreign` (`user_id`),
  KEY `division_applications_division_id_foreign` (`division_id`),
  CONSTRAINT `division_applications_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `division_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
DROP TABLE IF EXISTS `division_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `division_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `visibility` varchar(20) NOT NULL DEFAULT 'public',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `division_tags_division_id_name_unique` (`division_id`,`name`),
  CONSTRAINT `division_tags_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL
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
  `logo` varchar(191) DEFAULT NULL,
  `site_content` text DEFAULT NULL,
  `screenshots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`screenshots`)),
  `show_on_site` tinyint(1) NOT NULL DEFAULT 1,
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `handle_id` int(10) unsigned NOT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Indicates if this is the primary handle for the handle type',
  `member_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
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
  `label` mediumtext NOT NULL,
  `type` varchar(255) NOT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `url` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batch_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batch_manager` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `status` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
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
DROP TABLE IF EXISTS `job_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_manager` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` varchar(191) NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `queue` varchar(191) DEFAULT NULL,
  `connection` varchar(191) DEFAULT NULL,
  `available_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `failed` tinyint(1) DEFAULT NULL,
  `attempt` int(11) NOT NULL,
  `progress` int(11) DEFAULT NULL,
  `exception_message` text DEFAULT NULL,
  `status` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `job_queue_worker_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_manager_job_id_index` (`job_id`),
  KEY `job_manager_queue_index` (`queue`),
  KEY `job_manager_status_index` (`status`),
  KEY `job_manager_job_queue_worker_id_foreign` (`job_queue_worker_id`),
  CONSTRAINT `job_manager_job_queue_worker_id_foreign` FOREIGN KEY (`job_queue_worker_id`) REFERENCES `job_queue_workers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_queue_workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_queue_workers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `worker_pid` varchar(191) NOT NULL,
  `queue` varchar(191) NOT NULL,
  `connection` varchar(191) NOT NULL,
  `worker_server` varchar(191) DEFAULT NULL,
  `supervisor` varchar(191) DEFAULT NULL,
  `status` varchar(191) NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `stopped_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_queue_workers_worker_pid_index` (`worker_pid`),
  KEY `job_queue_workers_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `holder_id` int(10) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `hold_placed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_requests_member_id_index` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `division_tag_id` bigint(20) unsigned NOT NULL,
  `assigned_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_tag_member_id_division_tag_id_unique` (`member_id`,`division_tag_id`),
  KEY `member_tag_division_tag_id_foreign` (`division_tag_id`),
  KEY `member_tag_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `member_tag_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `member_tag_division_tag_id_foreign` FOREIGN KEY (`division_tag_id`) REFERENCES `division_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_tag_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `clan_id` mediumint(8) unsigned NOT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT 1,
  `platoon_id` mediumint(9) NOT NULL,
  `squad_id` mediumint(9) NOT NULL,
  `position` tinyint(4) NOT NULL DEFAULT 1,
  `division_id` int(10) unsigned NOT NULL,
  `ts_unique_id` varchar(255) DEFAULT NULL,
  `discord` varchar(191) DEFAULT NULL,
  `last_voice_activity` datetime DEFAULT NULL,
  `last_voice_status` varchar(191) DEFAULT NULL,
  `discord_id` bigint(20) DEFAULT NULL,
  `flagged_for_inactivity` tinyint(1) NOT NULL,
  `last_activity_reminder_at` timestamp NULL DEFAULT NULL,
  `activity_reminded_by_id` int(10) unsigned DEFAULT NULL,
  `posts` int(10) unsigned NOT NULL DEFAULT 0,
  `privacy_flag` tinyint(1) NOT NULL,
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
  `groups` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_clan_id_unique` (`clan_id`),
  KEY `members_division_id_index` (`division_id`),
  KEY `members_platoon_id_index` (`platoon_id`),
  KEY `members_squad_id_index` (`squad_id`),
  KEY `members_recruiter_id_index` (`recruiter_id`),
  KEY `members_last_voice_activity_index` (`last_voice_activity`),
  KEY `members_join_date_index` (`join_date`),
  KEY `members_division_id_join_date_index` (`division_id`,`join_date`),
  KEY `members_division_id_last_voice_activity_index` (`division_id`,`last_voice_activity`),
  KEY `members_activity_reminded_by_id_foreign` (`activity_reminded_by_id`),
  CONSTRAINT `members_activity_reminded_by_id_foreign` FOREIGN KEY (`activity_reminded_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
DROP TABLE IF EXISTS `model_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_reactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reactable_type` varchar(191) NOT NULL,
  `reactable_id` bigint(20) unsigned NOT NULL,
  `reactor_type` varchar(191) NOT NULL,
  `reactor_id` bigint(20) unsigned NOT NULL,
  `reaction` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `model_reactions_unique` (`reactable_type`,`reactable_id`,`reactor_type`,`reactor_id`,`reaction`),
  KEY `model_reactions_reactable_type_reactable_id_index` (`reactable_type`,`reactable_id`),
  KEY `model_reactions_reactor_type_reactor_id_index` (`reactor_type`,`reactor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  PRIMARY KEY (`id`),
  KEY `notes_member_id_index` (`member_id`),
  KEY `notes_author_id_index` (`author_id`)
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
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
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
  `description` varchar(191) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `division_id` mediumint(9) NOT NULL,
  `leader_id` mediumint(9) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platoons_division_id_index` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rank_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rank_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `rank` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `awarded_at` datetime DEFAULT NULL,
  `denied_at` datetime DEFAULT NULL,
  `deny_reason` text DEFAULT NULL,
  `declined_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  PRIMARY KEY (`id`),
  KEY `squads_platoon_id_index` (`platoon_id`)
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
  `notification_channel` varchar(191) DEFAULT NULL,
  `include_content_in_notification` tinyint(1) NOT NULL DEFAULT 0,
  `minimum_rank` tinyint(3) unsigned DEFAULT NULL,
  `role_access` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
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
  `external_message_id` char(36) NOT NULL,
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
DROP TABLE IF EXISTS `training_checkpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_checkpoints` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_section_id` bigint(20) unsigned NOT NULL,
  `label` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_checkpoints_training_section_id_foreign` (`training_section_id`),
  CONSTRAINT `training_checkpoints_training_section_id_foreign` FOREIGN KEY (`training_section_id`) REFERENCES `training_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `show_completion_form` tinyint(1) NOT NULL DEFAULT 1,
  `checkpoint_label` varchar(191) NOT NULL DEFAULT 'Talking Points',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `training_modules_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `training_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `training_module_id` bigint(20) unsigned NOT NULL,
  `title` varchar(191) NOT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `content` longtext NOT NULL,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_sections_training_module_id_foreign` (`training_module_id`),
  CONSTRAINT `training_sections_training_module_id_foreign` FOREIGN KEY (`training_module_id`) REFERENCES `training_modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `hold_placed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transfers_member_id_index` (`member_id`),
  KEY `transfers_division_id_index` (`division_id`),
  KEY `transfers_approved_by_foreign` (`approved_by`),
  CONSTRAINT `transfers_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` tinyint(4) NOT NULL DEFAULT 1,
  `member_id` int(10) unsigned DEFAULT NULL,
  `discord_id` varchar(191) DEFAULT NULL,
  `discord_username` varchar(191) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `forum_password` text DEFAULT NULL,
  `settings` text NOT NULL,
  `developer` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_name_unique` (`name`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_member_id_unique` (`member_id`),
  UNIQUE KEY `users_discord_id_unique` (`discord_id`)
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
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2016_06_01_000001_create_oauth_auth_codes_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2016_06_01_000002_create_oauth_access_tokens_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2016_06_01_000003_create_oauth_refresh_tokens_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2016_06_01_000004_create_oauth_clients_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2016_06_01_000005_create_oauth_personal_access_clients_table',8);
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
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2020_10_18_144619_create_ticket_comments_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2020_11_15_174622_add_ticket_boilerplate_column_to_ticket_types_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2020_11_17_110142_add_display_order_to_ticket_types_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2020_11_17_131641_add_rejected_to_ticket_types_enum',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2020_11_18_013001_create_failed_jobs_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2020_11_28_201211_add_role_access_to_ticket_types',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2021_06_10_080357_drop_staff_sergeants',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2021_07_05_095319_add_application_id_to_divisions_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2021_07_05_095336_add_privacy_flag_to_members_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2019_12_14_000001_create_personal_access_tokens_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2021_07_07_230419_add_slug_to_divisions_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2021_09_16_184407_add_allow_pm_column_to_members_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2021_12_30_135818_create_aod_member_sync_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2021_12_31_150017_create_transfers_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2021_12_31_150144_create_rank_actions_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2023_01_03_134514_add_discord_id_to_sync_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2023_01_03_134718_add_discord_id_to_members_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2023_05_17_143601_add_message_id_to_tickets_table',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2023_05_31_161731_add_auto_assign_to_column_to_ticket_types_table',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2024_03_21_080438_add_discord_data_to_member_sync',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2024_03_21_083940_add_discord_member_data',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2024_04_07_184652_add_voice_census',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2024_05_06_141608_fix_failed_jobs_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2024_05_26_110404_add_indexes_to_members',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2019_12_14_000001_add_expiration_to_personal_access_tokens_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2024_07_22_155600_add_logo_column_to_divisions_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2024_08_02_165416_rename_message_id_column_on_tickets_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2024_08_07_105630_rename_position_id_on_members_table',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2024_08_18_111718_rename_rank_id_on_members_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2024_08_18_111718_rename_rank_id_on_rank_actions_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2019_05_31_042934_create_versions_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2020_07_03_163707_add_deleted_at_to_versions',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2021_03_18_160750_make_user_nullable',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2024_12_21_100301_add_award_and_member_award_tables',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2024_12_23_131406_drop_division_from_rank_actions_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2024_12_23_131648_drop_expires_at_column_from_award_member_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2024_12_26_113523_add_site_content_to_divisions_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2024_12_28_171739_add_show_on_site_boolean_column_to_divisions_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2024_12_30_100223_fix_platoon_and_squad_leader_id_columns',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_01_07_210846_add_approved_column_to_versions_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_01_19_135739_migrate_notifications_to_voice',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2025_01_19_214404_set_default_voice_alerts_channels',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2025_01_26_045100_01_create_job_manager_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2025_01_26_045100_02_create_job_batch_manager_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2025_01_26_045100_03_create_job_queue_workers_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2025_01_26_045100_04_add_foreigns_to_job_manager_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2025_01_26_045111_create_job_batches_table',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2025_01_26_092341_create_application_items_table',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2025_01_28_102035_delete_application_items_table',70);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2025_01_30_104749_rename_rank_changed_setting_to_member_promoted',71);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2025_02_01_001217_remove_extended_column_on_leaves_table',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2025_02_02_133919_add_self_documenting_fields_to_rank_actions',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2025_02_03_065919_add_requester_to_award_member',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_02_03_094402_add_instructions_field_to_awards',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_02_03_145619_create_filament_comments_table',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2025_02_03_145620_add_index_to_subject',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2025_02_05_142418_add_denied_at_and_deny_reason_to_rank_actions',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2025_02_05_162521_add_max_pl_rank_approval_setting_to_divisions',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2025_04_13_201351_add_awarded_at_field_to_rank_actions_table',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2025_05_31_144027_add_approved_at_column_to_transfers',75);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2025_06_03_083558_add_hold_placed_at_column_to_transfers_table',76);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2025_06_19_104122_add_approver_to_rank_actions_table',77);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2025_06_26_055413_add_member_applied_setting_to_divisions',78);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2025_08_02_104753_add_incrementing_id_and_primary_bool_to_member_handle',79);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2025_09_13_165838_update_member_requests_fields',80);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2025_10_27_095305_add_division_thread_url_support',81);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2025_12_01_130452_add_soft_delete_to_awards',82);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2025_12_04_165900_update_user_settings_snow_value',83);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2025_12_04_171021_fix_ticket_notifications_string_values',84);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2025_12_17_181807_convert_members_and_handles_tables_to_utf8mb4',85);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2025_12_20_095510_add_screenshots_to_divisions_table',86);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2025_12_21_112435_create_division_tags_table',87);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2025_12_21_112510_create_member_tag_table',87);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2025_12_21_200200_modify_division_tags_add_visibility',87);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2025_12_21_214028_make_division_tags_division_id_nullable',87);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2025_12_24_193322_add_performance_indexes_to_tables',88);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2025_12_25_101651_drop_versions_table',89);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2025_12_26_093714_drop_unused_enum_tables',90);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2025_12_27_063206_create_training_modules_table',91);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2025_12_27_063212_create_training_sections_table',91);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2025_12_27_063213_create_training_checkpoints_table',91);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2025_12_27_111635_add_description_to_training_checkpoints_table',92);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2025_12_28_132732_add_activity_reminder_fields_to_members_table',93);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2025_12_28_145550_create_activity_reminders_table',93);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2025_12_28_224833_convert_activity_names_to_enum_values',93);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2025_12_28_231136_add_properties_to_activities_table',93);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2025_12_29_184316_add_repeatable_to_awards_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2025_12_29_185607_add_prerequisite_award_id_to_awards_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2025_12_29_190058_backfill_missing_prerequisite_awards',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2025_12_29_224033_add_tiered_description_to_awards_table',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2025_12_29_231114_import_tenure_award_data',94);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2025_12_30_212326_update_role_values_after_jr_ldr_removal',95);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2025_12_31_191101_rename_role_id',96);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2026_01_02_193642_add_approved_by_to_transfers_table',97);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2026_01_03_120114_add_discord_fields_to_users_table',98);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2026_01_03_120424_make_member_id_nullable_on_users_table',98);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_01_06_093529_remove_thread_id_from_division_recruiting_threads',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_01_11_173228_add_notification_channel_and_minimum_rank_to_ticket_types',100);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_01_11_181049_cleanup_orphaned_tickets',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_01_14_195416_add_creation_data_to_users_table',102);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_01_16_154630_add_description_to_platoons_table',102);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_01_18_145823_cleanup_incorrect_transferred_activities',102);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_01_25_150212_set_welcomed_for_officers',103);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_02_01_120000_create_division_application_fields_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_02_01_120001_create_division_applications_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_02_01_170517_add_recruited_at_to_division_applications_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_02_02_104817_drop_division_id_from_users_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_02_07_141750_create_commentions_tables',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_02_07_141751_create_commentions_reactions_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_02_07_141752_create_commentions_subscriptions_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_02_07_141753_migrate_filament_comments_to_commentions',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_02_08_000000_create_model_reactions_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_02_08_100129_create_model_reactions_table',106);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_02_08_100129_create_model_reactions_table',107);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_02_11_105144_add_include_content_in_notification_to_ticket_types_table',108);
