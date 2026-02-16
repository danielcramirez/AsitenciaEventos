<?php
class Controller {
    protected function view($viewPath, $data = []) {
        extract($data);
        
        ob_start();
        require_once __DIR__ . '/../views/' . $viewPath . '.php';
        $content = ob_get_clean();
        
        return $content;
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/index.php?action=login');
        }
    }
    
    protected function requireRole($allowedRoles) {
        $this->requireLogin();
        
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
            http_response_code(403);
            die('Acceso denegado');
        }
    }
    
    protected function validateCSRF() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('Token CSRF inválido');
        }
    }
    
    protected function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
