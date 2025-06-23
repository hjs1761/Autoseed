# Tests 디렉토리

이 디렉토리는 프로젝트 테스트 파일을 저장하는 공간입니다.

## 폴더 구조

```
tests/
  └── examples/     - 코드 예제 파일
      ├── validation_example.php  - 유효성 검증 예제
      ├── api_example.php         - API 호출 예제
      └── db_example.php          - 데이터베이스 조작 예제
```

## 예제 파일

다음의 예제 파일을 통해 시스템의 주요 기능을 테스트하고 사용법을 확인할 수 있습니다:

### 1. validation_example.php

유효성 검증 시스템의 사용 예제를 보여줍니다. `App\Utils\Validator` 클래스와 헬퍼 함수를 통해 다양한 유효성 검증 규칙을 적용하는 방법을 확인할 수 있습니다.

### 2. api_example.php

API 엔드포인트를 호출하고 응답을 처리하는 방법을 보여줍니다. 인플루언서 API를 예시로 하여 데이터 조회, 생성, 수정, 삭제 등의 작업을 수행합니다.

### 3. db_example.php

데이터베이스 작업을 수행하는 방법을 보여줍니다. Model 클래스를 사용하여 데이터를 조회, 삽입, 수정, 삭제하는 방법과 트랜잭션을 관리하는 방법을 확인할 수 있습니다.

### 4. middleware_example.php

미들웨어 아키텍처와 Request 클래스 사용법을 보여줍니다. 요청 처리, 데이터 유효성 검증, 미들웨어 체인 구성 등의 기능을 확인할 수 있습니다. 이 예제는 새로 구현된 미들웨어 시스템이 어떻게 동작하는지 이해하는 데 도움이 됩니다.

## 예제 실행 방법

```bash
# 예제 실행
php tests/examples/validation_example.php
php tests/examples/api_example.php
php tests/examples/db_example.php
php tests/examples/middleware_example.php
```

자세한 사용법은 각 예제 파일의 주석이나 다음 문서를 참조하세요:

- [유효성 검증 가이드](../docs/validator_guide.md)
- [미들웨어 및 Request 클래스 사용 가이드](../docs/middleware_guide.md)

새로운 테스트 파일이나 예제를 추가하려면 해당 디렉토리에 적절한 PHP 파일을 생성하세요. 