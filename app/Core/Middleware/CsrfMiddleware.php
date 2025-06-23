<?php
/**
 * 파일: app/Core/Middleware/CsrfMiddleware.php
 * 
 * 이 파일은 CSRF 공격을 방지하는 미들웨어 클래스를 정의합니다.
 */

namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\JsonResponse;

/**
 * CSRF 보호 미들웨어 클래스
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * CSRF 토큰 세션 키
     * @var string
     */
    private string $tokenKey = 'csrf_token';
    
    /**
     * CSRF 보호 미들웨어 처리
     * 
     * @param object $request 요청 객체
     * @param callable $next 다음 미들웨어 호출 함수
     * @return mixed 응답 결과
     */
    public function process($request, callable $next)
    {
        // 토큰이 없으면 생성
        if (!isset($_SESSION[$this->tokenKey])) {
            $_SESSION[$this->tokenKey] = bin2hex(random_bytes(32));
        }
        
        // GET 요청은 CSRF 검사 제외
        if ($request->getMethod() === 'GET') {
            return $next($request);
        }
        
        // API 요청은 CSRF 검사 대신 다른 보안 메커니즘 적용 가능
        // (예: API 키, JWT 등)
        if (strpos($request->getUri(), '/api/') === 0) {
            return $next($request);
        }
        
        // CSRF 토큰 검증
        $token = $request->getPost('csrf_token');
        if (!$token || $token !== $_SESSION[$this->tokenKey]) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'CSRF 토큰이 유효하지 않습니다.'
            ], 403);
        }
        
        return $next($request);
    }
    
    /**
     * CSRF 토큰 생성
     * 
     * @return string CSRF 토큰
     */
    public static function generateToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
} 