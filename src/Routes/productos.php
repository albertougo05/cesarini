<?php 

# PRODUCTOS
	# Productos / Producto (GET)
	$app->get('/productos/producto', 'ProductoController:getProducto')->setName('productos.producto');

	# productos / Producto (POST)
	$app->post('/productos/producto', 'ProductoController:postProducto');

	# Devuelve json con data producto (id debe ser numero)
	$app->get('/productos/dataproducto', 'ProductoController:dataProducto')->setName('productos.dataproducto');

	# Devuelve json con precio producto (id debe ser numero)
	$app->get('/productos/precioproducto', 'ProductoController:precioProducto')->setName('productos.precioproducto');

	# productos / producto / eliminar (GET)
	$app->get('/productos/producto/elimina', 'ProductoController:eliminaProducto')->setName('productos.producto.elimina');	

# LISTADOS
	# Productos / Listados (GET)
		$app->get('/productos/listados', 'ListadosController:listados')->setName('productos.listados');

	# Productos / Listados / Imprimir(GET)
		$app->get('/productos/imprimir', 'ListadosController:imprimir')->setName('productos.imprimir');

# TIPO PRODUCTO
	# Productos / Tipodeproducto (GET)
		$app->get('/productos/tipoproducto', 'TipoProductoController:getTipoProducto')->setName('productos.tipoproducto');

	# productos / tipoproducto (POST)
		$app->post('/productos/tipoproducto', 'TipoProductoController:postTipoProducto');

	# Devuelve json de TP (id debe ser numero)
		$app->get('/productos/datatipoproducto', 'TipoProductoController:dataTipoProducto')->setName('productos.datatipoproducto');

	# productos / tipoproducto / eliminar (GET)
		$app->get('/productos/tipoproducto/elimina', 'TipoProductoController:eliminaTipoProducto')->setName('productos.tipoproducto.elimina');

# STOCK ENVASES
	# Producto / Stock envases
		$app->get('/productos/stockenvases', 'ProductoController:stockEnvases')->setName('productos.stockenvases');
