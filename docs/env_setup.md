# 환경 변수 설정 가이드

이 문서는 인플루언서 솔루션의 환경 변수 설정 방법을 설명합니다.

## 목차

1. [환경 변수 설정 개요](#환경-변수-설정-개요)
2. [.env 파일 설정 방법](#env-파일-설정-방법)
3. [환경 변수 목록](#환경-변수-목록)
4. [개발 환경별 설정](#개발-환경별-설정)

## 환경 변수 설정 개요

인플루언서 솔루션은 환경별로 다른 설정을 적용하기 위해 `.env` 파일을 사용합니다. 이 방식은 다음과 같은 이점이 있습니다:

1. **보안 강화**: 중요한 자격 증명이 소스 코드에 하드코딩되지 않음
2. **환경별 분리**: 개발, 테스트, 프로덕션 환경별로 다른 설정 적용 가능
3. **유연성**: 배포 환경에 따라 설정을 쉽게 변경 가능
4. **관리 용이성**: 모든 환경 설정을 한 곳에서 관리

## .env 파일 설정 방법

1. 프로젝트 루트 디렉토리에 있는 `example.env` 파일을 `.env`로 복사합니다:
   ```bash
   cp example.env .env
   ```

2. 새로 생성된 `.env` 파일을 열고 환경에 맞게 값을 수정합니다.

3. `.env` 파일은 버전 관리 시스템에 추가하지 않도록 주의하세요.

## 환경 변수 목록

### 데이터베이스 설정
| 변수명 | 설명 | 기본값 |
|--------|------|--------|
| DB_CONNECTION | 데이터베이스 드라이버 | mysql |
| DB_HOST | 데이터베이스 호스트 | localhost |
| DB_PORT | 데이터베이스 포트 | 3306 |
| DB_DATABASE | 데이터베이스 이름 | influencer_db |
| DB_USERNAME | 데이터베이스 사용자 이름 | root |
| DB_PASSWORD | 데이터베이스 비밀번호 | (없음) |

### 애플리케이션 설정
| 변수명 | 설명 | 기본값 |
|--------|------|--------|
| APP_ENV | 애플리케이션 환경 (development, production) | development |
| APP_DEBUG | 디버그 모드 활성화 여부 | true |
| APP_URL | 애플리케이션 URL | http://localhost |

### 로깅 설정
| 변수명 | 설명 | 기본값 |
|--------|------|--------|
| LOG_LEVEL | 로그 레벨 (debug, info, warning, error) | debug |
| LOG_PATH | 로그 파일 경로 | storage/logs |

## 개발 환경별 설정

### 개발 환경 (Development)
```
APP_ENV=development
APP_DEBUG=true
LOG_LEVEL=debug
```

### 테스트 환경 (Testing)
```
APP_ENV=testing
APP_DEBUG=true
LOG_LEVEL=debug
DB_DATABASE=influencer_db_test
```

### 운영 환경 (Production)
```
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## 주의사항

1. `.env` 파일에는 민감한 정보가 포함되므로 절대 버전 관리 시스템에 추가하지 마세요.
2. 운영 환경에서는 보안을 위해 `APP_DEBUG=false`로 설정하세요.
3. 환경 변수 설정 후에는 애플리케이션을 재시작해야 변경사항이 적용됩니다. 