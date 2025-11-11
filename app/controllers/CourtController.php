<?php
// File: app/controllers/CourtController.php (UPDATED)

class CourtController extends Controller
{
    private $courtRepo;
    private $auth;
    private $middleware;

    public function __construct($conn = null, array $config = [])
    {
        parent::__construct($conn, $config);
        $this->courtRepo = new CourtRepository();
        $this->auth = Auth::getInstance();
        $this->middleware = new Middleware();
    }

    public function index()
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('manage_courts');
        
        $courts = $this->courtRepo->all();
        $csrf = Csrf::generate();
        $this->view('courts/index', ['courts' => $courts, 'csrf' => $csrf]);
    }

    public function store()
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('manage_courts');
        
        Csrf::verifyOrFail();

        $validator = new Validator($_POST);
        $validator->required('name')->between('name', 3, 50);

        if (!$validator->passes()) {
            $_SESSION['error'] = implode(', ', $validator->errors());
            return $this->redirect('courts');
        }

        $data = [
            'name' => Sanitizer::name($_POST['name']),
            'type' => Sanitizer::alphanumeric($_POST['type'])
        ];

        $this->courtRepo->create($data);
        $_SESSION['success'] = "Lapangan baru berhasil ditambahkan!";
        $this->redirect('courts');
    }

    public function delete($id)
    {
        // Require authentication and permission
        $this->middleware->auth();
        $this->middleware->permission('manage_courts');
        
        $this->courtRepo->delete($id);
        $_SESSION['success'] = "Lapangan berhasil dihapus!";
        $this->redirect('courts');
    }
}