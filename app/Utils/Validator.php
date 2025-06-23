<?php
/**
 * 파일: app/Utils/Validator.php
 * 
 * 이 파일은 데이터 유효성 검증을 위한 클래스를 정의합니다.
 * 다양한 유효성 검증 규칙을 체이닝 방식으로 적용할 수 있으며,
 * 유연하고 확장 가능한 검증 시스템을 제공합니다.
 * 
 * @package App\Utils
 */

namespace App\Utils;

/**
 * 유효성 검증 클래스
 * 
 * 다양한 데이터에 대한 유효성 검증 기능을 제공합니다.
 */
class Validator {
    /**
     * 오류 메시지 배열
     * @var array
     */
    private $errors = [];
    
    /**
     * 검증할 데이터
     * @var array
     */
    private $data;
    
    /**
     * 생성자
     * 
     * @param array $data 검증할 데이터 배열
     */
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    /**
     * 필수 필드 검증
     * 
     * @param string $field 필드명
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            $this->errors[$field] = $message ?? "{$field} 필드는 필수입니다.";
        }
        return $this;
    }
    
    /**
     * 이메일 형식 검증
     * 
     * @param string $field 필드명
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && 
            !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "유효한 이메일 형식이 아닙니다.";
        }
        return $this;
    }
    
    /**
     * 최소 길이 검증
     * 
     * @param string $field 필드명
     * @param int $length 최소 길이
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function minLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? "{$field} 필드는 최소 {$length}자 이상이어야 합니다.";
        }
        return $this;
    }
    
    /**
     * 최대 길이 검증
     * 
     * @param string $field 필드명
     * @param int $length 최대 길이
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function maxLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? "{$field} 필드는 최대 {$length}자까지 입력 가능합니다.";
        }
        return $this;
    }
    
    /**
     * 숫자 검증
     * 
     * @param string $field 필드명
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && 
            !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "{$field} 필드는 숫자여야 합니다.";
        }
        return $this;
    }
    
    /**
     * 숫자 범위 검증
     * 
     * @param string $field 필드명
     * @param int|float $min 최소값
     * @param int|float $max 최대값
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function range($field, $min, $max, $message = null) {
        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            $value = (float)$this->data[$field];
            if ($value < $min || $value > $max) {
                $this->errors[$field] = $message ?? "{$field} 필드는 {$min}에서 {$max} 사이의 값이어야 합니다.";
            }
        }
        return $this;
    }
    
    /**
     * 일치 검증
     * 
     * @param string $field 필드명
     * @param string $matchField 일치해야 하는 필드명
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function matches($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) && 
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? "{$field} 필드는 {$matchField} 필드와 일치해야 합니다.";
        }
        return $this;
    }
    
    /**
     * 날짜 형식 검증
     * 
     * @param string $field 필드명
     * @param string $format 날짜 형식
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $d = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "{$field} 필드는 {$format} 형식의 날짜여야 합니다.";
            }
        }
        return $this;
    }
    
    /**
     * 허용된 값 검증
     * 
     * @param string $field 필드명
     * @param array $allowedValues 허용된 값 배열
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function in($field, array $allowedValues, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowedValues)) {
            $this->errors[$field] = $message ?? "{$field} 필드는 허용된 값 중 하나여야 합니다.";
        }
        return $this;
    }
    
    /**
     * 사용자 정의 규칙 검증
     * 
     * @param string $field 필드명
     * @param callable $rule 검증 규칙 콜백 함수
     * @param string|null $message 오류 메시지
     * @return $this 체이닝을 위한 인스턴스 반환
     */
    public function custom($field, callable $rule, $message = null) {
        if (isset($this->data[$field]) && !$rule($this->data[$field])) {
            $this->errors[$field] = $message ?? "{$field} 필드가 유효하지 않습니다.";
        }
        return $this;
    }
    
    /**
     * 오류 여부 확인
     * 
     * @return bool 오류 존재 여부
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * 오류 배열 반환
     * 
     * @return array 오류 배열
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * 첫 번째 오류 메시지 반환
     * 
     * @return string|null 첫 번째 오류 메시지
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
} 