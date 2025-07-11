# 테스트 파일 사용 가이드

이 문서는 인플루언서 솔루션의 테스트 파일 구조와 사용법에 대해 설명합니다.

## 테스트 파일 구조

모든 테스트 파일은 `tests/examples/` 디렉토리에 위치해 있습니다:

```
tests/
  └── examples/
      ├── validation_example.php     - 유효성 검증 예제
      ├── api_example.php            - API 호출 예제
      ├── mock_db_example.php        - 모의 데이터베이스 예제
      ├── middleware_example.php     - 미들웨어 예제
      ├── request_test.php           - Request 클래스 테스트
      └── middleware_chain_test.php  - 미들웨어 체인 테스트
```

## 테스트 파일 실행 방법

각 테스트 파일은 다음과 같이 PHP CLI를 통해 실행할 수 있습니다:

```bash
php -f tests/examples/validation_example.php
php -f tests/examples/api_example.php
php -f tests/examples/mock_db_example.php
php -f tests/examples/middleware_example.php
php -f tests/examples/request_test.php
php -f tests/examples/middleware_chain_test.php
```

## 테스트 파일별 설명

### 1. validation_example.php

유효성 검증 시스템의 사용법을 보여줍니다:

- `App\Utils\Validator` 클래스 직접 사용 방법
- validate() 헬퍼 함수를 통한 유효성 검증
- 다양한 검증 규칙 적용 방법
- 성공/실패 케이스 시뮬레이션

### 2. api_example.php

API 호출 및 응답 처리 방법을 모의로 시뮬레이션합니다:

- 인플루언서 목록 조회 API 호출
- 인플루언서 상세 정보 조회 API 호출
- 인플루언서 생성 API 호출 (성공/실패 케이스)
- API 응답 처리 및 데이터 추출 방법

### 3. mock_db_example.php

실제 데이터베이스 연결 없이 데이터베이스 작업을 시뮬레이션합니다:

- MockDB 클래스를 통한 데이터베이스 모의 구현
- 기본 CRUD 작업 (생성, 조회, 수정, 삭제)
- 조건부 쿼리 및 정렬 기능
- 트랜잭션 관리 방법
- 관계(조인) 시뮬레이션

### 4. middleware_example.php

미들웨어 아키텍처 및 Request 클래스 사용법을 보여줍니다:

- Request 클래스 기본 사용법
- ValidatedRequest 클래스를 통한 유효성 검증
- 커스텀 미들웨어 구현 방법
- 미들웨어 체인 구성 및 실행 방법

### 5. request_test.php

Request 및 Response 클래스의 기능을 테스트합니다:

- Request 객체를 통한 GET/POST 파라미터 접근
- 헤더 및 요청 메타데이터 조회
- ValidatedRequest를 통한 입력 유효성 검증
- Response, JsonResponse, RedirectResponse 객체 생성 및 사용

#### Request 클래스 테스트 상세 예시

```php
// 1. 기본 Request 객체 생성
$request = new \App\Core\Http\Request();

// 2. GET 파라미터 테스트
$_GET['id'] = '123';
$_GET['page'] = '2';
$request = new \App\Core\Http\Request();  // 새 요청으로 GET 변경사항 반영
echo "ID: " . $request->getQuery('id') . "\n";
echo "Page: " . $request->getQuery('page') . "\n";
echo "Default value: " . $request->getQuery('unknown', 'default') . "\n";

// 3. POST 파라미터 테스트
$_POST['name'] = 'John Doe';
$_POST['email'] = 'john@example.com';
$request = new \App\Core\Http\Request();  // 새 요청으로 POST 변경사항 반영
echo "Name: " . $request->getPost('name') . "\n";
echo "Email: " . $request->getPost('email') . "\n";

// 4. 통합 파라미터 접근 테스트
echo "ID from get(): " . $request->get('id') . "\n";
echo "Name from get(): " . $request->get('name') . "\n";

// 5. 모든 파라미터 조회
$allParams = $request->all();
echo "All parameters count: " . count($allParams) . "\n";

// 6. 헤더 테스트
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test';
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$request = new \App\Core\Http\Request();
echo "User-Agent: " . $request->getHeader('User-Agent') . "\n";
echo "Accept: " . $request->getHeader('Accept') . "\n";

// 7. 요청 메타데이터 테스트
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/users?page=2';
$request = new \App\Core\Http\Request();
echo "Method: " . $request->getMethod() . "\n";
echo "URI: " . $request->getUri() . "\n";

// 8. 요청 메소드 확인 테스트
echo "Is POST? " . ($request->isMethod('POST') ? 'Yes' : 'No') . "\n";
echo "Is GET? " . ($request->isMethod('GET') ? 'Yes' : 'No') . "\n";
```

#### 컨트롤러 테스트를 위한 MockRequest 클래스

```php
/**
 * 테스트용 Request 모의 클래스
 */
class MockRequest extends \App\Core\Http\Request
{
    /**
     * 테스트를 위한 MockRequest 생성
     * 
     * @param array $get GET 파라미터
     * @param array $post POST 파라미터
     * @param array $server 서버 정보
     * @param string $content 요청 본문
     */
    public function __construct(array $get = [], array $post = [], array $server = [], string $content = '')
    {
        // 부모 클래스의 생성자를 호출하지 않고 직접 속성 설정
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->content = $content;
        $this->files = [];
        $this->cookies = [];
    }
    
    /**
     * 요청 본문 설정
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    
    /**
     * 서버 변수 설정
     */
    public function setServerParam(string $key, string $value): void
    {
        $this->server[$key] = $value;
    }
}

// 테스트 예시
$mockRequest = new MockRequest(
    ['page' => '1', 'limit' => '20'],                              // GET 파라미터
    ['name' => 'Jane Doe', 'email' => 'jane@example.com'],         // POST 파라미터
    ['HTTP_CONTENT_TYPE' => 'application/json', 'REQUEST_METHOD' => 'POST'] // 서버 정보
);

// 컨트롤러 테스트
$userController = new \App\Controllers\Api\UserApiController();
$result = $userController->list($mockRequest);
```

### 6. middleware_chain_test.php

미들웨어 체인의 작동 방식을 상세히 시뮬레이션합니다:

- 로깅, 인증, 유효성 검증 미들웨어 구현
- 미들웨어 스택 구성 및 순서 관리
- 다양한 요청 시나리오 테스트
  - 공개 경로 접근 (인증 불필요)
  - 보호된 경로 접근 (인증 필요)
  - 유효하지 않은 데이터로 요청
  - 유효한 데이터로 요청

## 테스트 작성 시 참고사항

새로운 테스트 파일을 작성할 때 참고할 수 있는 지침입니다:

1. **테스트 목적 정의**: 테스트하려는 기능/컴포넌트를 명확히 정의합니다.
2. **필요한 의존성 포함**: 테스트에 필요한 파일을 모두 require 합니다.
3. **클래스 및 함수에 주석 추가**: PHPDoc 형식으로 클래스, 메서드, 파라미터에 주석을 추가합니다.
4. **테스트 케이스 구성**: 성공/실패 케이스를 모두 포함하여 다양한 시나리오를 테스트합니다.
5. **명확한 출력**: 테스트 결과를 명확하게 출력하여 결과를 쉽게 확인할 수 있도록 합니다.
6. **모의 객체 활용**: 데이터베이스, 외부 API 등 외부 의존성이 있는 경우 모의 객체를 활용합니다.

## 코드 품질 유지를 위한 지침

테스트 코드 품질을 유지하기 위한 지침입니다:

1. **일관된 코딩 스타일**: PSR-12 코딩 표준을 따릅니다.
2. **의미 있는 변수명**: 변수와 함수 이름은 그 목적과 기능을 명확히 나타내야 합니다.
3. **적절한 주석**: 복잡한 로직이나 중요한 로직에는 주석을 추가합니다.
4. **오류 처리**: 예외가 발생할 수 있는 코드에는 try-catch 블록을 사용합니다.
5. **재사용성 고려**: 공통 로직은 별도의 함수나 클래스로 분리하여 재사용성을 높입니다.

## 관련 문서

테스트 관련 추가 정보는 다음 문서를 참조하세요:

- [유효성 검증 가이드](validator_guide.md)
- [미들웨어 및 Request 클래스 가이드](middleware_guide.md)
- [아키텍처 개선 가이드](architecture_improvements.md) 
- [Request 클래스 사용 가이드](request_guide.md) 
