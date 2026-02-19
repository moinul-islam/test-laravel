<?php
require_once 'config.php';
session_destroy();
header('Location: auth.php');
exit;
