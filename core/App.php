<?php

class App
{
    public function run(string $url)
    {
        $segments = explode('/', trim($url, '/'));

        $controllerName = !empty($segments[0]) ? $segments[0] : 'home';
        $method = isset($segments[1]) && $segments[1] !== '' ? $segments[1] : 'index';
        $params = array_slice($segments, 2);

        $controllerClass = ucfirst($controllerName) . 'Controller';

        if (!class_exists($controllerClass)){
            return $this->error404();
        }

        $controller = new $controllerClass();

        if (!is_callable([$controller, $method])){
            return $this->error404();
        } 

        call_user_func_array([$controller, $method], $params);
        var_dump($url); die;
    }

    private function error404()
    {
        http_response_code(404);
        require BASE_PATH . '/views/404.php';
        exit;
    }
}