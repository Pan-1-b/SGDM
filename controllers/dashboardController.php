<?php

require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../models/Equipos.php';
require_once __DIR__ . '/../models/Torneos.php';

class DashboardController
{
    public function index()
    {
        AuthMiddleware::verificar();

        $usuario = [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'rol' => $_SESSION['usuario_rol'] ?? ''
        ];

        global $pdo;
        $equiposModel = new Equipos($pdo);
        $torneosModel = new Torneos($pdo);
        if ($usuario['rol'] === 'organizador') {
            $estadisticas = [
                'torneos' => $torneosModel->contarTorneosPorOrganizador((int)$usuario['id']),
                'equipos' => 0,
                'inscripciones_pendientes' => $torneosModel->contarSolicitudesPendientes((int)$usuario['id'])
            ];
        } else {
            $resumenTorneos = $torneosModel->obtenerResumenUsuario((int)$usuario['id']);
            $estadisticas = [
                'torneos' => $resumenTorneos['torneos'],
                'equipos' => $equiposModel->contarEquiposDeUsuario((int)$usuario['id']),
                'inscripciones_pendientes' => $resumenTorneos['inscripciones_pendientes']
            ];
        }

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}