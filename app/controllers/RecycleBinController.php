<?php
// File: app/controllers/RecycleBinController.php (UPDATED)

class RecycleBinController extends Controller
{
    private $reservationRepo;
    private $auth;
    private $middleware;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->reservationRepo = new ReservationRepository();
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
    }

    public function index()
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('view_all_data');
        
        $trashed = $this->reservationRepo->trashed();
        $csrf = Csrf::generate();
        $this->view('recyclebin/index', ['trashed' => $trashed, 'csrf' => $csrf]);
    }

    public function restore($id)
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('view_all_data');
        
        $this->reservationRepo->restore($id);
        $_SESSION['success'] = "Data berhasil dikembalikan.";
        $this->redirect('recyclebin');
    }

    public function destroy($id)
    {
        // Require authentication and admin only
        $this->middleware->admin();
        
        $this->reservationRepo->forceDelete($id);
        $_SESSION['success'] = "Data berhasil dihapus permanen.";
        $this->redirect('recyclebin');
    }

    public function restoreBulk()
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('view_all_data');
        
        Csrf::verifyOrFail();
        
        $ids = $_POST['selected'] ?? [];

        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih.";
            return $this->redirect('recyclebin');
        }

        $this->reservationRepo->restoreBulk($ids);
        $_SESSION['success'] = count($ids) . " data berhasil dipulihkan.";
        $this->redirect('recyclebin');
    }

    public function deleteBulk()
    {
        // Require authentication and admin only
        $this->middleware->admin();
        
        Csrf::verifyOrFail();
        
        $ids = $_POST['selected'] ?? [];

        if (empty($ids)) {
            $_SESSION['error'] = "Tidak ada data yang dipilih.";
            return $this->redirect('recyclebin');
        }

        $this->reservationRepo->forceDeleteBulk($ids);
        $_SESSION['success'] = count($ids) . " data dihapus permanen.";
        $this->redirect('recyclebin');
    }

    public function autoDelete()
    {
        // Require authentication and admin only
        $this->middleware->admin();
        
        $count = $this->reservationRepo->autoDeleteOld(30);
        $_SESSION['success'] = "$count data lama dihapus permanen.";
        $this->redirect('recyclebin');
    }
}