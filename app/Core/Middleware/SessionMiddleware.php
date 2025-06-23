<?php
/**
 * 파일: app/Core/Middleware/SessionMiddleware.php
 * 
 * 이 파일은 세션 관리를 위한 미들웨어 클래스를 정의합니다.
 */

namespace App\Core\Middleware;

use App\Core\Http\Request;

/**
 * 세션 미들웨어 클래스
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * 세션 설정
     * @var array
     */
    private array $sessionOptions = [
        'lifetime' => 86400, // 24시간
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
        'domain' => '',
    ];
    
    /**
     * 세션 미들웨어 처리
     * 
     * @param object $request 요청 객체
     * @param callable $next 다음 미들웨어 호출 함수
     * @return mixed 응답 결과
     */
    public function process($request, callable $next)
    {
        // HTTPS 연결 확인
        $this->sessionOptions['secure'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        
        // 세션 설정 적용
        session_set_cookie_params($this->sessionOptions);
        
        // 세션 시작
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 세션 ID 재생성 (세션 고정 공격 방지)
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } else if (time() - $_SESSION['_created'] > 1800) {
            // 30분마다 세션 ID 재생성
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
        
        // 다음 미들웨어 또는 컨트롤러 실행
        $response = $next($request);
        
        // 세션 데이터 저장
        session_write_close();
        
        return $response;
    }
} 