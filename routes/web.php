<?php

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = BASE_URL;

if (strpos($request, $base) === 0) {
    $request = substr($request, strlen($base));
}

$request = '/' . trim($request, '/');

$method = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| INICIO
|--------------------------------------------------------------------------
*/

if ($request === '/') {

    require_once __DIR__ . '/../controllers/InicioController.php';

    $controller = new InicioController();
    $controller->index();

    exit;
}

if ($request === '/administracion') {
    require_once __DIR__ . '/../controllers/AdministracionController.php';
    (new AdministracionController())->index();
    exit;
}

if (preg_match('#^/administracion/usuarios/(\d+)/estado$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/AdministracionController.php';
    if ($method === 'POST') {
        (new AdministracionController())->actualizarEstado((int)$matches[1]);
    }
    exit;
}


/*
|--------------------------------------------------------------------------
| TORNEOS PERSONALIZADOS
|--------------------------------------------------------------------------
*/

if ($request === '/torneos/crear') {

    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'GET') {
        $controller->crear();
    } elseif ($method === 'POST') {
        $controller->guardar();
    }

    exit;
}

if (preg_match('#^/torneos/editar/(\d+)$#', $request, $matches)) {

    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'GET') {
        $controller->editar((int)$matches[1]);
    } elseif ($method === 'POST') {
        $controller->actualizar((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/torneos/estado/(\d+)$#', $request, $matches)) {

    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'POST') {
        $controller->actualizarEstado((int)$matches[1]);
    } else {
        header('Location: ' . BASE_URL . '/mis-torneos');
        exit;
    }

    exit;
}

if (preg_match('#^/torneos/eliminar/(\d+)$#', $request, $matches)) {

    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'POST') {
        $controller->eliminar((int)$matches[1]);
    } else {
        header('Location: ' . BASE_URL . '/torneos');
        exit;
    }

    exit;
}

if ($request === '/mis-torneos' || $request === '/solicitudes' || $request === '/mis-solicitudes') {

    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($request === '/mis-torneos' && $method === 'GET') {
        $controller->misTorneos();
    } elseif ($request === '/solicitudes' && $method === 'GET') {
        $controller->solicitudes();
    } elseif ($request === '/mis-solicitudes' && $method === 'GET') {
        $controller->misSolicitudes();
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido.',
            'data' => null,
            'redirect' => null
        ]);
    }

    exit;
}

if (preg_match('#^/solicitudes/(\d+)/estado$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'POST') {
        $controller->actualizarEstadoSolicitud((int)$matches[1]);
    }

    exit;
}

if ($request === '/equipos') {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'GET') {
        $controller->index();
    }

    exit;
}

if ($request === '/equipos/crear') {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'GET') {
        $controller->crear();
    } elseif ($method === 'POST') {
        $controller->guardar();
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/editar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'GET') {
        $controller->editar((int)$matches[1]);
    } elseif ($method === 'POST') {
        $controller->actualizar((int)$matches[1]);
    }

    exit;
}

if ($request === '/usuarios/participantes') {
    require_once __DIR__ . '/../controllers/UsuariosController.php';

    $controller = new UsuariosController();

    if ($method === 'GET') {
        $controller->participantes();
    } elseif ($method === 'POST') {
        $controller->invitarParticipante();
    }

    exit;
}

if (preg_match('#^/torneos/detalle/(\d+)$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'GET') {
        $controller->detalle((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/torneos/(\d+)/partidos-publicos$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'GET') {
        $controller->partidosPublicos((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/torneos/inscribirse/(\d+)$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';

    $controller = new TorneosController();

    if ($method === 'POST') {
        $controller->inscribirse((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'GET') {
        $controller->gestion((int)$matches[1]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/rondas$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->guardarRonda((int)$matches[1]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/rondas/(\d+)$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->actualizarRonda((int)$matches[1], (int)$matches[2]);
    } elseif ($method === 'DELETE') {
        $controller->eliminarRonda((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/rondas/(\d+)/eliminar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->eliminarRonda((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/partidos$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->guardarPartido((int)$matches[1]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/partidos/(\d+)$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->actualizarPartido((int)$matches[1], (int)$matches[2]);
    } elseif ($method === 'DELETE') {
        $controller->eliminarPartido((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/partidos/(\d+)/eliminar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->eliminarPartido((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/partidos/(\d+)/resultado$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->guardarResultado((int)$matches[1], (int)$matches[2]);
    } elseif ($method === 'DELETE') {
        $controller->eliminarResultado((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/torneos/(\d+)/gestion/partidos/(\d+)/resultado/eliminar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/TorneosController.php';
    $controller = new TorneosController();
    if ($method === 'POST') {
        $controller->eliminarResultado((int)$matches[1], (int)$matches[2]);
    }
    exit;
}

if (preg_match('#^/equipos/(\d+)$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'GET') {
        $controller->detalle((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/solicitar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->solicitarUnion((int)$matches[1]);
    }

    if (preg_match('#^/equipos/(\d+)/(cancelar-solicitud|salir)$#', $request, $matches)) {
        require_once __DIR__ . '/../controllers/EquiposController.php';
        $controller = new EquiposController();
        if ($method === 'POST') {
            $matches[2] === 'cancelar-solicitud'
                ? $controller->cancelarSolicitud((int)$matches[1])
                : $controller->salirEquipo((int)$matches[1]);
        }
        exit;
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/invitar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->invitarUsuario((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/capitan$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->asignarCapitan((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/invitacion/responder$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->responderInvitacion((int)$matches[1]);
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/invitaciones/(\d+)/cancelar$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->cancelarInvitacion((int)$matches[1], (int)$matches[2]);
    }

    exit;
}

if (preg_match('#^/equipos/(\d+)/solicitudes/(\d+)/responder$#', $request, $matches)) {
    require_once __DIR__ . '/../controllers/EquiposController.php';

    $controller = new EquiposController();

    if ($method === 'POST') {
        $controller->responderSolicitud((int)$matches[1], (int)$matches[2]);
    }

    if (preg_match('#^/equipos/(\d+)/miembros/(\d+)/eliminar$#', $request, $matches)) {
        require_once __DIR__ . '/../controllers/EquiposController.php';
        $controller = new EquiposController();
        if ($method === 'POST') {
            $controller->eliminarMiembro((int)$matches[1], (int)$matches[2]);
        }
        exit;
    }

    exit;
}

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

switch ($request) {

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    case '/login':

        require_once __DIR__ . '/../controllers/AuthController.php';

        $controller = new AuthController();

        if ($method === 'GET') {

            $controller->login();

        } elseif ($method === 'POST') {

            $controller->autenticar();

        }

        break;


    /*
    |--------------------------------------------------------------------------
    | REGISTRO
    |--------------------------------------------------------------------------
    */

    case '/registro':

        require_once __DIR__ . '/../controllers/UsuariosController.php';

        $controller = new UsuariosController();

        if ($method === 'GET') {

            $controller->registro();

        } elseif ($method === 'POST') {

            $controller->registrar();

        }

        break;

    case '/perfil':

        require_once __DIR__ . '/../controllers/UsuariosController.php';

        $controller = new UsuariosController();

        if ($method === 'GET') {
            $controller->perfil();
        } elseif ($method === 'POST') {
            $controller->actualizarPerfil();
        }

        break;


    /*
    |--------------------------------------------------------------------------
    | CERRAR SESIÓN
    |--------------------------------------------------------------------------
    */

    case '/logout':

        require_once __DIR__ . '/../controllers/AuthController.php';

        $controller = new AuthController();

        $controller->logout();

        break;


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    case '/dashboard':

        require_once __DIR__ . '/../controllers/DashboardController.php';

        $controller = new DashboardController();

        $controller->index();

        break;


    /*
    |--------------------------------------------------------------------------
    | TORNEOS
    |--------------------------------------------------------------------------
    */

    case '/torneos':

        require_once __DIR__ . '/../controllers/TorneosController.php';

        $controller = new TorneosController();

        $controller->index();

        break;


    /*
    |--------------------------------------------------------------------------
    | 404
    |--------------------------------------------------------------------------
    */

    default:

        http_response_code(404);

        echo "Página no encontrada";

        break;
}