<?php
/**
 *
 * @link http://www.ketangshang.cn/
 * @author tiankong <tianakong@aliyun.com>
 * @version 1.0
 */

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', function () {
        echo 'hello world';
    });
    $r->addRoute('GET', '/users/{id:\d+}', function ($id) {
        echo '我是users-' . $id;
    });
    $r->addRoute('GET', '/index/{id:\d+}', ['app\Controller\Index', 'index']);
    $r->get('/test.html', function(){
        echo 'test.html';
    });

    //分组
    $r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
        $r->get('/index', ['app\Controller\admin\Index', 'index']);
        $r->get('/main', ['app\Controller\admin\Index', 'main']);

    });
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit('404 Not Found');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // ... call $handler with $vars
        if (is_array($handler)) {
            $controller = $handler[0];
            call_user_func_array([new $handler[0](), $handler[1]], $vars);
            return ;
        }
        if (is_callable($handler)) {
            call_user_func_array($handler, $vars);
        }
        break;
}