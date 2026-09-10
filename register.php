<?php
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['role'])) {
    redirect($_SESSION['role'] === 'admin' ? 'controller/c_dashboard.php' : 'controller/c_user_dashboard.php');
}

require __DIR__ . '/controller/c_register.php';
