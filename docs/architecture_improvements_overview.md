# MVC 아키텍처 개선 요약

## 기존 구조의 문제점

### 미들웨어 구현
- 인라인 코드: `public/index.php`에 인증 로직이 직접 포함됨
- 재사용 어려움: 다른 프로젝트에 동일한 로직 적용 불가
- 테스트 어려움: 독립적인 테스트 불가능
- 유지보수 어려움: 로직이 애플리케이션 코드와 혼합됨

### 요청 처리
- 슈퍼글로벌 변수 직접 사용: `$_GET`, `$_POST`, `$_REQUEST` 직접 접근
- 유효성 검증 중복: 각 컨트롤러마다 같은 검증 코드 반복
- 테스트 어려움: 슈퍼글로벌 모킹 어려움
- 일관성 부족: 컨트롤러마다 다른 접근 방식

## 개선 방안

### 1. 미들웨어 클래스 도입
```php
// 미들웨어 인터페이스
interface MiddlewareInterface {
    public function process($request, callable $next);
}

// 인증 미들웨어 예시
class AuthMiddleware implements MiddlewareInterface {
    public function process($request, callable $next) {
        // 인증 검사 로직
        if (!$this->checkSession()) {
            // 인증 실패 처리
            return new Response(...);
        }
        return $next($request);
    }
}
```

### 2. Request 클래스 도입
```php
// Request 클래스 예시
class Request {
    protected array $get;
    protected array $post;
    
    public function __construct() {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
    }
    
    public function getQuery($key, $default = null) {
        return $this->get[$key] ?? $default;
    }
    
    public function getPost($key, $default = null) {
        return $this->post[$key] ?? $default;
    }
}
```

## 이점

### 미들웨어 클래스
- **재사용성**: 다른 프로젝트에서도 쉽게 사용 가능
- **테스트 용이성**: 독립적으로 단위 테스트 가능
- **유지보수성**: 각 미들웨어가 단일 책임을 가짐
- **확장성**: 새로운 미들웨어를 쉽게 추가 가능

### Request 클래스
- **일관된 API**: 요청 데이터 접근의 표준화
- **테스트 용이성**: 요청 객체를 쉽게 모킹 가능
- **보안 강화**: 입력 데이터의 일관된 필터링
- **유지보수성**: 요청 처리 로직 변경 시 한 곳만 수정

## 구현 단계

1. Request 클래스 먼저 도입
2. 컨트롤러에 Request 객체 주입
3. 미들웨어 인터페이스 정의
4. 핵심 미들웨어 구현 (인증, CSRF 등)
5. 미들웨어 스택 구성

## 기대 효과

- **코드 품질 향상**: 더 명확하고 테스트 가능한 구조
- **보안 강화**: 일관된 입력 검증과 보안 처리
- **개발 생산성**: 중복 코드 감소와 재사용성 증가
- **유지보수성**: 변경 사항의 영향 범위 축소 