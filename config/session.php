<?php
/**
 * 세션 설정 파일
 * 
 * 이 파일은 인플루언서 솔루션의 세션 동작을 구성합니다.
 * 세션 보안 설정, 쿠키 속성, 가비지 컬렉션 등을 관리합니다.
 * 
 * 주요 설정:
 * - 세션 수명 (lifetime): 기본값 24시간(86400초)
 * - 쿠키 보안 설정: HttpOnly, SameSite 정책 등
 * - 세션 이름: 'INFLUENCER_SESSION'
 * - 가비지 컬렉션: 만료된 세션 정리 주기
 */

// 세션 설정
return (function() {
    /**
     * 세션 수명 설정
     * 
     * SESSION_LIFETIME 상수가 정의되어 있으면 해당 값 사용
     * 그렇지 않으면 기본값 86400초(24시간) 사용
     * 
     * 참고: PHP의 세션 수명은 초 단위로 지정
     */
    $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400; // 24시간
    
    /**
     * 세션 쿠키 설정
     * 
     * - lifetime: 쿠키 만료 시간(초) 
     * - path: 쿠키가 유효한 경로, '/'는 전체 도메인
     * - domain: 쿠키가 유효한 도메인, 빈 문자열은 현재 도메인
     * - secure: HTTPS 연결에서만 쿠키 전송 여부 (true/false)
     * - httponly: JavaScript에서 쿠키 접근 불가 설정 (XSS 방지)
     * - samesite: CSRF 방지를 위한 설정 (Lax, Strict, None)
     *   - Lax: 대부분의 경우 적절한 보안과 사용성 균형
     *   - Strict: 가장 강력한 보안, 동일 사이트 내에서만 전송
     *   - None: 크로스 사이트 요청에도 항상 전송 (보안 취약)
     */
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    /**
     * 세션 이름 설정
     * 
     * 세션 식별을 위한 쿠키 이름 지정
     * 기본 이름인 'PHPSESSID' 대신 커스텀 이름을 사용하여 보안 강화
     */
    session_name('INFLUENCER_SESSION');
    
    /**
     * 세션 파일 수명 및 쿠키 수명 설정
     * 
     * - session.gc_maxlifetime: 서버에 저장된 세션 파일의 수명 (초)
     * - session.cookie_lifetime: 브라우저에 저장되는 세션 쿠키의 수명 (초)
     * 
     * 주의: gc_maxlifetime이 cookie_lifetime보다 짧으면
     * 세션이 아직 유효하다고 생각하는 사용자의 세션이 서버에서 삭제될 수 있음
     */
    ini_set('session.gc_maxlifetime', $lifetime);
    ini_set('session.cookie_lifetime', $lifetime);
    
    /**
     * 세션 가비지 컬렉션(GC) 설정
     * 
     * - session.gc_probability: GC 작동 확률의 분자
     * - session.gc_divisor: GC 작동 확률의 분모
     * 
     * 작동 확률 = gc_probability / gc_divisor
     * 예: 1/100 = 요청의 1% 확률로 GC 실행
     * 
     * 참고: 프로덕션 환경에서는 크론 작업 등으로 세션 정리하는 것도 고려
     */
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    return true;
})();
