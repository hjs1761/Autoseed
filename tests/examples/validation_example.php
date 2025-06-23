<?php
/**
 * 유효성 검증 테스트 예제
 * 
 * 이 파일은 App\Utils\Validator 클래스와 유효성 검증 헬퍼 함수의
 * 사용법을 보여주는 예제입니다.
 */

// 필요한 파일 포함
require_once __DIR__ . '/../../app/Utils/Validator.php';
require_once __DIR__ . '/../../include/validation_helper.php';

use App\Utils\Validator;

// 검증할 테스트 데이터
$userData = [
    'name' => '홍길동',
    'email' => 'hong@example.com',
    'password' => '12345678',
    'age' => 25
];

/**
 * 테스트 1: Validator 클래스 직접 사용
 * 
 * Validator 클래스를 인스턴스화하고 메서드 체이닝으로 검증 규칙을 설정합니다.
 */
echo "=== Validator 클래스 사용 예제 ===\n";
$validator = new Validator($userData);
$validator->required('name')
         ->email('email')
         ->minLength('password', 8)
         ->range('age', 18, 100);

if ($validator->hasErrors()) {
    echo "유효성 검증 실패: \n";
    print_r($validator->getErrors());
} else {
    echo "유효성 검증 성공!\n";
}

/**
 * 테스트 2: 헬퍼 함수 사용
 * 
 * validate() 헬퍼 함수를 사용하여 배열 형태로 규칙을 정의하고 한 번에 검증합니다.
 */
echo "\n=== 헬퍼 함수 사용 예제 ===\n";
$rules = [
    'name' => ['required'],
    'email' => ['required', 'email'],
    'password' => ['required', ['minLength', 8]],
    'age' => ['numeric', ['range', 18, 100]]
];

$validation = validate($userData, $rules);

if ($validation['isValid']) {
    echo "유효성 검증 성공!\n";
} else {
    echo "유효성 검증 실패: \n";
    print_r($validation['errors']);
}

/**
 * 테스트 3: 실패 케이스 테스트
 * 
 * 의도적으로 유효하지 않은 데이터를 사용하여 검증 실패 상황을 시뮬레이션합니다.
 */
echo "\n=== 실패 케이스 예제 ===\n";
$invalidData = [
    'name' => '',                // 비어 있어 required 규칙 위반
    'email' => 'invalid-email',  // 이메일 형식 아님
    'password' => '1234',        // 8자 미만
    'age' => 15                  // 18 미만
];

$validation = validate($invalidData, $rules);

if (!$validation['isValid']) {
    echo "예상대로 유효성 검증 실패: \n";
    foreach ($validation['errors'] as $field => $error) {
        echo "- $field: $error\n";
    }
} 