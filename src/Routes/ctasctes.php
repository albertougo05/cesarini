<?php 

# CUENTAS CORRIENTES
	# Cuentas Corrientes / Resumen
	$app->get('/ctasctes/resumen', 'ResumenController:resumen')->setName('ctasctes.resumen');
	# Cuentas Corrientes / Arma resumen
	$app->get('/ctasctes/armaresumen', 'ResumenController:armaResumen')->setName('ctasctes.armaresumen');
	# Cuentas Corrientes / Saldo
	$app->get('/ctasctes/saldoactual', 'ResumenController:saldoActual')->setName('ctasctes.saldoactual');

	# Cuentas Corrientes / Resumen detallado
	$app->get('/ctasctes/resumendetallado', 'ResumenDetalladoController:resumenDetallado')->setName('ctasctes.resumendetallado');
	# Cuentas Corrientes / Ver resumen detallado
	$app->get('/ctasctes/verresumendetallado', 'ResumenDetalladoController:verResumenDetallado')->setName('ctasctes.verresumendetallado');

	# Cuentas Corrientes / Facturacion Abonos
	$app->get('/ctasctes/factabonos', 'FacturacAbonosController:factAbonos')->setName('ctasctes.factabonos');
	# Cuentas Corrientes / Facturacion Abonos / Productos cliente del periodo a facturas
	$app->get('/ctasctes/factabonos/productos', 'FacturacAbonosController:productos')->setName('ctasctes.factabonos.productos');
	# Cuentas Corrientes / Facturacion Abonos / Confirma comprobante abono del periodo (GET)
	$app->get('/ctasctes/factabonos/confirmcomprob', 'FacturacAbonosController:confirmComprob')->setName('ctasctes.factabonos.confirmcomprob');
	# Cuentas Corrientes / Facturacion Abonos / Clientes de Guia de Reparto (GET)
	$app->get('/ctasctes/factabonos/clientesguiarep', 'FacturacAbonosController:clientesGuiaRep')->setName('ctasctes.factabonos.clientesguiarep');
	# Cuentas Corrientes / Facturacion Abonos / Fecha periodo (GET)
	$app->get('/ctasctes/factabonos/fechaperiodo', 'FacturacAbonosController:fechaPeriodo')->setName('ctasctes.factabonos.fechaperiodo');
	# Cuentas Corrientes / Facturacion Abonos / Clientes con abono (GET)
	$app->get('/ctasctes/factabonos/clientesabono', 'FacturacAbonosController:clientesAbono')->setName('ctasctes.factabonos.clientesabono');


	# Cuentas Corrientes / Comprobante
	$app->get('/ctasctes/comprobante', 'ComprobanteController:comprobante')->setName('ctasctes.comprobante');
	# Cuentas Corrientes / NumeroComprobante
	$app->get('/ctasctes/nrocomprobante', 'ComprobanteController:numeroComprobante')->setName('ctasctes.nrocomprobante');
	# Cuentas Corrientes / Generar Comprobante
	$app->post('/ctasctes/generacomprobante', 'ComprobanteController:generaComprobante')->setName('ctasctes.generacomprobante');

	# Cuentas Corrientes / Informe saldos a fecha
	$app->get('/ctasctes/infosaldosfecha', 'InfoSaldosFechaController:infoSaldosFecha')->setName('ctasctes.infosaldosfecha');

	# Cuentas Corrientes / Informe saldos a fecha / Imprime
	$app->get('/ctasctes/infosaldosfecha/imprime', 'InfoSaldosFechaController:imprime')->setName('ctasctes.infosaldosfecha.imprime');

	# Cuentas Corrientes / Informe cobranzas
	$app->get('/ctasctes/infocobranzas', 'InfoCobranzasController:infocobranzas')->setName('ctasctes.infocobranzas');
	# Cuentas Corrientes / Arma Informe cobranzas
	$app->get('/ctasctes/infocobranzas/armainfocobranzas', 'InfoCobranzasController:armainfocobranzas')->setName('ctasctes.infocobranzas.armainfocobranzas');

	# Cuentas Corrientes / Informe comprobantes
	$app->get('/ctasctes/infocomprobantes', 'InfoComprobantesController:infoComprobantes')->setName('ctasctes.infocomprobantes');
	# Cuentas Corrientes / Arma Informe cobranzas
	$app->get('/ctasctes/infocomprobantes/armainfocomprobantes', 'InfoComprobantesController:armaInfoComprobantes')->setName('ctasctes.infocomprobantes.armainfocomprobantes');
