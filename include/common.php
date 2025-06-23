<?php
use App\Core\SQLValue;

/**
 * 클라이언트 IP 반환
 *
 * @return string 클라이언트 IP 주소
 */
function getClientIp()
{
    $ipAddr = '';

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ipAddr = trim(end($ipList));
    } else {
        $ipAddr = $_SERVER['REMOTE_ADDR'];
    }

    if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
        return 'unknown';
    }

    return $ipAddr;
}

/**
 * 에러 메시지와 함께 JSON 응답 후 종료
 *
 * @param string $msg 에러 메시지
 * @param int $code HTTP 상태 코드
 */
function returnError($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'code' => $code
    ]);
    exit;
}

/**
 * 성공 메시지와 함께 JSON 응답 후 종료
 *
 * @param string $msg 성공 메시지
 * @param array $data 응답 데이터
 */
function returnSuccess($msg, $data = [])
{
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $msg,
        'data' => $data
    ]);
    exit;
}

/**
 * alert 창 출력 후 URL 리다이렉트
 *
 * @param string $msg 알림 메시지
 * @param string|null $url 리다이렉트 URL
 */
function alert($msg = '', $url = null)
{
    $script = '<script charset="utf-8" content="text/javascript;charset=utf-8">';
    if ($msg) {
        $script .= "alert('{$msg}');";
    }
    if ($url) {
        $script .= "location.href='{$url}';";
    }
    $script .= '</script>';

    echo $script;
    exit;
}

/**
 * 세션에 사용자 정보가 존재하는지 확인
 *
 * @return bool 세션 유효 여부
 */
function checkSession() {
    // 기본 세션 체크
    if(!isset($_SESSION['user_info'])) {
        return false;
    }
    
    // 필수 세션 값이 있는지 확인
    if(!isset($_SESSION['user_info']['id'])) {
        return false;
    }
    
    return true;
}

/**
 * 세션 만료 처리 및 응답
 */
function respondSessionExpired()
{
    session_unset();
    session_destroy();
        
    // AJAX 요청인지 확인
    $isAjax = 
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        // AJAX 요청일 경우 json 응답
        header('Content-Type: application/json');
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode([
            'success' => false,
            'message' => '세션이 만료되었습니다. 다시 로그인해주세요.',
            'code' => 401
        ]);
    } else {
        // 브라우저 요청일 경우 alert
        alert('세션이 만료되었습니다. 다시 로그인해주세요.', '/login');
    }
    exit;
}

/**
 * 날짜 유효성 검사
 *
 * @param string $date 검사할 날짜 문자열
 * @param string $format 날짜 형식
 * @return bool 유효 여부
 */
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * 랜덤 문자열 생성
 *
 * @param int $length 문자열 길이
 * @return string 생성된 랜덤 문자열
 */
function generateRandomString($length = 10) 
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * 파일 확장자 추출
 *
 * @param string $filename 파일명
 * @return string 파일 확장자
 */
function getFileExtension($filename) 
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * 허용된 파일 확장자인지 확인
 *
 * @param string $filename 파일명
 * @param array $allowedExtensions 허용된 확장자 배열
 * @return bool 허용 여부
 */
function isAllowedFileExtension($filename, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']) 
{
    $ext = getFileExtension($filename);
    return in_array($ext, $allowedExtensions);
}

/**
 * 배열을 CSV 형식 문자열로 변환
 *
 * @param array $data 데이터 배열
 * @param array $headers CSV 헤더
 * @return string CSV 문자열
 */
function arrayToCsv($data, $headers = []) 
{
    $output = fopen('php://temp', 'r+');
    
    if (!empty($headers)) {
        fputcsv($output, $headers);
    }
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * 관리자 IP 여부 확인
 *
 * @return bool 관리자 IP 여부
 */
function isAdmin()
{
    // 환경 변수에서 관리자 IP 가져오기
    $adminIp = $_ENV['ADMIN_IP_ADDR'] ?? getenv('ADMIN_IP_ADDR');
    
    // 환경 변수가 없을 경우 상수 사용 (이전 방식과 호환성 유지)
    if (empty($adminIp) && defined('ADMIN_IP_ADDR')) {
        $adminIp = ADMIN_IP_ADDR;
    }
    
    return getClientIp() === $adminIp;
}

/**
 * 관리자 권한 검증 (alert 또는 JSON 에러 응답)
 *
 * @param string $type 응답 타입 ('alert' 또는 'echo')
 */
function verifyAdmin($type = 'alert')
{
    $msg = "권한이 없습니다.";
    if (!isAdmin()) {
        switch ($type) {
            case 'alert':
                echo '<script charset="utf-8" content="text/javascript;charset=utf-8">alert("' . $msg . '"); location.href="/";</script>';
                break;
            case 'echo':
                returnError($msg, 403);
                break;
            default:
                returnError($msg, 403);
                break;
        }
        exit;
    }
}
