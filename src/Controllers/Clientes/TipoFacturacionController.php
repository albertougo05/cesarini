<?php 

namespace App\Controllers\Clientes;

use App\Models\Cliente;
use App\Models\TipoFacturacion;

use App\Controllers\Controller;


/**
 * Clase Tipo facturacion abono clientes
 * Name: /clientes/tipofact (GET)
 * 
 */
class TipoFacturacionController extends Controller
{

	public function tipoFacturacion($request, $response)
	{
		$tiposFact = TipoFacturacion::orderBy('Descripcion', 'asc')->get();
		$accion = 'Nuevo';

		$datos = array('titulo' => 'Cesarini - Tipo Facturación',
			           'tiposfact' => $tiposFact,
			           'accion' => $accion);

		return $this->view->render($response, 'clientes/tipofacturacion.twig', $datos);
	}

	/**
	 * POST Tipo Facturacion - Crea o actualiza un nuevo Tipo de Facturacion
	 * Name: /clientes/tipofact (POST)
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige a /clientes/tipofact (muestra cartel de tipo fact creado con éxito)
	 */
	public function postTipoFact($request, $response)
	{
		$abono = $this->utils->convStrToFloat( $request->getParam('importe') );
		$datos = array('Descripcion' => $request->getParam('descripcion'), 
	                   'MontoAbono'  => $abono );

		if (TipoFacturacion::where('Id', $request->getParam('idtipo'))->first()) {
			// Actualiza
			$tipofact = TipoFacturacion::where('Id', $request->getParam('idtipo'))
			                           ->lockForUpdate()
			                           ->update( $datos );

			// Actualizar tabla Clientes con nuevo abono
			$this->_actualizaAbonoClientes($request->getParam('idtipo'), $abono);

			$flash = 'Tipo facturacion actualizado con éxito !';

		} else {
			// Crea nuevo 
			$tipofact = TipoFacturacion::create($datos);
			$flash = 'Tipo facturación creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('clientes.tipofact'));
	}

	/**
	 * Devuelve lista con los tipos de facturación
	 * Name: /clientes/tipofact/data
	 * 
	 * @param  $request
	 * @param  $response 
	 * @return json - Todos los datos del tipo de facturación
	 */
	public function dataTipoFacturacion($request, $response)
	{
		$tipoFact = TipoFacturacion::find( $request->getParam('idtipo') );

		return $tipoFact->toJson();
	}

	/**
	 * Elimina tipo de producto
	 * Name: /clientes/tipofact/elimina
	 * 
	 * @return redirecciona a pagina nueva de tipo de producto
	 */
	public function eliminaTipoFact($request, $response)
	{
		TipoFacturacion::where('Id', $request->getParam('idtipo'))->delete();

		$this->flash->addMessage('info', "Tipo facturación eliminado !");

		return $response->withRedirect($this->router->pathFor('clientes.tipofact'));
	}

	/**
	 * Actualiza el abono para clientes que tengan ese tipo de facturacion
	 * 
	 * @param  int $id
	 * @param  float $abono
	 */
	private function _actualizaAbonoClientes($id, $abono)
	{
		Cliente::where('IdTipoFact', $id)
				->lockForUpdate()
		        ->update(['CostoAbono' => $abono]);
	}


}
