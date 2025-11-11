<?php
// File: app/controllers/NotificationController.php

class NotificationController extends Controller
{
    private $auth;
    private $middleware;
    private $notificationRepo;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
        $this->notificationRepo = new NotificationRepository();
    }

    public function index()
    {
        $this->middleware->auth();
        
        $notifications = $this->notificationRepo->getAllByUser($this->auth->id());
        $csrf = Csrf::generate();
        
        $this->view('notifications/index', [
            'notifications' => $notifications,
            'csrf' => $csrf
        ]);
    }

    public function markAsRead($id)
    {
        $this->middleware->auth();
        
        $this->notificationRepo->markAsRead($id);
        
        if (isset($_GET['redirect'])) {
            header('Location: ' . $_GET['redirect']);
        } else {
            $this->redirect('notification');
        }
    }

    public function markAllAsRead()
    {
        $this->middleware->auth();
        Csrf::verifyOrFail();
        
        $this->notificationRepo->markAllAsRead($this->auth->id());
        
        $_SESSION['success'] = 'Semua notifikasi telah ditandai sebagai dibaca.';
        $this->redirect('notification');
    }

    public function getUnreadCount()
    {
        $this->middleware->auth();
        
        $count = $this->notificationRepo->countUnread($this->auth->id());
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}