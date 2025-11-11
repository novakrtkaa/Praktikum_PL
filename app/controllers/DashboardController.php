<?php
// File: app/controllers/DashboardController.php (UPDATED)

class DashboardController extends Controller
{
    private $auth;
    private $middleware;
    private $reservationRepo;
    private $courtRepo;
    private $userRepo;
    private $notificationRepo;
    private $activityRepo;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
        $this->reservationRepo = new ReservationRepository();
        $this->courtRepo = new CourtRepository();
        $this->userRepo = new UserRepository();
        $this->notificationRepo = new NotificationRepository();
        $this->activityRepo = new ReservationActivityRepository();
    }

    public function index()
    {
        $this->middleware->auth();
        
        $role = $this->auth->role();
        
        switch ($role) {
            case 'admin':
                return $this->adminDashboard();
            case 'manager':
                return $this->managerDashboard();
            case 'staff':
                return $this->staffDashboard();
            default:
                $_SESSION['error'] = 'Role tidak dikenali.';
                $this->redirect('auth/logout');
        }
    }

    private function adminDashboard()
    {
        // Statistics
        $totalReservations = $this->reservationRepo->count();
        $totalCourts = count($this->courtRepo->all());
        $totalUsers = count($this->userRepo->all());
        $totalAdmins = $this->userRepo->countByRole('admin');
        $totalManagers = $this->userRepo->countByRole('manager');
        $totalStaff = $this->userRepo->countByRole('staff');
        
        // Workflow stats
        $workflowStats = $this->reservationRepo->getWorkflowStats();
        
        // Recent activities
        $recentActivities = $this->activityRepo->getRecent(10);
        
        // Pending items
        $pendingManager = $this->reservationRepo->getPendingForManager();
        $pendingReschedule = $this->reservationRepo->getPendingReschedule();
        $pendingCancel = $this->reservationRepo->getPendingCancel();
        
        // Notifications
        $notifications = $this->notificationRepo->getUnreadByUser($this->auth->id());
        $unreadCount = $this->notificationRepo->countUnread($this->auth->id());
        
        // Trashed items
        $trashedCount = count($this->reservationRepo->trashed());
        
        $data = [
            'user' => $this->auth->user(),
            'stats' => [
                'reservations' => $totalReservations,
                'courts' => $totalCourts,
                'users' => $totalUsers,
                'admins' => $totalAdmins,
                'managers' => $totalManagers,
                'staff' => $totalStaff,
                'trashed' => $trashedCount
            ],
            'workflow_stats' => $workflowStats,
            'pending_manager' => count($pendingManager),
            'pending_reschedule' => count($pendingReschedule),
            'pending_cancel' => count($pendingCancel),
            'recent_activities' => $recentActivities,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
        
        $this->view('dashboard/admin', $data);
    }

    private function managerDashboard()
    {
        // Statistics
        $totalReservations = $this->reservationRepo->count();
        $totalCourts = count($this->courtRepo->all());
        
        // Workflow stats
        $workflowStats = $this->reservationRepo->getWorkflowStats();
        
        // Pending approvals (tugas manager)
        $pendingApprovals = $this->reservationRepo->getPendingForManager();
        $pendingCancel = $this->reservationRepo->getPendingCancel();
        
        // Recent activities
        $recentActivities = $this->activityRepo->getRecent(10);
        
        // Notifications
        $notifications = $this->notificationRepo->getUnreadByUser($this->auth->id());
        $unreadCount = $this->notificationRepo->countUnread($this->auth->id());
        
        $data = [
            'user' => $this->auth->user(),
            'stats' => [
                'reservations' => $totalReservations,
                'courts' => $totalCourts
            ],
            'workflow_stats' => $workflowStats,
            'pending_approvals' => $pendingApprovals,
            'pending_cancel' => $pendingCancel,
            'recent_activities' => $recentActivities,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
        
        $this->view('dashboard/manager', $data);
    }

    private function staffDashboard()
    {
        // Recent reservations yang dibuat oleh staff ini
        $myActivities = $this->activityRepo->getByUser($this->auth->id(), 10);
        
        // Reservasi yang perlu di-reschedule (assigned ke staff ini)
        $needReschedule = $this->reservationRepo->getPendingReschedule();
        
        // Notifications
        $notifications = $this->notificationRepo->getUnreadByUser($this->auth->id());
        $unreadCount = $this->notificationRepo->countUnread($this->auth->id());
        
        // Recent reservations
        $recentReservations = $this->reservationRepo->all('', 'id', 'DESC', 10, 0);
        
        $data = [
            'user' => $this->auth->user(),
            'my_activities' => $myActivities,
            'need_reschedule' => $needReschedule,
            'recent_reservations' => $recentReservations,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ];
        
        $this->view('dashboard/staff', $data);
    }

    public function profile()
    {
        $this->middleware->auth();
        
        $user = $this->auth->user();
        $csrf = Csrf::generate();
        
        $this->view('dashboard/profile', [
            'user' => $user,
            'csrf' => $csrf
        ]);
    }

    public function updatePassword()
    {
        $this->middleware->auth();
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('current_password')
                  ->required('new_password')
                  ->between('new_password', 6, 50)
                  ->required('confirm_password');

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('dashboard/profile');
        }

        if (!$this->auth->user()->verifyPassword($_POST['current_password'])) {
            $_SESSION['error'] = 'Password lama tidak sesuai.';
            return $this->redirect('dashboard/profile');
        }

        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
            return $this->redirect('dashboard/profile');
        }

        $this->userRepo->updatePassword($this->auth->id(), $_POST['new_password']);
        
        $_SESSION['success'] = 'Password berhasil diubah.';
        $this->redirect('dashboard/profile');
    }
}