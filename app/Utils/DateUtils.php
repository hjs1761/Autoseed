<?php
namespace App\Utils;

/**
 * 날짜 관련 유틸리티 함수
 */
class DateUtils
{
    /**
     * 남은 일자 계산
     * 
     * @param string $endDate 종료일
     * @return int 남은 일자
     */
    public static function calculateRemainingDays(string $endDate): int
    {
        $endTimestamp = strtotime($endDate);
        $currentTimestamp = time();
        $daysDiff = floor(($endTimestamp - $currentTimestamp) / (60 * 60 * 24));
        
        return $daysDiff;
    }
    
    /**
     * 이벤트 상태 결정
     * 
     * @param int $remainingDays 남은 일수
     * @param string $startDate 시작일
     * @return string 상태
     */
    public static function determineEventStatus(int $remainingDays, string $startDate): string
    {
        if (strtotime($startDate) > time()) {
            return '예정';
        }
        
        if ($remainingDays <= 0) {
            return '종료됨';
        }
        
        if ($remainingDays <= 1) {
            return '마감 임박';
        }
        
        return '진행 중';
    }
    
    /**
     * 두 날짜 사이의 경과 일수 계산
     * 
     * @param string $startDate 시작일
     * @param string|null $endDate 종료일 (기본값: 현재 시간)
     * @return int 경과 일수
     */
    public static function getDaysBetween(string $startDate, ?string $endDate = null): int
    {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = $endDate ? strtotime($endDate) : time();
        $daysDiff = max(1, ceil(($endTimestamp - $startTimestamp) / (60 * 60 * 24)));
        
        return $daysDiff;
    }
    
    /**
     * 현재 날짜의 월 시작일 반환
     * 
     * @param int $monthsOffset 현재 월에서의 오프셋 (예: -1은 이전 달)
     * @return string Y-m-d 형식의 날짜
     */
    public static function getMonthStartDate(int $monthsOffset = 0): string
    {
        $date = new \DateTime();
        if ($monthsOffset !== 0) {
            $date->modify($monthsOffset . ' month');
        }
        
        return $date->format('Y-m-01');
    }
    
    /**
     * 현재 날짜의 월 마지막일 반환
     * 
     * @param int $monthsOffset 현재 월에서의 오프셋 (예: -1은 이전 달)
     * @return string Y-m-d 형식의 날짜
     */
    public static function getMonthEndDate(int $monthsOffset = 0): string
    {
        $date = new \DateTime();
        if ($monthsOffset !== 0) {
            $date->modify($monthsOffset . ' month');
        }
        
        return $date->format('Y-m-t');
    }
    
    /**
     * 날짜를 지정한 형식으로 포맷팅
     * 
     * @param string $date 날짜
     * @param string $format 출력 형식
     * @return string 포맷팅된 날짜
     */
    public static function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        return date($format, strtotime($date));
    }
    
    /**
     * 특정 날짜가 특정 기간 내에 있는지 확인
     * 
     * @param string $date 확인할 날짜
     * @param string $startDate 시작 날짜
     * @param string $endDate 종료 날짜
     * @return bool 기간 내 여부
     */
    public static function isDateInRange(string $date, string $startDate, string $endDate): bool
    {
        $timestamp = strtotime($date);
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        
        return ($timestamp >= $startTimestamp && $timestamp <= $endTimestamp);
    }
    
    /**
     * 날짜 유효성 검사
     * 
     * @param string $date 날짜 문자열
     * @param string $format 날짜 형식
     * @return bool 유효성 여부
     */
    public static function isValidDate(string $date, string $format = 'Y-m-d H:i:s'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}