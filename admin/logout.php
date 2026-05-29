<?php
require_once __DIR__ . '/../include/functions.php';
log_activity('logout', current_user_name() . ' logged out');
session_destroy();
redirect('login.php');

