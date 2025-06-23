# 미들웨어 사용 가이드

## 미들웨어 개요

미들웨어는 HTTP 요청이 애플리케이션에 도달하기 전에 요청을 가로채서 전처리하거나, 응답이 클라이언트에게 전달되기 전에 후처리하는 컴포넌트입니다. 미들웨어는 다음과 같은 작업을 수행할 수 있습니다:

- 세션 관리
- 사용자 인증 및 권한 검사
- CSRF 토큰 검증
- 요청 로깅
- 응답 압축
- 기타 다양한 전처리 및 후처리 작업

## 미들웨어 처리 흐름

현재 애플리케이션에서 미들웨어 처리 흐름은 다음과 같습니다:

1. 클라이언트에서 HTTP 요청 발생
2. `index.php`에서 요청 객체 생성
3. 등록된 미들웨어가 순차적으로 실행됨 (기본값: SessionMiddleware -> AuthMiddleware -> CsrfMiddleware)
4. 모든 미들웨어 처리가 완료되면 라우터 미들웨어가 실행되어 요청을 해당 컨트롤러로 전달
5. 컨트롤러에서 응답 생성
6. 응답이 클라이언트에게 반환

미들웨어 체인에서 어느 단계든 처리를 중단하고 직접 응답을 반환할 수 있습니다.

## 전역 미들웨어 등록

모든 요청에 적용되는 미들웨어는 `index.php` 파일에서 다음과 같이 등록합니다:

```php
// 미들웨어 등록 (실행 순서가 중요)
$app->addMiddleware(new \App\Core\Middleware\SessionMiddleware());
$app->addMiddleware(new \App\Core\Middleware\AuthMiddleware());
$app->addMiddleware(new \App\Core\Middleware\CsrfMiddleware());
```

## 특정 라우트에만 미들웨어 적용하기

### 가장 쉬운 방법: 미들웨어 내부에서 URI 확인

특정 라우트에만 미들웨어를 적용하는 가장 간단한 방법은 미들웨어 내부에서 URI 경로를 확인하여 선택적으로 처리하는 것입니다. 예를 들어, 관리자 페이지에만 적용되는 미들웨어를 만들고 싶다면:

```php
<?php
// app/Core/Middleware/AdminMiddleware.php
namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

class AdminMiddleware implements MiddlewareInterface
{
    // 미들웨어를 적용할 라우트 목록
    private array $adminRoutes = [
        '/admin',
        '/admin/dashboard',
        '/admin/users',
        '/admin/settings'
    ];
    
    // 또는 정규식 패턴으로 지정할 수도 있음
    private string $adminRoutePattern = '/^\/admin\/.*/';
    
    public function process($request, callable $next)
    {
        $uri = $request->getUri();
        
        // 1. 경로 목록으로 확인하는 방법
        if (in_array($uri, $this->adminRoutes)) {
            // 관리자 권한 확인
            if (!isset($_SESSION['user_info']['is_admin']) || $_SESSION['user_info']['is_admin'] !== true) {
                // API 요청인 경우 JSON 응답
                if (strpos($uri, '/api/') === 0) {
                    return new \App\Core\Http\JsonResponse([
                        'success' => false,
                        'message' => '관리자 권한이 필요합니다.'
                    ], 403);
                }
                
                // 일반 웹 요청인 경우 리다이렉트
                return new \App\Core\Http\RedirectResponse('/dashboard', '관리자 권한이 필요합니다.', 'error');
            }
        }
        
        // 2. 정규식 패턴으로 확인하는 방법
        // if (preg_match($this->adminRoutePattern, $uri)) {
        //     // 관리자 권한 확인 로직...
        // }
        
        // 적용하지 않을 라우트는 그냥 통과
        return $next($request);
    }
}
```

이 미들웨어를 `index.php`에서 기존 미들웨어와 함께 등록합니다:

```php
$app->addMiddleware(new \App\Core\Middleware\SessionMiddleware());
$app->addMiddleware(new \App\Core\Middleware\AuthMiddleware());
$app->addMiddleware(new \App\Core\Middleware\AdminMiddleware()); // 관리자 페이지 전용 미들웨어
$app->addMiddleware(new \App\Core\Middleware\CsrfMiddleware());
```

### 미들웨어 사용 팁

1. **미들웨어 실행 순서**가 중요합니다. 예를 들어, `AuthMiddleware` 이후에 `AdminMiddleware`를 실행해야 사용자 인증 정보를 활용할 수 있습니다.

2. **조건에 따라 처리 중단**: 미들웨어에서 조건을 확인하여 처리를 중단하고 직접 응답을 반환할 수 있습니다. 이 경우 이후의 미들웨어와 컨트롤러는 실행되지 않습니다.

3. **요청 객체 수정**: 미들웨어에서 `$request` 객체를 수정하여 다음 미들웨어나 컨트롤러에 전달할 수 있습니다. 이를 통해 데이터를 추가하거나 변경할 수 있습니다.

## 미들웨어 인터페이스

모든 미들웨어는 `MiddlewareInterface`를 구현해야 합니다:

```php
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
```

## 기존 미들웨어 설명

### SessionMiddleware

세션을 시작하고, 세션 쿠키 설정, 세션 타임아웃, 세션 고정 공격 방지 등을 처리합니다.

### AuthMiddleware

보호된 라우트에 대한 접근 시 사용자 로그인 상태를 확인하고, 인증되지 않은 사용자는 로그인 페이지로 리다이렉트합니다. `publicUrls` 배열에 있는 URL은 인증 검사에서 제외됩니다.

### CsrfMiddleware

CSRF 공격을 방지하기 위해 폼 제출 시 CSRF 토큰을 검증합니다. GET 요청과 API 요청은 CSRF 검증에서 제외됩니다. 