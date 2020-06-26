<?php

# DISPENSER
	# productos / dispenser (GET)
	$app->get('/productos/dispenser', 'DispenserController:dispenser')->setName('productos.dispenser');	

	# productos / dispenser (POST)
	$app->post('/productos/dispenser', 'DispenserController:postDispenser');	

	# productos / dispenser / elimina (GET)
	$app->get('/productos/dispenser/elimina', 'DispenserController:eliminaDispenser')->setName('productos.dispenser.elimina');	

	# productos / dispenser / validaserie (GET)
	$app->get('/productos/dispenser/validaserie', 'DispenserController:validaSerie')->setName('productos.dispenser.validaserie');	

	# productos / dispenser / validainterno (GET)
	$app->get('/productos/dispenser/validainterno', 'DispenserController:validaInterno')->setName('productos.dispenser.validainterno');	

	# productos / dispenser / buscar (GET)
	$app->get('/productos/dispenser/buscar', 'DispenserController:buscar')->setName('productos.dispenser.buscar');	

	# productos / dispenser / buscar (GET)
	$app->get('/productos/dispenser/ordenabuscar', 'DispenserController:ordenaBuscar')->setName('productos.dispenser.ordenabuscar');	

# TIPO DISPENSER
	# Productos / tipodispenser (GET)
	$app->get('/productos/tipodispenser', 'TipoDispenserController:getTipodispenser')->setName('productos.tipodispenser');

	# productos / tipodispenser (POST)
	$app->post('/productos/tipodispenser', 'TipoDispenserController:postTipoDispenser');

	# Devuelve json de TP (id debe ser numero)
	$app->get('/productos/datatipodispenser', 'TipoDispenserController:dataTipoDispenser')->setName('productos.datatipodispenser');

	# productos / tipodispenser / eliminar (GET)
	$app->get('/productos/tipodispenser/elimina', 'TipoDispenserController:eliminaTipoDispenser')->setName('productos.tipodispenser.elimina');

# INFORME DISPENSER
	# Productos / infodispenser (GET)
	$app->get('/productos/informedispenser', 'InfoDispenserController:informeDispenser')->setName('productos.informedispenser');

	# Productos / infodispimprime (GET)
	$app->get('/productos/infodispimprime', 'InfoDispenserController:infoDispImprime')->setName('productos.infodispimprime');

# LISTA DISPENSER EN STOCK
	# productos / dispenser / enstock (GET)
	$app->get('/productos/dispenser/enstock', 'DispenserController:enStock')->setName('productos.dispenser.enstock');

# LISTA DISPENSERS DE CLIENTE
	# productos / dispenser / decliente (GET)
	$app->get('/productos/dispenser/decliente', 'DispenserController:deCliente')->setName('productos.dispenser.decliente');
