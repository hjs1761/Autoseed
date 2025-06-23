# 테스트 사용법 가이드

## 개요

이 문서는 인플루언서 솔루션의 테스트 기능 사용법을 설명합니다. 테스트 예제 파일은 시스템의 주요 기능을 이해하고 확인하는 데 도움이 됩니다.

## 예제 파일 위치

모든 테스트 예제 파일은 `tests/examples/` 디렉토리에 위치하고 있습니다:

```
tests/
  └── examples/
      ├── validation_example.php  - 유효성 검증 예제
      ├── api_example.php         - API 호출 예제
      └── db_example.php          - 데이터베이스 조작 예제
```

## 예제 파일 실행 방법

각 예제 파일은 다음과 같이 PHP CLI를 통해 실행할 수 있습니다:

```bash
php tests/examples/validation_example.php
php tests/examples/api_example.php
php tests/examples/db_example.php
```

## 각 예제 파일 상세 설명

### 1. validation_example.php

이 파일은 유효성 검증 시스템의 사용법을 보여줍니다:

- `App\Utils\Validator` 클래스 직접 사용 방법
- `validation_helper.php`의 헬퍼 함수 사용 방법
- 유효성 검증 성공/실패 케이스 확인

주요 기능:
- 필수 필드 검증
- 이메일 형식 검증
- 최소 길이 검증
- 숫자 범위 검증

### 2. api_example.php

이 파일은 API 호출 및 응답 처리 방법을 시뮬레이션합니다:

- 인플루언서 목록 조회 API 호출
- 인플루언서 상세 정보 조회 API 호출
- 인플루언서 생성 API 호출 (성공/실패 케이스)

주요 기능:
- HTTP 메소드(GET, POST) 사용
- API 응답 형식 이해
- 에러 처리 방법

### 3. db_example.php

이 파일은 데이터베이스 조작 예제를 보여줍니다:

- `Model` 클래스 사용 방법
- DB 쿼리 실행 방법
- 트랜잭션 처리 방법

주요 기능:
- 데이터 조회
- 데이터 삽입
- 데이터 수정
- 데이터 삭제

### 4. middleware_example.php

이 파일은 새로 구현된 미들웨어 아키텍처와 Request 클래스 사용법을 보여줍니다:

- `Request` 클래스 사용 방법
- `ValidatedRequest` 클래스를 통한 유효성 검증
- 미들웨어 체인 구현 및 사용 방법

주요 기능:
- HTTP 요청 처리
- 요청 데이터 유효성 검증
- 인증, CSRF 보호, 로깅 등의 미들웨어 체인 구성

## 예제 파일 활용 방법

각 예제 파일은 독립적으로 실행하여 특정 기능의 동작을 확인할 수 있습니다. 이 예제 파일들을 통해 시스템의 주요 컴포넌트를 이해하고, 실제 개발 시 참조할 수 있습니다.

```php
// 예제 코드 중 일부
$request = new \App\Core\Http\Request();
$data = $request->getJson();  // JSON 요청 데이터 파싱
```

실제 개발 시에는 해당 예제를 참조하여 컨트롤러나 서비스 클래스에서 활용할 수 있습니다.

## 자체 테스트 작성 방법

자체 테스트를 작성하려면 다음 가이드라인을 따르세요:

1. `tests/examples/` 디렉토리에 새 PHP 파일 생성
2. 필요한 클래스 및 파일 포함(require_once)
3. 테스트 케이스 작성
4. 결과 출력

예시:
```php
<?php
// 필요한 파일 포함
require_once __DIR__ . '/../../app/Core/SomeClass.php';

// 테스트 데이터 준비
$testData = [/* 테스트 데이터 */];

// 기능 테스트
echo "=== 테스트 시작 ===\n";
// 테스트 코드 작성
// ...

echo "테스트 완료!\n";
```

## 문제 해결

테스트 실행 중 문제가 발생한 경우:

1. PHP 버전이 7.4 이상인지 확인
2. 필요한 확장 기능이 활성화되어 있는지 확인
3. 데이터베이스 연결 정보가 올바른지 확인
4. 환경 변수(.env)가 올바르게 설정되어 있는지 확인 