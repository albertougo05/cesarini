<?php
// DIC configuration

$container = $app->getContainer();

// Setup Illuminate Database con Eloquent
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Lo pasamos al contenedor...
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

// View renderer (phpRenderer)
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['../templates']);
};

// Monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Auth
$container['auth'] = function ($container) {
    
    return new \App\Auth\Auth;
};

// Flash messages service
$container['flash'] = function ($container) {

    return new \Slim\Flash\Messages;
};

// Registra Twig views en el container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('../templates', [
        'cache' => false,
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new Slim\Views\TwigExtension($router, $uri));

    // Set valores para la autorizacion del usuario logeado
    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user'  => $container->auth->user(),
    ]);

    // Agrego soporte para mensajes Flash
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

// Validator from https://github.com/Respect/Validation
$container['validator'] = function ($container) {
    
    return new App\Validation\Validator;
};

// Csrf
$container['csrf'] = function ($container) {
    
    return new \Slim\Csrf\Guard;
};

// PDO
$container['pdo'] = function ($container) {
    
    return new \App\Pdo\PdoClass($container);
};



// Agregados al Middleware:

// Csrf
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add($container->csrf);



// REGISTRO DE CONTROLADORES:

// Home
$container['HomeController'] = function ($container) {
    
    return new \App\Controllers\HomeController($container);
};

// Utils
$container['utils'] = function ($container) {
    
    return new \App\Utils\Utils($container);
};


// Repartos
    $container['RepartosController'] = function ($container) {

        return new \App\Controllers\Repartos\RepartosController($container);
    };

    $container['RepartosBuscarGuiaController'] = function ($container) {

        return new \App\Controllers\Repartos\RepartosBuscarGuiaController($container);
    };

    $container['RepartosBuscarClieController'] = function ($container) {

        return new \App\Controllers\Repartos\RepartosBuscarClieController($container);
    };

    $container['RepartosProductosaClieController'] = function ($container) {

        return new \App\Controllers\Repartos\RepartosProductosaClieController($container);
    };

// Movimientos dispenser
    $container['MovimientoDispenserController'] = function ($container) {

        return new \App\Controllers\Repartos\MovimientoDispenserController($container);
    };

// Informe movimientos dispenser
    $container['InformeMovDispenserController'] = function ($container) {

        return new \App\Controllers\Repartos\InformeMovDispenserController($container);
    };

// Visitas
    $container['VisitasController'] = function ($container) {

        return new \App\Controllers\Repartos\VisitasController($container);
    };
// Visita a planta
    $container['VisitaPlantaController'] = function ($container) {

        return new \App\Controllers\Repartos\VisitaPlantaController($container);
    };
// Visitas listado
    $container['VisitasListadoController'] = function ($container) {

        return new \App\Controllers\Repartos\VisitasListadoController($container);
    };

// Visitas según Guía Reparto
    $container['VisitasSegunGuiaController'] = function ($container) {

        return new \App\Controllers\Repartos\VisitasSegunGuiaController($container);
    };

// Visitas informe resumido 
    $container['VisitasInfoResumController'] = function ($container) {

        return new \App\Controllers\Repartos\VisitasInfoResumController($container);
    };

// Productos cliente
    $container['ProductosClienteController'] = function ($container) {

        return new \App\Controllers\Repartos\ProductosClienteController($container);
    };

// Tablas
    $container['EmpleadosController'] = function ($container) {

        return new \App\Controllers\Tablas\EmpleadosController($container);
    };
// Usuarios
    $container['UsuariosController'] = function ($container) {

        return new \App\Controllers\Tablas\UsuariosController($container);
    };
// Clientes
    $container['ClienteController'] = function ($container) {

        return new \App\Controllers\Clientes\ClienteController($container);
    };

// Tipos de facturación a clientes
    $container['TipoFacturacionController'] = function ($container) {

        return new \App\Controllers\Clientes\TipoFacturacionController($container);
    };

// Producto
    $container['ProductoController'] = function ($container) {

        return new \App\Controllers\Productos\ProductoController($container);
    };
// Producto / Listados
    $container['ListadosController'] = function ($container) {

        return new \App\Controllers\Productos\ListadosController($container);
    };
// Producto / Tipo Producto
    $container['TipoProductoController'] = function ($container) {

        return new \App\Controllers\Productos\TipoProductoController($container);
    };
// Productos / Dispenser
    $container['DispenserController'] = function ($container) {

        return new \App\Controllers\Productos\DispenserController($container);
    };
// Producto / Tipo Dispenser
    $container['TipoDispenserController'] = function ($container) {

        return new \App\Controllers\Productos\TipoDispenserController($container);
    };
// Producto / Informe Dispenser
    $container['InfoDispenserController'] = function ($container) {

        return new \App\Controllers\Productos\InfoDispenserController($container);
    };

// Cuentas Corientes / Resumen
    $container['ResumenController'] = function ($container) {

        return new \App\Controllers\Ctasctes\ResumenController($container);
    };

// Cuentas Corientes / Resumen Detallado
    $container['ResumenDetalladoController'] = function ($container) {

        return new \App\Controllers\Ctasctes\ResumenDetalladoController($container);
    };

// Cuentas Corientes / Informe Cobranzas
    $container['InfoCobranzasController'] = function ($container) {

        return new \App\Controllers\Ctasctes\InfoCobranzasController($container);
    };

// Cuentas Corientes / Comprobantes
    $container['ComprobanteController'] = function ($container) {

        return new \App\Controllers\Ctasctes\ComprobanteController($container);
    };

// Cuentas Corientes / Facturacion Abonos
    $container['FacturacAbonosController'] = function ($container) {

        return new \App\Controllers\Ctasctes\FacturacAbonosController($container);
    };

// Cuentas Corientes / Informe saldos a fecha
    $container['InfoSaldosFechaController'] = function ($container) {

        return new \App\Controllers\Ctasctes\InfoSaldosFechaController($container);
    };

// Cuentas Corientes / Informe comprobantes
    $container['InfoComprobantesController'] = function ($container) {

        return new \App\Controllers\Ctasctes\InfoComprobantesController($container);
    };


