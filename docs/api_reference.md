# 인플루언서 솔루션 API 레퍼런스

이 문서는 인플루언서 솔루션의 API 엔드포인트, 요청 및 응답 형식을 설명합니다.

## 기본 정보

- 기본 URL: `/api`
- 모든 응답은 JSON 형식으로 반환됩니다.
- 인증이 필요한 엔드포인트는 세션 기반 인증을 사용합니다.
- API 버전: v1 (모든 엔드포인트는 `/api/v1/...` 형식으로 호출)

### 응답 형식

#### 성공 응답

모든 성공 응답은 다음 형식을 따릅니다:

```json
{
  "success": true,
  "message": "성공 메시지(선택)",
  "data": {
    // 응답 데이터
  }
}
```

#### 오류 응답

모든 오류 응답은 다음 형식을 따릅니다:

```json
{
  "success": false,
  "message": "오류 메시지",
  "code": 400,
  "errors": [
    {
      "field": "필드명",
      "message": "상세 오류 메시지"
    }
  ]
}
```

### 공통 오류 코드

| 코드 | 설명 |
|------|------|
| 400 | 잘못된 요청 (Bad Request) |
| 401 | 인증 실패 (Unauthorized) |
| 403 | 권한 없음 (Forbidden) |
| 404 | 리소스 없음 (Not Found) |
| 422 | 유효성 검사 실패 (Unprocessable Entity) |
| 429 | 요청 횟수 초과 (Too Many Requests) |
| 500 | 서버 오류 (Internal Server Error) |

## 인증 API

### 로그인

사용자 자격 증명을 확인하고 세션을 시작합니다.

- **URL**: `/api/v1/auth/login`
- **Method**: `POST`
- **인증 필요**: 아니오

#### 요청 본문

```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "message": "로그인에 성공했습니다.",
  "data": {
    "user": {
      "id": 1,
      "name": "사용자명",
      "email": "user@example.com",
      "is_admin": true,
      "last_login_at": "2023-05-15 08:30:45"
    },
    "token": {
      "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "token_type": "Bearer",
      "expires_in": 3600
    }
  }
}
```

#### 오류 응답 (401 Unauthorized)

```json
{
  "success": false,
  "message": "이메일 또는 비밀번호가 올바르지 않습니다.",
  "code": 401
}
```

### 로그아웃

현재 세션을 종료합니다.

- **URL**: `/api/v1/auth/logout`
- **Method**: `POST`
- **인증 필요**: 예

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "message": "로그아웃 되었습니다."
}
```

### 회원가입

새 사용자 계정을 생성합니다.

- **URL**: `/api/v1/auth/register`
- **Method**: `POST`
- **인증 필요**: 아니오

#### 요청 본문

```json
{
  "name": "사용자명",
  "email": "user@example.com",
  "password": "your_password",
  "password_confirmation": "your_password"
}
```

#### 성공 응답 (201 Created)

```json
{
  "success": true,
  "message": "회원가입이 완료되었습니다.",
  "data": {
    "user": {
      "id": 1,
      "name": "사용자명",
      "email": "user@example.com",
      "created_at": "2023-05-15 08:30:45"
    }
  }
}
```

#### 오류 응답 (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "유효성 검사에 실패했습니다.",
  "code": 422,
  "errors": [
    {
      "field": "email",
      "message": "이미 사용중인 이메일입니다."
    },
    {
      "field": "password",
      "message": "비밀번호는 최소 8자 이상이어야 합니다."
    }
  ]
}
```

## 인플루언서 API

### 인플루언서 목록 조회

인플루언서 목록을 조회하고 필터링합니다.

- **URL**: `/api/v1/influencers`
- **Method**: `GET`
- **인증 필요**: 예

#### 쿼리 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| keyword | string | 아니오 | 검색 키워드 (이름, 핸들, 바이오 검색) |
| platform_id | integer | 아니오 | 플랫폼 ID로 필터링 |
| category_id | integer | 아니오 | 카테고리 ID로 필터링 |
| min_followers | integer | 아니오 | 최소 팔로워 수 |
| max_followers | integer | 아니오 | 최대 팔로워 수 |
| min_engagement | float | 아니오 | 최소 참여율 (%) |
| is_verified | boolean | 아니오 | 인증된 인플루언서 여부 |
| sort | string | 아니오 | 정렬 필드 (followers, engagement, created_at) |
| order | string | 아니오 | 정렬 방향 (asc, desc) |
| page | integer | 아니오 | 페이지 번호 (기본값: 1) |
| per_page | integer | 아니오 | 페이지당 결과 수 (기본값: 20, 최대: 100) |

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "data": {
    "influencers": [
      {
        "id": 1,
        "name": "홍길동",
        "handle": "@hong",
        "profile_image": "https://example.com/profiles/hong.jpg",
        "platform": {
          "id": 1,
          "name": "Instagram",
          "logo_url": "https://example.com/logos/instagram.png"
        },
        "follower_count": 125000,
        "engagement_rate": 3.75,
        "is_verified": true,
        "categories": [
          {"id": 1, "name": "패션"},
          {"id": 2, "name": "뷰티"}
        ],
        "created_at": "2023-01-01T00:00:00+09:00",
        "updated_at": "2023-05-15T08:30:45+09:00"
      },
      {
        "id": 2,
        "name": "김철수",
        "handle": "@chulsoo",
        "profile_image": "https://example.com/profiles/chulsoo.jpg",
        "platform": {
          "id": 2,
          "name": "YouTube",
          "logo_url": "https://example.com/logos/youtube.png"
        },
        "follower_count": 500000,
        "engagement_rate": 2.5,
        "is_verified": false,
        "categories": [
          {"id": 7, "name": "게임"},
          {"id": 8, "name": "테크"}
        ],
        "created_at": "2023-02-15T00:00:00+09:00",
        "updated_at": "2023-05-10T14:25:30+09:00"
      }
    ],
    "pagination": {
      "total": 248,
      "per_page": 20,
      "current_page": 1,
      "last_page": 13,
      "from": 1,
      "to": 20,
      "prev_page_url": null,
      "next_page_url": "https://api.example.com/api/v1/influencers?page=2"
    }
  }
}
```

### 인플루언서 상세 정보 조회

단일 인플루언서의 상세 정보를 조회합니다.

- **URL**: `/api/v1/influencers/{id}`
- **Method**: `GET`
- **인증 필요**: 예

#### URL 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| id | integer | 예 | 인플루언서 ID |

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "data": {
    "influencer": {
      "id": 1,
      "name": "홍길동",
      "handle": "@hong",
      "profile_url": "https://instagram.com/hong",
      "profile_image": "https://example.com/profiles/hong.jpg",
      "bio": "패션과 뷰티에 관한 콘텐츠를 제작하는 인플루언서입니다.",
      "follower_count": 125000,
      "following_count": 500,
      "post_count": 350,
      "engagement_rate": 3.75,
      "average_likes": 4500,
      "average_comments": 200,
      "is_verified": true,
      "location": "서울, 대한민국",
      "email": "contact@hong.com",
      "status": "active",
      "last_crawled_at": "2023-05-15T08:00:00+09:00",
      "created_at": "2023-01-01T00:00:00+09:00",
      "updated_at": "2023-05-15T08:30:45+09:00"
    },
    "platform": {
      "id": 1,
      "name": "Instagram",
      "website": "https://instagram.com",
      "logo_url": "https://example.com/logos/instagram.png"
    },
    "categories": [
      {"id": 1, "name": "패션", "description": "패션 관련 콘텐츠"},
      {"id": 2, "name": "뷰티", "description": "뷰티 관련 콘텐츠"}
    ],
    "recent_posts": [
      {
        "id": 101,
        "external_id": "Cg9kCABvQ",
        "post_type": "image",
        "url": "https://instagram.com/p/Cg9kCABvQ",
        "thumbnail_url": "https://example.com/thumbnails/post101.jpg",
        "like_count": 5200,
        "comment_count": 243,
        "engagement_rate": 4.3,
        "posted_at": "2023-05-10T12:30:00+09:00"
      },
      {
        "id": 102,
        "external_id": "Cg8jDACvP",
        "post_type": "carousel",
        "url": "https://instagram.com/p/Cg8jDACvP",
        "thumbnail_url": "https://example.com/thumbnails/post102.jpg",
        "like_count": 4800,
        "comment_count": 210,
        "engagement_rate": 4.0,
        "posted_at": "2023-05-05T18:45:00+09:00"
      }
    ]
  }
}
```

#### 오류 응답 (404 Not Found)

```json
{
  "success": false,
  "message": "인플루언서를 찾을 수 없습니다.",
  "code": 404
}
```

### 인플루언서 생성

새 인플루언서를 생성합니다.

- **URL**: `/api/v1/influencers`
- **Method**: `POST`
- **인증 필요**: 예 (관리자 권한)

#### 요청 본문

```json
{
  "name": "홍길동",
  "handle": "@hong",
  "profile_url": "https://instagram.com/hong",
  "profile_image": "https://example.com/profiles/hong.jpg",
  "bio": "패션과 뷰티에 관한 콘텐츠를 제작하는 인플루언서입니다.",
  "follower_count": 125000,
  "following_count": 500,
  "post_count": 350,
  "engagement_rate": 3.75,
  "platform_id": 1,
  "location": "서울, 대한민국",
  "email": "contact@hong.com",
  "status": "active",
  "is_verified": true,
  "category_ids": [1, 2]
}
```

#### 성공 응답 (201 Created)

```json
{
  "success": true,
  "message": "인플루언서가 생성되었습니다.",
  "data": {
    "id": 1,
    "name": "홍길동",
    "handle": "@hong",
    "platform_id": 1,
    "created_at": "2023-05-15T08:30:45+09:00"
  }
}
```

#### 오류 응답 (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "유효성 검사에 실패했습니다.",
  "code": 422,
  "errors": [
    {
      "field": "handle",
      "message": "이미 사용중인 핸들입니다."
    },
    {
      "field": "platform_id",
      "message": "존재하지 않는 플랫폼입니다."
    }
  ]
}
```

### 인플루언서 수정

기존 인플루언서 정보를 수정합니다.

- **URL**: `/api/v1/influencers/{id}`
- **Method**: `PUT`
- **인증 필요**: 예 (관리자 권한)

#### URL 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| id | integer | 예 | 인플루언서 ID |

#### 요청 본문

```json
{
  "name": "홍길동",
  "handle": "@hong",
  "profile_url": "https://instagram.com/hong",
  "profile_image": "https://example.com/profiles/hong_updated.jpg",
  "bio": "패션, 뷰티, 라이프스타일 인플루언서",
  "follower_count": 130000,
  "following_count": 520,
  "post_count": 375,
  "engagement_rate": 3.9,
  "platform_id": 1,
  "location": "서울, 대한민국",
  "email": "contact@hong.com",
  "status": "active",
  "is_verified": true,
  "category_ids": [1, 2, 5]
}
```

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "message": "인플루언서 정보가 업데이트되었습니다.",
  "data": {
    "id": 1,
    "name": "홍길동",
    "handle": "@hong",
    "updated_at": "2023-05-15T10:45:30+09:00"
  }
}
```

### 인플루언서 삭제

인플루언서를 삭제합니다.

- **URL**: `/api/v1/influencers/{id}`
- **Method**: `DELETE`
- **인증 필요**: 예 (관리자 권한)

#### URL 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| id | integer | 예 | 인플루언서 ID |

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "message": "인플루언서가 삭제되었습니다."
}
```

## 플랫폼 API

### 플랫폼 목록 조회

지원되는 소셜 미디어 플랫폼 목록을 조회합니다.

- **URL**: `/api/v1/platforms`
- **Method**: `GET`
- **인증 필요**: 예

#### 쿼리 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| active | boolean | 아니오 | 활성 상태 필터링 (기본값: true) |

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "data": {
    "platforms": [
      {
        "id": 1,
        "name": "Instagram",
        "website": "https://instagram.com",
        "logo_url": "https://example.com/logos/instagram.png",
        "active": true,
        "created_at": "2023-01-01T00:00:00+09:00",
        "updated_at": "2023-01-01T00:00:00+09:00"
      },
      {
        "id": 2,
        "name": "YouTube",
        "website": "https://youtube.com",
        "logo_url": "https://example.com/logos/youtube.png",
        "active": true,
        "created_at": "2023-01-01T00:00:00+09:00",
        "updated_at": "2023-01-01T00:00:00+09:00"
      }
    ]
  }
}
```

## 카테고리 API

### 카테고리 목록 조회

인플루언서 카테고리 목록을 조회합니다.

- **URL**: `/api/v1/categories`
- **Method**: `GET`
- **인증 필요**: 예

#### 쿼리 매개변수

| 매개변수 | 타입 | 필수 | 설명 |
|---------|------|------|------|
| active | boolean | 아니오 | 활성 상태 필터링 (기본값: true) |
| parent_id | integer | 아니오 | 부모 카테고리 ID (null이면 최상위 카테고리) |

#### 성공 응답 (200 OK)

```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "패션",
        "description": "패션 관련 콘텐츠",
        "parent_id": null,
        "active": true,
        "created_at": "2023-01-01T00:00:00+09:00",
        "updated_at": "2023-01-01T00:00:00+09:00"
      },
      {
        "id": 2,
        "name": "뷰티",
        "description": "뷰티 관련 콘텐츠",
        "parent_id": null,
        "active": true,
        "created_at": "2023-01-01T00:00:00+09:00",
        "updated_at": "2023-01-01T00:00:00+09:00"
      }
    ]
  }
}
```

## 사용자 관리 API

### 사용자 목록 조회

사용자 목록을 조회합니다.

- **URL**: `/api/users`
- **Method**: `GET`
- **인증 필요**: 예 (관리자만)

**쿼리 매개변수**:

- `status` (선택): 사용자 상태로 필터링 (active, inactive, deleted)
- `is_admin` (선택): 관리자 여부로 필터링 (1, 0)
- `search` (선택): 이름 또는 이메일로 검색
- `page` (선택): 페이지 번호 (기본값: 1)
- `limit` (선택): 페이지당 결과 수 (기본값: 20)

**성공 응답** (200 OK):

```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "name": "관리자",
        "email": "admin@example.com",
        "is_admin": 1,
        "status": "active",
        "last_login_at": "2023-01-01 00:00:00",
        "created_at": "2023-01-01 00:00:00"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "last_page": 5
    }
  }
}
```

### 사용자 상세 조회

단일 사용자의 상세 정보를 조회합니다.

- **URL**: `/api/users/{id}`
- **Method**: `GET`
- **인증 필요**: 예 (관리자 또는 본인)

**URL 매개변수**:

- `id`: 사용자 ID

**성공 응답** (200 OK):

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "관리자",
      "email": "admin@example.com",
      "is_admin": 1,
      "status": "active",
      "last_login_at": "2023-01-01 00:00:00",
      "created_at": "2023-01-01 00:00:00"
    }
  }
}
```

**오류 응답** (404 Not Found):

```json
{
  "success": false,
  "message": "사용자를 찾을 수 없습니다.",
  "code": 404
}
```

### 사용자 정보 수정

사용자 정보를 수정합니다.

- **URL**: `/api/users/{id}`
- **Method**: `PUT`
- **인증 필요**: 예 (관리자 또는 본인)

**URL 매개변수**:

- `id`: 사용자 ID

**요청 본문**:

```json
{
  "name": "홍길동",
  "password": "new_password",      // 선택적, 변경하지 않을 경우 생략
  "is_admin": 0,                   // 관리자만 변경 가능
  "status": "active"               // 관리자만 변경 가능
}
```

**성공 응답** (200 OK):

```json
{
  "success": true,
  "message": "사용자 정보가 성공적으로 수정되었습니다.",
  "data": {
    "user": {
      "id": 1,
      "name": "홍길동",
      "email": "user@example.com"
    }
  }
}
```

**오류 응답** (403 Forbidden):

```json
{
  "success": false,
  "message": "이 작업을 수행할 권한이 없습니다.",
  "code": 403
}
```

## 로그 API

### 로그 목록 조회

시스템 로그 목록을 조회합니다.

- **URL**: `/api/logs`
- **Method**: `GET`
- **인증 필요**: 예 (관리자만)

**쿼리 매개변수**:

- `type` (선택): 로그 유형으로 필터링 (DEFAULT, API, SYSTEM, USER, INFLUENCER, AUTH, IMPORT, ERROR)
- `result` (선택): 결과로 필터링 (SUCCESS, FAIL, ERROR)
- `start_date` (선택): 시작 날짜
- `end_date` (선택): 종료 날짜
- `search` (선택): 메시지 내용 검색
- `page` (선택): 페이지 번호 (기본값: 1)
- `limit` (선택): 페이지당 결과 수 (기본값: 20)

**성공 응답** (200 OK):

```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 1,
        "type": "API",
        "message": "인플루언서 조회 API 호출",
        "ip_address": "127.0.0.1",
        "result": "SUCCESS",
        "user_id": 1,
        "user_name": "관리자",
        "created_at": "2023-01-01 00:00:00"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "last_page": 5
    }
  }
}
```

### 로그 상세 조회

단일 로그의 상세 정보를 조회합니다.

- **URL**: `/api/logs/{id}`
- **Method**: `GET`
- **인증 필요**: 예 (관리자만)

**URL 매개변수**:

- `id`: 로그 ID

**성공 응답** (200 OK):

```json
{
  "success": true,
  "data": {
    "log": {
      "id": 1,
      "type": "API",
      "message": "인플루언서 조회 API 호출",
      "ip_address": "127.0.0.1",
      "result": "SUCCESS",
      "user_id": 1,
      "user_name": "관리자",
      "extra_data": "{\"params\":{\"id\":1}}",
      "created_at": "2023-01-01 00:00:00"
    }
  }
}
```

**오류 응답** (404 Not Found):

```json
{
  "success": false,
  "message": "로그를 찾을 수 없습니다.",
  "code": 404
}
``` 