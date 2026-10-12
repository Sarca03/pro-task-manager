<?php
require __DIR__ . '/includes/bootstrap.php';
require_post_with_csrf();
session_unset();
session_destroy();
header('Location: login.php');
exit;
