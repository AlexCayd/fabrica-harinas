<?php
// Este archivo: includes/config.php
$host = $_SERVER['HTTP_HOST'];

if ($host === 'localhost') {
    define('BASE_URL', '/fabricas-harinas/'); // tu carpeta en local
} else {
    define('BASE_URL', '/'); // ya estás en pruebas.cloudcode.com.mx/
}
?>