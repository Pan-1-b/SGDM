<?php
require_once __DIR__ . '/../models/Torneos.php';

class InicioController
{


    public function index()
    {
        global $pdo;

        $torneoModel = new Torneos($pdo);

        $torneos = $torneoModel->obtenerTorneosRecientes();

        require_once __DIR__ . '/../views/inicio/index.php';

    }
}