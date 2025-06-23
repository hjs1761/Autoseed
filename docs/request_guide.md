# Request 클래스 사용 가이드

이 문서는 `Request` 클래스 및 객체지향적 HTTP 요청 처리 방식에 대한 안내입니다.

## 1. 개요

`Request` 클래스는 HTTP 요청 데이터(`$_GET`, `$_POST`, `$_SERVER` 등의 슈퍼글로벌 변수)에 대한 객체지향적 접근 방식을 제공합니다. 이 클래스를 사용함으로써 코드의 가독성, 유지보수성, 테스트 용이성이 향상됩니다.

## 2. Request 클래스 구조

```php
namespace App\Core\Http;

class Request
{
    protected array $get;    // $_GET 데이터
    protected array $post;   // $_POST 데이터
    protected array $files;  // $_FILES 데이터
    protected array $server; // $_SERVER 데이터
    protected array $cookies;// $_COOKIE 데이터
    protected string $content; // HTTP 요청 본문

    // 메서드들...
}
```

## 3. 컨트롤러에서 Request 객체 사용하기

### 3.1 컨트롤러 메서드 시그니처

모든 컨트롤러 메서드는 첫 번째 파라미터로 `Request` 객체를 받아야 합니다:

```php
/**
 * 회원 목록 API (GET)
 * 
 * @param Request $request 요청 객체
 */
public function list(Request $request)
{
    // Request 객체 사용
}
```

### 3.2 쿼리 파라미터 접근

```php
// 특정 GET 파라미터 조회 (기본값 지정 가능)
$page = $request->getQuery('page', 1);
$keyword = $request->getQuery('keyword', '');

// 모든 GET 파라미터 조회
$allParams = $request->getQuery();
```

### 3.3 POST 데이터 접근

```php
// 특정 POST 파라미터 조회
$name = $request->getPost('name');
$email = $request->getPost('email', '');

// 모든 POST 파라미터 조회
$postData = $request->getPost();
```

### 3.4 통합 파라미터 접근 (GET + POST)

```php
// GET 또는 POST에서 파라미터 조회 (POST 우선)
$id = $request->get('id');

// 모든 파라미터 (GET + POST) 조회
$allParams = $request->all();
```

### 3.5 JSON 요청 데이터 접근

```php
// JSON 요청 본문 파싱
$jsonData = $request->getJson();
```

### 3.6 요청 메타데이터 접근

```php
// HTTP 메서드 조회
$method = $request->getMethod();

// URI 조회
$uri = $request->getUri();

// 헤더 조회
$userAgent = $request->getHeader('User-Agent');
$contentType = $request->getHeader('Content-Type');

// AJAX 요청 여부 확인
if ($request->isAjax()) {
    // AJAX 요청 처리
}
```

## 4. 객체지향적 방식의 이점

### 4.1 캡슐화

슈퍼글로벌 변수에 대한 직접 접근을 캡슐화하여 코드의 결합도를 낮추고 재사용성을 높입니다.

### 4.2 테스트 용이성

```php
// 테스트에서 Request 객체 모킹 가능
$mockRequest = $this->createMock(Request::class);
$mockRequest->method('getQuery')
    ->with('id')
    ->willReturn('123');

// 컨트롤러 테스트
$controller = new UserController();
$result = $controller->detail($mockRequest, 123);
```

### 4.3 미들웨어 지원

Request 객체는 미들웨어 체인을 통과하면서 변형될 수 있으며, 이를 통해 인증, 유효성 검증 등의 횡단 관심사를 처리할 수 있습니다.

### 4.4 일관된 인터페이스

프레임워크 전반에 걸쳐 일관된 방식으로 요청 데이터에 접근할 수 있어 개발자 경험이 향상됩니다.

## 5. ValidatedRequest 클래스

`ValidatedRequest`는 `Request`를 확장한 클래스로, 유효성 검증 기능을 추가로 제공합니다:

```php
// ValidatedRequest 객체 생성
$request = new ValidatedRequest();

// 유효성 검증 규칙 설정
$request->setRules([
    'name' => ['required', 'min:2'],
    'email' => ['required', 'email'],
    'password' => ['required', 'min:8']
]);

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

## 6. 모범 사례

### 6.1 컨트롤러에서 Request 객체 주입받기

```php
public function update(Request $request, $id)
{
    $params = [
        'name' => $request->getPost('name'),
        'email' => $request->getPost('email')
    ];
    
    // 서비스 계층 호출
    $this->userService->updateUser($id, $params);
}
```

### 6.2 서비스 계층에서 필요한 데이터만 전달받기

```php
// 좋은 예: 컨트롤러에서 필요한 데이터만 추출하여 서비스에 전달
$params = [
    'name' => $request->getPost('name'),
    'email' => $request->getPost('email')
];
$this->userService->updateUser($id, $params);

// 나쁜 예: Request 객체를 서비스 계층으로 전달 (계층 간 결합도 증가)
$this->userService->updateUser($id, $request);
```

### 6.3 헬퍼 메서드 작성

컨트롤러에서 자주 사용하는 패턴이 있다면 헬퍼 메서드를 작성하여 코드 중복을 줄이세요:

```php
/**
 * 요청 객체에서 검색 파라미터를 추출합니다.
 * 
 * @param Request $request 요청 객체
 * @return array 검색 파라미터
 */
private function getSearchParams(Request $request)
{
    return [
        'keyword' => $request->getQuery('keyword', ''),
        'status' => $request->getQuery('status'),
        'page' => (int)$request->getQuery('page', 1),
        'limit' => (int)$request->getQuery('limit', 20)
    ];
}
```

## 7. 마이그레이션 가이드

기존 코드를 슈퍼글로벌 변수에서 Request 객체 사용 방식으로 마이그레이션할 때 다음 단계를 따르세요:

1. 컨트롤러 메서드가 첫 번째 파라미터로 `Request $request`를 받도록 수정
2. 슈퍼글로벌 변수 직접 참조를 Request 객체 메서드 호출로 대체
3. 공통으로 사용되는 Request 관련 로직을 헬퍼 메서드로 추출
4. 단계적으로 모든 컨트롤러에 적용

### 변경 예시:

**변경 전:**
```php
public function list()
{
    $keyword = $_GET['keyword'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    // ...
}
```

**변경 후:**
```php
public function list(Request $request)
{
    $keyword = $request->getQuery('keyword', '');
    $page = intval($request->getQuery('page', 1));
    // ...
}
``` 