# 미들웨어 및 Request 클래스 사용 가이드

## 개요

이 문서는 PHP MVC 프레임워크의 미들웨어 아키텍처와 Request 클래스 사용법을 설명합니다. 이 기능들은 코드의 가독성, 재사용성, 유지보수성을 크게 향상시킵니다.

## 1. 미들웨어 아키텍처

### 1.1 미들웨어란?

미들웨어는 HTTP 요청이 애플리케이션에 도달하기 전이나 응답이 클라이언트에게 전송되기 전에 실행되는 코드 레이어입니다. 주요 역할은 다음과 같습니다:

- 인증 및 권한 검사
- 세션 처리
- CSRF 보호
- 로깅
- 응답 압축

### 1.2 미들웨어 인터페이스

모든 미들웨어는 `MiddlewareInterface`를 구현해야 합니다:

```php
interface MiddlewareInterface
{
    public function process($request, callable $next);
}
```

각 미들웨어는 요청을 처리하고 다음 미들웨어에 제어를 넘깁니다.

### 1.3 기본 제공 미들웨어

시스템에는 다음과 같은 기본 미들웨어가 포함되어 있습니다:

1. **SessionMiddleware**: 세션 시작 및 관리
2. **AuthMiddleware**: 사용자 인증 검사
3. **CsrfMiddleware**: CSRF 토큰 검증

### 1.4 미들웨어 등록

미들웨어는 `App` 클래스의 `addMiddleware` 메서드를 통해 등록합니다:

```php
// 애플리케이션 인스턴스 생성
$app = new \App\Core\App($dispatcher, $db, $logger);

// 미들웨어 등록
$app->addMiddleware(new \App\Core\Middleware\SessionMiddleware());
$app->addMiddleware(new \App\Core\Middleware\AuthMiddleware());
$app->addMiddleware(new \App\Core\Middleware\CsrfMiddleware());
```

미들웨어는 등록된 순서대로 실행됩니다.

### 1.5 커스텀 미들웨어 작성

필요에 따라 커스텀 미들웨어를 작성할 수 있습니다:

```php
namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process($request, callable $next)
    {
        // 요청 처리 전 작업
        $start = microtime(true);
        $uri = $request->getUri();
        $method = $request->getMethod();
        
        // 다음 미들웨어 실행
        $response = $next($request);
        
        // 요청 처리 후 작업
        $duration = microtime(true) - $start;
        error_log("$method $uri - {$duration}s");
        
        return $response;
    }
}
```

## 2. Request 클래스

### 2.1 개요

`Request` 클래스는 HTTP 요청 데이터(`$_GET`, `$_POST`, `$_SERVER` 등)에 대한 객체지향적 접근을 제공합니다.

### 2.2 Request 클래스 사용

```php
// 요청 객체 생성
$request = new \App\Core\Http\Request();

// GET 파라미터 조회
$page = $request->getQuery('page', 1); // 두 번째 인자는 기본값

// POST 파라미터 조회
$name = $request->getPost('name');

// GET/POST 통합 파라미터 조회
$id = $request->get('id');

// 모든 파라미터 조회
$allParams = $request->all();

// HTTP 메소드 조회
$method = $request->getMethod();

// URI 조회
$uri = $request->getUri();

// 헤더 조회
$userAgent = $request->getHeader('User-Agent');

// Ajax 요청 확인
if ($request->isAjax()) {
    // Ajax 처리
}

// JSON 요청 데이터 파싱
$jsonData = $request->getJson();
```

### 2.3 ValidatedRequest 클래스

`ValidatedRequest`는 `Request`를 확장하여 유효성 검증 기능을 추가한 클래스입니다:

```php
// 요청 객체 생성
$request = new \App\Core\Http\ValidatedRequest();

// 유효성 검증 규칙 설정
$rules = [
    'name' => ['required', 'min:2'],
    'email' => ['required', 'email'],
    'password' => ['required', 'min:8'],
    'age' => ['required', 'numeric', 'min_value:18']
];

$request->setRules($rules);

// 유효성 검증 실행
if ($request->validate()) {
    // 검증 성공 - 검증된 데이터 사용
    $validData = $request->validated();
    // 비즈니스 로직 처리...
} else {
    // 검증 실패 - 오류 메시지 반환
    $errors = $request->getErrors();
    // 오류 처리...
}
```

## 3. 컨트롤러에서 사용

컨트롤러 메서드에서는 첫 번째 파라미터로 `Request` 객체를 받습니다:

```php
class UserController
{
    public function store(Request $request)
    {
        $name = $request->getPost('name');
        $email = $request->getPost('email');
        
        // 비즈니스 로직 처리...
        
        return new JsonResponse([
            'success' => true,
            'message' => '사용자가 생성되었습니다.'
        ]);
    }
}
```

유효성 검증이 필요한 경우 `ValidatedRequest`를 사용할 수 있습니다:

```php
class UserController
{
    public function store(Request $request)
    {
        $validatedRequest = new ValidatedRequest();
        $validatedRequest->setRules([
            'name' => ['required'],
            'email' => ['required', 'email']
        ]);
        
        if (!$validatedRequest->validate()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $validatedRequest->getErrors()
            ], 422);
        }
        
        $data = $validatedRequest->validated();
        // 비즈니스 로직 처리...
        
        return new JsonResponse([
            'success' => true,
            'message' => '사용자가 생성되었습니다.'
        ]);
    }
}
```

## 4. 응답 클래스

다양한 응답 클래스를 사용하여 HTTP 응답을 생성할 수 있습니다:

```php
// 기본 응답
return new Response('Hello World', 200);

// JSON 응답
return new JsonResponse(['name' => '홍길동', 'age' => 30]);

// 리다이렉트 응답
return new RedirectResponse('/dashboard');
```

## 5. 예제 코드

미들웨어와 Request 클래스 사용 예제는 `tests/examples/middleware_example.php` 파일에서 확인할 수 있습니다. 이 파일을 실행하여 미들웨어 체인과 Request 클래스의 작동 방식을 직접 확인해보세요.

```bash
php tests/examples/middleware_example.php
```