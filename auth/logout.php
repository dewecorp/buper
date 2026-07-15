<?php
require_once __DIR__ . '/../config/koneksi.php';
session_unset();
session_destroy();
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
redirect('../auth/login.php');
