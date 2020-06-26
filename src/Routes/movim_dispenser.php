<?php 

# MOVIMIENTOS DISPENSER
	# Repartos / Movimientos Dispenser
	$app->get('/repartos/movimientodispenser', 'MovimientoDispenserController:movimientoDispenser')->setName('repartos.movimientodispenser');

	$app->post('/repartos/movimientodispenser', 'MovimientoDispenserController:postMovimDispenser');

	$app->get('/repartos/movimientodispenser/buscar', 'MovimientoDispenserController:buscar')->setName('repartos.movimientodispenser.buscar');

	$app->get('/repartos/movimientodispenser/buscarcliente', 'MovimientoDispenserController:buscarCliente')->setName('repartos.movimientodispenser.buscarcliente');

	$app->get('/repartos/movimientodispenser/elimina', 'MovimientoDispenserController:elimina')->setName('repartos.movimientodispenser.elimina');

# INFORME MOV. DISPENSER
    # Repartos / Informe Mov. dispenser
	$app->get('/repartos/informemovdispenser', 'InformeMovDispenserController:informeMovDispenser')->setName('repartos.informemovdispenser');

	$app->get('/repartos/ordenainforme', 'InformeMovDispenserController:ordenaInforme')->setName('repartos.ordenainforme');

	$app->get('/repartos/imprimeinforme', 'InformeMovDispenserController:imprimeInforme')->setName('repartos.imprimeinforme');

	$app->get('/repartos/datamovimdisp', 'InformeMovDispenserController:dataMovimDisp')->setName('repartos.datamovimdisp');