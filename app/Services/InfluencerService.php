<?php
/**
 * 파일: app/Services/InfluencerService.php
 * 
 * 이 파일은 인플루언서 관련 비즈니스 로직을 처리하는 서비스 클래스를 정의합니다.
 * 컨트롤러와 모델 사이의 중간 계층으로서, 인플루언서 검색, 조회, 저장, 삭제 등의 기능을 제공합니다.
 * 비즈니스 규칙을 캡슐화하고 트랜잭션 관리와 예외 처리를 담당합니다.
 * 
 * @package App\Services
 */

namespace App\Services;

use App\Models\Influencer;
use App\Models\Platform;
use App\Models\Category;
use App\Core\DB;

/**
 * 인플루언서 서비스 클래스
 * 
 * 인플루언서 관련 비즈니스 로직을 처리합니다.
 */
class InfluencerService
{
    /**
     * 인플루언서 검색 서비스
     * 
     * @param array $params 검색 매개변수
     * @return array 응답 데이터
     */
    public function search($params)
    {
        try {
            $keyword = $params['keyword'] ?? '';
            $platformId = $params['platform_id'] ?? null;
            $categoryId = $params['category_id'] ?? null;
            $limit = $params['limit'] ?? 20;
            $page = $params['page'] ?? 1;
            $offset = ($page - 1) * $limit;
            
            $db = DB::getInstance();
            
            // 기본 쿼리
            $sql = "SELECT DISTINCT i.* FROM influencers i";
            $whereConditions = [];
            $queryParams = [];
            
            // 카테고리 필터
            if ($categoryId) {
                $sql .= " JOIN influencer_categories ic ON i.id = ic.influencer_id";
                $whereConditions[] = "ic.category_id = :category_id";
                $queryParams[':category_id'] = $categoryId;
            }
            
            // 플랫폼 필터
            if ($platformId) {
                $whereConditions[] = "i.platform_id = :platform_id";
                $queryParams[':platform_id'] = $platformId;
            }
            
            // 키워드 검색
            if ($keyword) {
                $whereConditions[] = "(i.name LIKE :keyword OR i.handle LIKE :keyword OR i.bio LIKE :keyword)";
                $queryParams[':keyword'] = "%$keyword%";
            }
            
            // WHERE 절 추가
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            // 정렬 및 페이지네이션
            $sql .= " ORDER BY i.follower_count DESC LIMIT :limit OFFSET :offset";
            $queryParams[':limit'] = $limit;
            $queryParams[':offset'] = $offset;
            
            $stmt = $db->prepare($sql);
            
            foreach ($queryParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $result = $db->execute($stmt);
            $influencers = [];
            
            if ($result) {
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    $influencer = new Influencer();
                    $influencer->attributes = $row;
                    $influencers[] = $influencer;
                }
            }
            
            // 전체 결과 수 카운트
            $countSql = "SELECT COUNT(DISTINCT i.id) as total FROM influencers i";
            if (!empty($whereConditions)) {
                if ($categoryId) {
                    $countSql .= " JOIN influencer_categories ic ON i.id = ic.influencer_id";
                }
                $countSql .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $countStmt = $db->prepare($countSql);
            foreach ($queryParams as $key => $value) {
                if ($key != ':limit' && $key != ':offset') {
                    $countStmt->bindValue($key, $value);
                }
            }
            
            $countResult = $db->execute($countStmt);
            $totalCount = 0;
            
            if ($countResult && $row = $countResult->fetch(\PDO::FETCH_ASSOC)) {
                $totalCount = (int)$row['total'];
            }
            
            return [
                'success' => true,
                'message' => '인플루언서 목록을 성공적으로 조회했습니다.',
                'data' => [
                    'influencers' => $influencers,
                    'pagination' => [
                        'total' => $totalCount,
                        'per_page' => $limit,
                        'current_page' => $page,
                        'last_page' => ceil($totalCount / $limit),
                        'from' => $offset + 1,
                        'to' => min($offset + $limit, $totalCount)
                    ]
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '인플루언서 검색 중 오류가 발생했습니다: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
    
    /**
     * 인플루언서 데이터 유효성 검사
     * 
     * @param array $data 검사할 데이터
     * @param bool $isCreate 생성 모드인지 여부
     * @return array ['isValid' => bool, 'errors' => array]
     */
    private function validateInfluencerData(array $data, bool $isCreate = true): array
    {
        $validator = new \App\Utils\Validator($data);
        
        // 기본 필수 필드 검증
        $validator->required('name')
                 ->required('handle');
        
        // 플랫폼 검증
        if ($isCreate) {
            $validator->required('platform_id');
        }
        
        // 숫자 필드 검증
        if (isset($data['follower_count'])) {
            $validator->numeric('follower_count')
                     ->range('follower_count', 0, PHP_INT_MAX);
        }
        
        if (isset($data['engagement_rate'])) {
            $validator->numeric('engagement_rate')
                     ->range('engagement_rate', 0, 100);
        }
        
        return [
            'isValid' => !$validator->hasErrors(),
            'errors' => $validator->getErrors()
        ];
    }
    
    /**
     * 인플루언서 생성/업데이트 서비스
     * 
     * @param array $data 인플루언서 데이터
     * @return array 응답 데이터
     */
    public function saveInfluencer($data)
    {
        // 유효성 검증 추가
        $isCreate = !isset($data['id']);
        $validation = $this->validateInfluencerData($data, $isCreate);
        
        if (!$validation['isValid']) {
            return [
                'success' => false,
                'message' => '유효성 검사에 실패했습니다.',
                'code' => 422,
                'errors' => $validation['errors']
            ];
        }
        
        $db = DB::getInstance();
        $db->beginTransaction();
        
        try {
            // 플랫폼 확인 또는 생성
            $platform = null;
            if (isset($data['platform_name'])) {
                $platform = Platform::findByName($data['platform_name']);
                if (!$platform) {
                    $platform = new Platform();
                    $platform->name = $data['platform_name'];
                    $platform->website = $data['platform_url'] ?? '';
                    $platform->save();
                }
                $data['platform_id'] = $platform->id;
            }
            
            // 인플루언서 저장
            $influencer = null;
            if (isset($data['id'])) {
                $influencer = Influencer::find($data['id']);
                if (!$influencer) {
                    return [
                        'success' => false,
                        'message' => '업데이트할 인플루언서를 찾을 수 없습니다.',
                        'code' => 404
                    ];
                }
            }
            
            if (!$influencer) {
                $influencer = new Influencer();
            }
            
            // 기본 필드 설정
            $influencer->name = $data['name'] ?? '';
            $influencer->handle = $data['handle'] ?? '';
            $influencer->bio = $data['bio'] ?? '';
            $influencer->follower_count = $data['follower_count'] ?? 0;
            $influencer->engagement_rate = $data['engagement_rate'] ?? 0;
            $influencer->platform_id = $data['platform_id'] ?? null;
            $influencer->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
            $influencer->updated_at = date('Y-m-d H:i:s');
            
            $influencer->save();
            
            // 카테고리 처리
            if (isset($data['categories']) && is_array($data['categories'])) {
                // 기존 카테고리 연결 해제
                $sql = "DELETE FROM influencer_categories WHERE influencer_id = :influencer_id";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':influencer_id', $influencer->id);
                $db->execute($stmt);
                
                // 새 카테고리 연결
                foreach ($data['categories'] as $categoryName) {
                    $category = Category::findByName($categoryName);
                    if (!$category) {
                        $category = new Category();
                        $category->name = $categoryName;
                        $category->save();
                    }
                    
                    Category::linkToInfluencer($category->id, $influencer->id);
                }
            }
            
            $db->commit();
            
            return [
                'success' => true,
                'message' => '인플루언서가 성공적으로 저장되었습니다.',
                'data' => $influencer
            ];
        } catch (\Exception $e) {
            $db->rollback();
            return [
                'success' => false,
                'message' => '인플루언서 저장 중 오류가 발생했습니다: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
    
    /**
     * 인플루언서 삭제 서비스
     * 
     * @param int $id 삭제할 인플루언서 ID
     * @return array 응답 데이터
     */
    public function deleteInfluencer($id)
    {
        $db = DB::getInstance();
        $db->beginTransaction();
        
        try {
            // 인플루언서가 존재하는지 확인
            $influencer = Influencer::find($id);
            if (!$influencer) {
                return [
                    'success' => false,
                    'message' => '삭제할 인플루언서를 찾을 수 없습니다.',
                    'code' => 404
                ];
            }
            
            // 카테고리 연결 삭제
            $sql = "DELETE FROM influencer_categories WHERE influencer_id = :influencer_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':influencer_id', $id);
            $db->execute($stmt);
            
            // 인플루언서 삭제
            Influencer::delete($id);
            
            $db->commit();
            
            return [
                'success' => true,
                'message' => '인플루언서가 성공적으로 삭제되었습니다.'
            ];
        } catch (\Exception $e) {
            $db->rollback();
            return [
                'success' => false,
                'message' => '인플루언서 삭제 중 오류가 발생했습니다: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
    
    /**
     * 인플루언서 상세 정보 조회
     * 
     * @param int $id 조회할 인플루언서 ID
     * @return array 응답 데이터
     */
    public function getInfluencerDetail($id)
    {
        try {
            $influencer = Influencer::find($id);
            
            if (!$influencer) {
                return [
                    'success' => false,
                    'message' => '인플루언서를 찾을 수 없습니다.',
                    'code' => 404
                ];
            }
            
            $categories = $influencer->getCategories();
            $platform = $influencer->getPlatform();
            
            return [
                'success' => true,
                'message' => '인플루언서 정보를 성공적으로 조회했습니다.',
                'data' => [
                    'influencer' => $influencer,
                    'categories' => $categories,
                    'platform' => $platform
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '인플루언서 정보 조회 중 오류가 발생했습니다: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }
} 