<?php

namespace Tito\App\Core;

class Router
{
    protected array $registeredRoutes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->registerRoute(HttpMethod::GET, $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->registerRoute(HttpMethod::POST, $path, $handler);
    }

    public function dispatch(): void
    {
        $requestUri = $this->resolveRequestUri();
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? HttpMethod::GET->value;

        $routeHandler = $this->findRouteHandler($httpMethod, $requestUri);

        if ($routeHandler === null) {
            $this->sendNotFoundResponse();
            return;
        }

        $this->executeHandler($routeHandler);
    }

    private function registerRoute(HttpMethod $httpMethod, string $routePath, callable|array $handler): void
    {
        $normalizedPath = rtrim($routePath, '/') ?: '/';
        $this->registeredRoutes[$httpMethod->value][$normalizedPath] = $handler;
    }

    private function resolveRequestUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    private function findRouteHandler(string $httpMethod, string $requestUri): callable|array|null
    {
        return $this->registeredRoutes[$httpMethod][$requestUri] ?? null;
    }

    private function executeHandler(callable|array $handler): void
    {
        if (is_callable($handler) && !is_array($handler)) {
            $handler();
            return;
        }

        if (!is_array($handler) || count($handler) < 2) {
            $this->sendInternalErrorResponse('Configuración de ruta inválida');
            return;
        }

        [$controllerClass, $actionMethod] = $handler;

        if (!class_exists($controllerClass)) {
            $this->sendInternalErrorResponse("Controlador no encontrado: {$controllerClass}");
            return;
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $actionMethod)) {
            $this->sendInternalErrorResponse("Método no encontrado: {$controllerClass}::{$actionMethod}");
            return;
        }

        $controllerInstance->$actionMethod();
    }

    private function sendNotFoundResponse(): void
    {
        http_response_code(404);
        echo '404 - Página no encontrada';
    }

    private function sendInternalErrorResponse(string $errorMessage): void
    {
        http_response_code(500);
        echo "500 - Error interno del servidor: {$errorMessage}";
    }
}
