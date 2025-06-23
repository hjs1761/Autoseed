<?php
/**
 * Request 클래스와 응답 처리 테스트 예제
 * 
 * 이 파일은 Request 및 Response 클래스의 기능을 테스트합니다.
 */

// 필요한 파일 포함
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../include/common.php';
require_once __DIR__ . '/../../include/validation_helper.php';

/**
 * RequestResponseTest 클래스
 * 
 * 이 클래스는 Request, ValidatedRequest, Response 등의 HTTP 관련 클래스들의
 * 기능을 테스트하고 사용법을 보여주는 예제를 제공합니다.
 */
class RequestResponseTest
{
    /**
     * Request 생성 및 사용 테스트
     * 
     * 이 메서드는 다음 기능을 테스트합니다:
     * - Request 객체 생성
     * - GET/POST 파라미터 조회
     * - 요청 메서드 및 URI 조회
     * - HTTP 헤더 조회
     */
    public function testRequest()
    {
        echo "=== Request 클래스 테스트 ===\n";
        
        // GET 파라미터 설정
        $_GET = [
            'page' => 1,
            'category' => 'beauty',
            'sort' => 'followers'
        ];
        
        // POST 파라미터 설정
        $_POST = [
            'name' => '테스트 인플루언서',
            'handle' => 'testinfluencer',
            'bio' => '인플루언서 테스트 계정입니다.',
            'platform_id' => 1
        ];
        
        // 서버 정보 설정
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/influencers?page=1&category=beauty';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test';
        
        // Request 객체 생성
        $request = new \App\Core\Http\Request();
        
        // GET 파라미터 테스트
        echo "GET 파라미터 테스트:\n";
        echo "- page: " . $request->getQuery('page') . "\n";
        echo "- category: " . $request->getQuery('category') . "\n";
        echo "- 없는 파라미터 (기본값 사용): " . $request->getQuery('unknown', 'default') . "\n";
        echo "- 모든 GET 파라미터: " . json_encode($request->getQuery()) . "\n";
        
        // POST 파라미터 테스트
        echo "\nPOST 파라미터 테스트:\n";
        echo "- name: " . $request->getPost('name') . "\n";
        echo "- handle: " . $request->getPost('handle') . "\n";
        echo "- 없는 파라미터 (기본값 사용): " . $request->getPost('unknown', 'default') . "\n";
        echo "- 모든 POST 파라미터: " . json_encode($request->getPost()) . "\n";
        
        // 통합 파라미터 조회
        echo "\n통합 파라미터 조회 (GET + POST):\n";
        echo "- page: " . $request->get('page') . "\n";
        echo "- name: " . $request->get('name') . "\n";
        echo "- 모든 파라미터: " . json_encode($request->all()) . "\n";
        
        // 요청 정보 조회
        echo "\n요청 정보 조회:\n";
        echo "- HTTP 메소드: " . $request->getMethod() . "\n";
        echo "- URI: " . $request->getUri() . "\n";
        echo "- Accept 헤더: " . $request->getHeader('Accept') . "\n";
        echo "- User-Agent 헤더: " . $request->getHeader('User-Agent') . "\n";
    }
    
    /**
     * ValidatedRequest 테스트
     * 
     * 이 메서드는 다음 기능을 테스트합니다:
     * - ValidatedRequest 객체 생성
     * - 유효성 검증 규칙 설정
     * - 성공/실패 케이스에 대한 유효성 검증
     * - 검증된 데이터 및 오류 메시지 조회
     */
    public function testValidatedRequest()
    {
        echo "\n=== ValidatedRequest 클래스 테스트 ===\n";
        
        // 성공 케이스 테스트
        echo "성공 케이스 테스트:\n";
        $_POST = [
            'name' => '김예지',
            'email' => 'kim@example.com',
            'age' => 25,
            'password' => 'secure123'
        ];
        
        $request = new \App\Core\Http\ValidatedRequest();
        $rules = [
            'name' => ['required', 'min:2'],
            'email' => ['required', 'email'],
            'age' => ['required', 'numeric', 'min_value:18'],
            'password' => ['required', 'min:6']
        ];
        
        $request->setRules($rules);
        $result = $request->validate();
        
        echo "- 유효성 검증 결과: " . ($result ? '성공' : '실패') . "\n";
        
        if ($result) {
            $validData = $request->validated();
            echo "- 검증된 데이터: " . json_encode($validData) . "\n";
        } else {
            echo "- 오류: " . json_encode($request->getErrors()) . "\n";
        }
        
        // 실패 케이스 테스트
        echo "\n실패 케이스 테스트:\n";
        $_POST = [
            'name' => 'K', // 너무 짧음
            'email' => 'invalid-email', // 이메일 형식 아님
            'age' => 17, // 18 미만
            'password' => 'short' // 6자 미만
        ];
        
        $request = new \App\Core\Http\ValidatedRequest();
        $request->setRules($rules);
        $result = $request->validate();
        
        echo "- 유효성 검증 결과: " . ($result ? '성공' : '실패') . "\n";
        
        if ($result) {
            $validData = $request->validated();
            echo "- 검증된 데이터: " . json_encode($validData) . "\n";
        } else {
            echo "- 오류: " . json_encode($request->getErrors()) . "\n";
        }
    }
    
    /**
     * Response 클래스 테스트
     * 
     * 이 메서드는 다음 기능을 테스트합니다:
     * - 기본 Response 객체 생성
     * - JsonResponse 객체 생성 및 데이터 설정
     * - RedirectResponse 객체 생성 및 리다이렉트 URL 설정
     */
    public function testResponse()
    {
        echo "\n=== Response 클래스 테스트 ===\n";
        
        // 테스트를 위한 출력 캡처 시작
        ob_start();
        
        // 기본 Response 생성
        $response = new \App\Core\Http\Response('Hello World', 200, ['X-Test' => 'Test-Value']);
        
        // 출력 버퍼 비우기
        ob_end_clean();
        
        echo "기본 Response 테스트:\n";
        echo "- 내용 요약: " . get_class($response) . " 객체, 상태 코드: 200\n";
        
        // JsonResponse 테스트
        $data = [
            'success' => true,
            'message' => '요청이 성공적으로 처리되었습니다.',
            'data' => [
                'id' => 1,
                'name' => '홍길동',
                'follower_count' => 50000
            ]
        ];
        
        $jsonResponse = new \App\Core\Http\JsonResponse($data, 200);
        
        echo "\nJsonResponse 테스트:\n";
        echo "- 내용 요약: " . get_class($jsonResponse) . " 객체, 상태 코드: 200\n";
        echo "- 데이터: " . var_export($data, true) . "\n";
        
        // RedirectResponse 테스트
        $redirectResponse = new \App\Core\Http\RedirectResponse('/dashboard', 302);
        
        echo "\nRedirectResponse 테스트:\n";
        echo "- 내용 요약: " . get_class($redirectResponse) . " 객체, 상태 코드: 302\n";
        echo "- 리다이렉트 URL: /dashboard\n";
    }
    
    /**
     * 모든 테스트 실행
     * 
     * 이 메서드는 클래스 내의 모든 테스트 메서드를 순차적으로 실행합니다.
     */
    public function runAllTests()
    {
        $this->testRequest();
        $this->testValidatedRequest();
        $this->testResponse();
    }
}

// 테스트 실행
// 이 파일이 직접 실행될 때 모든 테스트를 자동으로 실행합니다.
$test = new RequestResponseTest();
$test->runAllTests(); 