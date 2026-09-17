<?php

// Todas las vistas HTML se entregan explícitamente como UTF-8. Los endpoints
// JSON definen su propio Content-Type en sus respectivos controladores.
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../routes/web.php';

/*echo json_encode([
    'success' => true,
    'message' => 'Sistema de Gestión Deportiva Modular'
]);*/

