<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Http\Request;
use App\Services\LogService;
use Exception;

/**
 * 로그 관리 컨트롤러
 */
class LogController extends BaseController
{
    public function __construct($db = null, $logger = null)
    {
        parent::__construct($db, $logger);
    }

    /**
     * 로그 목록 페이지
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

            $data = [
                'title' => '시스템 로그'
            ];
            
            $this->render('log/index.php', $data);
        } catch (Exception $e) {
            $this->handleErrorAndRender(
                $e, 
                '로그 목록 조회', 
                '오류', 
                '로그 목록을 조회하는 중 오류가 발생했습니다: ' . $e->getMessage()
            );
        }
    }
}