<?php 

namespace App\Controllers\Productos;

use App\Models\Dispenser;
use App\Models\TipoDispenser;
use App\Models\MovimientoDispenser;
use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\Empleado;
use App\Models\VisitaMovimDispenser;

use App\Controllers\Controller;


/**
 * Url: /productos/dispenser
 * 
 */
class DispenserController extends Controller
{

	/**
	 * Dispenser
	 * Name: productos.dispenser
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View 
	 */
	public function dispenser($request, $response)
	{
		$infoMovDisp = false;
		// Si viene parametro 'id'...
		if ($request->getParam('id')) {

			$accion  = 'Modifica';			
			$data = Dispenser::where('Id', $request->getParam('id'))->first();
			$movDisp = $this->_buscarMovDisp($request->getParam('id'));
			// Si el 'Estado' es 'Cliente', busco y muestro sus datos
			if ($data->Estado == 'Cliente') {
				$dataClie = $this->_buscarDatosCliente($request->getParam('id'));
			} else $dataClie = false;

		} else {
			$accion  = 'Nuevo';
	    	date_default_timezone_set("America/Buenos_Aires");
	    	$fecha = date('Y-m-d');
			$data = ['FechaAlta' => $fecha, 'Estado' => 'Stock'];
			$dataClie = false;
			$movDisp = false;
		}

		$estados = ['Stock', 'Service', 'Cliente', 'Baja'];
		$tipos   = TipoDispenser::where('Id', '>', 0)->get();
		$datos = array('titulo'    => 'Cesarini - Dispenser',
			           'tiposDisp' => $tipos,
			           'estados'   => $estados,
			           'accion'    => $accion,
			           'movimDisp' => $movDisp,
			           'infoMovDisp' => $infoMovDisp,
			           'dataClie' => $dataClie,
			       	   'data'      => $data);

		return $this->view->render($response, 'productos/dispenser.twig', $datos);
	}

	/**
	 * POST dispenser - Crea o actualiza dispenser
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View Redirige /productos/dispenser 
	 */
	public function postDispenser($request, $response)
	{
		//
		// Validar: NroSerie y NroInterno que NO se repita (Queda en suspenso hasta que sean obligatorios)
		// 

		$datos = array( 'NroSerie' => ($request->getParam('nroSerie') == '') ? null : $request->getParam('nroSerie'),
					    'NroInterno' => ($request->getParam('nroInterno') == '') ? null : $request->getParam('nroInterno'),
					    'Modelo' => $request->getParam('modelo'),
					    'IdTipo' => $request->getParam('selectTipo'),
					    'FechaAlta' => $request->getParam('fechaAlta'),
					    'FechaUltService' => ($request->getParam('ultService') == '') ? null : $request->getParam('ultService'),
					    'FechaBaja' => ($request->getParam('fechaBaja') == '') ? null : $request->getParam('fechaBaja'),
					    'Estado' => $request->getParam('estado'),
					);

		if (Dispenser::where('Id', $request->getParam('id'))->first()) {
			// Actualiza
			Dispenser::where('Id', $request->getParam('id'))
			          ->lockForUpdate()
			          ->update([ 'NroSerie' => ($request->getParam('nroSerie') == '') ? null : $request->getParam('nroSerie'),
					  		     'NroInterno' => ($request->getParam('nroInterno') == '') ? null : $request->getParam('nroInterno'),
					    		 'Modelo' => $request->getParam('modelo'),
					    		 'IdTipo' => $request->getParam('selectTipo') ]);
			$flash = 'Dispenser actualizado con éxito !';

		} else {

			// Crea nuevo 
			$dispenser = Dispenser::create($datos);
			// Id del nuevo registro
			$Id = Dispenser::max('Id');
			// Crea registro en tabla MovimientosDispenser
			MovimientoDispenser::create([ 'IdDispenser' => $Id,
									      'Fecha'       => $request->getParam('fechaAlta'),
									      'IdEmpleado'  => null,
									      'IdCliente'   => null,
									      'IdDomicilio' => null,
									      'Observaciones' => 'Alta',
									      'Estado' => 'Stock' ]);

			$flash = 'Dispenser creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('productos.dispenser'));
	}

	/**
	 * Elimina dispenser
	 * Url: productos/dispenser/elimina (Name: productos/dispenser/elimina)
	 * 
	 * @return redirecciona a pagina nueva de dispenser
	 */
	public function eliminaDispenser($request, $response)
	{
		$id = $request->getParam('id');

		Dispenser::where('Id', $id)->delete();

		// Todos los Ids de movimientos de es dispenser...
		$movsDisp = MovimientoDispenser::select('Id')
		                               ->where('IdDispenser', $id)
		                               ->get();

		// Borrar los Movimientos asociados a Visitas
		if ( $movsDisp->isNotEmpty() ) {
			foreach ($movsDisp as $value) {
				VisitaMovimDispenser::where('IdMovDisp', $value->Id);
			}
		}

		// Eliminar todas la referencias a ESE dispenser
		MovimientoDispenser::where('IdDispenser', $id)->delete();

		$this->flash->addMessage('info', "Dispenser eliminado !");

		return $response->withRedirect($this->router->pathFor('productos.dispenser'));
	}

	/**
	 * Valida si existe el número de serie
	 * 
	 * @param   $request
	 * @param  $response
	 * @return json
	 */
	public function validaSerie($request, $response)
	{
		if (Dispenser::where('NroSerie', $request->getParam('valor'))->first()) {
			$existe = ['status' => true];
		} else $existe = ['status' => false];

		return json_encode($existe);
	}

	/**
	 * Valida si existe el número interno
	 * 
	 * @param   $request
	 * @param  $response
	 * @return json
	 */
	public function validaInterno($request, $response)
	{
		if (Dispenser::where('NroInterno', $request->getParam('valor'))->first()) {
			$existe = ['status' => true];
		} else $existe = ['status' => false];

		return json_encode($existe);
	}

	/**
	 * Inicio de busqueda de Dispenser (ordena por fecha - Mas actuales arriba)
	 * Name: productos.dispenser.buscar
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View
	 */
	public function buscar($request, $response)
	{
		$movimDisp = $infoMovDisp = $urlParam = false;

		if ($request->getParam('movimDisp')) {
			// Si viene a buscar desde Movimientos de Dispenser..
			$movimDisp = $request->getParam('movimDisp');

		} else if ($request->getParam('infoMovDisp')) {
			// Si viene a buscar desde Informe de movimientos de Dispenser..
			$infoMovDisp = $request->getParam('infoMovDisp');
			$urlParam = $_SESSION['urlParam'];
		}

		// Por defecto la primera vez NO muestra los dado de baja...
		$listado = Dispenser::where('Estado', '!=', 'Baja')->orderBy('FechaAlta', 'desc')->get();
		$txtOrdenadoPor = 'Fecha Alta (desc)';
		$radioButtonCheck = "FechaAlta";

		$datos = ['titulo'           => 'Cesarini - Dispenser',
				  'accion'           => 'Buscar',
				  'txtOrdenadoPor'   => $txtOrdenadoPor,
				  'radioButtonCheck' => $radioButtonCheck,
				  'listado'          => $listado,
				  'movimDisp'        => $movimDisp,
				  'infoMovDisp'      => $infoMovDisp,
				  'urlParam'         => $urlParam ];

		return $this->view->render($response, 'productos/dispenser.buscar.twig', $datos);
	}

	/**
	 * Ordenar busqueda de Dispenser
	 * Name: productos.dispenser.ordenabuscar
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View
	 */
	public function ordenaBuscar($request, $response)
	{
		// Si viene a buscar desde Movimientos de Dispenser..
		if ($request->getParam('movimDisp')) {
			$movimDisp = $request->getParam('movimDisp');
		} else $movimDisp = false;

		$mostrarBajas = $request->getParam('mostrarbajas');
		$column = $request->getParam('column');
		$orden = $request->getParam('orden');
		$desde = $request->getParam('desde');
		$hasta = $request->getParam('hasta');
		$infoDesdeHasta = '';		
		$mensajes = ['NroSerie' => "Nro. de Serie",
					 'NroInterno' => "Nro. Interno",
					 'Modelo' => "Modelo",
					 'Estado' => "Estado",
					 'FechaAlta' => "Fecha de alta",
					 'FechaUltService' => "Fecha último service",
					 'FechaBaja' => "Fecha de baja"];

		if ($column == 'NroSerie' || $column == 'NroInterno' || $column == 'Modelo' || $column == 'Estado') {
			if ($mostrarBajas == 'true') {
				$listado = Dispenser::orderBy($column, $orden)->get();
			} else $listado = Dispenser::where('Estado', '!=', 'Baja')->orderBy($column, $orden)->get();

		} elseif ($desde == '' && $hasta == '') {
			if ($mostrarBajas == 'true') {
				$listado = Dispenser::orderBy($column, $orden)->get();
			} else $listado = Dispenser::where('Estado', '!=', 'Baja')->orderBy($column, $orden)->get();

		} elseif ($desde != '' && $hasta == '') {
			if ($mostrarBajas == 'true') {
				$listado = Dispenser::whereDate($column, '>=', $desde)->orderBy($column, $orden)->get();
			} else $listado = Dispenser::where('Estado', '!=', 'Baja')->whereDate($column, '>=', $desde)->orderBy($column, $orden)->get();
			$infoDesdeHasta = ", desde: ".$desde;

		} elseif ($desde == '' && $hasta != '') {
			if ($mostrarBajas == 'true') {
				$listado = Dispenser::whereDate($column, '<=', $hasta)->orderBy($column, $orden)->get();
			} else $listado = Dispenser::where('Estado', '!=', 'Baja')->whereDate($column, '<=', $hasta)->orderBy($column, $orden)->get();
			$infoDesdeHasta = ", hasta: ".$hasta;

		} elseif ($desde != '' && $hasta != '') {
			if ($mostrarBajas == 'true') {
				$listado = Dispenser::where([ [$column, '>=', $desde], 
								              [$column, '<=', $hasta] ])
									->orderBy($column, $orden)
									->get();
			} else {
				$listado = Dispenser::where('Estado', '!=', 'Baja')
									->where([ [$column, '>=', $desde], 
								              [$column, '<=', $hasta] ])
									->orderBy($column, $orden)
									->get();
			}
			$infoDesdeHasta = ", desde: ".$desde.", hasta: ".$hasta;
		} 

		$txtOrdenadoPor = $mensajes[$column].$infoDesdeHasta." (".$orden.")";

		$datos = ['titulo'   => 'Cesarini - Dispenser',
				  'accion'   => 'Buscar',
				  'listado'  => $listado,
				  'radioButtonCheck' => $column,
				  'txtOrdenadoPor' => $txtOrdenadoPor,
				  'mostrarbajas' => $mostrarBajas,
				  'movimDisp' => $movimDisp
		];

		return $this->view->render($response, 'productos/dispenser.buscar.twig', $datos);
	}

	/**
	 * Devuelve lista (json) de Dispensers en Stock 
	 * (para select en Modal Visitas->Agregar dispenser)
	 * Name: productos.dispenser.enstock
	 * 
	 * @param Request  $request
	 * @param Response $response
	 * @return json
	 */
    public function enStock($request, $response)
    {
		$lista = Dispenser::select('Id', 'NroInterno', 'Modelo')
		                  ->where('Estado', 'Stock')
		                  ->orderBy('NroInterno', 'asc')
		                  ->get();

		return $lista->toJson();
    }

	/**
	 * Devuelve lista (json) de Dispensers de un cliente
	 * (para select en Modal Visitas->Agregar dispenser)
	 * Name: productos.dispenser.decliente
	 * 
	 * @param Request  $request
	 * @param Response $response
	 * @return json
	 */
    public function deCliente($request, $response)
    {
    	$lista = $this->dispensersDeCliente($request->getParam('id'));

		return json_encode($lista);
    }

    /**
     * Busca los dispenser de un cliente
     * 
     * @param  integer $idCli
     * @return array
     */
    public function dispensersDeCliente($idCli)
    {
    	$lista   = [];
		$dispens = MovimientoDispenser::where([ ['IdCliente', '=', $idCli], 
												['Estado', '=', 'Cliente'] ])
		                              ->orderBy('Fecha', 'desc')
		                              ->get();

		foreach ($dispens as $value) {

			// Solo los que el Id sea el último movimiento del dispenser
			$idMov = MovimientoDispenser::where('IdDispenser', $value->IdDispenser)
			                            ->orderBy('Id', 'desc')
			                            ->first();

			if ($idMov->Id === $value->Id) {

				$lista[] = [ 'Fecha'       => $value->Fecha,
							 'IdDispenser' => $value->IdDispenser, 
			                 'NroInterno'  => $value->Dispenser->NroInterno,
			                 'Modelo'      => $value->Dispenser->Modelo,
			                 'Direccion'   => $value->ClienteDomicilio->Direccion ];
			}
		}

//echo "<pre>";
//print_r($lista);
//echo "<pre><br>";
//die('Ver');

		return $lista;
    }


	/**
	 * Busca datos movimiento y del empleado que lo hizo...
	 * 
	 * @param  int $idDisp
	 * @return array
	 */
	private function _buscarMovDisp($idDisp)
	{
		$movDisp = MovimientoDispenser::where('IdDispenser', $idDisp)
		                              ->orderBy('Fecha', 'desc')
		                              ->first();

		if ($movDisp->IdEmpleado > 0) {
			$registro = Empleado::find($movDisp->IdEmpleado);
			$empleado = $registro->ApellidoNombre;

		} else $empleado = "";
		
		$arrMovDisp = [ 'Fecha'    => $movDisp->Fecha,
	                    'Empleado' => $empleado,
	                    'Observac' => $movDisp->Observaciones ];

		return $arrMovDisp;
	}

	/**
	 * Busca datos del cliente, cuando Dispenser tiene estado == cliente
	 * 
	 * @param  int $idCli
	 * @param  obj $movDisp
	 * @return array
	 */
	private function _buscarDatosCliente($idDisp)
	{
		// Busco el ultimo Mov. de Disp. con Estado == Cliente
		$movDisp = MovimientoDispenser::where('IdDispenser', $idDisp)
		                               ->where('Estado', 'Cliente')
		                                ->orderBy('Fecha', 'desc')
		                                 ->first();

		$cliente = Cliente::find($movDisp->IdCliente);
		$clieDom = ClienteDomicilio::find($movDisp->IdDomicilio);
		$arrDataCli = [ 'ApellidoNombre' => $cliente->ApellidoNombre,
	                    'Direccion' => $clieDom->Direccion, 
	                    'Localidad' => $clieDom->Localidad,
	                    'Provincia' => $clieDom->Provincia,
	                    'CodPostal' => $clieDom->CodPostal,
	                    'Telefono'  => $clieDom->Telefono,
	                    'Celular'   => $clieDom->Celular,
	                    'Contacto'  => $clieDom->Contacto,
	                    'Email'     => $cliente->Email,
	                    'UltimoMov' => $movDisp->Fecha,
	                     ];

		return $arrDataCli;
	}

}