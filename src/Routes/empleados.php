<?php

use App\Middleware\OldInputMiddleware;

	# EMPLEADOS
		$app->group('', function () use ($app) {
			# Tablas / Empleado (GET)
			$app->get('/tablas/empleado', 'EmpleadosController:getEmpleado')->setName('tablas.empleado');

			# Tablas / Empleado (POST)
			$app->post('/tablas/empleado', 'EmpleadosController:postEmpleado');

		})->add(new OldInputMiddleware($container));

		# Prueba de dataEmpleado (id debe ser numero) -    Probar con y sin 'name' !!
		$app->get('/tablas/dataempleado', 'EmpleadosController:dataEmpleado')->setName('tablas.dataempleado');

		# Tablas / Empleado / eliminar (GET)
		$app->get('/tablas/empleado/eliminar', 'EmpleadosController:eliminarEmpleado')->setName('tablas.empleado.eliminar');
