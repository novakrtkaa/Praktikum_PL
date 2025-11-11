<?php
// File: app/controllers/ReservationController.php (FIXED)

class ReservationController extends Controller
{
    private $reservationRepo;
    private $courtRepo;
    private $auth;
    private $middleware;
    private $activityRepo;
    private $db; // ✅ TAMBAHKAN PROPERTI INI

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->reservationRepo = new ReservationRepository();
        $this->courtRepo = new CourtRepository();
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
        $this->activityRepo = new ReservationActivityRepository();
        $this->db = new Database(); // ✅ INISIALISASI DATABASE
    }

    public function index()
    {
        $this->middleware->auth();
        
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'id';
        $order = $_GET['order'] ?? 'DESC';
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $reservations = $this->reservationRepo->all($search, $sort, $order, $limit, $offset);
        $total = $this->reservationRepo->count($search);
        $pages = ceil($total / $limit);

        $this->view('reservations/index', [
            'reservations' => $reservations,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'pages' => $pages
        ]);
    }

    // === STAFF: Create & Submit for Manager Approval ===
    public function create()
    {
        $this->middleware->auth();
        
        $courts = $this->courtRepo->all();
        $csrf = Csrf::generate();
        $this->view('reservations/create', ['courts' => $courts, 'csrf' => $csrf]);
    }

    public function store()
    {
        $this->middleware->auth();
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('customer_name')
                  ->between('customer_name', 3, 50)
                  ->numeric('court_id')
                  ->dateFormat('start_time', 'Y-m-d\TH:i')
                  ->dateFormat('end_time', 'Y-m-d\TH:i');

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('reservation/create');
        }

        $data = [
            'customer_name' => Sanitizer::name($_POST['customer_name']),
            'court_id' => $_POST['court_id'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'status' => 'pending',
            'workflow_status' => 'pending_manager',
            'created_by' => $this->auth->id(),
            'notes' => Sanitizer::text($_POST['notes'] ?? '')
        ];

        $reservationId = $this->reservationRepo->create($data);
        
        // Notify managers
        $this->notifyManagers(
            'new_reservation',
            'Reservasi Baru Menunggu Approval',
            "Reservasi baru untuk {$data['customer_name']} memerlukan persetujuan Anda",
            "?c=reservation&a=edit&id={$reservationId}"
        );
        
        $_SESSION['success'] = "Reservasi berhasil dibuat dan menunggu approval Manager!";
        $this->redirect('reservation');
    }

    public function edit($id)
    {
        $this->middleware->auth();
        
        $reservation = $this->reservationRepo->find($id);
        
        if (!$reservation) {
            $_SESSION['error'] = 'Reservasi tidak ditemukan.';
            return $this->redirect('reservation');
        }
        
        $courts = $this->courtRepo->all();
        $csrf = Csrf::generate();
        $activities = $this->activityRepo->getByReservation($id);
        
        $this->view('reservations/edit', [
            'reservation' => $reservation,
            'courts' => $courts,
            'csrf' => $csrf,
            'activities' => $activities
        ]);
    }

    public function update($id)
    {
        $this->middleware->auth();
        Csrf::verifyOrFail();
        
        $reservation = $this->reservationRepo->find($id);
        
        if (!in_array($reservation['workflow_status'], ['draft', 'pending_reschedule', 'pending_manager'])) {
            $_SESSION['error'] = 'Reservasi dengan status ' . $reservation['workflow_status'] . ' tidak dapat diubah.';
            return $this->redirect('reservation/edit/' . $id);
        }
        
        $data = [
            'customer_name' => Sanitizer::name($_POST['customer_name']),
            'court_id' => $_POST['court_id'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'status' => 'pending',
            'notes' => Sanitizer::text($_POST['notes'] ?? '')
        ];
        
        $this->reservationRepo->update($id, $data);
        
        if ($reservation['workflow_status'] == 'pending_reschedule') {
            $this->db->query(
                "UPDATE reservations SET workflow_status = 'pending_manager' WHERE id = ?",
                [$id]
            );
            
            $this->activityRepo->log(
                $id,
                $this->auth->id(),
                'rescheduled',
                'pending_reschedule',
                'pending_manager',
                'Jadwal diperbarui, menunggu approval ulang'
            );
            
            $this->notifyManagers(
                'rescheduled',
                'Reservasi Dijadwal Ulang',
                "Reservasi untuk {$data['customer_name']} telah dijadwalkan ulang",
                "?c=reservation&a=edit&id={$id}"
            );
        }
        
        $_SESSION['success'] = "Data reservasi berhasil diperbarui!";
        $this->redirect('reservation');
    }

    // === MANAGER: Review & Approve ===
    public function managerPending()
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        
        $pending = $this->reservationRepo->getPendingForManager();
        $csrf = Csrf::generate();
        
        $this->view('reservations/manager_pending', [
            'reservations' => $pending,
            'csrf' => $csrf
        ]);
    }

    public function approve($id)
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        Csrf::verifyOrFail();
        
        $notes = Sanitizer::text($_POST['notes'] ?? 'Disetujui oleh Manager');
        
        if ($this->reservationRepo->approveReservation($id, $this->auth->id(), $notes)) {
            $_SESSION['success'] = 'Reservasi berhasil disetujui!';
        } else {
            $_SESSION['error'] = 'Gagal menyetujui reservasi.';
        }
        
        $this->redirect('reservation/managerPending');
    }

    public function requestReschedule($id)
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        Csrf::verifyOrFail();
        
        $reason = Sanitizer::text($_POST['reason'] ?? '');
        
        if (empty($reason)) {
            $_SESSION['error'] = 'Alasan reschedule harus diisi.';
            return $this->redirect('reservation/edit/' . $id);
        }
        
        if ($this->reservationRepo->requestReschedule($id, $this->auth->id(), $reason)) {
            $_SESSION['success'] = 'Permintaan reschedule berhasil dikirim ke Staff.';
        } else {
            $_SESSION['error'] = 'Gagal mengirim permintaan reschedule.';
        }
        
        $this->redirect('reservation/managerPending');
    }

    public function reject($id)
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        Csrf::verifyOrFail();
        
        $reason = Sanitizer::text($_POST['reason'] ?? '');
        
        if (empty($reason)) {
            $_SESSION['error'] = 'Alasan penolakan harus diisi.';
            return $this->redirect('reservation/edit/' . $id);
        }
        
        if ($this->reservationRepo->rejectReservation($id, $this->auth->id(), $reason)) {
            $_SESSION['success'] = 'Reservasi berhasil ditolak.';
        } else {
            $_SESSION['error'] = 'Gagal menolak reservasi.';
        }
        
        $this->redirect('reservation/managerPending');
    }

    // === STAFF: Request Cancel ===
    public function requestCancel($id)
    {
        $this->middleware->auth();
        Csrf::verifyOrFail();
        
        $reservation = $this->reservationRepo->find($id);
        
        if ($reservation['workflow_status'] !== 'approved') {
            $_SESSION['error'] = 'Hanya reservasi yang sudah disetujui yang dapat dibatalkan.';
            return $this->redirect('reservation/edit/' . $id);
        }
        
        $reason = Sanitizer::text($_POST['cancellation_reason'] ?? '');
        
        if (empty($reason)) {
            $_SESSION['error'] = 'Alasan pembatalan harus diisi.';
            return $this->redirect('reservation/edit/' . $id);
        }
        
        if ($this->reservationRepo->requestCancel($id, $this->auth->id(), $reason)) {
            $_SESSION['success'] = 'Permintaan pembatalan berhasil dikirim ke Manager.';
        } else {
            $_SESSION['error'] = 'Gagal mengirim permintaan pembatalan.';
        }
        
        $this->redirect('reservation');
    }

    // === MANAGER: Approve Cancel ===
    public function pendingCancel()
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        
        $pending = $this->reservationRepo->getPendingCancel();
        $csrf = Csrf::generate();
        
        $this->view('reservations/pending_cancel', [
            'reservations' => $pending,
            'csrf' => $csrf
        ]);
    }

    public function approveCancel($id)
    {
        $this->middleware->auth();
        $this->middleware->roles(['manager', 'admin']);
        Csrf::verifyOrFail();
        
        $notes = Sanitizer::text($_POST['notes'] ?? 'Pembatalan disetujui');
        
        if ($this->reservationRepo->approveCancel($id, $this->auth->id(), $notes)) {
            $_SESSION['success'] = 'Pembatalan reservasi berhasil disetujui!';
        } else {
            $_SESSION['error'] = 'Gagal menyetujui pembatalan.';
        }
        
        $this->redirect('reservation/pendingCancel');
    }

    // === Soft Delete ===
    public function delete($id)
    {
        $this->middleware->auth();
        
        $this->reservationRepo->softDelete($id);
        $_SESSION['success'] = "Data berhasil dipindahkan ke Recycle Bin.";
        $this->redirect('reservation');
    }

    // ✅ FIXED: Helper method dengan akses $this->db yang benar
    private function notifyManagers(string $type, string $title, string $message, string $link)
    {
        $notificationRepo = new NotificationRepository();
        $stmt = $this->db->query("SELECT id FROM users WHERE role IN ('manager', 'admin') AND is_active = 1");
        $managers = $stmt->fetchAll();

        foreach ($managers as $manager) {
            $notificationRepo->create(
                $manager['id'],
                $type,
                $title,
                $message,
                $link
            );
        }
    }
}