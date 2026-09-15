<?php
/**
 * Logout
 */

require_once __DIR__ . '/includes/Session.php';

Session::start();
Session::destroy();

header('Location: login.php');
exit;
?>
