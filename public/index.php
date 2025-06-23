<?php


// CORS 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization, accept, client-security-token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}


ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Composer Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// .env 로드
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // .env 파일이 없을 수도 있으니 safeLoad() 사용

// 상수 정의 로드
require_once __DIR__ . '/../config/define.php';

// 헬퍼 파일 로드
require_once __DIR__ . '/../include/common.php';
require_once __DIR__ . '/../include/validation_helper.php';

// echo 'aaa';
// exit;
// PDO 생성
$pdo = require __DIR__ . '/../config/database.php';

// Monolog 로깅 설정
$logger = require __DIR__ . '/../config/logging.php';

// 커스텀 DB 인스턴스 생성
$db = new \App\Core\DB($pdo, $logger);

// FastRoute 디스패처 생성
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /**
     * View Controller
     */
    $r->addRoute('GET', '/',                   ['App\Controllers\HomeController', 'index']);
    $r->addRoute('GET', '/login',              ['App\Controllers\HomeController', 'login']);
    $r->addRoute('GET', '/register',           ['App\Controllers\HomeController', 'register']);
    $r->addRoute('GET', '/logout',             ['App\Controllers\HomeController', 'logout']);
    $r->addRoute('GET', '/dashboard',          ['App\Controllers\HomeController', 'dashboard']);
    
    // 인플루언서 관리
    $r->addRoute('GET', '/influencers',              ['App\Controllers\InfluencerController', 'index']);
    $r->addRoute('GET', '/influencers/form',         ['App\Controllers\InfluencerController', 'form']);
    $r->addRoute('GET', '/influencers/form/{id:\d+}', ['App\Controllers\InfluencerController', 'form']);
    $r->addRoute('GET', '/influencers/search',       ['App\Controllers\InfluencerController', 'search']);
    $r->addRoute('POST', '/influencers/search',      ['App\Controllers\InfluencerController', 'doSearch']);
    $r->addRoute('GET', '/influencers/{id:\d+}',     ['App\Controllers\InfluencerController', 'show']);
    $r->addRoute('POST', '/influencers/save',        ['App\Controllers\InfluencerController', 'save']);
    $r->addRoute('GET', '/influencers/delete/{id:\d+}', ['App\Controllers\InfluencerController', 'delete']);
    
    /**
     * API Controller (JSON)
     */
    // 인증 API
    $r->addRoute('POST', '/api/auth/login',    ['App\Controllers\Api\AuthApiController', 'login']);
    $r->addRoute('POST', '/api/auth/register', ['App\Controllers\Api\AuthApiController', 'register']);
    
    // 인플루언서 API
    $r->addRoute('GET',    '/api/influencers',          ['App\Controllers\Api\InfluencerApiController', 'index']);
    $r->addRoute('GET',    '/api/influencers/{id:\d+}', ['App\Controllers\Api\InfluencerApiController', 'show']);
    $r->addRoute('POST',   '/api/influencers',          ['App\Controllers\Api\InfluencerApiController', 'store']);
    $r->addRoute('DELETE', '/api/influencers/{id:\d+}', ['App\Controllers\Api\InfluencerApiController', 'delete']);
    $r->addRoute('POST',   '/api/influencers/import',   ['App\Controllers\Api\InfluencerApiController', 'import']);
    
    // 사용자 관리 API
    $r->addRoute('GET',    '/api/users',          ['App\Controllers\Api\UserApiController', 'index']);
    $r->addRoute('GET',    '/api/users/{id:\d+}', ['App\Controllers\Api\UserApiController', 'show']);
    $r->addRoute('PUT',    '/api/users/{id:\d+}', ['App\Controllers\Api\UserApiController', 'update']);
    
    // 로그 API
    $r->addRoute('GET',    '/api/logs',          ['App\Controllers\Api\LogApiController', 'index']);
    $r->addRoute('GET',    '/api/logs/{id:\d+}', ['App\Controllers\Api\LogApiController', 'show']);
});

// 애플리케이션 인스턴스 생성
$app = new \App\Core\App($dispatcher, $db, $logger);

// 미들웨어 등록
$app->addMiddleware(new \App\Core\Middleware\SessionMiddleware());
$app->addMiddleware(new \App\Core\Middleware\AuthMiddleware());
$app->addMiddleware(new \App\Core\Middleware\CsrfMiddleware());

// 요청 객체 생성
$request = new \App\Core\Http\Request();

// print_r($request);
// 애플리케이션 실행
$app->run($request); 
