<?php
namespace App\Controllers\Api;

use App\Core\BaseApiController;
use App\Core\Http\Request;
use App\Services\LogService;
use Exception;

/**
 * 로그 API 컨트롤러
 * 
 * BO, FO, API 로그 관련 API 요청을 처리하는 컨트롤러 클래스입니다.
 * 로그 목록 조회 및 상세 조회 기능을 제공합니다.
 */
class LogApiController extends BaseApiController
{
    /**
     * 로그 서비스 인스턴스
     * 
     * @var LogService
     */
    protected LogService $logService;

    /**
     * 생성자
     * 
     * @param mixed $db 데이터베이스 연결 객체 또는 null
     * @param mixed $logger 로거 객체 또는 null
     */
    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        $this->logService = $this->initService(LogService::class);
    }

    /**
     * BO 로그 목록 API (GET)
     * 
     * BO(Back Office) 로그 목록을 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @return void JSON 응답을 반환합니다.
     */
    public function boLogList(Request $request)
    {
        try {
            $this->processLogList($request, 'bo', 'bl_seq', 'BO 로그 목록 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'BO 로그 목록 조회', $e);
        }
    }
    
    /**
     * FO 로그 목록 API (GET)
     * 
     * FO(Front Office) 로그 목록을 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @return void JSON 응답을 반환합니다.
     */
    public function foLogList(Request $request)
    {
        try {
            $this->processLogList($request, 'fo', 'fl_seq', 'FO 로그 목록 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'FO 로그 목록 조회', $e);
        }
    }
    
    /**
     * API 로그 목록 API (GET)
     * 
     * API 호출 로그 목록을 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @return void JSON 응답을 반환합니다.
     */
    public function apiLogList(Request $request)
    {
        try {
            $this->processLogList($request, 'api', 'al_seq', 'API 로그 목록 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'API 로그 목록 조회', $e);
        }
    }
    
    /**
     * 로그 목록 조회 공통 처리
     * 
     * 로그 타입에 따른 목록 조회 로직의 공통 처리 메서드입니다.
     * 필터링, 정렬, 페이지네이션을 지원합니다.
     * 
     * @param Request $request 요청 객체
     * @param string $type 로그 유형 (bo, fo, api)
     * @param string $sortField 기본 정렬 필드
     * @param string $successMessage 성공 메시지
     * @return void
     */
    private function processLogList(Request $request, $type, $sortField, $successMessage)
    {
        // 필터 파라미터 처리
        $params = $this->getFilterParamsFromRequest($request);
        
        // 정렬 파라미터 처리
        [$sortField, $sortDirection] = $this->getSortParamsFromRequest($request, $sortField, 'desc');
        $params['sort_field'] = $sortField;
        $params['sort_direction'] = $sortDirection;
        
        // 페이지네이션 파라미터 처리
        [$page, $limit] = $this->getPaginationParamsFromRequest($request);
        
        // 로그 타입에 따른 서비스 메서드 매핑
        $logMethods = [
            'bo' => ['getAllBoLogs', 'countBoLogs'],
            'fo' => ['getAllFoLogs', 'countFoLogs'],
            'api' => ['getAllApiLogs', 'countApiLogs']
        ];
        
        // 로그 조회 및 응답
        $list = $this->logService->{$logMethods[$type][0]}($params, $page, $limit);
        $totalCount = $this->logService->{$logMethods[$type][1]}($params);
        
        $this->tabulatorResponse($list, $totalCount, $page, $limit, $successMessage);
    }
    
    /**
     * 요청 객체에서 필터 파라미터 추출
     *
     * @param Request $request 요청 객체
     * @return array 필터 파라미터
     */
    private function getFilterParamsFromRequest(Request $request)
    {
        $params = [];
        $allParams = $request->getQuery();
        
        // 기본 GET 파라미터 처리 (페이지네이션, 정렬 등 특수 파라미터 제외)
        foreach ($allParams as $key => $value) {
            if (in_array($key, ['page', 'limit', 'sort_by', 'sort_dir'])) {
                continue;
            }
            
            if (!empty($value)) {
                $params[$key] = $value;
            }
        }
        
        return $params;
    }
    
    /**
     * 요청 객체에서 정렬 파라미터 추출
     *
     * @param Request $request 요청 객체
     * @param string $defaultField 기본 정렬 필드
     * @param string $defaultDirection 기본 정렬 방향
     * @return array [필드, 방향] 정렬 파라미터
     */
    private function getSortParamsFromRequest(Request $request, $defaultField = '', $defaultDirection = 'desc')
    {
        $field = $request->getQuery('sort_by', $defaultField);
        $direction = $request->getQuery('sort_dir', $defaultDirection);
        
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = $defaultDirection;
        }
        
        return [$field, $direction];
    }
    
    /**
     * 요청 객체에서 페이지네이션 파라미터 추출
     *
     * @param Request $request 요청 객체
     * @param int $defaultLimit 기본 페이지 크기
     * @param int $maxLimit 최대 페이지 크기
     * @return array [페이지, 한 페이지당 항목 수] 페이지네이션 파라미터
     */
    private function getPaginationParamsFromRequest(Request $request, $defaultLimit = 10, $maxLimit = 100)
    {
        $page = max(1, intval($request->getQuery('page', 1)));
        $limit = max(1, min($maxLimit, intval($request->getQuery('limit', $defaultLimit))));
        
        return [$page, $limit];
    }
    
    /**
     * BO 로그 상세 API (GET)
     * 
     * BO(Back Office) 로그의 상세 정보를 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 조회할 로그 ID
     * @return void JSON 응답을 반환합니다.
     */
    public function boLogDetail(Request $request, $id)
    {
        try {
            $this->processLogDetail($request, 'bo', $id, 'BO 로그 상세 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'BO 로그 상세 조회', $e);
        }
    }
    
    /**
     * FO 로그 상세 API (GET)
     * 
     * FO(Front Office) 로그의 상세 정보를 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 조회할 로그 ID
     * @return void JSON 응답을 반환합니다.
     */
    public function foLogDetail(Request $request, $id)
    {
        try {
            $this->processLogDetail($request, 'fo', $id, 'FO 로그 상세 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'FO 로그 상세 조회', $e);
        }
    }
    
    /**
     * API 로그 상세 API (GET)
     * 
     * API 호출 로그의 상세 정보를 조회합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 조회할 로그 ID
     * @return void JSON 응답을 반환합니다.
     */
    public function apiLogDetail(Request $request, $id)
    {
        try {
            $this->processLogDetail($request, 'api', $id, 'API 로그 상세 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_DEFAULT, 'API 로그 상세 조회', $e);
        }
    }
    
    /**
     * 로그 상세 조회 공통 처리
     * 
     * 로그 타입에 따른 상세 조회 로직의 공통 처리 메서드입니다.
     * 
     * @param Request $request 요청 객체
     * @param string $type 로그 유형 (bo, fo, api)
     * @param int $id 로그 ID
     * @param string $successMessage 성공 메시지
     * @return void
     * @throws Exception 잘못된 파라미터가 전달되면 예외 발생
     */
    private function processLogDetail(Request $request, $type, $id, $successMessage)
    {
        if (!is_numeric($id)) {
            throw new Exception('잘못된 파라미터입니다.');
        }
        
        // 로그 타입에 따른 서비스 메서드 매핑
        $methodMap = [
            'bo' => 'getBoLog',
            'fo' => 'getFoLog',
            'api' => 'getApiLog'
        ];
        
        $log = $this->logService->{$methodMap[$type]}($id);
        
        if (empty($log)) {
            return $this->errorResponse('요청한 로그를 찾을 수 없습니다.', 404);
        }
        
        $this->successResponse($log, $successMessage);
    }
}