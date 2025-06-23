<?php
namespace App\Core;

use App\Core\BaseController;
use App\Core\Http\Request;
use App\Services\LogService;
use Exception;

/**
 * API 컨트롤러 기본 클래스
 * JSON 응답 포맷팅, 에러 처리, 요청 검증 기능 제공
 */
class BaseApiController extends BaseController
{
    /**
     * 요청 객체
     * 
     * @var Request
     */
    protected $request;
    
    /**
     * 생성자
     * 
     * @param mixed $db 데이터베이스 객체 또는 null
     * @param mixed $logger 로거 객체 또는 null
     */
    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        
        // Request 객체 초기화
        $this->request = new Request();
        
        // 보안 헤더 설정
        header('Content-Type: application/json; charset=utf-8');
        // CSRF 보호 헤더 (Cross-Site Request Forgery)
        header('X-Content-Type-Options: nosniff');
        // XSS 보호 헤더 (Cross-Site Scripting)
        header('X-XSS-Protection: 1; mode=block');
        // 클릭재킹 방지 (Clickjacking)
        header('X-Frame-Options: DENY');
    }

    /**
     * 에러 응답 반환 - 단순히 HTTP 응답으로 에러 정보 반환 (로깅 없음)
     *
     * @param string $message 에러 메시지
     * @param int $code HTTP 상태 코드
     * @param array $errors 상세 오류 정보
     */
    protected function errorResponse($message, $code = 400, $errors = [])
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 성공 응답 반환
     *
     * @param mixed $data 반환할 데이터
     * @param string $message 성공 메시지
     * @param int $code HTTP 상태 코드
     */
    protected function successResponse($data = null, $message = '', $code = 200)
    {
        http_response_code($code);
        $response = [
            'success' => true,
            'data'   => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($message)) {
            $response['message'] = $message;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * JSON 요청 데이터 파싱 및 검증
     * 
     * @param array $requiredFields 필수 필드 목록
     * @return array 파싱된 요청 데이터
     * @throws Exception 파싱 실패 시
     */
    protected function getJsonRequest(array $requiredFields = [])
    {
        $data = $this->request->getJson();
        
        if (empty($data)) {
            throw new Exception('요청 본문이 비어있습니다.');
        }
        
        // 필수 필드 검증
        if (!empty($requiredFields)) {
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception('필수 필드가 누락되었습니다: ' . implode(', ', $missingFields));
            }
        }
        
        return $data;
    }
    
    /**
     * 페이지네이션 파라미터 처리
     * 
     * @param int $defaultLimit 기본 페이지 크기
     * @param int $maxLimit 최대 페이지 크기
     * @return array [page, limit] 배열
     */
    protected function getPaginationParams($defaultLimit = 10, $maxLimit = 100)
    {
        $page = max(1, intval($this->request->getQuery('page', 1)));
        $limit = max(1, min($maxLimit, intval($this->request->getQuery('limit', $defaultLimit))));
        
        return [$page, $limit];
    }

    /**
     * 필터 파라미터 처리
     * 
     * @return array 처리된 필터 조건
     */
    protected function getFilterParams()
    {
        $params = [];
        $allParams = $this->request->getQuery();
        
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
     * 정렬 파라미터 처리
     * 
     * @param string $defaultField 기본 정렬 필드
     * @param string $defaultDirection 기본 정렬 방향 (asc 또는 desc)
     * @return array [field, direction] 배열
     */
    protected function getSortParams($defaultField = '', $defaultDirection = 'desc')
    {
        $field = $this->request->getQuery('sort_by', $defaultField);
        $direction = $this->request->getQuery('sort_dir', $defaultDirection);
        
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = $defaultDirection;
        }
        
        return [$field, $direction];
    }

    /**
     * 페이지네이션 응답 포맷팅
     * 
     * @param array $data 데이터 목록
     * @param int $totalCount 전체 항목 수
     * @param int $page 현재 페이지
     * @param int $limit 페이지당 항목 수
     * @param string $message 성공 메시지
     * @return void
     */
    protected function paginationResponse(array $data, int $totalCount, int $page, int $limit, string $message = '')
    {
        $pagination = [
            'page' => $page,
            'per_page' => $limit,
            'total' => $totalCount,
            'total_pages' => ceil($totalCount / $limit)
        ];
        
        $responseData = [
            'items' => $data,
            'pagination' => $pagination
        ];
        
        $this->successResponse($responseData, $message);
    }

    /**
     * WHERE 조건 생성 (SQL 조건 및 바인딩 매개변수)
     * 
     * @param array $params 검색 매개변수
     * @param array $exactFields 정확히 일치해야 하는 필드 목록
     * @param array $likeFields LIKE 검색을 사용할 필드 목록
     * @param array $dateRange 날짜 범위 검색 필드 목록 ['field' => ['start_param', 'end_param']]
     * @return array [$whereClause, $bindParams]
     */
    protected function buildWhereConditions(array $params, array $exactFields = [], array $likeFields = [], array $dateRange = [])
    {
        $whereConditions = [];
        $bindParams = [];
        
        // 정확한 일치 조건
        foreach ($exactFields as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $whereConditions[] = "$field = :$field";
                $bindParams[":$field"] = $params[$field];
            }
        }
        
        // LIKE 조건
        foreach ($likeFields as $field) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $whereConditions[] = "$field LIKE :$field";
                $bindParams[":$field"] = '%' . $params[$field] . '%';
            }
        }
        
        // 날짜 범위 조건
        foreach ($dateRange as $field => $paramNames) {
            [$startParam, $endParam] = $paramNames;
            
            if (isset($params[$startParam]) && $params[$startParam] !== '' && validateDate($params[$startParam], 'Y-m-d')) {
                $whereConditions[] = "$field >= :$startParam";
                $bindParams[":$startParam"] = $params[$startParam] . ' 00:00:00';
            }
            
            if (isset($params[$endParam]) && $params[$endParam] !== '' && validateDate($params[$endParam], 'Y-m-d')) {
                $whereConditions[] = "$field <= :$endParam";
                $bindParams[":$endParam"] = $params[$endParam] . ' 23:59:59';
            }
        }
        
        // WHERE 절 생성
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        return [$whereClause, $bindParams];
    }

    /**
     * API 에러 처리 및 응답
     * 
     * @param string $module 모듈 이름
     * @param string $action 액션 이름
     * @param Exception $e 발생한 예외
     * @param int $code HTTP 상태 코드
     */
    protected function handleApiError($module, $action, Exception $e, $code = 400)
    {
        $message = $e->getMessage() ?: '알 수 없는 오류가 발생했습니다.';
        
        // 로깅
        if ($this->logger) {
            $logData = [
                'module' => $module,
                'action' => $action,
                'error_message' => $message,
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $this->request->all()
            ];
            $this->logger->error("API 오류: $action", $logData);
        }
        
        $this->errorResponse($message, $code);
    }
    
    /**
     * Tabulator 형식의 응답 반환
     * 
     * @param array $data 데이터 목록
     * @param int $totalCount 전체 항목 수
     * @param int $page 현재 페이지
     * @param int $limit 페이지당 항목 수
     * @param string $message 성공 메시지
     * @return void
     */
    protected function tabulatorResponse(array $data, int $totalCount, int $page, int $limit, string $message = '')
    {
        http_response_code(200);
        
        $response = [
            'success' => true,
            'last_page' => ceil($totalCount / $limit),
            'data' => $data,
            'total' => $totalCount
        ];
        
        if (!empty($message)) {
            $response['message'] = $message;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

