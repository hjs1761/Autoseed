# 유효성 검증 시스템 가이드

이 문서는 인플루언서 솔루션에 새롭게 도입된 유효성 검증 시스템의 목적, 설계 방식, 활용 방법에 대해 설명합니다.

## 목차

1. [도입 배경 및 이유](#도입-배경-및-이유)
2. [설계 원칙](#설계-원칙)
3. [주요 구성 요소](#주요-구성-요소)
4. [사용 방법](#사용-방법)
5. [코드 예시](#코드-예시)
6. [확장 방법](#확장-방법)

## 도입 배경 및 이유

기존 유효성 검증 시스템은 다음과 같은 문제점을 가지고 있었습니다:

1. **코드 중복**: 각 서비스와 컨트롤러에서 유사한 유효성 검증 코드가 반복됨
2. **일관성 부족**: 다양한 유효성 검증 방식으로 인해 오류 메시지와 처리 방식이 불일치
3. **확장성 제한**: 새로운 검증 규칙 추가 시 여러 곳에서 수정 필요
4. **유지보수 어려움**: 검증 로직이 비즈니스 로직과 혼재되어 있어 코드 가독성 저하
5. **재사용성 부족**: 공통 검증 로직을 쉽게 재사용할 수 없음

새로운 유효성 검증 시스템은 다음과 같은 이점을 제공합니다:

1. **코드 재사용성**: 중앙 집중식 검증 클래스로 코드 중복 제거
2. **일관된 API**: 표준화된 방법으로 모든 유효성 검증 수행
3. **선언적 스타일**: 체이닝 방식의 명확하고 가독성 높은 코드
4. **확장 용이성**: 새로운 검증 규칙을 쉽게 추가 가능
5. **명확한 책임 분리**: 비즈니스 로직과 검증 로직의 분리
6. **구조화된 오류 관리**: 필드별 오류 메시지 관리 및 일관된 오류 응답

## 설계 원칙

유효성 검증 시스템은 다음 원칙에 따라 설계되었습니다:

1. **단일 책임 원칙(SRP)**: 검증 로직을 전담하는 별도의 클래스 구현
2. **개방/폐쇄 원칙(OCP)**: 기존 코드 수정 없이 새로운 검증 규칙 추가 가능
3. **인터페이스 분리 원칙(ISP)**: 필요한 검증 규칙만 사용하도록 설계
4. **메서드 체이닝**: 유창한 인터페이스로 가독성 향상
5. **명확한 오류 메시지**: 사용자 친화적인 오류 메시지 기본 제공
6. **유연한 사용성**: 다양한 사용 패턴 지원 (클래스 직접 사용, 헬퍼 함수 사용)

## 주요 구성 요소

유효성 검증 시스템은 다음 두 가지 주요 구성 요소로 이루어져 있습니다:

### 1. Validator 클래스 (`app/Utils/Validator.php`)

- 다양한 검증 규칙 메서드 제공
- 메서드 체이닝 지원
- 오류 수집 및 관리 기능

### 2. 검증 헬퍼 함수 (`include/validation_helper.php`)

- `validate()`: 간편하게 데이터와 규칙을 전달하여 검증
- `respondValidationErrors()`: 유효성 검증 실패 시 JSON 응답
- `validateOrThrow()`: 유효성 검증 실패 시 예외 발생

## 사용 방법

### 1. Validator 클래스 직접 사용

```php
use App\Utils\Validator;

$validator = new Validator($_POST);
$validator->required('name')
         ->required('email')
         ->email('email')
         ->minLength('password', 8)
         ->matches('password_confirm', 'password');

if ($validator->hasErrors()) {
    // 오류 처리
    $errors = $validator->getErrors();
    // ...
}
```

### 2. 헬퍼 함수 사용 (가장 간편)

```php
// include/validation_helper.php가 로드되어 있어야 함
$validation = validate($_POST, [
    'name' => ['required'],
    'email' => ['required', 'email'],
    'password' => ['required', ['minLength', 8]],
    'password_confirm' => ['required', ['matches', 'password']]
]);

if (!$validation['isValid']) {
    // 오류 처리
    $errors = $validation['errors'];
    // ...
}
```

### 3. API 응답 처리

```php
$validation = validate($_POST, [
    'name' => ['required'],
    'email' => ['required', 'email']
]);

// 유효성 검증 실패 시 자동으로 JSON 응답을 반환하고 스크립트 종료
respondValidationErrors($validation);

// 여기서부터는 유효성 검증에 성공한 경우에만 실행됨
// ...
```

## 코드 예시

### 사용자 서비스에서의 활용

```php
public function createUser(array $data)
{
    // 유효성 검증
    $validation = validate($data, [
        'name' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', ['minLength', 8]]
    ]);
    
    validateOrThrow($validation);
    
    // 이메일 중복 확인
    $existingUser = $this->getUserByEmail($data['email']);
    if ($existingUser) {
        throw new Exception('이미 사용중인 이메일입니다.');
    }
    
    // 비밀번호 암호화
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // 이하 사용자 저장 로직
    // ...
}
```

### 인플루언서 API 컨트롤러에서의 활용

```php
public function store()
{
    $data = $this->getRequestData();
    
    // 유효성 검증
    $validation = validate($data, [
        'name' => ['required'],
        'handle' => ['required'],
        'platform_id' => ['required', 'numeric'],
        'follower_count' => ['numeric', ['range', 0, PHP_INT_MAX]],
        'engagement_rate' => ['numeric', ['range', 0, 100]]
    ]);
    
    // 오류가 있으면 JSON 응답 반환 후 스크립트 종료
    respondValidationErrors($validation);
    
    // 유효성 검증 통과 후 서비스 호출
    return $this->influencerService->saveInfluencer($data);
}
```

## 확장 방법

### 1. 새로운 검증 규칙 추가

`Validator` 클래스에 새로운 메서드를 추가하여 검증 규칙을 확장할 수 있습니다:

```php
/**
 * URL 형식 검증
 * 
 * @param string $field 필드명
 * @param string|null $message 오류 메시지
 * @return $this 체이닝을 위한 인스턴스 반환
 */
public function url($field, $message = null) {
    if (isset($this->data[$field]) && $this->data[$field] !== '' && 
        !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
        $this->errors[$field] = $message ?? "유효한 URL 형식이 아닙니다.";
    }
    return $this;
}
```

### 2. 사용자 정의 규칙 사용

특정 상황에 맞는 임시 규칙은 `custom` 메서드를 통해 추가할 수 있습니다:

```php
$validator->custom('username', function($value) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $value);
}, '사용자명은 영문자, 숫자, 밑줄만 포함할 수 있습니다.');
```

이 방식으로 유효성 검증 시스템은 프로젝트의 다양한 요구 사항에 맞게 유연하게 확장할 수 있습니다. 