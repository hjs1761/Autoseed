<?php
/**
 * 인플루언서 테이블에 인증 여부 컬럼 추가
 * 
 * 이 파일은 인플루언서 테이블에 'is_verified' 컬럼을 추가하는 마이그레이션입니다.
 * 인증된 인플루언서인지 여부를 표시하는 컬럼입니다.
 * 
 * 작성일: 2023-05-15
 */

class AddVerifiedColumnToInfluencers {
    /**
     * 마이그레이션 실행
     * 
     * @param PDO $db 데이터베이스 연결 객체
     * @return bool 성공 여부
     */
    public function up(PDO $db) {
        $sql = "
            ALTER TABLE `influencers` 
            ADD COLUMN `is_verified` TINYINT(1) DEFAULT 0 AFTER `status`,
            ADD INDEX `idx_is_verified` (`is_verified`);
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
        $sql = "
            ALTER TABLE `influencers` 
            DROP COLUMN `is_verified`,
            DROP INDEX `idx_is_verified`;
        ";
        
        $db->exec($sql);
        return true;
    }
} 