<?php
require_once 'includes/functions.php';

// Redirigir al dashboard apropiado según el rol
if (isLoggedIn()) {
    redirectToRolePage();
} else {
    header('Location: login.php');
    exit();
}
?>