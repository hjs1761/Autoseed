<?php
/**
 * 로깅 설정 파일
 * 
 * 이 파일은 Monolog 라이브러리를 사용하여 로깅 시스템을 구성합니다.
 * 일별 로그 파일을 생성하여 애플리케이션의 활동과 오류를 기록합니다.
 * 
 * 주요 기능:
 * - 일별 로그 파일 생성 ('app-YYYY-MM-DD.log' 형식)
 * - 로그 레벨 설정 (DEBUG, INFO, WARNING, ERROR 등)
 * - 로그 메시지 포맷 지정
 * 
 * Monolog 레벨:
 * - DEBUG (100): 개발 중에 유용한 상세한 디버그 정보
 * - INFO (200): 일반적인 정보성 메시지
 * - NOTICE (250): 정상이지만 중요한 이벤트
 * - WARNING (300): 경고 메시지 (오류는 아니지만 주의 필요)
 * - ERROR (400): 런타임 오류
 * - CRITICAL (500): 시스템 구성요소 실패
 * - ALERT (550): 즉시 조치 필요
 * - EMERGENCY (600): 시스템 사용 불가
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

// 로그 채널 생성
$logger = new Logger('app');

// 로그 디렉토리 설정
$logDir = __DIR__ . '/../storage/logs';

// 필요하다면 폴더 생성 코드 등을 추가하세요
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// 오늘 날짜로 로그 파일명 생성
$today = date('Y-m-d');
$logFile = $logDir . '/app-' . $today . '.log';

// 로그 파일 핸들러 설정
// 두 번째 매개변수는 최소 로그 레벨 (DEBUG가 가장 낮은 레벨)
$handler = new StreamHandler($logFile, Logger::DEBUG);

// 로그 포맷 설정
// 포맷: [날짜 시간] 채널.레벨: 메시지 컨텍스트 추가정보
$dateFormat = "Y-m-d H:i:s";
$output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
$formatter = new LineFormatter($output, $dateFormat);
$handler->setFormatter($formatter);

// 핸들러 추가
$logger->pushHandler($handler);

// 필요시 다른 Handler도 추가 가능
// 예: Slack 알림, 이메일 알림, 데이터베이스 저장 등
// $logger->pushHandler(new SlackWebhookHandler(...));

return $logger;
