<?php
// Validar permisos de TI
    if (session_status() == PHP_SESSION_NONE){ //Solo inicia sesión si no está activa
        session_start();
    }

    if (!isset($_SESSION['user_id'])){
        header('Location: /fabrica-harinas/index.php');
    } 

    if (isset($_SESSION['rol'])){
        $location = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
        $rol = $_SESSION['rol'];
        $path = '/fabrica-harinas/modulos/';
        
        $permisos = [
            'TI' => [ 
                $path.'analisiscalidad.php', $path.'analisiscalidadform.php', $path.'certificadosform.php',
                $path.'clientes_editar.php', $path.'clientes.php', $path.'clientesform.php', $path.'estadisticos.php',
                $path.'historico.php', $path.'laboratorios.php', $path.'laboratoriosform.php', $path.'reportes.php', $path.'usuarios.php', $path.'usuariosform.php',
                $path.'resultadosestadisticos.php'                
                ],
            'Laboratorio' => [ 
                $path.'analisiscalidad.php', $path.'analisiscalidadform.php', $path.'certificadosform.php',
                $path.'clientes_editar.php', $path.'clientes.php', $path.'clientesform.php', $path.'estadisticos.php',
                $path.'historico.php', $path.'laboratorios.php', $path.'laboratoriosform.php'
                ],
            'Gerencia de Control de Calidad' => [ 
                $path.'analisiscalidad.php', $path.'analisiscalidadform.php', $path.'certificadosform.php',
                $path.'clientes_editar.php', $path.'clientes.php', $path.'clientesform.php', $path.'estadisticos.php',
                $path.'historico.php', $path.'laboratorios.php', $path.'laboratoriosform.php'
            ],
            'Gerencia de Aseguramiento de Calidad' => [ 
                $path.'analisiscalidad.php', $path.'analisiscalidadform.php', $path.'certificadosform.php',
                $path.'clientes_editar.php', $path.'clientes.php', $path.'clientesform.php', $path.'estadisticos.php',
                $path.'historico.php', $path.'laboratorios.php', $path.'laboratoriosform.php'
            ],
            'Gerente de Planta' => [
                $path.'estadisticos.php'
            ],
            'Director de Operaciones'  => [
                $path.'estadisticos.php'
            ]
        ];

        // Recorrer roles y validar permisos
        if (isset($permisos[$rol])) {
            if (!in_array($location, $permisos[$rol])) {
                $_SESSION['error'] = 'No tienes permisos para acceder esta sección.';
                header('Location: /fabrica-harinas/menu.php');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Rol no reconocido.';
            header('Location: /fabrica-harinas/menu.php');
            exit;
        }
        
    }else{
        header ('Location: /fabrica-harinas/index.php');
    }
