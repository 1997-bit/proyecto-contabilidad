<?php
class App
{
    public function run(string $url): void
    {
        $segments = explode('/', trim($url, '/'));

        $controllerName = !empty($segments[0]) ? $segments[0] : 'home';
        $method = isset($segments[1]) && $segments[1] !== '' ? $segments[1] : 'index';
        $params = array_slice($segments, 2);

        $controllerClass = ucfirst($controllerName) . 'Controller';

        if (!class_exists($controllerClass)) {
            $this->error404();
            return;
        }

        $controller = new $controllerClass();

        if (!is_callable([$controller, $method])) {
            $this->error404();
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function error404(): void
    {
        http_response_code(404);
        require BASE_PATH . '/views/404.PHP';
        exit;
    }
}
