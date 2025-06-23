# 인플루언서 솔루션

인플루언서 데이터를 관리하고 검색할 수 있는 솔루션입니다. MVC 패턴을 기반으로 구현되었으며, 인플루언서 데이터 관리를 위한 웹 인터페이스와 API를 제공합니다.

## 주요 기능

- 인플루언서 목록 조회 및 검색
- 인플루언서 상세 정보 조회
- 인플루언서 생성 및 수정
- 인플루언서 카테고리 관리
- 플랫폼별 인플루언서 조회
- 외부 시스템으로부터 인플루언서 데이터 임포트

## 기술 스택

- PHP 7.4 이상
- MySQL 5.7 이상
- [Composer](https://getcomposer.org/)
- [nikic/fast-route](https://github.com/nikic/FastRoute) - 라우팅
- [monolog/monolog](https://github.com/Seldaek/monolog) - 로깅
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) - 환경 변수 관리
- [symfony/http-foundation](https://github.com/symfony/http-foundation) - HTTP 요청/응답 처리

## 디렉토리 구조

```
app/
  Controllers/       - 웹 및 API 컨트롤러
    Api/            - API 컨트롤러
  Core/             - 핵심 프레임워크 클래스
  Models/           - 데이터 모델
  Views/            - 뷰 템플릿
  Services/         - 비즈니스 로직
  Utils/            - 유틸리티 클래스
config/             - 구성 파일
include/            - 공통 포함 파일
public/             - 공개 접근 디렉토리
storage/            - 로그 및 임시 파일
vendor/             - Composer 패키지
docs/               - 문서 파일
.github/            - GitHub 템플릿 및 워크플로우
```

## 설치 방법

1. 저장소를 복제합니다:
   ```
   git clone https://github.com/yourusername/influencer-solution.git
   cd influencer-solution
   ```

2. Composer 종속성을 설치합니다:
   ```
   composer install
   ```

3. 환경 설정 파일을 생성합니다:
   ```
   cp example.env .env
   ```

4. 환경 설정 파일을 편집하여 데이터베이스 연결 정보를 설정합니다:
   ```
   DB_HOST=localhost
   DB_NAME=influencer_db
   DB_USER=root
   DB_PASS=your_password
   ```

5. 데이터베이스 테이블을 생성합니다:
   ```
   php database/migrate.php
   ```

## 환경 설정

프로젝트를 실행하기 전에 환경 설정이 필요합니다:

1. `example.env` 파일을 `.env`로 복사하고 환경에 맞게 수정합니다.
2. 자세한 환경 변수 설정 방법은 [환경 변수 설정 가이드](docs/env_setup.md)를 참조하세요.

## 프로젝트 기여하기

이 프로젝트에 기여하고 싶으시다면 다음 가이드를 참조해주세요:

### GitHub 템플릿 사용 안내

저희 프로젝트는 일관된 이슈 및 PR 작성을 위해 템플릿을 제공하고 있습니다:

1. **이슈 작성하기**:
   - 버그 보고: `버그 리포트` 템플릿 사용
   - 기능 요청: `기능 요청` 템플릿 사용

2. **Pull Request 작성하기**:
   - PR 작성 시 제공되는 템플릿을 사용하여 변경 내용을 명확하게 설명해주세요.
   - 관련 이슈 번호를 반드시 포함해주세요.

3. **자세한 기여 방법**:
   - 상세한 기여 방법은 [기여 가이드](.github/CONTRIBUTING.md)를 참조해주세요.
   - 코드 스타일, 커밋 메시지 형식, 브랜칭 전략 등의 정보를 확인할 수 있습니다.

## API 문서

API 문서는 별도의 파일로 제공됩니다. 자세한 API 사용 방법 및 엔드포인트 설명은 다음 문서를 참조하세요:

- [API 레퍼런스](docs/api_reference.md)

## 개발 가이드

- [Git 사용 가이드](docs/git_guide.md) - 브랜치, 커밋, PR 규칙

## 테스트

프로젝트의 tests 디렉토리에는 다양한 코드 예제가 포함되어 있습니다:

```bash
# 예제 실행
php tests/examples/validation_example.php
php tests/examples/api_example.php
php tests/examples/db_example.php
```

테스트 사용법에 대한 자세한 내용은 다음 문서를 참조하세요:
- [테스트 디렉토리 안내](tests/README.md)
- [테스트 사용법 가이드](docs/test_usage.md)

## 문서

프로젝트 관련 문서는 `docs` 디렉토리에서 확인할 수 있습니다:

- [마이그레이션 가이드](migrations/README.md) - 데이터베이스 마이그레이션 사용 방법
- [유효성 검증 가이드](docs/validator_guide.md) - 유효성 검증 시스템 사용 방법
- [환경 설정 가이드](docs/env_guide.md) - 환경 변수 설정 방법
- [미들웨어 사용 가이드](docs/middleware_usage.md) - 미들웨어 사용 방법
- [아키텍처 개선 가이드](docs/architecture_improvements.md) - MVC 아키텍처 개선 방안 상세 설명
- [아키텍처 개선 요약](docs/architecture_improvements_overview.md) - MVC 아키텍처 개선 방안 요약 

## MVC 아키텍처 및 요청 처리 흐름

### MVC 구조 개요

이 프로젝트는 Model-View-Controller(MVC) 패턴을 기반으로 구축되었습니다. 각 구성 요소의 역할은 다음과 같습니다:

#### 1. Model (app/Models/)

- **역할**: 데이터 액세스 및 비즈니스 로직 처리
- **구성 요소**:
  - `Model.php`: 모든 모델의 기본 클래스
  - `Influencer.php`, `Category.php`, `Platform.php` 등: 각 엔티티에 대한 모델 클래스
- **주요 기능**:
  - 데이터베이스 CRUD 연산 처리
  - 데이터 유효성 검증
  - 엔티티 간 관계 관리

#### 2. View (app/Views/)

- **역할**: 사용자 인터페이스 표현
- **구성 요소**:
  - 레이아웃 파일 (layout/header.php, layout/footer.php)
  - 페이지별 뷰 파일 (dashboard.php, userList.php 등)
  - 재사용 가능한 뷰 조각 (fragments/)
- **주요 기능**:
  - 컨트롤러에서 전달받은 데이터를 HTML로 렌더링
  - 사용자 인터페이스 구조 정의
  - 레이아웃 및 컴포넌트 재사용

#### 3. Controller (app/Controllers/)

- **역할**: 사용자 요청 처리 및 응답 생성
- **구성 요소**:
  - `BaseController.php`: 모든 컨트롤러의 기본 클래스
  - `HomeController.php`, `UserController.php` 등: 각 기능별 컨트롤러
  - `Api/`: API 전용 컨트롤러 (JSON 응답 반환)
- **주요 기능**:
  - HTTP 요청 수신 및 처리
  - 모델을 통한 데이터 조작
  - 적절한 뷰 선택 및 데이터 전달
  - 응답 생성 (HTML 페이지 또는 JSON)

#### 4. Service Layer (app/Services/)

- **역할**: 컨트롤러와 모델 사이의 중간 계층으로, 비즈니스 로직 캡슐화
- **구성 요소**:
  - `UserService.php`, `InfluencerService.php` 등: 각 도메인별 서비스 클래스
- **주요 기능**:
  - 복잡한 비즈니스 로직 처리
  - 트랜잭션 관리
  - 여러 모델 간의 상호작용 조정

### 요청 처리 흐름

HTTP 요청이 발생하면 다음과 같은 순서로 처리됩니다:

1. **진입점 (public/index.php)**:
   - 모든 요청은 `public/index.php` 파일로 전달됩니다.
   - Composer 오토로더, 환경 변수, 설정 파일 등을 로드합니다.
   - FastRoute 라이브러리를 사용하여 라우트를 정의합니다.
   - 애플리케이션 인스턴스를 생성하고 미들웨어를 등록합니다.

2. **미들웨어 처리 (app/Core/Middleware/)**:
   - 등록된 미들웨어가 순차적으로 실행됩니다:
     1. **SessionMiddleware**: 세션 시작 및 관리
     2. **AuthMiddleware**: 사용자 인증 상태 확인
     3. **CsrfMiddleware**: CSRF 토큰 검증 (POST 요청의 경우)
   - 각 미들웨어는 요청을 전처리하고, 조건에 따라 다음 미들웨어로 전달하거나 직접 응답을 반환할 수 있습니다.

3. **라우팅 (FastRoute)**:
   - 모든 미들웨어가 성공적으로 실행되면 라우터가 URI와 HTTP 메서드를 기반으로 적절한 컨트롤러와 메서드를 결정합니다.
   - 라우트 정보는 `index.php`에 정의되어 있습니다.

4. **컨트롤러 실행**:
   - 라우터가 결정한 컨트롤러와 메서드가 실행됩니다.
   - 컨트롤러는 Request 객체를 첫 번째 매개변수로 받습니다.
   - 컨트롤러는 필요한 서비스 또는 모델 클래스를 초기화합니다.

5. **비즈니스 로직 처리 (Service 및 Model)**:
   - 컨트롤러는 서비스 레이어를 통해 비즈니스 로직을 처리합니다.
   - 서비스는 모델을 사용하여 데이터베이스와 상호작용합니다.
   - 모델은 데이터 유효성 검증 및 CRUD 연산을 수행합니다.

6. **응답 생성**:
   - 웹 페이지 요청: 컨트롤러가 `render()` 메서드를 호출하여 뷰를 렌더링합니다.
   - API 요청: 컨트롤러가 `successResponse()` 또는 `errorResponse()` 메서드를 호출하여 JSON 응답을 생성합니다.

7. **응답 전송**:
   - 생성된 응답(Response 객체)이 클라이언트에게 전송됩니다.
   - 응답에는 HTTP 상태 코드, 헤더, 본문이 포함됩니다.

### 코드 예시

#### 요청 처리 흐름 예시 (웹 페이지)

```
클라이언트 요청 (/influencers)
    ↓
public/index.php (앱 초기화 및 라우팅)
    ↓
SessionMiddleware (세션 처리)
    ↓
AuthMiddleware (인증 확인)
    ↓
CsrfMiddleware (CSRF 토큰 확인)
    ↓
InfluencerController::index() 메서드 실행
    ↓
InfluencerService를 통한 데이터 조회
    ↓
Influencer 모델에서 데이터베이스 쿼리 실행
    ↓
컨트롤러에서 뷰 렌더링 (render 메서드 호출)
    ↓
응답 전송 (HTML 페이지)
```

#### 요청 처리 흐름 예시 (API)

```
클라이언트 요청 (/api/influencers)
    ↓
public/index.php (앱 초기화 및 라우팅)
    ↓
SessionMiddleware (세션 처리)
    ↓
AuthMiddleware (인증 확인)
    ↓
InfluencerApiController::index() 메서드 실행
    ↓
InfluencerService를 통한 데이터 조회
    ↓
Influencer 모델에서 데이터베이스 쿼리 실행
    ↓
컨트롤러에서 JSON 응답 생성 (successResponse 메서드 호출)
    ↓
응답 전송 (JSON 데이터)
```

이 MVC 구조는 관심사의 분리를 통해 코드의 유지보수성과 확장성을 높이며, 체계적인 요청 처리 흐름을 제공합니다. 