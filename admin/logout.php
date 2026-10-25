<?php
require_once dirname(__DIR__).'/config/config.php';
unset($_SESSION['admin_id'],$_SESSION['admin_name']);session_regenerate_id(true);header('Location: login.php');exit;
