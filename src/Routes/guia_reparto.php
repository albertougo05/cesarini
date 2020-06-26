<?php 

# GUIA DE REPARTO
	# Repartos / Guia de reparto (GET) / Nueva Guia - Inicio
	$app->get('/repartos/guiareparto', 'RepartosController:getGuiaReparto')->setName('repartos.guiareparto');

	# Repartos / Guia de reparto (POST)
	$app->post('/repartos/guiareparto', 'RepartosController:postGuiaReparto');

	# Repartos / Guia de reparto (GET) / Eliminar/Borrar actual guia de reparto
	$app->get('/repartos/guiareparto/eliminarguia', 'RepartosController:eliminarGuiaReparto')->setName('repartos.guiareparto.eliminarguia');

	# Repartos / Guia de reparto / Borrar Cliente(DELETE)
	$app->get('/repartos/guiareparto/borrarcliente', 'RepartosController:borrarCliente')->setName('repartos.guiareparto.borrarcliente');

	# Repartos / Guia de reparto / Imprimir Guia de Reparto
	$app->get('/repartos/guiareparto/imprimir', 'RepartosController:imprimirGuiaReparto')->setName('repartos.guiareparto.imprimirguia');

	# Repartos / Guia de Reparto / Reordenar Vistias
	$app->get('/repartos/guiareparto/reordenarvisitas', 'RepartosController:reordenarVisitas')->setName('repartos.guiareparto.reordenarvisitas');


	# Repartos / Guia de Reparto / Buscar Guia de Reparto
	$app->get('/repartos/buscarguiarep', 'RepartosBuscarGuiaController:buscarGuiaDeReparto')->setName('repartos.buscarguiarep');

	# Repartos / Guia de Reparto / Buscar Cliente
	$app->get('/repartos/buscarcliente', 'RepartosBuscarClieController:buscarCliente')->setName('repartos.buscarcliente');

	# Repartos / Guia de Reparto / Agregar productos a Cliente
	$app->get('/repartos/productosacliente', 'RepartosProductosaClieController:productosACliente')->setName('repartos.productosacliente');

	# Repartos / Guia de reparto / Agregar productos a Cliente(POST)
	$app->post('/repartos/productosacliente', 'RepartosProductosaClieController:postProductosACliente');

	# Repartos / Guia de Reparto / Modifica cantidad de producto
	$app->get('/repartos/modifcantproducto', 'RepartosProductosaClieController:modifcantproducto')->setName('repartos.modifcantproducto');
