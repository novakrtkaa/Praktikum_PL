<?php
// File: app/controllers/AuthController.php

class AuthController extends Controller
{
    private $auth;
    private $middleware;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
    }

    /**
     * Menampilkan halaman login
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        $this->middleware->guest();
        
        $csrf = Csrf::generate();
        $this->view('auth/login', ['csrf' => $csrf]);
    }

    /**
     * Proses login
     */
    public function authenticate()
    {
        Csrf::verifyOrFail();
        
        // Rate limiting
        $this->middleware->rateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 15);

        // Validate input
        $validator = new Validator($_POST);
        $validator->required('username')->required('password');

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('auth/login');
        }

        $username = Sanitizer::alphanumeric($_POST['username']);
        $password = $_POST['password'];

        // Attempt login
        if ($this->auth->attempt($username, $password)) {
            // Redirect ke intended URL atau dashboard
            $intendedUrl = $_SESSION['intended_url'] ?? null;
            unset($_SESSION['intended_url']);
            
            if ($intendedUrl) {
                header("Location: " . $intendedUrl);
                exit;
            }
            
            $_SESSION['success'] = 'Selamat datang, ' . $this->auth->user()->getFullName() . '!';
            return $this->redirect('dashboard');
        }

        // Login failed
        return $this->redirect('auth/login');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->auth->logout();
        $_SESSION['success'] = 'Anda berhasil logout.';
        $this->redirect('auth/login');
    }

    /**
     * Halaman register (optional)
     */
    public function register()
    {
        $this->middleware->guest();
        
        $csrf = Csrf::generate();
        $this->view('auth/register', ['csrf' => $csrf]);
    }

    /**
     * Proses register
     */
    public function store()
    {
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('username')
                  ->between('username', 3, 50)
                  ->required('email')
                  ->required('password')
                  ->between('password', 6, 50)
                  ->required('full_name')
                  ->between('full_name', 3, 100);

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('auth/register');
        }

        $userRepo = new UserRepository();
        
        // Check if username exists
        if ($userRepo->findByUsername($_POST['username'])) {
            $_SESSION['error'] = 'Username sudah digunakan.';
            return $this->redirect('auth/register');
        }

        // Check if email exists
        if ($userRepo->findByEmail($_POST['email'])) {
            $_SESSION['error'] = 'Email sudah digunakan.';
            return $this->redirect('auth/register');
        }

        $data = [
            'username' => Sanitizer::alphanumeric($_POST['username']),
            'email' => Sanitizer::email($_POST['email']),
            'password' => $_POST['password'],
            'full_name' => Sanitizer::name($_POST['full_name']),
            'role' => 'staff' // Default role
        ];

        $userRepo->create($data);
        
        $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
        $this->redirect('auth/login');
    }
}