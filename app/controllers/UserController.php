<?php
// File: app/controllers/UserController.php

class UserController extends Controller
{
    private $auth;
    private $middleware;
    private $userRepo;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
        $this->userRepo = new UserRepository();
    }

    /**
     * Daftar semua user (Admin only)
     */
    public function index()
    {
        $this->middleware->admin();
        
        $users = $this->userRepo->all();
        $csrf = Csrf::generate();
        
        $this->view('users/index', [
            'users' => $users,
            'csrf' => $csrf
        ]);
    }

    /**
     * Form tambah user baru (Admin only)
     */
    public function create()
    {
        $this->middleware->admin();
        
        $csrf = Csrf::generate();
        $this->view('users/create', ['csrf' => $csrf]);
    }

    /**
     * Simpan user baru
     */
    public function store()
    {
        $this->middleware->admin();
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('username')
                  ->between('username', 3, 50)
                  ->required('email')
                  ->required('password')
                  ->between('password', 6, 50)
                  ->required('full_name')
                  ->between('full_name', 3, 100)
                  ->required('role');

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('user/create');
        }

        // Check if username exists
        if ($this->userRepo->findByUsername($_POST['username'])) {
            $_SESSION['error'] = 'Username sudah digunakan.';
            return $this->redirect('user/create');
        }

        // Check if email exists
        if ($this->userRepo->findByEmail($_POST['email'])) {
            $_SESSION['error'] = 'Email sudah digunakan.';
            return $this->redirect('user/create');
        }

        $data = [
            'username' => Sanitizer::alphanumeric($_POST['username']),
            'email' => Sanitizer::email($_POST['email']),
            'password' => $_POST['password'],
            'full_name' => Sanitizer::name($_POST['full_name']),
            'role' => $_POST['role']
        ];

        $this->userRepo->create($data);
        
        $_SESSION['success'] = 'User baru berhasil ditambahkan!';
        $this->redirect('user');
    }

    /**
     * Form edit user
     */
    public function edit($id)
    {
        $this->middleware->admin();
        
        $user = $this->userRepo->findById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User tidak ditemukan.';
            return $this->redirect('user');
        }

        $csrf = Csrf::generate();
        $this->view('users/edit', [
            'user' => $user,
            'csrf' => $csrf
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $this->middleware->admin();
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('username')
                  ->between('username', 3, 50)
                  ->required('email')
                  ->required('full_name')
                  ->between('full_name', 3, 100)
                  ->required('role');

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('user/edit/' . $id);
        }

        $data = [
            'username' => Sanitizer::alphanumeric($_POST['username']),
            'email' => Sanitizer::email($_POST['email']),
            'full_name' => Sanitizer::name($_POST['full_name']),
            'role' => $_POST['role'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $this->userRepo->update($id, $data);
        
        $_SESSION['success'] = 'Data user berhasil diperbarui!';
        $this->redirect('user');
    }

    /**
     * Nonaktifkan user
     */
    public function delete($id)
    {
        $this->middleware->admin();
        
        // Prevent deleting self
        if ($id == $this->auth->id()) {
            $_SESSION['error'] = 'Anda tidak dapat menghapus akun sendiri.';
            return $this->redirect('user');
        }

        $this->userRepo->delete($id);
        
        $_SESSION['success'] = 'User berhasil dinonaktifkan!';
        $this->redirect('user');
    }
}