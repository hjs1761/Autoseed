<?php
/**
 * 인플루언서 게시물 테이블 생성
 * 
 * 이 파일은 인플루언서가 작성한 게시물 정보를 저장할 테이블을 생성합니다.
 * 인스타그램, 유튜브 등 각 플랫폼의 게시물 정보를 저장합니다.
 * 
 * 작성일: 2023-06-10
 */

class CreateInfluencerPostsTable {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO $db) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `influencer_posts` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `influencer_id` INT UNSIGNED NOT NULL,
                `platform_id` INT UNSIGNED NOT NULL,
                `external_id` VARCHAR(100) NOT NULL,
                `post_type` ENUM('image', 'video', 'carousel', 'text', 'story', 'reel', 'short') NOT NULL,
                `content` TEXT,
                `url` VARCHAR(255) NOT NULL,
                `thumbnail_url` VARCHAR(255),
                `like_count` INT UNSIGNED DEFAULT 0,
                `comment_count` INT UNSIGNED DEFAULT 0,
                `view_count` INT UNSIGNED DEFAULT 0,
                `share_count` INT UNSIGNED DEFAULT 0,
                `engagement_rate` FLOAT DEFAULT 0,
                `posted_at` DATETIME,
                `crawled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`influencer_id`) REFERENCES `influencers` (`id`) ON DELETE CASCADE,
                FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE,
                UNIQUE KEY `platform_post` (`platform_id`, `external_id`),
                INDEX `idx_posted_at` (`posted_at`),
                INDEX `idx_engagement_rate` (`engagement_rate`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($sql);
        return true;
    }
    
    /**
     * 마이그레이션 롤백
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function down(PDO $db) {
        $sql = "DROP TABLE IF EXISTS `influencer_posts`";
        $db->exec($sql);
        return true;
    }
} 