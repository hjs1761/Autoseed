<?php
// 배포 구분
define('DEPLOY_TYPE', $_ENV['APP_ENV'] ?? 'development'); // 'development' 또는 'production'

// 애플리케이션 경로 설정
define('APP_PATH', dirname(__DIR__) . '/app');

// 기본 경로 설정
define('SITE_DOMAIN', $_ENV['APP_URL'] ?? 'http://localhost');
define('SITE_DIR', '');
define('MAIN_PAGE', '/dashboard');

// 데이터베이스 설정은 .env에서 관리됨
// DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

// 파일 업로드 설정
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'csv', 'xlsx']);

// 페이지네이션 설정
define('DEFAULT_PER_PAGE', 20);

// 로깅 설정
define('LOG_DIR', __DIR__ . '/../' . ($_ENV['LOG_PATH'] ?? 'logs'));
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'debug'); // debug, info, warning, error

// 세션 설정
define('SESSION_LIFETIME', 86400); // 24시간
?> 