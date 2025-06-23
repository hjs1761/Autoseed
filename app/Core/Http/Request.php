<?php
/**
 * 파일: app/Core/Http/Request.php
 * 
 * 이 파일은 HTTP 요청을 처리하기 위한 클래스를 정의합니다.
 * $_GET, $_POST 등의 슈퍼글로벌 변수에 대한 접근을 캡슐화하여
 * 객체지향적인 방식으로 HTTP 요청 데이터에 접근할 수 있게 합니다.
 * 
 * @package App\Core\Http
 */

namespace App\Core\Http;

/**
 * HTTP 요청 클래스
 * 
 * 이 클래스는 HTTP 요청 데이터에 대한 객체지향적 인터페이스를 제공합니다.
 * 슈퍼글로벌 변수($_GET, $_POST 등)를 캡슐화하여 타입 안정성과 
 * 테스트 용이성을 높입니다.
 */
class Request
{
    /**
     * GET 요청 파라미터
     * @var array
     */
    protected array $get;
    
    /**
     * POST 요청 파라미터
     * @var array
     */
    protected array $post;
    
    /**
     * 업로드된 파일 정보
     * @var array
     */
    protected array $files;
    
    /**
     * 서버 및 환경 변수
     * @var array
     */
    protected array $server;
    
    /**
     * 쿠키 데이터
     * @var array
     */
    protected array $cookies;
    
    /**
     * 원시 요청 본문
     * @var string
     */
    protected string $content;
    
    /**
     * 요청 객체 생성
     * 
     * 슈퍼글로벌 변수를 읽어와 객체 속성으로 초기화합니다.
     */
    public function __construct()
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->content = file_get_contents('php://input');
    }
    
    /**
     * GET 파라미터 조회
     * 
     * @param string|null $key 파라미터 키 (null인 경우 모든 GET 파라미터 반환)
     * @param mixed $default 키가 존재하지 않을 경우 반환할 기본값
     * @return mixed 파라미터 값 또는 기본값
     */
    public function getQuery(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        
        return $this->get[$key] ?? $default;
    }
    
    /**
     * POST 파라미터 조회
     * 
     * @param string|null $key 파라미터 키 (null인 경우 모든 POST 파라미터 반환)
     * @param mixed $default 키가 존재하지 않을 경우 반환할 기본값
     * @return mixed 파라미터 값 또는 기본값
     */
    public function getPost(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        
        return $this->post[$key] ?? $default;
    }
    
    /**
     * POST와 GET 파라미터를 모두 검색 (POST 우선)
     * 
     * @param string $key 파라미터 키
     * @param mixed $default 키가 존재하지 않을 경우 반환할 기본값
     * @return mixed 파라미터 값 또는 기본값
     */
    public function get(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }
    
    /**
     * 모든 요청 파라미터 반환 (GET + POST)
     * 
     * @return array 모든 요청 파라미터
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }
    
    /**
     * JSON 요청 데이터 파싱
     * 
     * Content-Type이 application/json인 경우 요청 본문을 JSON으로 파싱합니다.
     * 
     * @return array 파싱된 JSON 데이터 (파싱 실패 시 빈 배열)
     */
    public function getJson(): array
    {
        if (empty($this->content)) {
            return [];
        }
        
        $contentType = $this->getHeader('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            return json_decode($this->content, true) ?? [];
        }
        
        return [];
    }
    
    /**
     * 요청 메소드 반환
     * 
     * @return string HTTP 메소드 (GET, POST, PUT, DELETE 등)
     */
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * 요청 URI 반환
     * 
     * 쿼리 스트링을 제외한 URI 경로를 반환합니다.
     * 
     * @return string URI 경로
     */
    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        // 쿼리스트링 제거
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return rawurldecode($uri);
    }
    
    /**
     * 헤더 값 조회
     * 
     * @param string $name 헤더 이름
     * @param mixed $default 기본값
     * @return mixed 헤더 값
     */
    public function getHeader(string $name, $default = null)
    {
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($name));
        return $this->server[$headerName] ?? $default;
    }
    
    /**
     * 모든 헤더 값 조회
     * 
     * @return array 모든 HTTP 헤더
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * 요청이 AJAX인지 확인
     * 
     * X-Requested-With 헤더가 XMLHttpRequest인 경우 AJAX 요청으로 간주합니다.
     * 
     * @return bool AJAX 요청 여부
     */
    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * 요청이 특정 HTTP 메소드인지 확인
     * 
     * @param string $method 확인할 HTTP 메소드
     * @return bool 메소드 일치 여부
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->getMethod()) === strtoupper($method);
    }
    
    /**
     * 업로드된 파일 조회
     * 
     * @param string $key 파일 필드 이름
     * @return array|null 파일 정보
     */
    public function getFile(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * 모든 업로드된 파일 조회
     * 
     * @return array 모든 업로드된 파일
     */
    public function getFiles(): array
    {
        return $this->files;
    }
    
    /**
     * 요청 IP 주소 조회
     * 
     * @return string IP 주소
     */
    public function getIp(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * 세션 변수 조회
     * 
     * @param string $key 세션 키
     * @param mixed $default 기본값
     * @return mixed 세션 값
     */
    public function getSession(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * 사용자 에이전트 문자열 조회
     * 
     * @return string 사용자 에이전트
     */
    public function getUserAgent(): string
    {
        return $this->getHeader('User-Agent', '');
    }
} 