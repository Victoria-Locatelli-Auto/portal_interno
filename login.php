<?php
/**
 * Controller da tela de login - Portal MCM.
 */

require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/includes/helpers/login.php';

Session::start();

$login_page = loginPreparePage(__DIR__);

extract($login_page, EXTR_SKIP);

require __DIR__ . '/includes/views/login-view.php';
