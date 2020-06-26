<?php 

namespace App\Controllers\Productos;

use App\Models\Producto;
use App\Models\TipoProducto;
use App\Controllers\Controller;


/**
 * Url: /productos/producto
 * 
 */
class ProductoController extends Controller
{
	public function getProducto($request, $response)
	{
		$tipoProducto = TipoProducto::orderBy('Descripcion', 'asc')->get();
		$productos = Producto::orderBy('Descripcion', 'asc')->get();
		$accion = 'Nuevo';

		$datos = array('titulo' => 'Cesarini - Tipo Producto',
					   'productos' => $productos,
			           'tipoProducto' => $tipoProducto,
			           'accion' => $accion);

		return $this->view->render($response, 'productos/producto.twig', $datos);
	}

	/**
	 * POST Producto - Crea o actualiza un nuevo Producto
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /productos/producto (muestra cartel de usuario creado con éxito)
	 */
	public function postProducto($request, $response)
	{
		$datos = array('Descripcion'     => $request->getParam('descripcion'),
					   'IdTipoProducto'  => $request->getParam('selectTipoProd'),
					   'Presentacion'    => $request->getParam('presentacion'),
					   'Precio'          => $request->getParam('precio'),
					   'PrecioExcedente' => $request->getParam('precioExced'),
					   'ConStock'        => (integer) $request->getParam('constock'));

		// Saco puntos de miles y cambio coma por punto decimal. Convierto a float.
		$datos['Precio'] = (float) preg_replace(['/\./', '/,/'],['', '.'], $datos['Precio']);
		$datos['PrecioExcedente'] = (float) preg_replace(['/\./', '/,/'],['', '.'], $datos['PrecioExcedente']);

		if (Producto::where('Id', $request->getParam('idprod'))->first()) {
			// Actualiza
			$producto = Producto::where('Id', $request->getParam('idprod'))
			                    ->lockForUpdate()
			                    ->update($datos);
			$flash = 'Producto actualizado con éxito !';

		} else {
			// Crea nuevo 
			$producto = Producto::create($datos);
			$flash = 'Producto creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('productos.producto'));
	}

	/**
	 * Url: /productos/dataproducto
	 * 
	 * @param  $request
	 * @param  $response 
	 * @return json - Todos los datos de producto
	 */
	public function dataProducto($request, $response)
	{
		$producto = Producto::where('Id', $request->getParam('idprod'))->first();

		return $producto->toJson();
	}

	/**
	 * Elimina producto
	 * 
	 * @return redirecciona a pagina nueva de producto
	 */
	public function eliminaProducto($request, $response)
	{
		Producto::where('Id', $request->getParam('idprod'))->delete();

		$this->flash->addMessage('info', "Producto eliminado !");

		return $response->withRedirect($this->router->pathFor('productos.producto'));
	}

	/**
	 * Devuelve json con stock actual de envases
	 * Name: productos.stockenvases
	 * 
	 * @param  $request
	 * @param  $response
	 * @return json
	 */
	public function stockEnvases( $request, $response )
	{
		return json_encode([ 'stock' => $this->utils->stockEnvases( $request->getParam('idpro'), 
																	$request->getParam('idcli') ) 
						   ]);
	}



}
