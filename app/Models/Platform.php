<?php
/**
 * 파일: app/Models/Platform.php
 * 
 * 이 파일은 인플루언서가 활동하는 플랫폼 모델 클래스를 정의합니다.
 * 소셜 미디어 플랫폼 정보를 관리하고, 특정 플랫폼에 속한 인플루언서들을 조회하는 기능을 제공합니다.
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\Model;

/**
 * 플랫폼 모델 클래스
 * 
 * 인플루언서가 활동하는 소셜 미디어 플랫폼 정보를 관리합니다.
 */
class Platform extends Model
{
    protected static $table = 'platforms';
    protected static $primaryKey = 'id';

    /**
     * 이 플랫폼에 속한 인플루언서 목록을 조회합니다.
     * 
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 인플루언서 모델 인스턴스 배열
     */
    public function getInfluencers($limit = null, $offset = null)
    {
        return Influencer::findByPlatform($this->id, $limit, $offset);
    }

    /**
     * 이름으로 플랫폼을 검색합니다.
     * 
     * @param string $name 플랫폼 이름
     * @return Platform|null 플랫폼 모델 인스턴스 또는 null
     */
    public static function findByName($name)
    {
        $platforms = self::all(['name' => $name]);
        return !empty($platforms) ? $platforms[0] : null;
    }
} 