<?php
require_once 'includes/functions.php';

if (is_authenticated()) {
    logout();
    header('Location: '.url('index.php').'&logout=true');
} else {
    header('Location: '.url('index.php'));
}