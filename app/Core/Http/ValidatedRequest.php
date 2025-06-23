<?php
/**
 * 파일: app/Core/Http/ValidatedRequest.php
 * 
 * 이 파일은 유효성 검증 기능이 통합된 Request 클래스를 정의합니다.
 */

namespace App\Core\Http;

use App\Utils\Validator;

/**
 * 유효성 검증 기능이 통합된 요청 클래스
 */
class ValidatedRequest extends Request
{
    /**
     * 유효성 검증 규칙
     * @var array
     */
    protected array $rules = [];
    
    /**
     * 유효성 검증 오류
     * @var array
     */
    protected array $errors = [];
    
    /**
     * 요청 데이터 유효성 검증
     * 
     * @param array|null $rules 유효성 검증 규칙
     * @return bool 유효성 검증 결과
     */
    public function validate(array $rules = null): bool
    {
        $rulesToUse = $rules ?? $this->rules;
        if (empty($rulesToUse)) {
            return true;
        }
        
        $data = $this->all();
        $validation = validate($data, $rulesToUse);
        
        if (!$validation['isValid']) {
            $this->errors = $validation['errors'];
            return false;
        }
        
        return true;
    }
    
    /**
     * 유효성 검증 오류 반환
     * 
     * @return array 유효성 검증 오류
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * 유효성 검증 후 데이터 반환
     * 
     * @return array 유효성 검증된 데이터
     * @throws \Exception 유효성 검증 실패 시
     */
    public function validated(): array
    {
        if (empty($this->rules)) {
            return $this->all();
        }
        
        if (!$this->validate()) {
            throw new \Exception('유효성 검증에 실패했습니다.');
        }
        
        // 규칙에 정의된 필드만 반환
        return array_intersect_key($this->all(), array_flip(array_keys($this->rules)));
    }
    
    /**
     * 유효성 검증 규칙 설정
     * 
     * @param array $rules 유효성 검증 규칙
     * @return $this
     */
    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }
} 