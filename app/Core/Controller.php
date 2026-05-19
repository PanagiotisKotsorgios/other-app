<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        $this->redirect($referer);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }
                if (str_starts_with($r, 'max:')) {
                    $max = (int)substr($r, 4);
                    if (strlen($data[$field] ?? '') > $max) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at most {$max} characters.";
                        break;
                    }
                }
                if ($r === 'email' && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email address.';
                    break;
                }
                if ($r === 'numeric' && !empty($data[$field]) && !is_numeric($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                    break;
                }
            }
        }
        return $errors;
    }
}
