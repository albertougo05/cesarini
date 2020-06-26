<?php

use App\Middleware\OldInputMiddleware;

	# USUARIOS
		$app->group('', function () use ($app) {
			# GET - Registrar usuario
			$app->get('/tablas/usuarios', 'UsuariosController:getUsuario')->setName('tablas.usuarios');
			# POST
			$app->post('/tablas/usuarios', 'UsuariosController:postUsuario');
		})->add(new OldInputMiddleware($container));

		# Usuario por su id (id debe ser numero)
		$app->get('/tablas/datausuario', 'UsuariosController:dataUsuario')->setName('tablas.datausuario');

		# Eliminar usuario
		$app->get('/tablas/usuarios/eliminar', 'UsuariosController:eliminarUsuario')->setName('tablas.usuarios.eliminar');

		# Verifica nombre de usuario no duplicado
		$app->get('/tablas/usuariodisponible', 'UsuariosController:usuarioDisponible')->setName('tablas.usuariodisponible');
