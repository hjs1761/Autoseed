<?php
/**
 * 파일: app/Core/Middleware/MiddlewareInterface.php
 * 
 * 이 파일은 미들웨어의 기본 인터페이스를 정의합니다.
 * 모든 미들웨어는 이 인터페이스를 구현해야 합니다.
 */

namespace App\Core\Middleware;

/**
 * 미들웨어 인터페이스
 * 
 * 미들웨어 파이프라인 구조를 위한 인터페이스입니다.
 * 각 미들웨어는 요청을 처리하고 다음 미들웨어에 제어를 넘깁니다.
 */
interface MiddlewareInterface
{
    /**
     * 미들웨어 처리 실행
     * 
     * @param object $request 요청 객체
     * @param callable $next 다음 미들웨어 호출 함수
     * @return mixed 응답 결과
     */
    public function process($request, callable $next);
} 