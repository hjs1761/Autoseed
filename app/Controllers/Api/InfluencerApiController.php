<?php
/**
 * 파일: app/Controllers/Api/InfluencerApiController.php
 * 
 * 이 파일은 인플루언서 데이터 관리를 위한 API 컨트롤러 클래스를 정의합니다.
 * RESTful API 엔드포인트를 제공하여 외부 시스템에서 인플루언서 데이터를 조회, 생성, 수정, 삭제하는 기능을 제공합니다.
 * 인플루언서 데이터 임포트 기능도 포함합니다.
 * 
 * @package App\Controllers\Api
 */

namespace App\Controllers\Api;

use App\Core\BaseApiController;
use App\Core\Http\Request;
use App\Services\InfluencerService;
use App\Services\DataImportService;

/**
 * 인플루언서 API 컨트롤러 클래스
 * 
 * 인플루언서 데이터 관리를 위한 API 엔드포인트를 제공합니다.
 */
class InfluencerApiController extends BaseApiController
{
    private $influencerService;
    private $dataImportService;
    
    /**
     * 컨트롤러 인스턴스를 초기화합니다.
     * 
     * @param mixed $db 데이터베이스 객체
     * @param mixed $logger 로거 객체
     */
    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        $this->influencerService = new InfluencerService();
        $this->dataImportService = new DataImportService();
    }
    
    /**
     * 인플루언서 목록을 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @return array 응답 데이터
     */
    public function index(Request $request)
    {
        $params = $this->getQueryParamsFromRequest($request);
        return $this->influencerService->search($params);
    }
    
    /**
     * 인플루언서 상세 정보를 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 인플루언서 ID
     * @return array 응답 데이터
     */
    public function show(Request $request, $id)
    {
        return $this->influencerService->getInfluencerDetail($id);
    }
    
    /**
     * 인플루언서를 생성하거나 업데이트합니다.
     * 
     * @param Request $request 요청 객체
     * @return array 응답 데이터
     */
    public function store(Request $request)
    {
        $data = $this->getRequestDataFromRequest($request);
        
        // 헬퍼 함수를 사용한 유효성 검증
        $validation = validate($data, [
            'name' => ['required'],
            'handle' => ['required'],
            'platform_id' => ['required', 'numeric'],
            'follower_count' => ['numeric', ['range', 0, PHP_INT_MAX]],
            'engagement_rate' => ['numeric', ['range', 0, 100]]
        ]);
        
        // 오류가 있으면 JSON 응답 반환 후 스크립트 종료
        respondValidationErrors($validation);
        
        // 유효성 검증 통과 후 서비스 호출
        return $this->influencerService->saveInfluencer($data);
    }
    
    /**
     * 인플루언서를 삭제합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 인플루언서 ID
     * @return array 응답 데이터
     */
    public function delete(Request $request, $id)
    {
        return $this->influencerService->deleteInfluencer($id);
    }
    
    /**
     * 외부 데이터를 임포트합니다.
     * 
     * @param Request $request 요청 객체
     * @return array 응답 데이터
     */
    public function import(Request $request)
    {
        $rawData = $request->getJson();
        
        if (empty($rawData)) {
            return [
                'success' => false,
                'message' => '요청 본문이 비어 있습니다.',
                'code' => 400
            ];
        }
        
        return $this->dataImportService->processApiRequest($rawData);
    }
    
    /**
     * 요청 객체에서 검색 파라미터를 추출합니다.
     * 
     * @param Request $request 요청 객체
     * @return array 검색 파라미터
     */
    private function getQueryParamsFromRequest(Request $request)
    {
        return [
            'keyword' => $request->getQuery('keyword', ''),
            'platform_id' => $request->getQuery('platform_id'),
            'category_id' => $request->getQuery('category_id'),
            'page' => (int)$request->getQuery('page', 1),
            'limit' => (int)$request->getQuery('limit', 20)
        ];
    }
    
    /**
     * 요청 객체에서 요청 데이터를 추출합니다.
     * 
     * @param Request $request 요청 객체
     * @return array 요청 데이터
     */
    private function getRequestDataFromRequest(Request $request)
    {
        $jsonData = $request->getJson();
        $postData = $request->getPost();
        
        // JSON 데이터와 POST 데이터 병합
        return array_merge($postData, $jsonData);
    }
} 