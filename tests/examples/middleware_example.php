<?php
/**
 * 미들웨어와 Request 클래스 사용 예제
 * 
 * 이 파일은 미들웨어 및 Request 클래스 활용 방법을 보여줍니다.
 */

// Composer Autoload
require_once __DIR__ . '/../../vendor/autoload.php';

// 헬퍼 파일 로드
require_once __DIR__ . '/../../include/common.php';
require_once __DIR__ . '/../../include/validation_helper.php';

// Test 클래스 정의
class MiddlewareTest
{
    /**
     * Request 클래스 사용 예제 실행
     */
    public function testRequest()
    {
        echo "\n=== Request 클래스 사용 예제 ===\n";
        
        // Request 객체 생성 (모의 데이터로 GET 및 POST 설정)
        $_GET = ['page' => 1, 'sort' => 'name'];
        $_POST = ['search' => '홍길동', 'filter' => 'active'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/users?page=1&sort=name';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        
        $request = new \App\Core\Http\Request();
        
        // GET 파라미터 조회
        echo "GET 파라미터: " . print_r($request->getQuery(), true);
        echo "GET 'page' 파라미터: " . $request->getQuery('page') . "\n";
        
        // POST 파라미터 조회
        echo "POST 파라미터: " . print_r($request->getPost(), true);
        echo "POST 'search' 파라미터: " . $request->getPost('search') . "\n";
        
        // 통합 파라미터 조회
        echo "모든 파라미터: " . print_r($request->all(), true);
        
        // 요청 메소드 및 URI 조회
        echo "요청 메소드: " . $request->getMethod() . "\n";
        echo "요청 URI: " . $request->getUri() . "\n";
        
        // 헤더 및 Ajax 요청 확인
        echo "X-Requested-With 헤더: " . $request->getHeader('X-Requested-With') . "\n";
        echo "Ajax 요청 여부: " . ($request->isAjax() ? 'true' : 'false') . "\n";
    }
    
    /**
     * ValidatedRequest 클래스 사용 예제 실행
     */
    public function testValidatedRequest()
    {
        echo "\n=== ValidatedRequest 클래스 사용 예제 ===\n";
        
        // ValidatedRequest 객체 생성 (모의 데이터 설정)
        $_POST = [
            'name' => '홍길동',
            'email' => 'hong@example.com',
            'password' => 'secret123',
            'age' => 25
        ];
        
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
            echo "유효성 검증 성공!\n";
            echo "검증된 데이터: " . print_r($request->validated(), true);
        } else {
            echo "유효성 검증 실패!\n";
            echo "오류 메시지: " . print_r($request->getErrors(), true);
        }
        
        // 잘못된 데이터로 테스트
        echo "\n- 잘못된 데이터 테스트 -\n";
        $_POST = [
            'name' => 'A', // 너무 짧음
            'email' => 'invalid-email', // 이메일 형식 아님
            'password' => '123', // 너무 짧음
            'age' => 16 // 18세 미만
        ];
        
        $request = new \App\Core\Http\ValidatedRequest();
        $request->setRules($rules);
        
        if ($request->validate()) {
            echo "유효성 검증 성공!\n";
            echo "검증된 데이터: " . print_r($request->validated(), true);
        } else {
            echo "유효성 검증 실패!\n";
            echo "오류 메시지: " . print_r($request->getErrors(), true);
        }
    }
    
    /**
     * 미들웨어 체인 사용 예제 실행
     */
    public function testMiddlewareChain()
    {
        echo "\n=== 미들웨어 체인 사용 예제 ===\n";
        
        // 간단한 로깅 미들웨어 생성
        $loggingMiddleware = new class implements \App\Core\Middleware\MiddlewareInterface {
            public function process($request, callable $next) {
                echo "로깅 미들웨어: 요청 처리 시작\n";
                $response = $next($request);
                echo "로깅 미들웨어: 요청 처리 완료\n";
                return $response;
            }
        };
        
        // 권한 검사 미들웨어 생성
        $authMiddleware = new class implements \App\Core\Middleware\MiddlewareInterface {
            public function process($request, callable $next) {
                echo "인증 미들웨어: 사용자 인증 검사\n";
                
                // 인증 검사 모의 처리 (실제로는 세션 등 확인)
                $isAuthenticated = true;
                
                if (!$isAuthenticated) {
                    return new \App\Core\Http\Response("인증 실패", 401);
                }
                
                echo "인증 미들웨어: 인증 성공\n";
                return $next($request);
            }
        };
        
        // CSRF 보호 미들웨어 생성
        $csrfMiddleware = new class implements \App\Core\Middleware\MiddlewareInterface {
            public function process($request, callable $next) {
                echo "CSRF 미들웨어: 토큰 검증\n";
                
                // CSRF 토큰 검증 모의 처리
                $isValidToken = true;
                
                if (!$isValidToken) {
                    return new \App\Core\Http\Response("CSRF 토큰 검증 실패", 403);
                }
                
                echo "CSRF 미들웨어: 토큰 검증 성공\n";
                return $next($request);
            }
        };
        
        // 미들웨어 스택 생성
        $middlewares = [$loggingMiddleware, $authMiddleware, $csrfMiddleware];
        
        // Request 객체 생성
        $request = new \App\Core\Http\Request();
        
        // 미들웨어 체인 실행 함수
        $runner = function ($request, $middlewares, $index = 0) use (&$runner) {
            if ($index >= count($middlewares)) {
                echo "모든 미들웨어 통과 - 컨트롤러 실행\n";
                return new \App\Core\Http\Response("Hello World", 200);
            }
            
            $middleware = $middlewares[$index];
            return $middleware->process($request, function ($request) use ($runner, $middlewares, $index) {
                return $runner($request, $middlewares, $index + 1);
            });
        };
        
        // 미들웨어 체인 실행
        $response = $runner($request, $middlewares, 0);
        
        // 응답 결과 출력
        echo "\n응답 결과:\n";
        echo "응답 객체: " . print_r($response, true) . "\n";
    }
    
    /**
     * 모든 테스트 실행
     */
    public function runAllTests()
    {
        $this->testRequest();
        $this->testValidatedRequest();
        $this->testMiddlewareChain();
    }
}

// 테스트 실행
$test = new MiddlewareTest();
$test->runAllTests(); 