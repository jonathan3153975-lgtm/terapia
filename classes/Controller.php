<?php

namespace Classes;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $file = dirname(__DIR__) . '/app/views/' . $view . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException('View nao encontrada: ' . $view);
        }

        extract($data);
        require $file;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function success(string $message, array $extra = []): void
    {
        $this->json(array_merge(['success' => true, 'message' => $message], $extra));
    }

    protected function error(string $message, int $statusCode = 400): void
    {
        $this->json(['success' => false, 'message' => $message], $statusCode);
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
