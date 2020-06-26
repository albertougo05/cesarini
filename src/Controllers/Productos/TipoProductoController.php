<?php 

namespace App\Controllers\Productos;

use App\Models\TipoProducto;
use App\Controllers\Controller;


/**
 * Url: /productos/tipoproducto
 * 
 */
class TipoProductoController extends Controller
{
	public function getTipoProducto($request, $response)
	{
		$tipoProducto = TipoProducto::orderBy('Descripcion', 'asc')->get();
		$accion = 'Nuevo';

		$datos = array('titulo' => 'Cesarini - Tipo Producto',
			           'tipoProducto' => $tipoProducto,
			           'accion' => $accion);

		return $this->view->render($response, 'productos/tipoproducto.twig', $datos);
	}

	/**
	 * POST Tipo de Producto - Crea o actualiza un nuevo Tipo de Producto
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /productos/tipoproducto (muestra cartel de usuario creado con éxito)
	 */
	public function postTipoProducto($request, $response)
	{

		$datos = array('Descripcion' => $request->getParam('descripcion'));

		if (TipoProducto::where('Id', $request->getParam('idtipo'))->first()) {
			// Actualiza
			$empleado = TipoProducto::where('Id', $request->getParam('idtipo'))
			                    ->lockForUpdate()->update($datos);
			$flash = 'Tipo producto actualizado con éxito !';

		} else {
			// Crea nuevo 
			$empleado = TipoProducto::create($datos);
			$flash = 'Tipo producto creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('productos.tipoproducto'));
	}

	/**
	 * Url: /productos/datatipoproducto
	 * 
	 * @param  $request
	 * @param  $response 
	 * @return json - Todos los datos del tipo de producto
	 */
	public function dataTipoProducto($request, $response)
	{
		$tipoProducto = TipoProducto::where('Id', $request->getParam('idtipo'))->first();

		return $tipoProducto->toJson();
	}

	/**
	 * Elimina tipo de producto
	 * 
	 * @return redirecciona a pagina nueva de tipo de producto
	 */
	public function eliminaTipoProducto($request, $response)
	{
		$id = $request->getParam('idtipo');

		TipoProducto::where('Id', $id)->delete();

		$this->flash->addMessage('info', "Tipo producto eliminado !");

		return $response->withRedirect($this->router->pathFor('productos.tipoproducto'));
	}

}
