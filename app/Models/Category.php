<?php
/**
 * 파일: app/Models/Category.php
 * 
 * 이 파일은 인플루언서 카테고리 모델 클래스를 정의합니다.
 * 카테고리 정보를 관리하고, 인플루언서와 카테고리 간의 연결 관계를 처리하는 기능을 제공합니다.
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\Model;
use App\Core\DB;

/**
 * 카테고리 모델 클래스
 * 
 * 인플루언서 카테고리 정보를 관리합니다.
 */
class Category extends Model
{
    protected static $table = 'categories';
    protected static $primaryKey = 'id';

    /**
     * 이 카테고리에 속한 인플루언서 목록을 조회합니다.
     * 
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 인플루언서 모델 인스턴스 배열
     */
    public function getInfluencers($limit = null, $offset = null)
    {
        return Influencer::findByCategory($this->id, $limit, $offset);
    }

    /**
     * 이름으로 카테고리를 검색합니다.
     * 
     * @param string $name 카테고리 이름
     * @return Category|null 카테고리 모델 인스턴스 또는 null
     */
    public static function findByName($name)
    {
        $categories = self::all(['name' => $name]);
        return !empty($categories) ? $categories[0] : null;
    }

    /**
     * 인플루언서에 카테고리를 연결합니다.
     * 
     * @param int $categoryId 카테고리 ID
     * @param int $influencerId 인플루언서 ID
     * @return bool 연결 성공 여부
     */
    public static function linkToInfluencer($categoryId, $influencerId)
    {
        $db = DB::getInstance();
        $sql = "INSERT INTO influencer_categories (influencer_id, category_id) VALUES (:influencer_id, :category_id)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':influencer_id', $influencerId);
        $stmt->bindValue(':category_id', $categoryId);
        $result = $db->execute($stmt);
        
        return $result ? true : false;
    }

    /**
     * 인플루언서와 카테고리 연결을 해제합니다.
     * 
     * @param int $categoryId 카테고리 ID
     * @param int $influencerId 인플루언서 ID
     * @return bool 연결 해제 성공 여부
     */
    public static function unlinkFromInfluencer($categoryId, $influencerId)
    {
        $db = DB::getInstance();
        $sql = "DELETE FROM influencer_categories WHERE influencer_id = :influencer_id AND category_id = :category_id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':influencer_id', $influencerId);
        $stmt->bindValue(':category_id', $categoryId);
        $result = $db->execute($stmt);
        
        return $result ? true : false;
    }
} 