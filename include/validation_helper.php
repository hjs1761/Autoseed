<?php
/**
 * 파일: include/validation_helper.php
 * 
 * 이 파일은 유효성 검증을 위한 헬퍼 함수들을 제공합니다.
 * Validator 클래스를 쉽게 사용할 수 있는 함수들을 포함하고 있습니다.
 */

use App\Utils\Validator;

/**
 * 유효성 검증 헬퍼 함수
 * 
 * 간편하게 데이터의 유효성을 검증합니다.
 * 
 * @param array $data 검증할 데이터
 * @param array $rules 검증 규칙
 *        예: ['email' => ['required', 'email'], 'password' => ['required', ['minLength', 8]]]
 * @return array ['isValid' => bool, 'errors' => array]
 */
function validate(array $data, array $rules): array
{
    $validator = new Validator($data);
    
    foreach ($rules as $field => $ruleSet) {
        foreach ($ruleSet as $rule) {
            $ruleName = $rule;
            $ruleParams = [];
            
            // 파라미터가 있는 규칙 처리
            if (is_array($rule)) {
                $ruleName = $rule[0];
                $ruleParams = array_slice($rule, 1);
            }
            
            // 규칙 적용
            if (method_exists($validator, $ruleName)) {
                call_user_func_array([$validator, $ruleName], array_merge([$field], $ruleParams));
            }
        }
    }
    
    return [
        'isValid' => !$validator->hasErrors(),
        'errors' => $validator->getErrors()
    ];
}

/**
 * 유효성 검증 결과를 JSON 형식으로 응답합니다.
 * 
 * @param array $validation validate() 함수의 반환값
 * @param string $message 오류 메시지
 * @param int $code HTTP 상태 코드
 * @return void
 */
function respondValidationErrors(array $validation, string $message = '유효성 검사에 실패했습니다.', int $code = 422): void
{
    if (!$validation['isValid']) {
        $errors = [];
        
        foreach ($validation['errors'] as $field => $errorMessage) {
            $errors[] = [
                'field' => $field,
                'message' => $errorMessage
            ];
        }
        
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * 유효성 검증 결과 오류가 있으면 예외를 발생시킵니다.
 * 
 * @param array $validation validate() 함수의 반환값
 * @throws Exception 유효성 검사 실패 시
 * @return void
 */
function validateOrThrow(array $validation): void
{
    if (!$validation['isValid']) {
        throw new Exception(reset($validation['errors']));
    }
} 