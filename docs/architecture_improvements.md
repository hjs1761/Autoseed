# MVC 패턴 개선 가이드: 미들웨어와 Request 클래스

## 1. 현재 구조의 한계

### 1.1 현재 미들웨어 구현 방식

현재 시스템에서는 미들웨어가 `public/index.php` 파일에 인라인 코드로 직접 구현되어 있습니다:

```php
// 인증이 필요하지 않은 URL 목록
$publicUrls = ['/', '/login', '/register', '/api/auth/login', '/api/auth/register'];

// 세션 체크 (공개 URL이 아닌 경우)
if (!in_array($uri, $publicUrls) && !checkSession()) {
    if (strpos($uri, '/api/') === 0) {
        // API 요청일 경우 JSON 응답
        returnError('세션이 만료되었습니다. 다시 로그인해주세요.', 401);
    } else {
        // 웹 요청일 경우 로그인 페이지로 리다이렉트
        respondSessionExpired();
    }
}

// 세션 활동 체크
if (isset($_SESSION['lastActivity'])) {
    $inactive = time() - $_SESSION['lastActivity'];
    if ($inactive > $expireAfter) {
        // 세션 만료
        respondSessionExpired();
    }
}
$_SESSION['lastActivity'] = time();
```

이 방식의 문제점:
1. **코드 재사용이 어려움**: 동일한 미들웨어 로직을 다른 애플리케이션에서 사용하기 어려움
2. **테스트 어려움**: 미들웨어 로직을 독립적으로 테스트하기 어려움
3. **유지보수 어려움**: 미들웨어 로직이 메인 애플리케이션 파일에 섞여 있어 관리가 어려움
4. **확장성 제한**: 새로운 미들웨어를 추가하거나 조합하기 어려운 구조

### 1.2 현재 요청 처리 방식

현재 시스템에서는 HTTP 요청 데이터를 직접 슈퍼글로벌 변수(`$_GET`, `$_POST`, `$_REQUEST`)를 통해 접근합니다:

```php
// 컨트롤러 메소드 예시
public function store()
{
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 유효성 검증
    if (empty($name) || empty($email) || empty($password)) {
        return $this->errorResponse('필수 필드가 누락되었습니다.');
    }
    
    // 데이터 처리...
}
```

이 방식의 문제점:
1. **유효성 검증 중복**: 각 컨트롤러마다 동일한 유효성 검증 코드 반복
2. **테스트 어려움**: 슈퍼글로벌 변수를 모킹하기 어려워 단위 테스트가 어려움
3. **보안 위험**: 입력 필터링이 일관되게 적용되지 않을 위험
4. **코드 일관성 부족**: 요청 데이터 접근 방식이 컨트롤러마다 다를 수 있음

## 2. 미들웨어 클래스 도입

### 2.1 미들웨어 인터페이스 정의

미들웨어를 클래스로 분리하여 재사용 가능하고 테스트하기 쉬운 구조로 개선할 수 있습니다:

```php
<?php
// app/Core/Middleware/MiddlewareInterface.php
namespace App\Core\Middleware;

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

### 2.2 인증 미들웨어 구현 예시

```php
<?php
// app/Core/Middleware/AuthMiddleware.php
namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    private array $publicUrls = ['/', '/login', '/register', '/api/auth/login', '/api/auth/register'];
    
    /**
     * 인증 미들웨어 처리
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
                return new Response\JsonResponse([
                    'success' => false,
                    'message' => '세션이 만료되었습니다. 다시 로그인해주세요.'
                ], 401);
            } else {
                // 웹 요청인 경우 로그인 페이지로 리다이렉트
                return new Response\RedirectResponse('/login');
            }
        }
        
        // 세션 활동 시간 업데이트
        $_SESSION['lastActivity'] = time();
        
        // 다음 미들웨어 또는 컨트롤러 실행
        return $next($request);
    }
    
    /**
     * 세션 상태 확인
     */
    private function checkSession(): bool
    {
        if (!isset($_SESSION['user_info'])) {
            return false;
        }
        
        if (!isset($_SESSION['user_info']['id'])) {
            return false;
        }
        
        // 세션 타임아웃 확인
        if (isset($_SESSION['lastActivity'])) {
            $expireAfter = 60 * 60; // 1시간
            $inactive = time() - $_SESSION['lastActivity'];
            if ($inactive > $expireAfter) {
                return false;
            }
        }
        
        return true;
    }
}
```

### 2.3 CSRF 미들웨어 구현 예시

```php
<?php
// app/Core/Middleware/CsrfMiddleware.php
namespace App\Core\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * CSRF 보호 미들웨어 처리
     */
    public function process($request, callable $next)
    {
        // GET 요청은 CSRF 검사 제외
        if ($request->getMethod() === 'GET') {
            return $next($request);
        }
        
        // API 요청은 CSRF 검사 대신 다른 보안 메커니즘 적용 가능
        if (strpos($request->getUri(), '/api/') === 0) {
            // API 요청은 별도 인증 메커니즘 사용
            return $next($request);
        }
        
        // CSRF 토큰 검증
        $token = $request->getPost('csrf_token');
        if (!$token || $token !== $_SESSION['csrf_token']) {
            return new Response\JsonResponse([
                'success' => false, 
                'message' => 'CSRF 토큰이 유효하지 않습니다.'
            ], 403);
        }
        
        return $next($request);
    }
}
```

### 2.4 미들웨어 스택 설정 방법

```php
<?php
// public/index.php (일부)

// 미들웨어 스택 설정
$middlewareStack = [
    new \App\Core\Middleware\SessionMiddleware(),
    new \App\Core\Middleware\AuthMiddleware(),
    new \App\Core\Middleware\CsrfMiddleware(),
    // 추가 미들웨어...
];

// 미들웨어 실행 함수
$executeMiddleware = function ($request, $middlewares, $index = 0) use (&$executeMiddleware) {
    if ($index >= count($middlewares)) {
        // 모든 미들웨어 통과 후 라우터 실행
        return $this->router->dispatch($request);
    }
    
    $middleware = $middlewares[$index];
    return $middleware->process($request, function ($request) use ($middlewares, $index, $executeMiddleware) {
        return $executeMiddleware($request, $middlewares, $index + 1);
    });
};

// HTTP 요청 객체 생성
$request = new \App\Core\Http\Request();

// 미들웨어 스택 실행
$response = $executeMiddleware($request, $middlewareStack);

// 응답 전송
$response->send();
```

## 3. Request 클래스 도입

### 3.1 Request 클래스 구현

슈퍼글로벌 변수를 직접 사용하는 대신 Request 클래스를 통해 요청 데이터를 캡슐화합니다:

```php
<?php
// app/Core/Http/Request.php
namespace App\Core\Http;

class Request
{
    protected array $get;
    protected array $post;
    protected array $files;
    protected array $server;
    protected array $cookies;
    protected string $content;
    
    /**
     * 요청 객체 생성
     */
    public function __construct()
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->content = file_get_contents('php://input');
    }
    
    /**
     * GET 파라미터 조회
     */
    public function getQuery(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        
        return $this->get[$key] ?? $default;
    }
    
    /**
     * POST 파라미터 조회
     */
    public function getPost(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        
        return $this->post[$key] ?? $default;
    }
    
    /**
     * POST와 GET 파라미터를 모두 검색
     */
    public function get(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }
    
    /**
     * 모든 요청 파라미터 반환
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }
    
    /**
     * JSON 요청 데이터 파싱
     */
    public function getJson(): array
    {
        if (empty($this->content)) {
            return [];
        }
        
        $contentType = $this->getHeader('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            return json_decode($this->content, true) ?? [];
        }
        
        return [];
    }
    
    /**
     * 요청 메소드 반환
     */
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * 요청 URI 반환
     */
    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // 쿼리스트링 제거
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return rawurldecode($uri);
    }
    
    /**
     * 헤더 값 조회
     */
    public function getHeader(string $name, $default = null)
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($name));
        return $this->server[$headerName] ?? $default;
    }
    
    /**
     * 요청이 AJAX인지 확인
     */
    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * 업로드된 파일 조회
     */
    public function getFile(string $key)
    {
        return $this->files[$key] ?? null;
    }
}
```

### 3.2 유효성 검증 통합

Request 클래스를 확장하여 유효성 검증 기능을 통합할 수 있습니다:

```php
<?php
// app/Core/Http/ValidatedRequest.php
namespace App\Core\Http;

use App\Utils\Validator;

class ValidatedRequest extends Request
{
    protected array $rules = [];
    protected array $errors = [];
    
    /**
     * 요청 데이터 유효성 검증
     */
    public function validate(array $rules = null): bool
    {
        $rulesToUse = $rules ?? $this->rules;
        if (empty($rulesToUse)) {
            return true;
        }
        
        $data = $this->all();
        $validation = validate($data, $rulesToUse);
        
        if (!$validation['isValid']) {
            $this->errors = $validation['errors'];
            return false;
        }
        
        return true;
    }
    
    /**
     * 유효성 검증 오류 반환
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * 유효성 검증 후 데이터 반환
     */
    public function validated(): array
    {
        if (empty($this->rules)) {
            return $this->all();
        }
        
        if (!$this->validate()) {
            throw new \Exception('유효성 검증에 실패했습니다.');
        }
        
        return array_intersect_key($this->all(), array_flip(array_keys($this->rules)));
    }
}
```

### 3.3 컨트롤러에서 Request 클래스 사용 예시

```php
<?php
// app/Controllers/Api/InfluencerApiController.php
namespace App\Controllers\Api;

use App\Core\BaseApiController;
use App\Core\Http\Request;
use App\Services\InfluencerService;

class InfluencerApiController extends BaseApiController
{
    private $influencerService;
    
    public function __construct(InfluencerService $influencerService)
    {
        parent::__construct();
        $this->influencerService = $influencerService;
    }
    
    /**
     * 인플루언서 목록 조회
     */
    public function index(Request $request)
    {
        $params = [
            'platform_id' => $request->getQuery('platform_id'),
            'keyword' => $request->getQuery('keyword'),
            'page' => (int)$request->getQuery('page', 1),
            'limit' => (int)$request->getQuery('limit', 20)
        ];
        
        $result = $this->influencerService->search($params);
        return $this->successResponse($result);
    }
    
    /**
     * 인플루언서 생성
     */
    public function store(Request $request)
    {
        // 유효성 검증 규칙
        $rules = [
            'name' => ['required'],
            'handle' => ['required'],
            'platform_id' => ['required', 'numeric'],
            'follower_count' => ['numeric', ['range', 0, PHP_INT_MAX]],
            'engagement_rate' => ['numeric', ['range', 0, 100]]
        ];
        
        // 데이터 유효성 검증
        $validation = validate($request->all(), $rules);
        if (!$validation['isValid']) {
            return $this->errorResponse(
                '유효성 검사에 실패했습니다.',
                422,
                $validation['errors']
            );
        }
        
        // 서비스 호출
        $result = $this->influencerService->saveInfluencer($request->all());
        return $this->successResponse($result, '인플루언서가 성공적으로 생성되었습니다.');
    }
}
```

## 4. 개선된 구조의 이점

### 4.1 미들웨어 클래스 이점

1. **코드 재사용성**: 미들웨어 로직을 다른 프로젝트에서도 쉽게 재사용 가능
2. **테스트 용이성**: 각 미들웨어를 독립적으로 단위 테스트 가능
3. **유지보수성**: 각 미들웨어가 단일 책임을 가지므로 이해하고 유지보수하기 쉬움
4. **확장성**: 새로운 미들웨어를 쉽게 추가하고 조합 가능
5. **구성 가능성**: 특정 라우트나 컨트롤러에 특정 미들웨어만 적용 가능

### 4.2 Request 클래스 이점

1. **일관된 API**: 요청 데이터에 접근하는 일관된 방법 제공
2. **테스트 용이성**: 요청 객체를 쉽게 모킹하여 컨트롤러 단위 테스트 가능
3. **보안 강화**: 입력 데이터에 대한 일관된 필터링과 유효성 검증
4. **유지보수성**: 요청 처리 로직 변경 시 한 곳만 수정하면 됨
5. **재사용성**: 커스텀 Request 클래스를 만들어 특정 API에 대한 유효성 검증 규칙 캡슐화 가능

## 5. 구현 전략

### 5.1 단계적 도입 전략

1. **Request 클래스 먼저 도입**: 슈퍼글로벌 변수 접근을 Request 클래스로 교체
2. **컨트롤러 의존성 주입 준비**: 컨트롤러 생성자 파라미터로 Request 객체 전달
3. **미들웨어 인터페이스 정의**: 미들웨어 아키텍처 기반 마련
4. **핵심 미들웨어 구현**: AuthMiddleware, CsrfMiddleware 등 핵심 미들웨어 구현
5. **미들웨어 스택 구성**: 애플리케이션에 미들웨어 스택 도입

### 5.2 실용적인 고려사항

1. **기존 코드와의 호환성**: 점진적 마이그레이션을 위한 호환성 계층 고려
2. **성능 영향**: 추가 추상화 계층의 성능 영향 최소화
3. **학습 곡선**: 팀원들이 새로운 아키텍처를 쉽게 이해할 수 있도록 문서화
4. **프레임워크 종속성 방지**: 특정 프레임워크에 종속되지 않는 설계 지향

## 6. 결론

미들웨어와 Request 클래스를 도입함으로써 코드의 구조, 재사용성, 테스트 용이성, 보안성을 크게 향상시킬 수 있습니다. 이러한 개선은 애플리케이션의 복잡성이 증가함에 따라 더욱 중요해지며, 장기적으로는 개발 생산성과 코드 품질을 높이는 데 기여합니다.

이 접근 방식은 현대적인 PHP 프레임워크(Laravel, Symfony 등)에서 사용하는 패턴을 채택하면서도, 순수 PHP 개발자가 이해하기 쉽고 점진적으로 도입할 수 있는 형태로 제안되었습니다. 