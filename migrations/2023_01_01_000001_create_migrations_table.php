<?php
/**
 * 마이그레이션 이력 테이블 생성
 * 
 * 이 파일은 마이그레이션 이력을 추적하기 위한 테이블을 생성합니다.
 * 다른 모든 마이그레이션 파일들의 실행 이력이 이 테이블에 저장됩니다.
 */

class CreateMigrationsTable {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO $db) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($sql);
        return true;
    }
    
    /**
     * 마이그레이션 롤백
     * 
     * 마이그레이션 테이블은 롤백하지 않습니다. (모든 이력 관리용 테이블)
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function down(PDO $db) {
        // 마이그레이션 이력 테이블은 롤백하지 않음
        return true;
    }
} 