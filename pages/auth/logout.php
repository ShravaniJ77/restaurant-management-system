<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';

logoutAdmin();
header('Location: ' . BASE_URL . '/pages/auth/login.php');
exit;
