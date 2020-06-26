<?php 

namespace App\Controllers\Productos;

use App\Models\TipoDispenser;
use App\Controllers\Controller;


/**
 * Url: /productos/tipoDispenser
 * 
 */
class TipoDispenserController extends Controller
{
	public function getTipoDispenser($request, $response)
	{
		$tipoDispenser = TipoDispenser::orderBy('Descripcion', 'asc')->get();
		$accion = 'Nuevo';

		$datos = array('titulo' => 'Cesarini - Tipo Dispenser',
			           'tipoDispenser' => $tipoDispenser,
			           'accion' => $accion);

		return $this->view->render($response, 'productos/tipodispenser.twig', $datos);
	}

	/**
	 * POST Tipo de Dispenser - Crea o actualiza un nuevo Tipo de Dispenser
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /productos/tipodispenser (muestra cartel de usuario creado con éxito)
	 */
	public function postTipoDispenser($request, $response)
	{

		$datos = array('Descripcion' => $request->getParam('descripcion'));

		if (TipoDispenser::where('Id', $request->getParam('idtipo'))->first()) {
			// Actualiza
			$empleado = TipoDispenser::where('Id', $request->getParam('idtipo'))
			                    ->lockForUpdate()->update($datos);
			$flash = 'Tipo dispenser actualizado con éxito !';

		} else {
			// Crea nuevo 
			$empleado = TipoDispenser::create($datos);
			$flash = 'Tipo dispenser creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('productos.tipodispenser'));
	}

	/**
	 * Url: /productos/datatipodispenser
	 * 
	 * @param  $request
	 * @param  $response 
	 * @return json - Todos los datos del tipo de dispenser
	 */
	public function dataTipoDispenser($request, $response)
	{
		$tipoDispenser = TipoDispenser::where('Id', $request->getParam('idtipo'))->first();

		return $tipoDispenser->toJson();
	}

	/**
	 * Elimina tipo de dispenser
	 * 
	 * @return redirecciona a pagina nueva de tipo de dispenser
	 */
	public function eliminaTipoDispenser($request, $response)
	{
		$id = $request->getParam('idtipo');

		TipoDispenser::where('Id', $id)->delete();

		$this->flash->addMessage('info', "Tipo dispenser eliminado !");

		return $response->withRedirect($this->router->pathFor('productos.tipodispenser'));
	}

}
