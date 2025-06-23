<?php
/**
 * 파일: app/Core/Middleware/AuthMiddleware.php
 * 
 * 이 파일은 인증을 처리하는 미들웨어 클래스를 정의합니다.
 */

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\JsonResponse;
use App\Core\Http\RedirectResponse;

/**
 * 인증 미들웨어 클래스
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * 인증이 필요하지 않은 URL 목록
     * @var array
     */
    private array $publicUrls = [
        '/', 
        '/login', 
        '/register', 
        '/api/auth/login', 
        '/api/auth/register'
    ];
    
    /**
     * 세션 만료 시간 (초)
     * @var int
     */
    private int $sessionLifetime = 3600; // 1시간
    
    /**
     * 인증 미들웨어 처리
     * 
     * @param object $request 요청 객체
     * @param callable $next 다음 미들웨어 호출 함수
     * @return mixed 응답 결과
     */
    public function process($request, callable $next)
    {
        $uri = $request->getUri();
        
        // 공개 URL은 인증 검사 없이 통과
        if (in_array($uri, $this->publicUrls)) {
            return $next($request);
        }
        
        // 세션 확인
        if (!$this->checkSession()) {
            // API 요청 확인
            if (strpos($uri, '/api/') === 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => '세션이 만료되었습니다. 다시 로그인해주세요.'
                ], 401);
            } else {
                // 웹 요청인 경우 로그인 페이지로 리다이렉트
                return new RedirectResponse('/login');
            }
        }
        
        // 세션 활동 시간 업데이트
        $_SESSION['lastActivity'] = time();
        
        // 다음 미들웨어 또는 컨트롤러 실행
        return $next($request);
    }
    
    /**
     * 세션 상태 확인
     * 
     * @return bool 세션 유효 여부
     */
    private function checkSession(): bool
    {
        // 기본 세션 정보 확인
        if (!isset($_SESSION['user_info'])) {
            return false;
        }
        
        if (!isset($_SESSION['user_info']['id'])) {
            return false;
        }
        
        // 세션 타임아웃 확인
        if (isset($_SESSION['lastActivity'])) {
            $inactive = time() - $_SESSION['lastActivity'];
            if ($inactive > $this->sessionLifetime) {
                return false;
            }
        }
        
        return true;
    }
} 