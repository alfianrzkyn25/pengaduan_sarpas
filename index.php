<?php
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['role'])) {
    redirect($_SESSION['role'] === 'admin' ? 'view/admin/dashboard.php' : 'view/user/dashboard.php');
}

redirect('login.php');
