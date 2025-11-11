<?php

class App
{
    protected array $config;
    protected $conn;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
        
        require_once __DIR__ . '/Database.php';
        $db = new Database($this->config['db']);
        $this->conn = $db->getConnection();

        $controllerName = $_GET['c'] ?? 'reservation';
        
        $aliases = [
            'reservations' => 'reservation',
            'courts' => 'court',
            'recyclebin' => 'recyclebin'
        ];
        
        if (isset($aliases[$controllerName])) {
            $controllerName = $aliases[$controllerName];
        }
        
        $controller = ucfirst($controllerName) . 'Controller';
        $action = $_GET['a'] ?? 'index';
        $id = $_GET['id'] ?? null;

        if (!class_exists($controller)) {
            http_response_code(404);
            echo "Controller '{$controller}' not found.";
            echo "<br>Requested: " . htmlspecialchars($_GET['c'] ?? 'none');
            echo "<br>Available controllers: ReservationController, CourtController, RecycleBinController";
            exit;
        }

        $instance = new $controller($this->conn, $this->config);

        if (!method_exists($instance, $action)) {
            http_response_code(404);
            echo "Action '{$action}' not found in controller {$controller}.";
            exit;
        }

        if ($id !== null) {
            $instance->{$action}($id);
        } else {
            $instance->{$action}();
        }
    }
}
