<?php
namespace App\Utils;

/**
 * 수학/계산 관련 유틸리티 함수
 */
class MathUtils
{
    /**
     * 할인율 계산
     * 
     * @param float $originalPrice 원래 가격
     * @param float $salePrice 판매 가격
     * @return int 할인율 (0-100)
     */
    public static function calculateDiscountRate(float $originalPrice, float $salePrice): int
    {
        if ($originalPrice <= 0) {
            return 0;
        }
        
        $discountRate = (($originalPrice - $salePrice) / $originalPrice) * 100;
        return round($discountRate);
    }
    
    /**
     * 증감률 계산
     * 
     * @param float $currentValue 현재 값
     * @param float $previousValue 이전 값
     * @param int $precision 반환 소수점 자릿수
     * @return float 증감률
     */
    public static function calculateGrowthRate(float $currentValue, float $previousValue, int $precision = 1): float
    {
        if ($previousValue == 0) {
            return 0;
        }
        
        $growthRate = (($currentValue - $previousValue) / $previousValue) * 100;
        return round($growthRate, $precision);
    }
    
    /**
     * 진행률 계산
     * 
     * @param float $current 현재 값
     * @param float $target 목표 값
     * @return int 진행률 (0-100)
     */
    public static function calculateProgressPercent(float $current, float $target): int
    {
        if ($target <= 0) {
            return 0;
        }
        
        $progressPercent = ($current / $target) * 100;
        return min(100, floor($progressPercent));
    }
    
    /**
     * 숫자에 천 단위 구분자 추가
     * 
     * @param mixed $number 숫자
     * @return string 포맷팅된 숫자
     */
    public static function formatNumber($number): string
    {
        return number_format($number);
    }
    
    /**
     * 금액을 통화 형식으로 변환
     * 
     * @param float $amount 금액
     * @param string $currency 통화 단위 (기본값: '₩')
     * @return string 포맷팅된 금액
     */
    public static function formatCurrency(float $amount, string $currency = '₩'): string
    {
        return $currency . ' ' . number_format($amount);
    }
    
    /**
     * 일별 평균 계산
     * 
     * @param float $total 총량
     * @param int $days 일수
     * @param int $precision 반환 소수점 자릿수
     * @return float 일별 평균
     */
    public static function calculateDailyAverage(float $total, int $days, int $precision = 1): float
    {
        if ($days <= 0) {
            return 0;
        }
        
        $average = $total / $days;
        return round($average, $precision);
    }
}