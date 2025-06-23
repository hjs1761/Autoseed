<?php
/**
 * 파일: app/Core/Http/Response.php
 * 
 * 이 파일은 HTTP 응답을 처리하기 위한 기본 클래스를 정의합니다.
 */

namespace App\Core\Http;

/**
 * 기본 응답 클래스
 */
class Response
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected $content = '';
    
    /**
     * 응답 생성
     * 
     * @param mixed $content 응답 내용
     * @param int $statusCode HTTP 상태 코드
     * @param array $headers HTTP 헤더
     */
    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge($this->headers, $headers);
    }
    
    /**
     * 응답 전송
     */
    public function send(): void
    {
        // 상태 코드 설정
        http_response_code($this->statusCode);
        
        // 헤더 설정
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        // 내용 출력
        echo $this->content;
    }
    
    /**
     * 헤더 추가
     * 
     * @param string $name 헤더 이름
     * @param string $value 헤더 값
     * @return $this
     */
    public function withHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
}

/**
 * JSON 응답 클래스
 */
class JsonResponse extends Response
{
    /**
     * JSON 응답 생성
     * 
     * @param mixed $data 응답 데이터
     * @param int $statusCode HTTP 상태 코드
     * @param array $headers HTTP 헤더
     */
    public function __construct($data = null, int $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON 인코딩 오류: ' . json_last_error_msg());
        }
        
        parent::__construct($content, $statusCode, $headers);
    }
}

/**
 * 리다이렉트 응답 클래스
 */
class RedirectResponse extends Response
{
    /**
     * 리다이렉트 응답 생성
     * 
     * @param string $url 리다이렉트 URL
     * @param int $statusCode HTTP 상태 코드
     * @param array $headers HTTP 헤더
     */
    public function __construct(string $url, int $statusCode = 302, array $headers = [])
    {
        $headers['Location'] = $url;
        parent::__construct('', $statusCode, $headers);
    }
} 