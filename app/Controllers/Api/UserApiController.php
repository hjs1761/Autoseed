<?php
namespace App\Controllers\Api;

use App\Core\BaseApiController;
use App\Core\Http\Request;
use App\Services\UserService;
use App\Services\LogService;
use Exception;

/**
 * 회원 관리 API 컨트롤러
 */
class UserApiController extends BaseApiController
{
    protected UserService $userService;

    /**
     * 생성자: BaseApiController의 생성자 호출 후 UserService 인스턴스 생성
     *
     * @param mixed $db 데이터베이스 객체
     * @param mixed $logger 로거 객체
     */
    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        $this->userService = $this->initService(UserService::class);
    }

    /**
     * 회원 목록 API (GET)
     * 
     * @param Request $request 요청 객체
     */
    public function list(Request $request)
    {
        try {
            $params = [
                'user_id' => $request->getQuery('user_id'),
                'user_name' => $request->getQuery('user_name'),
                'user_type' => $request->getQuery('user_type')
            ];
            
            [$page, $limit] = $this->getPaginationParams();
            
            $list = $this->userService->getAllUsers($params, $page, $limit);
            $totalCount = $this->userService->getUserCount($params);
            
            $this->successResponse([
                'list' => $list,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total_count' => $totalCount,
                    'total_pages' => ceil($totalCount / $limit)
                ]
            ], '회원 목록을 성공적으로 조회했습니다.');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_USER, '목록 조회', $e);
        }
    }
    
    /**
     * 회원 상세 API (GET)
     * 
     * @param Request $request 요청 객체
     * @param int $id 회원 ID
     */
    public function detail(Request $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                throw new Exception('잘못된 파라미터입니다.');
            }
            
            $user = $this->userService->getUser($id);
            
            $this->successResponse($user, '회원 상세 조회 성공');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_USER, '상세 조회', $e);
        }
    }
    
    /**
     * 회원 등록 API (POST)
     * 
     * @param Request $request 요청 객체
     */
    public function insert(Request $request)
    {
        try {
            if(!isAdmin()) {
                return $this->errorResponse('권한이 없습니다.', 403);
            }
            
            $params = $this->getJsonRequest([
                'mall_id', 'user_id', 'user_name', 'user_type'
            ]);
            
            $params['shop_no'] = $params['shop_no'] ?? 1;
            
            $userSeq = $this->userService->insertUser($params);
            
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'INSERT',
                '회원 등록 성공',
                ['id' => $userSeq]
            );
            
            $this->successResponse(['ui_seq' => $userSeq], '회원이 등록되었습니다.', 201);
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_USER, '등록', $e);
        }
    }
    
    /**
     * 회원 수정 API (PUT)
     * 
     * @param Request $request 요청 객체
     * @param int $id 회원 ID
     */
    public function update(Request $request, $id)
    {
        try {
            if(!isAdmin()) {
                return $this->errorResponse('권한이 없습니다.', 403);
            }
            
            $params = $this->getJsonRequest([
                'user_name', 'user_type'
            ]);
            
            $user = $this->userService->getUser($id);
            
            if (empty($user)) {
                return $this->errorResponse('잘못된 요청입니다.', 404);
            }
            
            $result = $this->userService->updateUser($id, $params);
            
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'UPDATE',
                '회원 수정 성공',
                ['ui_seq' => $id]
            );
            
            $this->successResponse(null, '회원 정보가 수정되었습니다.');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_USER, '수정', $e);
        }
    }
    
    /**
     * 회원 삭제 API (DELETE)
     * 
     * @param Request $request 요청 객체
     * @param int $id 회원 ID
     */
    public function delete(Request $request, $id)
    {
        try {
            if(!isAdmin()) {
                return $this->errorResponse('권한이 없습니다.', 403);
            }
            
            $user = $this->userService->getUser($id);
            
            if (empty($user)) {
                return $this->errorResponse('잘못된 요청입니다.', 404);
            }
            
            $result = $this->userService->deleteUser($id);
            
            $this->logService->logSuccess(
                LogService::TYPE_USER,
                'DELETE',
                '회원 삭제',
                ['ui_seq' => $id]
            );
            
            $this->successResponse(null, '회원이 삭제되었습니다.');
        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_USER, '삭제', $e);
        }
    }

    /**
     * 담당자 정보 수정 API
     * 
     * @param Request $request 요청 객체
     */
    public function updateManagerInfo(Request $request)
    {
        try {
            $id = $_SESSION['user_info']['ui_seq'];

            $params = $this->getJsonRequest([
                'manager_name', 'manager_phone', 'manager_email', 'agree_service', 'agree_privacy'
            ]);

            $params['manager_phone'] = preg_replace('/[^0-9]/', '', $params['manager_phone']);

            $this->successResponse($params, '담당자 정보가 수정되었습니다.');
            exit;
            
            $result = $this->userService->updateManagerInfo($id, $params);
            
            $this->logService->logSuccess(
                LogService::TYPE_MANAGER,
                'UPDATE',
                '담당자 정보 수정 성공',
                ['ui_seq' => $id]
            );

            $this->successResponse(null, '담당자 정보가 수정되었습니다.');

        } catch (Exception $e) {
            $this->handleApiError(LogService::TYPE_MANAGER, '수정', $e);
        }
    }
}
