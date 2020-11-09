<?php 

# GUIA DE REPARTO
	# Repartos / Guia de reparto (GET) / Nueva Guia - Inicio
	$app->get('/repartos/guiareparto', 'RepartosController:guiaReparto')->setName('repartos.guiareparto');
	# Repartos / Guia de reparto (GET) / Guia de Reparto/id
	$app->get('/repartos/getguiareparto/{id}', 'RepartosController:getGuiaReparto')->setName('repartos.getguiareparto');

	# Repartos / Guia de reparto (POST)
	$app->post('/repartos/guiareparto', 'RepartosController:postGuiaReparto')->setName('repartos.guiareparto');

	# Repartos / Guia de reparto / Borrar Cliente(DELETE)
	$app->get('/repartos/guiareparto/borrarcliente', 'RepartosController:borrarCliente')->setName('repartos.guiareparto.borrarcliente');

	# Repartos / Guia de reparto / Imprimir Guia de Reparto
	$app->get('/repartos/guiareparto/imprimir/{id}', 'RepartosController:imprimirGuiaReparto')->setName('repartos.guiareparto.imprimirguia');

	# Repartos / Guia de Reparto / Buscar Cliente
	$app->get('/repartos/buscarcliente', 'RepartosBuscarClieController:buscarCliente')->setName('repartos.buscarcliente');

	# Repartos / Guia de Reparto / Buscar Guia de Reparto
	$app->get('/repartos/buscarguiarep', 'RepartosBuscarGuiaController:buscarGuiaDeReparto')->setName('repartos.buscarguiarep');
