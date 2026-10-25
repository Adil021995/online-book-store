<?php
require_once __DIR__ . '/config/config.php';
unset($_SESSION['user_id'],$_SESSION['user_name']);
session_regenerate_id(true);
set_flash('You have been logged out.');
header('Location: index.php'); exit;
