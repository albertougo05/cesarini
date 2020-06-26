<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\Middleware\AuthMiddleware;
// ver: https://www.youtube.com/watch?v=ZLmCdbNYHAo&list=PLfdtiltiRHWGc_yY90XRdq6mRww042aEC&index=28

// RUTAS:

# Index
$app->get('/', 'HomeController:login')->setName('login');

# Login. 
//$app->get('/login', 'HomeController:login')->setName('login');

# Home. Pagina con menus y usuario
$app->get('/home', 'HomeController:index')->setName('home');

# Post Login para validar ingreso
$app->post('/postlogin', 'HomeController:postLogin')->setName('postlogin');

# Grupo de todas las páginas, valida ingreso el middleware AuthMiddleware
$app->group('', function () use ($app, $container) {

	# REPARTOS
		# VISITAS
			require __DIR__ . "/../src/Routes/visitas.php";

		# GUIA DE REPARTO
			require __DIR__ . "/../src/Routes/guia_reparto.php";

		# DETALLE VISITAS SEGUN GUIA REPARTO
			# Repartos / Detalle visitas según GR
			$app->get('/repartos/visitassegunguia', 'VisitasSegunGuiaController:visitasSegunGuia')->setName('repartos.visitassegunguia');
			# Repartos / Detalle visitas según GR / Imprime
			$app->get('/repartos/visitassegunguia/imprime', 'VisitasSegunGuiaController:imprimeVSGR')->setName('repartos.visitassegunguia.imprime');

		# PRODUCTOS CLIENTE
			# Repartos / Productos Cliente
			$app->get('/repartos/productoscliente', 'ProductosClienteController:prodsCliente')->setName('repartos.productoscliente');
			# Repartos / ProductosCliente / Listado
			$app->get('/repartos/productoscliente/listado', 'ProductosClienteController:listado')->setName('repartos.productoscliente.listado');

	# CUENTAS CORRIENTES
		require __DIR__ . "/../src/Routes/ctasctes.php";

	# CLIENTES
		require __DIR__ . "/../src/Routes/clientes.php";

	# PRODUCTOS
		require __DIR__ . "/../src/Routes/productos.php";

	# DISPENSER
		require __DIR__ . "/../src/Routes/dispenser.php";

	# MOVIMIENTOS E INFORME DISPENSER
		require __DIR__ . "/../src/Routes/movim_dispenser.php";

	# EMPLEADOS
		require __DIR__ . "/../src/Routes/empleados.php";

	# USUARIOS
		require __DIR__ . "/../src/Routes/usuarios.php";


})->add(new AuthMiddleware($container));



