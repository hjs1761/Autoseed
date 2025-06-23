<?php
/**
 * 암호화 유틸리티 함수
 * 
 * 이 파일은 문자열을 안전하게 암호화하고 복호화하는 함수를 제공합니다.
 * AES-256-CBC 암호화 알고리즘을 사용하며, OpenSSL 확장을 필요로 합니다.
 * 
 * 주의사항:
 * - 암호화 키는 안전하게 관리되어야 합니다.
 * - 프로덕션 환경에서는 키를 소스 코드에 직접 포함시키지 말고 환경 변수나 안전한 저장소에서 로드하세요.
 * - PHP 7.2 이상을 권장합니다.
 */

// 암호화 키 (프로덕션 환경에서는 .env 파일이나 안전한 위치에서 로드하는 것이 좋습니다)
// 이상적으로는 아래와 같이 환경 변수에서 로드하는 것이 좋습니다:
// $encryptionKey = $_ENV['ENCRYPTION_KEY'] ?? 'fallback_key';
$encryptionKey = 'InfluencerSolution2023!SecretKey';

/**
 * 문자열 암호화
 * 
 * AES-256-CBC 암호화 알고리즘과 무작위 IV(Initialization Vector)를 사용하여
 * 문자열을 안전하게 암호화합니다. 결과는 base64로 인코딩됩니다.
 * 
 * @param string $plainText 암호화할 평문
 * @return string 암호화된 문자열 (base64 인코딩됨)
 */
function encrypt($plainText)
{
    global $encryptionKey;
    $method = "AES-256-CBC";                                      // 암호화 알고리즘
    $key = substr(hash('sha256', $encryptionKey, true), 0, 32);   // 키 해싱 (32바이트)
    $iv = openssl_random_pseudo_bytes(16);                        // 무작위 IV 생성 (16바이트)
    $ciphertext = openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);                      // IV + 암호문을 base64로 인코딩
}

/**
 * 문자열 복호화
 * 
 * encrypt() 함수로 암호화된 문자열을 복호화합니다.
 * base64 디코딩 후 IV를 추출하고 원본 평문을 복구합니다.
 * 
 * @param string $encryptedText 복호화할 암호문 (base64 인코딩됨)
 * @return string|false 복호화된 평문 또는 실패 시 false
 */
function decrypt($encryptedText)
{
    global $encryptionKey;
    $method = "AES-256-CBC";                                      // 암호화 알고리즘
    $key = substr(hash('sha256', $encryptionKey, true), 0, 32);   // 키 해싱 (32바이트)
    $data = base64_decode($encryptedText);                        // base64 디코딩
    $iv = substr($data, 0, 16);                                   // IV 추출 (처음 16바이트)
    $ciphertext = substr($data, 16);                              // 암호문 추출 (나머지 부분)
    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}
