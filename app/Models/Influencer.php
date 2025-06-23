<?php
/**
 * 파일: app/Models/Influencer.php
 * 
 * 이 파일은 인플루언서 모델 클래스를 정의합니다.
 * 인플루언서 정보를 데이터베이스에서 조회, 저장, 수정, 삭제하는 기능을 제공하며,
 * 플랫폼 및 카테고리와의 관계를 관리합니다.
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\Model;
use App\Core\DB;

/**
 * 인플루언서 모델 클래스
 * 
 * 인플루언서 정보를 관리하는 모델 클래스입니다.
 */
class Influencer extends Model
{
    protected static $table = 'influencers';
    protected static $primaryKey = 'id';

    /**
     * 특정 플랫폼에 속한 인플루언서 목록을 조회합니다.
     * 
     * @param int $platformId 플랫폼 ID
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 인플루언서 모델 인스턴스 배열
     */
    public static function findByPlatform($platformId, $limit = null, $offset = null)
    {
        return self::all(['platform_id' => $platformId], 'follower_count DESC', $limit, $offset);
    }

    /**
     * 특정 카테고리에 속한 인플루언서 목록을 조회합니다.
     * 
     * @param int $categoryId 카테고리 ID
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 인플루언서 모델 인스턴스 배열
     */
    public static function findByCategory($categoryId, $limit = null, $offset = null)
    {
        $db = DB::getInstance();
        $sql = "SELECT i.* FROM " . self::$table . " i
                JOIN influencer_categories ic ON i.id = ic.influencer_id
                WHERE ic.category_id = :category_id
                ORDER BY i.follower_count DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId);
        $result = $db->execute($stmt);
        $items = [];
        
        if ($result) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $model = new static();
                $model->attributes = $row;
                $items[] = $model;
            }
        }
        
        return $items;
    }

    /**
     * 키워드로 인플루언서를 검색합니다.
     * 
     * @param string $keyword 검색 키워드
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 인플루언서 모델 인스턴스 배열
     */
    public static function search($keyword, $limit = null, $offset = null)
    {
        $db = DB::getInstance();
        $sql = "SELECT * FROM " . self::$table . " 
                WHERE name LIKE :keyword OR handle LIKE :keyword OR bio LIKE :keyword
                ORDER BY follower_count DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%");
        $result = $db->execute($stmt);
        $items = [];
        
        if ($result) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $model = new static();
                $model->attributes = $row;
                $items[] = $model;
            }
        }
        
        return $items;
    }

    /**
     * 인플루언서의 카테고리 목록을 조회합니다.
     * 
     * @return array 카테고리 모델 인스턴스 배열
     */
    public function getCategories()
    {
        $db = DB::getInstance();
        $sql = "SELECT c.* FROM categories c
                JOIN influencer_categories ic ON c.id = ic.category_id
                WHERE ic.influencer_id = :influencer_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':influencer_id', $this->id);
        $result = $db->execute($stmt);
        $categories = [];
        
        if ($result) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $category = new Category();
                $category->attributes = $row;
                $categories[] = $category;
            }
        }
        
        return $categories;
    }

    /**
     * 인플루언서의 플랫폼 정보를 조회합니다.
     * 
     * @return Platform|null 플랫폼 모델 인스턴스 또는 null
     */
    public function getPlatform()
    {
        return Platform::find($this->platform_id);
    }
} 