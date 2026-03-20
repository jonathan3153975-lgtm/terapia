<?php

namespace Classes;

/**
 * Classe Controller base para todos os controllers
 */
abstract class Controller
{
    protected array $data = [];

    /**
     * Renderiza uma view
     */
    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        $viewPath = __DIR__ . "/../app/views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            die("View não encontrada: {$view}");
        }

        extract($this->data);
        require_once $viewPath;
    }

    /**
     * Redireciona para uma URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Retorna JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Retorna resposta de sucesso
     */
    protected function success(string $message = 'Operação realizada com sucesso', array $data = []): void
    {
        $this->json(array_merge(['success' => true, 'message' => $message], $data));
    }

    /**
     * Retorna resposta de erro
     */
    protected function error(string $message = 'Erro ao processar requisição', int $statusCode = 400): void
    {
        $this->json(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * Verifica se é uma requisição AJAX
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Obtém dados do POST
     */
    protected function post(): array
    {
        return $_POST;
    }

    /**
     * Obtém um valor específico do POST
     */
    protected function postValue(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtém dados do GET
     */
    protected function get(): array
    {
        return $_GET;
    }

    /**
     * Obtém um valor específico do GET
     */
    protected function getValue(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
