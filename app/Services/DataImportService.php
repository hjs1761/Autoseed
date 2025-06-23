<?php
/**
 * 파일: app/Services/DataImportService.php
 * 
 * 이 파일은 외부 소스로부터 인플루언서 데이터를 가져와 시스템에 임포트하는 서비스 클래스를 정의합니다.
 * API 요청으로부터 데이터를 받아 검증, 정규화하고 데이터베이스에 저장하는 기능을 제공합니다.
 * 중복 데이터 관리와 트랜잭션 처리를 담당합니다.
 * 
 * @package App\Services
 */

namespace App\Services;

use App\Core\DB;
use App\Models\Influencer;
use App\Models\Platform;
use App\Models\Category;

/**
 * 데이터 임포트 서비스 클래스
 * 
 * 외부 소스로부터 인플루언서 데이터를 가져와 시스템에 임포트합니다.
 */
class DataImportService
{
    private $influencerService;
    
    /**
     * 서비스 인스턴스를 초기화합니다.
     */
    public function __construct()
    {
        $this->influencerService = new InfluencerService();
    }
    
    /**
     * 외부 API로부터 받은 인플루언서 데이터를 임포트합니다.
     * 
     * @param array $data 인플루언서 데이터 배열
     * @return array 응답 데이터
     */
    public function importInfluencers($data)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => [
                'imported' => 0,
                'failed' => 0,
                'errors' => []
            ]
        ];
        
        if (!is_array($data)) {
            $result['message'] = '유효하지 않은 데이터 형식입니다.';
            return $result;
        }
        
        $db = DB::getInstance();
        $db->beginTransaction();
        
        try {
            foreach ($data as $item) {
                try {
                    // 필수 필드 검증
                    if (empty($item['name']) || empty($item['handle']) || empty($item['platform_name'])) {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = '필수 필드가 누락되었습니다: ' . json_encode($item);
                        continue;
                    }
                    
                    // 중복 검사 (동일 플랫폼의 동일 핸들인 경우)
                    $platform = Platform::findByName($item['platform_name']);
                    if ($platform) {
                        $existingInfluencers = Influencer::all([
                            'handle' => $item['handle'],
                            'platform_id' => $platform->id
                        ]);
                        
                        if (!empty($existingInfluencers)) {
                            // 업데이트
                            $item['id'] = $existingInfluencers[0]->id;
                        }
                    }
                    
                    $saveResult = $this->influencerService->saveInfluencer($item);
                    if ($saveResult['success']) {
                        $result['data']['imported']++;
                    } else {
                        $result['data']['failed']++;
                        $result['data']['errors'][] = $saveResult['message'];
                    }
                } catch (\Exception $e) {
                    $result['data']['failed']++;
                    $result['data']['errors'][] = '인플루언서 저장 중 오류 발생: ' . $e->getMessage();
                }
            }
            
            $db->commit();
            
            $result['success'] = true;
            $result['message'] = $result['data']['imported'] . '개의 인플루언서가 성공적으로 임포트되었습니다.';
            
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            $result['message'] = '트랜잭션 오류: ' . $e->getMessage();
            $result['data']['errors'][] = $result['message'];
            return $result;
        }
    }
    
    /**
     * 데이터를 검증하고 정규화합니다.
     * 
     * @param array $data 원본 데이터 배열
     * @return array 정규화된 데이터 배열
     */
    public function validateAndNormalizeData($data)
    {
        $validatedData = [];
        
        foreach ($data as $item) {
            // 필수 필드 검증
            if (empty($item['name']) || empty($item['handle']) || empty($item['platform_name'])) {
                continue;
            }
            
            // 데이터 정규화
            $normalizedItem = [
                'name' => trim($item['name']),
                'handle' => trim($item['handle']),
                'platform_name' => trim($item['platform_name']),
                'bio' => isset($item['bio']) ? trim($item['bio']) : '',
                'follower_count' => isset($item['follower_count']) ? (int)$item['follower_count'] : 0,
                'engagement_rate' => isset($item['engagement_rate']) ? (float)$item['engagement_rate'] : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 카테고리 처리
            if (isset($item['categories']) && is_array($item['categories'])) {
                $normalizedItem['categories'] = array_map('trim', $item['categories']);
            }
            
            $validatedData[] = $normalizedItem;
        }
        
        return $validatedData;
    }
    
    /**
     * API 요청으로부터 데이터를 처리합니다.
     * 
     * @param string $request JSON 형식의 요청 데이터
     * @return array 응답 데이터
     */
    public function processApiRequest($request)
    {
        $data = json_decode($request, true);
        
        if (!$data) {
            return [
                'success' => false,
                'message' => '유효하지 않은 JSON 데이터입니다.',
                'code' => 400
            ];
        }
        
        $validatedData = $this->validateAndNormalizeData($data);
        
        if (empty($validatedData)) {
            return [
                'success' => false,
                'message' => '유효한 데이터가 없습니다.',
                'code' => 400
            ];
        }
        
        $importResult = $this->importInfluencers($validatedData);
        
        return [
            'success' => $importResult['success'],
            'message' => $importResult['message'],
            'data' => [
                'imported' => $importResult['data']['imported'],
                'failed' => $importResult['data']['failed'],
                'errors' => $importResult['data']['errors']
            ]
        ];
    }
} 