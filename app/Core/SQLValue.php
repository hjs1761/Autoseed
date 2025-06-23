<?php
namespace App\Core;

/**
 * SQLValue
 * - 쿼리 내에 직접 함수/표현식을 삽입하고 싶을 때 사용
 *   ex) new SQLValue('NOW()'), new SQLValue('COUNT(*)')
 */
class SQLValue
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
