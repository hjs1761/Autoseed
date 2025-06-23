<?php
// app/Services/UserService.php
namespace App\Services;

use App\Core\DB;
use App\Core\SQLValue;
use Exception;
use Monolog\Logger;

/**
 * 회원 관리 서비스
 */
class UserService
{
    protected DB $db;
    protected ?Logger $logger;
    protected LogService $logService;

    /**
     * 회원 상태값 상수
     */
    const STATUS_ACTIVE = 'active';       // 활성
    const STATUS_INACTIVE = 'inactive';   // 비활성
    const STATUS_DELETED = 'deleted';     // 삭제됨
    
    /**
     * 유효한 회원 상태값 목록
     */
    const VALID_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_DELETED
    ];

    public function __construct(DB $db, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->logService = new LogService($db, $logger);
    }

    /**
     * 회원 목록 조회
     * 
     * @param array $params 검색 조건
     * @param int $page 페이지 번호
     * @param int $limit 페이지당 항목 수
     * @return array 회원 목록과 페이징 정보
     */
    public function getAllUsers(array $params = [], int $page = 1, int $limit = 10): array
    {
        // 관리자 권한 체크
        if(!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true) {
            throw new Exception('권한이 없습니다.');
        }

        // 기본 조건
        $where = [];
        
        // 검색 조건 추가
        if (!empty($params['email'])) {
            $where['email LIKE'] = "%{$params['email']}%";
        }
        
        if (!empty($params['name'])) {
            $where['name LIKE'] = "%{$params['name']}%";
        }
        
        if (!empty($params['status'])) {
            $where['status'] = $params['status'];
        }

        // 페이지네이션 처리
        $offset = ($page - 1) * $limit;
        
        $users = $this->db->select(
            'users',
            $where,
            ['*'],
            [
                'order_by' => 'id DESC',
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
        // 전체 개수 구하기
        $total = $this->getUserCount($params);
        
        return [
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * 총 회원 수 조회
     * 
     * @param array $params 검색 조건
     * @return int 총 회원 수
     */
    public function getUserCount(array $params = []): int
    {
        // 기본 조건
        $where = [];
        
        // 검색 조건 추가
        if (!empty($params['email'])) {
            $where['email LIKE'] = "%{$params['email']}%";
        }
        
        if (!empty($params['name'])) {
            $where['name LIKE'] = "%{$params['name']}%";
        }
        
        if (!empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        
        $result = $this->db->select('users', $where, ['COUNT(*) as total']);
        return (int)($result[0]['total'] ?? 0);
    }
    
    /**
     * 회원 상세 조회 (ID로)
     * 
     * @param int $id 회원 ID
     * @return array|null 회원 상세 정보
     * @throws Exception 데이터 없을 경우
     */
    public function getUserById(int $id): ?array
    {
        if (!is_numeric($id)) {
            throw new Exception('잘못된 파라미터입니다.');
        }

        $user = $this->db->select(
            'users',
            ['id' => $id]
        )[0] ?? null;
        
        if (empty($user)) {
            return null;
        }
        
        return $user;
    }
    
    /**
     * 이메일로 회원 조회
     * 
     * @param string $email 이메일
     * @return array|null 회원 정보
     */
    public function getUserByEmail(string $email): ?array
    {
        if (empty($email)) {
            return null;
        }
        
        $user = $this->db->select(
            'users',
            ['email' => $email]
        )[0] ?? null;
        
        return $user;
    }
    
    /**
     * 회원 등록
     * 
     * @param array $data 회원 데이터
     * @return int 생성된 회원 ID
     * @throws Exception 유효성 검사 실패 시
     */
    public function createUser(array $data): int
    {
        try {
            // 데이터 유효성 검사
            $this->validateUserData($data);
            
            // 이메일 중복 확인
            $existingUser = $this->getUserByEmail($data['email']);
            if ($existingUser) {
                throw new Exception('이미 사용중인 이메일입니다.');
            }
            
            // 비밀번호 암호화
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // 생성 시간 추가
            $data['created_at'] = new SQLValue('NOW()');
            $data['updated_at'] = new SQLValue('NOW()');
            
            // 기본 상태 설정
            $data['status'] = $data['status'] ?? self::STATUS_ACTIVE;
            
            // 기본 관리자 여부 설정
            $data['is_admin'] = $data['is_admin'] ?? false;
            
            // 데이터 삽입
            $userId = $this->db->insert('users', $data);
            
            // 로그 기록
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'user.create',
                '사용자 생성: ' . $data['email'],
                ['user_id' => $userId, 'email' => $data['email']]
            );
            
            return $userId;
        } catch (Exception $e) {
            $this->logService->logException(
                LogService::TYPE_USER,
                'user.create.error',
                $e,
                ['data' => $data]
            );
            throw $e;
        }
    }
    
    /**
     * 회원 정보 수정
     * 
     * @param int $id 회원 ID
     * @param array $data 수정할 데이터
     * @return bool 성공 여부
     * @throws Exception 유효성 검사 실패 시
     */
    public function updateUser(int $id, array $data): bool
    {
        try {
            // 회원 존재 확인
            $user = $this->getUserById($id);
            if (!$user) {
                throw new Exception('존재하지 않는 회원입니다.');
            }
            
            // 권한 확인 (본인 또는 관리자만 수정 가능)
            if ($_SESSION['user_info']['id'] != $id && 
                (!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true)) {
                throw new Exception('수정 권한이 없습니다.');
            }
            
            // 비밀번호가 있는 경우 암호화
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                // 비밀번호가 없으면 수정하지 않음
                unset($data['password']);
            }
            
            // 수정 시간 업데이트
            $data['updated_at'] = new SQLValue('NOW()');
            
            // 데이터 수정
            $success = $this->db->update('users', $data, ['id' => $id]);
            
            // 로그 기록
            $logData = array_merge(['id' => $id], $data);
            unset($logData['password']); // 비밀번호는 로그에 남기지 않음
            
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'user.update',
                '사용자 정보 수정: ID ' . $id,
                $logData
            );
            
            return $success;
        } catch (Exception $e) {
            $this->logService->logException(
                LogService::TYPE_USER,
                'user.update.error',
                $e,
                ['id' => $id, 'data' => $data]
            );
            throw $e;
        }
    }
    
    /**
     * 회원 삭제 (상태 변경)
     * 
     * @param int $id 회원 ID
     * @return bool 성공 여부
     * @throws Exception
     */
    public function deleteUser(int $id): bool
    {
        try {
            // 회원 존재 확인
            $user = $this->getUserById($id);
            if (!$user) {
                throw new Exception('존재하지 않는 회원입니다.');
            }
            
            // 관리자 권한 확인
            if (!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true) {
                throw new Exception('삭제 권한이 없습니다.');
            }
            
            // 상태 변경으로 처리 (실제 삭제 X)
            $data = [
                'status' => self::STATUS_DELETED,
                'updated_at' => new SQLValue('NOW()')
            ];
            
            $success = $this->db->update('users', $data, ['id' => $id]);
            
            // 로그 기록
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'user.delete',
                '사용자 삭제: ID ' . $id,
                ['id' => $id, 'email' => $user['email']]
            );
            
            return $success;
        } catch (Exception $e) {
            $this->logService->logException(
                LogService::TYPE_USER,
                'user.delete.error',
                $e,
                ['id' => $id]
            );
            throw $e;
        }
    }
    
    /**
     * 로그인 인증
     * 
     * @param string $email 이메일
     * @param string $password 비밀번호
     * @return array|null 회원 정보
     * @throws Exception 인증 실패 시
     */
    public function authenticate(string $email, string $password): ?array
    {
        try {
            // 이메일로 회원 조회
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                throw new Exception('존재하지 않는 사용자입니다.');
            }
            
            // 상태 확인
            if ($user['status'] !== self::STATUS_ACTIVE) {
                throw new Exception('비활성화된 계정입니다.');
            }
            
            // 비밀번호 확인
            if (!password_verify($password, $user['password'])) {
                // 로그인 실패 로그
                $this->logService->logFailure(
                    LogService::TYPE_AUTH,
                    'user.login.fail',
                    '로그인 실패: 비밀번호 불일치',
                    ['email' => $email]
                );
                
                throw new Exception('비밀번호가 일치하지 않습니다.');
            }
            
            // 마지막 로그인 시간 업데이트
            $this->db->update('users', 
                ['last_login_at' => new SQLValue('NOW()')], 
                ['id' => $user['id']]
            );
            
            // 로그인 성공 로그
            $this->logService->logSuccess(
                LogService::TYPE_AUTH,
                'user.login.success',
                '로그인 성공',
                ['id' => $user['id'], 'email' => $user['email']]
            );
            
            // 세션에 필요한 정보만 필터링하여 반환
            $sessionData = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'is_admin' => (bool)$user['is_admin']
            ];
            
            return $sessionData;
        } catch (Exception $e) {
            $this->logService->logException(
                LogService::TYPE_AUTH,
                'user.login.error',
                $e,
                ['email' => $email]
            );
            throw $e;
        }
    }
    
    /**
     * 회원 데이터 유효성 검사
     * 
     * @param array $data 검사할 데이터
     * @param bool $isCreate 생성 모드인지 여부
     * @throws Exception 유효성 검사 실패 시
     */
    protected function validateUserData(array $data, bool $isCreate = true): void
    {
        $validator = new \App\Utils\Validator($data);
        
        if ($isCreate) {
            $validator->required('email')
                     ->required('password')
                     ->required('name')
                     ->email('email')
                     ->minLength('password', 8);
        }
        
        if (isset($data['status'])) {
            $validator->in('status', self::VALID_STATUSES);
        }
        
        if ($validator->hasErrors()) {
            throw new Exception($validator->getFirstError());
        }
    }
}
