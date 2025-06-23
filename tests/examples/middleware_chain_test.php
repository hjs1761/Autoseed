<?php
/**
 * 미들웨어 체인 테스트 예제
 * 
 * 이 파일은 미들웨어 체인의 작동 방식을 시뮬레이션합니다.
 */

// 필요한 파일 포함
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../include/common.php';

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\JsonResponse;
use App\Core\Middleware\MiddlewareInterface;

/**
 * 간단한 요청 객체
 * 
 * 이 클래스는 HTTP 요청을 표현하는 간단한 객체입니다.
 * 실제 Request 클래스의 기본 기능만 구현하여 테스트에 사용합니다.
 */
class SimpleRequest
{
    public $uri;
    public $method;
    public $headers = [];
    public $data = [];
    
    public function __construct($uri, $method, $data = [], $headers = [])
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->data = $data;
        $this->headers = $headers;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function getHeader($name, $default = null)
    {
        return $this->headers[$name] ?? $default;
    }
    
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}

/**
 * 간단한 응답 객체
 * 
 * 이 클래스는 HTTP 응답을 표현하는 간단한 객체입니다.
 * 실제 Response 클래스의 기본 기능만 구현하여 테스트에 사용합니다.
 */
class SimpleResponse
{
    public $content;
    public $statusCode;
    public $headers = [];
    
    public function __construct($content = '', $statusCode = 200, $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
}

/**
 * 로깅 미들웨어
 * 
 * 이 미들웨어는 요청 처리 시작과 완료를 로깅하고 처리 시간을 측정합니다.
 */
class LoggingMiddleware implements MiddlewareInterface
{
    public function process($request, callable $next)
    {
        echo "[ LoggingMiddleware ] 요청 시작: {$request->getMethod()} {$request->getUri()}\n";
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = round((microtime(true) - $start) * 1000, 2);
        echo "[ LoggingMiddleware ] 요청 완료: {$duration}ms\n";
        
        return $response;
    }
}

/**
 * 인증 미들웨어
 * 
 * 이 미들웨어는 요청에 포함된 인증 토큰을 검증하고,
 * 공개 경로인 경우 인증 없이 통과시킵니다.
 */
class AuthMiddleware implements MiddlewareInterface
{
    private $publicPaths = ['/', '/login', '/register', '/public'];
    
    public function process($request, callable $next)
    {
        $uri = $request->getUri();
        echo "[ AuthMiddleware ] 인증 검사 URI: $uri\n";
        
        // 공개 경로는 인증 필요 없음
        if (in_array($uri, $this->publicPaths)) {
            echo "[ AuthMiddleware ] 공개 경로, 인증 필요 없음\n";
            return $next($request);
        }
        
        // 인증 토큰 검사
        $token = $request->getHeader('Authorization');
        if (!$token) {
            echo "[ AuthMiddleware ] 인증 토큰 없음, 접근 거부\n";
            return new SimpleResponse('인증이 필요합니다.', 401);
        }
        
        // 모의 토큰 검증 (실제로는 더 복잡한 검증 로직이 있을 것)
        if ($token !== 'valid-token') {
            echo "[ AuthMiddleware ] 유효하지 않은 토큰, 접근 거부\n";
            return new SimpleResponse('유효하지 않은 인증 토큰입니다.', 401);
        }
        
        echo "[ AuthMiddleware ] 인증 성공\n";
        return $next($request);
    }
}

/**
 * 요청 유효성 검증 미들웨어
 * 
 * 이 미들웨어는 요청 데이터의 유효성을 검증합니다.
 * 각 엔드포인트별로 정의된 규칙에 따라 요청 데이터를 검사합니다.
 */
class ValidationMiddleware implements MiddlewareInterface
{
    private $rules = [
        '/users/create' => [
            'name' => 'required',
            'email' => 'required|email'
        ],
        '/products/create' => [
            'name' => 'required',
            'price' => 'required|numeric'
        ]
    ];
    
    public function process($request, callable $next)
    {
        $uri = $request->getUri();
        echo "[ ValidationMiddleware ] 요청 데이터 검증 URI: $uri\n";
        
        // 해당 URI에 대한 규칙이 있는지 확인
        if (!isset($this->rules[$uri])) {
            echo "[ ValidationMiddleware ] 이 URI에 대한 검증 규칙 없음\n";
            return $next($request);
        }
        
        $rules = $this->rules[$uri];
        $valid = true;
        $errors = [];
        
        // 간단한 검증 로직 (실제로는 더 복잡한 검증 로직이 있을 것)
        foreach ($rules as $field => $rule) {
            echo "[ ValidationMiddleware ] '$field' 필드 검증 중\n";
            
            if (strpos($rule, 'required') !== false && !isset($request->data[$field])) {
                $valid = false;
                $errors[$field] = "$field 필드는 필수입니다.";
                continue;
            }
            
            if (isset($request->data[$field])) {
                $value = $request->data[$field];
                
                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $valid = false;
                    $errors[$field] = "$field 필드는 유효한 이메일이어야 합니다.";
                }
                
                if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                    $valid = false;
                    $errors[$field] = "$field 필드는 숫자여야 합니다.";
                }
            }
        }
        
        if (!$valid) {
            echo "[ ValidationMiddleware ] 검증 실패: " . json_encode($errors) . "\n";
            return new SimpleResponse(json_encode(['errors' => $errors]), 422);
        }
        
        echo "[ ValidationMiddleware ] 검증 성공\n";
        return $next($request);
    }
}

/**
 * 미들웨어 체인 관리 클래스
 * 
 * 이 클래스는 여러 미들웨어를 등록하고 순차적으로 실행하는 체인을 관리합니다.
 * 모든 미들웨어를 통과한 후에는 최종 핸들러가 실행됩니다.
 */
class MiddlewareChain
{
    private $middlewares = [];
    
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    
    public function handle($request)
    {
        $runner = $this->createRunner();
        return $runner($request);
    }
    
    /**
     * 미들웨어 체인 실행을 위한 함수 생성
     * 
     * 재귀적인 방식으로 미들웨어 체인을 실행하는 함수를 반환합니다.
     * 
     * @return callable 미들웨어 체인 실행 함수
     */
    private function createRunner()
    {
        $runner = function ($request, $middlewares, $index = 0) use (&$runner) {
            if ($index >= count($middlewares)) {
                // 모든 미들웨어를 통과하면 최종 처리기 실행
                return $this->finalHandler($request);
            }
            
            $middleware = $middlewares[$index];
            return $middleware->process($request, function ($request) use ($runner, $middlewares, $index) {
                return $runner($request, $middlewares, $index + 1);
            });
        };
        
        return function ($request) use ($runner) {
            return $runner($request, $this->middlewares, 0);
        };
    }
    
    /**
     * 최종 처리 핸들러
     * 
     * 모든 미들웨어를 통과한 후 실행되는 기본 처리 로직입니다.
     * 실제 애플리케이션에서는 컨트롤러가 이 역할을 합니다.
     * 
     * @param mixed $request 처리할 요청 객체
     * @return SimpleResponse 응답 객체
     */
    private function finalHandler($request)
    {
        // 실제 애플리케이션에서는 컨트롤러가 실행될 것
        echo "[ FinalHandler ] 모든 미들웨어 통과, 최종 처리기 실행\n";
        
        $uri = $request->getUri();
        $method = $request->getMethod();
        
        echo "[ FinalHandler ] 처리 중: $method $uri\n";
        
        // 간단한 라우팅 로직
        if ($uri === '/dashboard' && $method === 'GET') {
            return new SimpleResponse('대시보드 컨텐츠', 200);
        } else if ($uri === '/users/create' && $method === 'POST') {
            $name = $request->get('name');
            return new SimpleResponse("사용자 $name 생성됨", 201);
        } else if ($uri === '/products/create' && $method === 'POST') {
            $name = $request->get('name');
            return new SimpleResponse("제품 $name 생성됨", 201);
        } else {
            return new SimpleResponse('요청한 리소스를 찾을 수 없습니다.', 404);
        }
    }
}

// 테스트 실행
echo "=== 미들웨어 체인 테스트 ===\n\n";

// 테스트 설명 주석 추가
// 이 테스트는 다양한 시나리오에서 미들웨어 체인의 동작을 검증합니다:
// 1. 공개 경로 접근 - 인증 없이 접근 가능
// 2. 보호된 경로에 인증 없이 접근 - 접근 거부
// 3. 보호된 경로에 유효한 인증으로 접근 - 접근 허용
// 4. 유효하지 않은 데이터로 사용자 생성 - 유효성 검증 실패
// 5. 유효한 데이터로 사용자 생성 - 성공
// 6. 존재하지 않는 경로 접근 - 404 응답

// 미들웨어 체인 설정
$chain = new MiddlewareChain();
$chain->addMiddleware(new LoggingMiddleware())
      ->addMiddleware(new AuthMiddleware())
      ->addMiddleware(new ValidationMiddleware());

// 테스트 1: 공개 경로 접근 (인증 필요 없음)
echo "테스트 1: 공개 경로 접근 (인증 필요 없음)\n";
$request = new SimpleRequest('/public', 'GET');
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n";

// 테스트 2: 보호된 경로 접근 (인증 필요)
echo "테스트 2: 보호된 경로 접근 (인증 없음)\n";
$request = new SimpleRequest('/dashboard', 'GET');
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n";

// 테스트 3: 인증된 요청으로 보호된 경로 접근
echo "테스트 3: 인증된 요청으로 보호된 경로 접근\n";
$request = new SimpleRequest('/dashboard', 'GET', [], ['Authorization' => 'valid-token']);
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n";

// 테스트 4: 유효하지 않은 데이터로 사용자 생성 시도
echo "테스트 4: 유효하지 않은 데이터로 사용자 생성 시도\n";
$request = new SimpleRequest('/users/create', 'POST', ['name' => '홍길동'], ['Authorization' => 'valid-token']);
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n";

// 테스트 5: 유효한 데이터로 사용자 생성
echo "테스트 5: 유효한 데이터로 사용자 생성\n";
$request = new SimpleRequest('/users/create', 'POST', 
    ['name' => '홍길동', 'email' => 'hong@example.com'], 
    ['Authorization' => 'valid-token']
);
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n";

// 테스트 6: 존재하지 않는 경로 접근
echo "테스트 6: 존재하지 않는 경로 접근\n";
$request = new SimpleRequest('/unknown', 'GET', [], ['Authorization' => 'valid-token']);
$response = $chain->handle($request);
echo "응답 상태 코드: {$response->statusCode}\n";
echo "응답 내용: {$response->content}\n\n"; 