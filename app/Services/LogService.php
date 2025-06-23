<?php
namespace App\Services;

use App\Core\DB;
use App\Core\SQLValue;
use Monolog\Logger;
use Exception;

/**
 * 로그 서비스
 */
class LogService
{
    protected DB $db;
    protected ?Logger $logger;
    
    // 로그 타입 상수
    const TYPE_DEFAULT = 'DEFAULT';
    const TYPE_API = 'API';
    const TYPE_SYSTEM = 'SYSTEM';
    const TYPE_USER = 'USER';
    const TYPE_INFLUENCER = 'INFLUENCER';
    const TYPE_AUTH = 'AUTH';
    const TYPE_IMPORT = 'IMPORT';
    const TYPE_ERROR = 'ERROR';
    
    // 결과 상태 상수
    const RESULT_SUCCESS = 'SUCCESS';
    const RESULT_FAIL = 'FAIL'; 
    const RESULT_ERROR = 'ERROR';
    
    /**
     * 유효한 로그 타입 목록
     */
    const VALID_TYPES = [
        self::TYPE_DEFAULT,
        self::TYPE_API,
        self::TYPE_SYSTEM,
        self::TYPE_USER,
        self::TYPE_INFLUENCER,
        self::TYPE_AUTH,
        self::TYPE_IMPORT,
        self::TYPE_ERROR
    ];

    public function __construct(DB $db, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * 시스템 로그 기록 (BO_LOG 테이블)
     *
     * @param string $type 로그 타입
     * @param string $action 작업 내용
     * @param string $result 결과 상태
     * @param string $detail 상세 내용 
     * @param array|null $params 추가 파라미터
     * @param bool $silent 실패 시 예외 무시 여부
     * @return int|bool 로그 ID 또는 성공 여부
     */
    public function log($type, $action, $result, $detail, $params = null, $silent = true)
    {
        try {
            $insertData = [
                'user_id'        => $_SESSION['user_info']['id'] ?? '',
                'request_uri'    => $_SERVER['REQUEST_URI'] ?? '',
                'type'           => $type,
                'action'         => $action,
                'result'         => $result,
                'detail'         => $detail,
                'ip_addr'        => getClientIp(),
                'created_at'     => new SQLValue('NOW()'),
            ];

            if ($params) {
                // 트레이스 데이터 제한
                if (is_array($params) && isset($params['trace']) && is_string($params['trace'])) {
                    $params['trace'] = substr($params['trace'], 0, 500);
                }
                
                // 데이터 정규화
                $params = is_array($params) ? $this->normalizeData($params) : $params;
                $insertData['params'] = is_string($params) ? $params : json_encode($params, JSON_UNESCAPED_UNICODE);
            }

            return $this->db->insert('logs', $insertData);
        } catch (Exception $e) {
            if ($silent) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * API 호출 로그 기록 (API_LOG 테이블)
     *
     * @param string $endpoint API 엔드포인트
     * @param string $method HTTP 메서드
     * @param mixed $data 요청 데이터
     * @param mixed $response 응답 데이터
     * @param mixed $errorMsg 오류 메시지
     * @param string $userId 사용자 ID
     * @param bool $silent 실패 시 예외 무시 여부
     * @return int|bool 로그 ID 또는 성공 여부
     */
    public function apiLog($endpoint, $method, $data, $response, $errorMsg = null, $userId = null, $silent = true)
    {
        try {
            // 데이터 정규화
            $userId = $userId ?? ($_SESSION['user_info']['id'] ?? '');
            
            $insertData = [
                'user_id'         => $userId,
                'request_uri'     => $_SERVER['REQUEST_URI'] ?? '',
                'endpoint'        => $endpoint,
                'method'          => $method,
                'request_data'    => $this->normalizeData($data),
                'response'        => $this->normalizeData($response),
                'error_message'   => $errorMsg ?: new SQLValue('NULL'),
                'ip_addr'         => getClientIp(),
                'created_at'      => new SQLValue('NOW()'),
            ];

            return $this->db->insert('api_logs', $insertData);
        } catch (Exception $e) {
            if ($silent) {
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * 예외 로깅
     * 
     * @param string $type 로그 타입
     * @param string $action 수행한 작업
     * @param Exception $e 발생한 예외
     * @param array $context 추가 컨텍스트 데이터
     * @param bool $silent 실패 시 예외 무시 여부
     * @return bool 로깅 성공 여부
     */
    public function logException($type, $action, Exception $e, array $context = [], $silent = true)
    {
        // 기본 예외 정보
        $data = [
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 500)
        ];
        
        // 컨텍스트 병합
        $mergedData = array_merge($data, $context);
        
        // 로그 기록
        return $this->log(
            $type,
            $action,
            self::RESULT_ERROR,
            "Exception: " . $e->getMessage(),
            $mergedData,
            $silent
        );
    }
    
    /**
     * 서비스 오류 로깅
     * 
     * @param string $service 서비스 이름
     * @param string $action 작업 이름
     * @param Exception $e 발생한 예외
     * @param array $context 추가 컨텍스트 데이터
     * @param string $logType 로그 타입
     * @param bool $silent 실패 시 예외 무시 여부
     * @return bool 로깅 성공 여부
     */
    public function logServiceError($service, $action, Exception $e, array $context = [], $logType = self::TYPE_ERROR, $silent = true)
    {
        $fullAction = $service . ':' . $action;
        return $this->logException($logType, $fullAction, $e, $context, $silent);
    }
    
    /**
     * 성공 로그
     * 
     * @param string $type 로그 타입
     * @param string $action 작업 이름
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @param bool $silent 실패 시 예외 무시 여부
     * @return bool 로깅 성공 여부
     */
    public function logSuccess($type, $action, $message, array $data = [], $silent = true)
    {
        return $this->log($type, $action, self::RESULT_SUCCESS, $message, $data, $silent);
    }
    
    /**
     * 실패 로그
     * 
     * @param string $type 로그 타입
     * @param string $action 작업 이름
     * @param string $message 메시지
     * @param array $data 추가 데이터
     * @param bool $silent 실패 시 예외 무시 여부
     * @return bool 로깅 성공 여부
     */
    public function logFailure($type, $action, $message, array $data = [], $silent = true)
    {
        return $this->log($type, $action, self::RESULT_FAIL, $message, $data, $silent);
    }
    
    /**
     * 데이터 정규화
     * 
     * @param mixed $data 정규화할 데이터
     * @param int $maxSize 최대 데이터 크기
     * @return string JSON 인코딩된 데이터
     */
    public function normalizeData($data, $maxSize = 10000)
    {
        if (is_null($data)) {
            return '';
        }
        
        if (is_scalar($data)) {
            return (string)$data;
        }
        
        try {
            // 객체나 배열을 JSON으로 변환
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            // 최대 크기 제한
            if (strlen($json) > $maxSize) {
                return substr($json, 0, $maxSize) . '... [truncated]';
            }
            
            return $json;
        } catch (\Exception $e) {
            return 'Data normalization error: ' . $e->getMessage();
        }
    }
    
    /**
     * 로그 조회
     * 
     * @param int $id 로그 ID
     * @return array|null 로그 데이터
     */
    public function getLog(int $id): ?array
    {
        $result = $this->db->select('logs', ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * API 로그 조회
     * 
     * @param int $id 로그 ID
     * @return array|null 로그 데이터
     */
    public function getApiLog(int $id): ?array
    {
        $result = $this->db->select('api_logs', ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * 모든 로그 조회
     * 
     * @param array $params 검색 조건
     * @param int $page 페이지 번호
     * @param int $limit 페이지당 항목 수
     * @return array 로그 목록 및 페이지네이션 정보
     */
    public function getAllLogs(array $params = [], int $page = 1, int $limit = 10): array
    {
        return $this->getLogs('logs', $params, $page, $limit);
    }
    
    /**
     * 모든 API 로그 조회
     * 
     * @param array $params 검색 조건
     * @param int $page 페이지 번호
     * @param int $limit 페이지당 항목 수
     * @return array 로그 목록 및 페이지네이션 정보
     */
    public function getAllApiLogs(array $params = [], int $page = 1, int $limit = 10): array
    {
        return $this->getLogs('api_logs', $params, $page, $limit);
    }
    
    /**
     * 로그 조회 공통 메서드
     * 
     * @param string $table 테이블 이름
     * @param array $params 검색 조건
     * @param int $page 페이지 번호
     * @param int $limit 페이지당 항목 수
     * @param bool $countOnly 카운트만 반환할지 여부
     * @return array|int 로그 목록 및 페이지네이션 정보 또는 카운트
     */
    private function getLogs(string $table, array $params = [], int $page = 1, int $limit = 10, bool $countOnly = false)
    {
        // 검색 조건 생성
        list($where, $bindParams) = $this->buildWhereCondition($params);
        
        // 쿼리 기본 구성
        $sql = "FROM $table";
        
        // 조건 추가
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // 카운트만 필요한 경우
        if ($countOnly) {
            $countSql = "SELECT COUNT(*) as total $sql";
            $stmt = $this->db->prepare($countSql);
            
            foreach ($bindParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $result = $this->db->execute($stmt);
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            
            return (int)($row['total'] ?? 0);
        }
        
        // 페이지네이션 처리
        $offset = ($page - 1) * $limit;
        
        // 정렬 추가 (최신순)
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare("SELECT * $sql");
        
        // 바인딩 파라미터 추가
        foreach ($bindParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $result = $this->db->execute($stmt);
        $logs = [];
        
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $logs[] = $row;
        }
        
        // 전체 개수 조회
        $total = $this->getLogs($table, $params, $page, $limit, true);
        
        // 결과 반환
        return [
            'logs' => $logs,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * 로그 개수 조회
     * 
     * @param array $params 검색 조건
     * @return int 로그 개수
     */
    public function countLogs(array $params = []): int
    {
        return $this->getLogs('logs', $params, 1, 10, true);
    }
    
    /**
     * API 로그 개수 조회
     * 
     * @param array $params 검색 조건
     * @return int 로그 개수
     */
    public function countApiLogs(array $params = []): int
    {
        return $this->getLogs('api_logs', $params, 1, 10, true);
    }
    
    /**
     * 검색 조건 생성
     * 
     * @param array $params 검색 조건
     * @return array [where절 배열, 바인딩 파라미터 배열]
     */
    private function buildWhereCondition(array $params): array
    {
        $where = [];
        $bindParams = [];
        
        // 공통 필드
        $fields = [
            'type' => 'exact',
            'action' => 'like',
            'result' => 'exact',
            'user_id' => 'exact',
            'ip_addr' => 'exact'
        ];
        
        foreach ($fields as $field => $operator) {
            if (isset($params[$field]) && $params[$field] !== '') {
                $this->addFilterCondition($where, $field, $params[$field], $operator, $bindParams);
            }
        }
        
        // 키워드 검색 (detail 또는 params 필드)
        if (isset($params['keyword']) && $params['keyword'] !== '') {
            $keyword = $params['keyword'];
            $where[] = "(detail LIKE :keyword OR params LIKE :keyword)";
            $bindParams[':keyword'] = "%$keyword%";
        }
        
        // 날짜 범위 검색
        if (isset($params['start_date']) && $params['start_date'] !== '') {
            $where[] = "created_at >= :start_date";
            $bindParams[':start_date'] = $params['start_date'] . ' 00:00:00';
        }
        
        if (isset($params['end_date']) && $params['end_date'] !== '') {
            $where[] = "created_at <= :end_date";
            $bindParams[':end_date'] = $params['end_date'] . ' 23:59:59';
        }
        
        return [$where, $bindParams];
    }
    
    /**
     * 검색 조건 추가
     * 
     * @param array &$where 조건 배열
     * @param string $field 필드명
     * @param mixed $value 값
     * @param string $operator 연산자 (exact 또는 like)
     * @param array &$bindParams 바인딩 파라미터 배열
     */
    private function addFilterCondition(array &$where, string $field, $value, string $operator = 'like', array &$bindParams): void
    {
        $paramName = ":$field";
        
        if ($operator === 'like') {
            $where[] = "$field LIKE $paramName";
            $bindParams[$paramName] = "%$value%";
        } else {
            $where[] = "$field = $paramName";
            $bindParams[$paramName] = $value;
        }
    }
}
