<?php
// app/Core/BaseController.php
namespace App\Core;

use App\Core\Http\Request;
use App\Services\LogService;
use App\Core\DB;
use Exception;
use Monolog\Logger;

/**
 * 기본 컨트롤러 클래스
 * 모든 컨트롤러의 기반이 되는 클래스입니다.
 */
class BaseController
{
    protected $db;
    protected $logger;
    
    /**
     * 요청 객체
     * 
     * @var Request
     */
    protected $request;
    
    public function __construct($db = null, $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        
        // Request 객체 초기화
        $this->request = new Request();
    }
    
    /**
     * 서비스 클래스 인스턴스 생성
     * 
     * @param string $serviceClass 서비스 클래스명
     * @return object 서비스 인스턴스
     */
    protected function initService($serviceClass)
    {
        return new $serviceClass($this->db, $this->logger);
    }
    
    /**
     * 뷰 렌더링
     * 
     * @param string $view 뷰 파일 경로
     * @param array $data 뷰에 전달할 데이터
     * @return string 렌더링된 HTML
     */
    protected function render($view, $data = [])
    {
        if (strpos($view, '.php') === false) {
            $view = $view . '.php';
        }
        
        $viewPath = APP_PATH . '/Views/' . $view;
        
        // echo $viewPath;
        // exit;
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: $viewPath");
        }
        
        // 데이터 추출
        extract($data);
        
        // 출력 버퍼링 시작
        ob_start();
        
        // 뷰 파일 포함
        include $viewPath;
        
        // 버퍼 내용 반환 후 종료
        return ob_get_clean();
    }
    
    /**
     * 에러 핸들링 및 로깅
     * 
     * @param Exception $e 발생한 예외
     * @param string $action 에러 발생 액션/컨텍스트
     * @param array $extraData 추가 데이터
     * @param string $type 로그 타입
     * @return void
     */
    protected function handleError(Exception $e, $action, $extraData = [], $type = LogService::TYPE_DEFAULT)
    {
        if ($this->logger) {
            $logData = [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $this->request->all()
            ];
            
            if (!empty($extraData)) {
                $logData = array_merge($logData, $extraData);
            }
            
            $this->logger->error("[$type] 오류: $action", $logData);
        }
    }
    
    /**
     * 리다이렉트
     * 
     * @param string $url 리다이렉트할 URL
     * @param int $statusCode HTTP 상태 코드
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * 플래시 메시지 설정
     * 
     * @param string $type 메시지 타입 (success, error, info, warning)
     * @param string $message 메시지 내용
     * @return void
     */
    protected function setFlash($type, $message)
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * 플래시 메시지 가져오기 및 삭제
     * 
     * @return array 플래시 메시지 배열
     */
    protected function getFlashMessages()
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
}
