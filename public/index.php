<?php
// File: public/index.php (UPDATED)

session_start();

// Load Core Classes
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Csrf.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/core/Auth.php';        // NEW
require_once __DIR__ . '/../app/core/Middleware.php';   // NEW

// Load Helpers
foreach (glob(__DIR__ . '/../app/helpers/*.php') as $file) {
    require_once $file;
}

// Load Models
foreach (glob(__DIR__ . '/../app/models/*.php') as $file) {
    require_once $file;
}

// Load Repositories
foreach (glob(__DIR__ . '/../app/repositories/*.php') as $file) {
    require_once $file;
}

// Load Controllers
foreach (glob(__DIR__ . '/../app/controllers/*.php') as $file) {
    require_once $file;
}

// Start Application
$app = new App();