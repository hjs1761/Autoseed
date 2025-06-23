<?php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Http\Request;
use App\Services\UserService;
use Exception;

/**
 * 회원 관리 컨트롤러
 */
class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        $this->userService = $this->initService(UserService::class);
    }

    /**
     * 회원 목록 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function index(Request $request)
    {
        try {
            // 관리자 권한 확인
            if(!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true) {
                $this->redirect('/dashboard', '관리자만 접근할 수 있습니다.', 'error');
                return;
            }

            // 사용자 목록 조회
            $users = $this->userService->getAllUsers();
            
            $data = [
                'title' => '회원 관리',
                'users' => $users
            ];
            
            $this->render('user/index.php', $data);
        } catch (Exception $e) {
            $this->handleErrorAndRender(
                $e, 
                '사용자 목록 조회', 
                '오류', 
                '사용자 목록을 조회하는 중 오류가 발생했습니다: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * 내 정보 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function mypage(Request $request)
    {
        try {
            // 세션에서 사용자 정보 가져오기
            $userId = $request->getSession('user_info.id');
            $user = $this->userService->getUserById($userId);
            
            if (!$user) {
                $this->redirect('/logout', '사용자 정보를 찾을 수 없습니다.', 'error');
                return;
            }
            
            $data = [
                'title' => '내 정보',
                'user' => $user
            ];
            
            $this->render('user/mypage.php', $data);
        } catch (Exception $e) {
            $this->handleErrorAndRender(
                $e, 
                '내 정보 조회', 
                '오류', 
                '사용자 정보를 조회하는 중 오류가 발생했습니다: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * 회원 상세 페이지
     * 
     * @param Request $request 요청 객체
     * @param int $id 회원 ID
     */
    public function show(Request $request, $id)
    {
        try {
            // 관리자 권한 확인
            if(!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true) {
                $this->redirect('/dashboard', '관리자만 접근할 수 있습니다.', 'error');
                return;
            }
            
            // 사용자 정보 조회
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                $this->redirect('/users', '사용자 정보를 찾을 수 없습니다.', 'error');
                return;
            }
            
            $data = [
                'title' => '회원 상세 정보',
                'user' => $user
            ];
            
            $this->render('user/show.php', $data);
        } catch (Exception $e) {
            $this->handleErrorAndRender(
                $e, 
                '사용자 상세 조회', 
                '오류', 
                '사용자 정보를 조회하는 중 오류가 발생했습니다: ' . $e->getMessage()
            );
        }
    }
}
