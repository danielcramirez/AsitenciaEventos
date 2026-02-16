<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/index.php?action=dashboard');
        }
        
        echo $this->view('auth/login', [
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/index.php?action=login');
        }
        
        $this->validateCSRF();
        
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Por favor ingrese usuario y contraseña';
            $this->redirect(BASE_URL . '/index.php?action=login');
        }
        
        $result = $this->userModel->authenticate($username, $password);
        
        if ($result === false) {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            $this->redirect(BASE_URL . '/index.php?action=login');
        }
        
        if (is_array($result) && isset($result['error'])) {
            if ($result['error'] === 'account_locked') {
                $lockoutTime = date('H:i:s', strtotime($result['lockout_until']));
                $_SESSION['error'] = "Cuenta bloqueada por múltiples intentos fallidos. Intente después de las $lockoutTime";
            }
            $this->redirect(BASE_URL . '/index.php?action=login');
        }
        
        // Successful login
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['role'] = $result['role'];
        $_SESSION['full_name'] = $result['full_name'];
        
        session_regenerate_id(true);
        
        $this->redirect(BASE_URL . '/index.php?action=dashboard');
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $userModel = new User();
            $userModel->logAudit('logout', 'user', $_SESSION['user_id'], 'User logged out', $_SESSION['user_id']);
        }
        
        session_destroy();
        $this->redirect(BASE_URL . '/index.php?action=login');
    }
    
    public function dashboard() {
        $this->requireLogin();
        
        $data = [
            'user' => [
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ]
        ];
        
        echo $this->view('shared/header', $data);
        echo $this->view('auth/dashboard', $data);
        echo $this->view('shared/footer');
    }
}
