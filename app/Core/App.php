<?php
/**
 * 파일: app/Core/App.php
 * 
 * 이 파일은 애플리케이션의 기본 클래스를 정의합니다.
 * 미들웨어 스택을 관리하고 요청을 처리합니다.
 */

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Middleware\MiddlewareInterface;
use FastRoute\Dispatcher;

/**
 * 애플리케이션 클래스
 */
class App
{
    /**
     * 미들웨어 스택
     * @var array
     */
    private array $middlewares = [];
    
    /**
     * 라우터 인스턴스
     * @var Dispatcher
     */
    private $router;
    
    /**
     * DB 인스턴스
     * @var DB
     */
    private $db;
    
    /**
     * 로거 인스턴스
     * @var \Monolog\Logger
     */
    private $logger;
    
    /**
     * 애플리케이션 생성
     * 
     * @param Dispatcher $router 라우터 인스턴스
     * @param DB $db DB 인스턴스
     * @param \Monolog\Logger $logger 로거 인스턴스
     */
    public function __construct(Dispatcher $router, DB $db, $logger)
    {
        $this->router = $router;
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * 미들웨어 추가
     * 
     * @param MiddlewareInterface $middleware 미들웨어 인스턴스
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }
    
    /**
     * 애플리케이션 실행
     * 
     * @param Request $request 요청 객체
     * @return void
     */
    public function run(Request $request): void
    {
        $response = $this->handleRequest($request);
        $response->send();
    }
    
    /**
     * 요청 처리
     * 
     * @param Request $request 요청 객체
     * @return Response 응답 객체
     */
    private function handleRequest(Request $request): Response
    {
        // 미들웨어 스택 실행
        $runner = $this->createMiddlewareRunner();
        return $runner($request);
    }
    
    /**
     * 미들웨어 실행 함수 생성
     * 
     * @return callable 미들웨어 실행 함수
     */
    private function createMiddlewareRunner(): callable
    {
        // 라우터를 최종 미들웨어로 추가
        $middlewares = array_merge($this->middlewares, [
            new class($this->router, $this->db, $this->logger) implements MiddlewareInterface {
                private $router;
                private $db;
                private $logger;
                
                public function __construct($router, $db, $logger)
                {
                    $this->router = $router;
                    $this->db = $db;
                    $this->logger = $logger;
                }
                
                public function process($request, callable $next)
                {
                    $method = $request->getMethod();
                    $uri = $request->getUri();
                    
                    $routeInfo = $this->router->dispatch($method, $uri);
                    
                    switch ($routeInfo[0]) {
                        case Dispatcher::NOT_FOUND:
                            return new Http\Response('404 Not Found', 404);
                            
                        case Dispatcher::METHOD_NOT_ALLOWED:
                            return new Http\Response('405 Method Not Allowed', 405);
                            
                        case Dispatcher::FOUND:
                            $handler = $routeInfo[1];
                            $vars = $routeInfo[2];
                            
                            [$class, $method] = $handler;
                            $controller = new $class($this->db, $this->logger);
                            
                            // 컨트롤러에 요청 객체 전달
                            $vars = array_merge([$request], $vars);
                            $result = call_user_func_array([$controller, $method], $vars);
                            
                            // 응답이 Response 객체가 아니면 Response 객체로 변환
                            if (!($result instanceof Http\Response)) {
                                if (is_array($result) || is_object($result)) {
                                    $result = new Http\JsonResponse($result);
                                } else {
                                    $result = new Http\Response($result);
                                }
                            }
                            
                            return $result;
                    }
                }
            }
        ]);
        
        // 미들웨어 실행 함수 정의
        $runner = function ($request, $middlewares, $index = 0) use (&$runner) {
            if ($index >= count($middlewares)) {
                return new Http\Response('');
            }
            
            $middleware = $middlewares[$index];
            return $middleware->process($request, function ($request) use ($runner, $middlewares, $index) {
                return $runner($request, $middlewares, $index + 1);
            });
        };
        
        return function ($request) use ($runner, $middlewares) {
            return $runner($request, $middlewares, 0);
        };
    }
} 