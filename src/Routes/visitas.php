<?php 

# VISITAS
	# Repartos / Visitas
	$app->get('/repartos/visitas', 'VisitasController:visitas')->setName('repartos.visitas');
	# Repartos / Visitas / ConIdVisita
	$app->get('/repartos/visitas/conidvisita', 'VisitasController:conIdVisita')->setName('repartos.visitas.conidvisita');
	# Repartos / Visitas / Conguia
	$app->get('/repartos/visitas/conguia', 'VisitasController:conGuia')->setName('repartos.visitas.conguia');
	# Repartos / Visitas / Eliminar
	$app->get('/repartos/visitas/eliminar', 'VisitasController:eliminar')->setName('repartos.visitas.eliminar');
	# Repartos / Visitas / imprimir
	$app->get('/repartos/visitas/imprimir', 'VisitasController:imprimir')->setName('repartos.visitas.imprimir');
	# Repartos / Visitas / Cliente
	$app->get('/repartos/visitas/cliente', 'VisitasController:cliente')->setName('repartos.visitas.cliente');
	# Repartos / Visitas / Guardar
	$app->post('/repartos/visitas/guardar', 'VisitasController:guardar')->setName('repartos.visitas.guardar');
	# Repartos / Visitas / reordenar
	$app->post('/repartos/visitas/reordenar', 'VisitasController:reordenar')->setName('repartos.visitas.reordenar');
	# Repartos / Visitas / Listado
	$app->get('/repartos/visitas/listado', 'VisitasController:listadoBuscar')->setName('repartos.visitas.listado');

# VISITA A PLANTA
	# Repartos / Visita a Planta
	$app->get('/repartos/visitaplanta', 'VisitaPlantaController:visitaPlanta')->setName('repartos.visitaplanta');
	# Repartos / Visita a Planta / con data
	$app->get('/repartos/visitaplanta/condata', 'VisitaPlantaController:visitaConData')->setName('repartos.visitaplanta.condata');
	# Repartos / Visitas a planta / Guardar id Empleado
	$app->get('/repartos/visitaplanta/guardaidempleado', 'VisitaPlantaController:guardaIdEmpleado')->setName('repartos.visitaplanta.guardaidempleado');
	# Repartos / Visitas a planta / Guardar Visita
	$app->get('/repartos/visitaplanta/guardavisita', 'VisitaPlantaController:guardaVisita')->setName('repartos.visitaplanta.guardavisita');
	# Repartos / Visitas a planta / Guardar Visita
	$app->get('/repartos/visitaplanta/imprimir', 'VisitaPlantaController:imprimir')->setName('repartos.visitaplanta.imprimir');

# VISITAS LISTADO
	# Repartos / Listado de visitas
	$app->get('/repartos/visitaslistado', 'VisitasListadoController:listado')->setName('repartos.visitaslistado');
	# Repartos / Listado visitas / Armar Listado
	$app->get('/repartos/visitaslistado/armarlista', 'VisitasListadoController:armarLista')->setName('repartos.visitaslistado.armarlista');
	# Repartos / Listado visitas / Cliente por codigo
	$app->get('/repartos/visitaslistado/clieporcod', 'VisitasListadoController:clientePorCodigo')->setName('repartos.visitaslistado.clieporcod');

# INFORME VISITAS RESUMIDO
	# Repartos / Informe visitas resumido
	$app->get('/repartos/visitasinforesum', 'VisitasInfoResumController:informeResum')->setName('repartos.visitasinforesum');
	# Repartos / Informe Visitas resumido
	$app->get('/repartos/visitasinforesum/armainfovisitas', 'VisitasInfoResumController:armaInfoVisitas')->setName('repartos.visitasinforesum.armainfovisitas');

# INFORME PRODUCTOS Y DEBITOS EN VISTA
	# Repartos / Informe productos y debitos en Visitas
	$app->get('/repartos/visitas/infoprodsdebs', 'InformeProductosDebitosVisitasController:infoProdsDebs')->setName('repartos.visitas.infoprodsdebs');
	# Repartos / Informe productos y debitos en Visitas
	$app->get('/repartos/visitas/imprimeinfoprodsdebs', 'InformeProductosDebitosVisitasController:imprimeInfoProdsDebs')->setName('repartos.visitas.imprimeinfoprodsdebs');
