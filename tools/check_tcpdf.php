<?php
require_once __DIR__ . '/../vendor/autoload.php';
echo (class_exists('TCPDF') ? "TCPDF OK\n" : "TCPDF MISSING\n");
?>