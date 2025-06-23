<?php
/**
 * 플랫폼 테이블 생성
 * 
 * 이 파일은 인플루언서 플랫폼 정보를 저장할 테이블을 생성합니다.
 * (예: Instagram, YouTube, TikTok 등)
 */

class CreatePlatformsTable {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO $db) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `platforms` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `website` VARCHAR(255),
                `logo_url` VARCHAR(255),
                `active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `name` (`name`)
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
        $sql = "DROP TABLE IF EXISTS `platforms`";
        $db->exec($sql);
        return true;
    }
} 