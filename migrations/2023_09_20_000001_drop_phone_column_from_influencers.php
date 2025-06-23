<?php
/**
 * 인플루언서 테이블에서 전화번호 컬럼 삭제
 * 
 * 이 파일은 인플루언서 테이블에서 'phone' 컬럼을 삭제하는 마이그레이션입니다.
 * 개인정보 보호 강화를 위해 전화번호 정보는 더 이상 저장하지 않습니다.
 * 
 * 작성일: 2023-09-20
 */

class DropPhoneColumnFromInfluencers {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO $db) {
        // 기존 데이터 백업 (선택사항)
        $db->exec("
            CREATE TABLE IF NOT EXISTS `influencer_backup_phone` (
                `influencer_id` INT UNSIGNED PRIMARY KEY,
                `phone` VARCHAR(50),
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        $db->exec("
            INSERT INTO `influencer_backup_phone` (`influencer_id`, `phone`)
            SELECT `id`, `phone` FROM `influencers` WHERE `phone` IS NOT NULL;
        ");
        
        // 컬럼 삭제
        $sql = "ALTER TABLE `influencers` DROP COLUMN `phone`;";
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
        // 컬럼 다시 추가
        $db->exec("ALTER TABLE `influencers` ADD COLUMN `phone` VARCHAR(50) AFTER `email`;");
        
        // 백업 데이터 복원 (백업 테이블이 있다면)
        $stmt = $db->query("SHOW TABLES LIKE 'influencer_backup_phone'");
        if ($stmt->rowCount() > 0) {
            $db->exec("
                UPDATE `influencers` i
                JOIN `influencer_backup_phone` b ON i.id = b.influencer_id
                SET i.phone = b.phone
                WHERE b.phone IS NOT NULL;
            ");
        }
        
        return true;
    }
} 