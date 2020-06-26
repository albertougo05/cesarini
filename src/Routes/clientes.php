<?php

# CLIENTES
	# Clientes / cliente (GET)
		$app->get('/clientes/cliente', 'ClienteController:getCliente')->setName('clientes.cliente');

	# Clientes / cliente (POST)
		$app->post('/clientes/cliente', 'ClienteController:postCliente');

	# Clientes / Informe
		$app->get('/clientes/informe', 'ClienteController:informe')->setName('clientes.informe');

	# Prueba de dataEmpleado (id debe ser numero) -    Probar con y sin 'name' !!
		$app->get('/clientes/datacliente', 'ClienteController:dataCliente')->setName('clientes.datacliente');

	# Clientes / cliente / eliminar (GET)
		$app->get('/clientes/cliente/eliminar', 'ClienteController:eliminarCliente')->setName('clientes.cliente.eliminar');

	# Clientes / Buscar Cliente
		$app->get('/clientes/buscarcliente', 'ClienteController:buscarCliente')->setName('clientes.buscarcliente');

	# Clientes/cliente/otrodomicilio (POST)
		$app->post('/clientes/cliente/otrodomicilio', 'ClienteController:postOtroDomicilio')->setName('clientes.cliente.otrodomicilio');

	# Clientes/cliente/eliminardomicilio (GET)
		$app->get('/clientes/cliente/eliminardomicilio', 'ClienteController:eliminarDomicilio')->setName('clientes.cliente.eliminardomicilio');

	# Clientes/comprobarcuitdni (GET)
		$app->get('/clientes/comprobarcuitdni', 'ClienteController:comprobarCuitDni')->setName('clientes.comprobarcuitdni');

	# Clientes / Tipos de Facturación
		$app->get('/clientes/tipofact', 'TipoFacturacionController:tipoFacturacion')->setName('clientes.tipofact');

	# Clientes / Tipos de Facturación (POST)
		$app->post('/clientes/tipofact', 'TipoFacturacionController:postTipoFact');

	# Clientes / tipofact / eliminar (GET)
		$app->get('/clientes/tipofact/elimina', 'TipoFacturacionController:eliminaTipoFact')->setName('clientes.tipofact.elimina');

	# Clientes / Tipos de Facturación /data
		$app->get('/clientes/tipofact/data', 'TipoFacturacionController:dataTipoFacturacion')->setName('clientes.tipofact.data');