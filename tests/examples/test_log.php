<?php
// Composer 오토로드 추가
require_once __DIR__ . '/vendor/autoload.php';

// 로그 객체 가져오기
$logger = require_once __DIR__ . '/config/logging.php';

// 테스트 로그 메시지 작성
$logger->debug('로그 테스트 - 일별 로그 파일 생성 확인');
$logger->info('정보 로그 테스트');
$logger->warning('경고 로그 테스트');
$logger->error('에러 로그 테스트');

echo "로그 테스트 완료. storage/logs 디렉토리를 확인하세요.\n"; 