<?php
$host = 'localhost';
$db   = 'harinas';
$user = 'tester';
$pass = '1234567890';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Para lanzar excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Resultados como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa sentencias preparadas reales
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Error en la conexión: ' . $e->getMessage());
}
