# SGDM — Sistema de Gestión Deportiva Modular

Proyecto web desarrollado para el proyecto UTULAB del Instituto Tecnológico Superior Arias-Balparda.

## Descripción

SGDM (Sistema de Gestión Deportiva Modular) es una aplicación web orientada a la gestión de torneos deportivos. El sistema contempla usuarios y roles, equipos, torneos, solicitudes e información de participantes.

## Tecnologías

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Apache
- PDO

## Estructura principal

- `core/` — componentes centrales y middleware.
- `models/` — modelos de datos.
- `controllers/` — controladores de la aplicación.
- `views/` — vistas de la interfaz.
- `routes/` — rutas.
- `public/` — punto de entrada público, CSS y JavaScript.
- `config/` — configuración de la aplicación y conexión a base de datos.
- `script_DB/` — script SQL de la base de datos.

## Instalación local

1. Instalar Apache, PHP y MySQL.
2. Crear la base de datos utilizando `script_DB/Query.txt`.
3. Configurar las credenciales de MySQL en `config/conexion.php`.
4. Ajustar `BASE_URL` en `config/config.php` según la ubicación local del proyecto.
5. Configurar Apache para que el directorio `public/` sea el punto de entrada de la aplicación.
6. Abrir la aplicación desde el navegador.

> **Importante:** las credenciales reales de la base de datos no se incluyen en este repositorio. La configuración del archivo `config/conexion.php` debe completarse localmente.

## Proyecto académico

Este repositorio forma parte de la documentación y desarrollo del proyecto UTULAB.
