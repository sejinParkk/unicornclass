/*M!999999\- enable the sandbox mode */ 

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
DROP TABLE IF EXISTS `lc_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_admin` (
  `admin_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`admin_idx`),
  UNIQUE KEY `login_id` (`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_cart` (
  `cart_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `class_idx` int(10) unsigned NOT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_idx`),
  UNIQUE KEY `uq_cart` (`member_idx`,`class_idx`),
  KEY `fk_cart_class` (`class_idx`),
  CONSTRAINT `fk_cart_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`),
  CONSTRAINT `fk_cart_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_class` (
  `class_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_idx` int(10) unsigned DEFAULT NULL COMMENT '클래스 카테고리 FK',
  `instructor_idx` int(10) unsigned NOT NULL,
  `type` enum('free','premium') NOT NULL,
  `title` varchar(200) NOT NULL,
  `summary` varchar(500) DEFAULT NULL COMMENT '짧은 설명',
  `description` text DEFAULT NULL COMMENT '상세 설명 (HTML)',
  `thumbnail` varchar(255) DEFAULT NULL,
  `price` int(10) unsigned DEFAULT 0,
  `price_origin` int(10) unsigned DEFAULT 0 COMMENT '정가 (할인 전)',
  `duration_days` smallint(6) DEFAULT 180 COMMENT '수강 기간 (일), 무료는 0',
  `total_episodes` smallint(6) DEFAULT 0,
  `kakao_url` varchar(500) DEFAULT NULL COMMENT '카카오 오픈채팅 링크',
  `vimeo_url` varchar(500) DEFAULT NULL COMMENT '프리미엄: Vimeo 링크',
  `badge_hot` tinyint(1) NOT NULL DEFAULT 0,
  `badge_new` tinyint(1) NOT NULL DEFAULT 0,
  `sale_end_at` datetime DEFAULT NULL COMMENT '판매 종료일 (D-day 기준)',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` smallint(6) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`class_idx`),
  KEY `fk_class_instructor` (`instructor_idx`),
  KEY `fk_class_category` (`category_idx`),
  CONSTRAINT `fk_class_category` FOREIGN KEY (`category_idx`) REFERENCES `lc_class_category` (`category_idx`) ON DELETE SET NULL,
  CONSTRAINT `fk_class_instructor` FOREIGN KEY (`instructor_idx`) REFERENCES `lc_instructor` (`instructor_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_class_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_class_category` (
  `category_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`category_idx`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_class_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_class_chapter` (
  `chapter_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_idx` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `vimeo_url` varchar(500) NOT NULL,
  `duration` int(10) unsigned DEFAULT 0 COMMENT '영상 길이 (초)',
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`chapter_idx`),
  KEY `fk_chapter_class` (`class_idx`),
  CONSTRAINT `fk_chapter_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_enroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_enroll` (
  `enroll_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `class_idx` int(10) unsigned NOT NULL,
  `order_idx` int(10) unsigned DEFAULT NULL COMMENT '무료는 NULL',
  `type` enum('free','premium') NOT NULL,
  `kakao_url` varchar(500) DEFAULT NULL COMMENT '수강 시점 카카오 링크 스냅샷',
  `vimeo_url` varchar(500) DEFAULT NULL,
  `enrolled_at` datetime DEFAULT current_timestamp(),
  `expire_at` datetime DEFAULT NULL COMMENT '무료는 NULL',
  PRIMARY KEY (`enroll_idx`),
  UNIQUE KEY `uq_enroll` (`member_idx`,`class_idx`),
  KEY `fk_enroll_class` (`class_idx`),
  CONSTRAINT `fk_enroll_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`),
  CONSTRAINT `fk_enroll_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_faq` (
  `faq_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL COMMENT '수강, 결제, 계정, 기술, 기타',
  `question` varchar(300) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`faq_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_instructor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_instructor` (
  `instructor_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_idx` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `field` varchar(100) NOT NULL COMMENT '분야 (커머스, AI 활용 등)',
  `photo` varchar(255) DEFAULT NULL COMMENT '사진 경로',
  `intro` text DEFAULT NULL COMMENT '소개 본문',
  `career` text DEFAULT NULL COMMENT '경력 목록 (줄바꿈 구분 텍스트)',
  `sns_youtube` varchar(255) DEFAULT NULL,
  `sns_instagram` varchar(255) DEFAULT NULL,
  `sns_facebook` varchar(255) DEFAULT NULL,
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`instructor_idx`),
  KEY `fk_instructor_category` (`category_idx`),
  CONSTRAINT `fk_instructor_category` FOREIGN KEY (`category_idx`) REFERENCES `lc_instructor_category` (`category_idx`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_instructor_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_instructor_category` (
  `category_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`category_idx`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_member` (
  `member_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(50) NOT NULL,
  `mb_password` varchar(255) DEFAULT NULL COMMENT '소셜 로그인은 NULL',
  `mb_name` varchar(50) NOT NULL,
  `mb_phone` varchar(20) DEFAULT NULL,
  `mb_email` varchar(100) DEFAULT NULL,
  `signup_type` enum('email','kakao','naver') DEFAULT 'email',
  `social_id` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `mb_mailling` tinyint(1) DEFAULT 0 COMMENT '이메일 수신 동의',
  `mb_sms` tinyint(1) DEFAULT 0 COMMENT 'SMS 수신 동의',
  `leave_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`member_idx`),
  UNIQUE KEY `mb_id` (`mb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_notice` (
  `notice_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0 COMMENT '상단 고정',
  `is_active` tinyint(1) DEFAULT 1,
  `views` int(10) unsigned DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`notice_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_order` (
  `order_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `class_idx` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL COMMENT '실 결제금액',
  `amount_origin` int(10) unsigned NOT NULL COMMENT '정가',
  `status` enum('paid','refund_req','refunded','free') NOT NULL,
  `toss_payment_key` varchar(200) DEFAULT NULL,
  `toss_order_id` varchar(200) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `refund_reason` varchar(500) DEFAULT NULL,
  `refunded_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`order_idx`),
  KEY `fk_order_member` (`member_idx`),
  KEY `fk_order_class` (`class_idx`),
  CONSTRAINT `fk_order_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`),
  CONSTRAINT `fk_order_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_progress` (
  `progress_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `class_idx` int(10) unsigned NOT NULL,
  `chapter_idx` int(10) unsigned NOT NULL,
  `is_complete` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`progress_idx`),
  UNIQUE KEY `uq_progress` (`member_idx`,`chapter_idx`),
  KEY `fk_progress_class` (`class_idx`),
  KEY `fk_progress_chapter` (`chapter_idx`),
  CONSTRAINT `fk_progress_chapter` FOREIGN KEY (`chapter_idx`) REFERENCES `lc_class_chapter` (`chapter_idx`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_qna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_qna` (
  `qna_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `category` enum('class','payment','account','tech','etc') NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('wait','done') DEFAULT 'wait',
  `answer` text DEFAULT NULL,
  `answered_by` int(10) unsigned DEFAULT NULL COMMENT '답변 관리자 idx',
  `answered_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`qna_idx`),
  KEY `fk_qna_member` (`member_idx`),
  CONSTRAINT `fk_qna_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_search_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_search_log` (
  `log_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `result_count` smallint(6) DEFAULT 0,
  `searched_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`log_idx`),
  KEY `idx_keyword` (`keyword`),
  KEY `idx_searched_at` (`searched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_search_suggest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_search_suggest` (
  `suggest_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL,
  `sort_order` smallint(6) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`suggest_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_site_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_site_config` (
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_label` varchar(200) DEFAULT NULL COMMENT '관리자 화면 표시용 라벨',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lc_wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lc_wishlist` (
  `wish_idx` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_idx` int(10) unsigned NOT NULL,
  `class_idx` int(10) unsigned NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`wish_idx`),
  UNIQUE KEY `uq_wish` (`member_idx`,`class_idx`),
  KEY `fk_wish_class` (`class_idx`),
  CONSTRAINT `fk_wish_class` FOREIGN KEY (`class_idx`) REFERENCES `lc_class` (`class_idx`),
  CONSTRAINT `fk_wish_member` FOREIGN KEY (`member_idx`) REFERENCES `lc_member` (`member_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

