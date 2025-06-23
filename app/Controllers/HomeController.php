<?php
// app/Controllers/HomeController.php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Http\Request;
use App\Core\SQLValue;
use App\Services\UserService;
use App\Services\LogService;
use App\Core\DB;
use Exception;

/**
 * 메인 홈 컨트롤러
 * 로그인, 회원가입, 대시보드 렌더링 담당
 */
class HomeController extends BaseController
{
    protected UserService $userService;

    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
        $this->userService = $this->initService(UserService::class);
    }

    /**
     * 메인 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function index(Request $request)
    {

        
        // exit;
        // 이미 로그인되어 있으면 대시보드로 리다이렉트
        if (checkSession()) {
            $this->redirect('/dashboard');
            return;
        }

        // 메인 페이지 렌더링
        $this->render('index.php', [
            'title' => '인플루언서 관리 솔루션'
        ]);
    }

    /**
     * 로그인 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function login(Request $request)
    {
        // 이미 로그인되어 있으면 대시보드로 리다이렉트
        if (checkSession()) {
            $this->redirect('/dashboard');
            return;
        }

        // 로그인 페이지 렌더링
        $this->render('auth/login.php', [
            'title' => '로그인'
        ], true);
    }

    /**
     * 회원가입 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function register(Request $request)
    {
        // 이미 로그인되어 있으면 대시보드로 리다이렉트
        if (checkSession()) {
            $this->redirect('/dashboard');
            return;
        }

        // 회원가입 페이지 렌더링
        $this->render('auth/register.php', [
            'title' => '회원가입'
        ], true);
    }

    /**
     * 로그아웃 처리
     * 
     * @param Request $request 요청 객체
     */
    public function logout(Request $request)
    {
        session_unset();
        session_destroy();
        
        $this->redirect('/login', '로그아웃 되었습니다.', 'info');
    }

    /**
     * 대시보드 페이지
     * 
     * @param Request $request 요청 객체
     */
    public function dashboard(Request $request)
    {
        try {
            // 사용자 정보 조회
            $userId = $request->getSession('user_info.id');
            $user = $this->userService->getUserById($userId);
            
            if (!$user) {
                $this->redirect('/logout', '사용자 정보를 찾을 수 없습니다.', 'error');
                return;
            }
            
            // 통계 데이터 가져오기
            $stats = [
                'total_influencers' => $this->db->query("SELECT COUNT(*) as count FROM influencers")[0]['count'] ?? 0,
                'total_platforms' => $this->db->query("SELECT COUNT(*) as count FROM platforms")[0]['count'] ?? 0,
                'total_categories' => $this->db->query("SELECT COUNT(*) as count FROM categories")[0]['count'] ?? 0
            ];
            
            // 최근 추가된 인플루언서 목록
            $recentInfluencers = $this->db->query("SELECT i.*, p.name as platform_name 
                FROM influencers i 
                LEFT JOIN platforms p ON i.platform_id = p.id 
                ORDER BY i.created_at DESC LIMIT 5");
            
            // 대시보드 페이지 렌더링
            $this->render('dashboard/index.php', [
                'title' => '대시보드',
                'user' => $user,
                'stats' => $stats,
                'recentInfluencers' => $recentInfluencers
            ]);
        } catch (Exception $e) {
            $this->handleError($e, '대시보드 로딩');
            $this->render('error.php', [
                'title' => '오류',
                'message' => '대시보드를 로드하는 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }
}
