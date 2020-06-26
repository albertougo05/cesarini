<?php

namespace App\Controllers\Repartos;

use App\Models\Dispenser;
use App\Models\MovimientoDispenser;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\ClienteDomicilio;

use App\Controllers\Controller;


/**
 * Url: '/repartos/movimientodispenser'
 * 
 * Name: 'repartos.movimientodispenser'
 * 
 */
class MovimientoDispenserController extends Controller
{
	private $_accion;
	private $_editable;
    private $_editableBtnClie;

	/**
	 * Movimientos de dispenser
	 * Name: 'repartos.movimientodispenser'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function movimientoDispenser($request, $response)
	{
		$this->_editable = true;
		$this->_editableBtnClie = true;

		if (empty($request->getParams())) {
			# Si no hay parametros pasados...
			$this->_accion  = 'Nuevo';
	    	date_default_timezone_set("America/Buenos_Aires");
	    	$fecha = date('Y-m-d');
			$data = [ 'Fecha' => $fecha,
                      'IdEmpleado' => 0,
					  'IdCliente'  => 0,
					  'Cliente' => ['IdDom' => 0 ]];
		    // Vacío los datos de session
			$_SESSION['dataMovimDisp'] = [];
			// Para saber si no es edicion/modificacion de un movimiento...
			$_SESSION['accion'] = 'Nuevo';
			$_SESSION['dataMovimDisp'] = $data;

		} else {
			# Si hay parametros...
			$data = $this->_actualizaDataSession($request);

			//if ($data['Dispenser']['Estado'] != 'Cliente' && $this->_accion  == 'Modifica') {
			//	$this->_editableBtnClie = false;
			//}
			$this->_accion = $_SESSION['accion'];
		}

		$estados = ['Stock', 'Service', 'Cliente', 'Baja'];
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();

		$datos = array('titulo'    => 'Cesarini - Movim. dispenser',
			           'estados'   => $estados,
			           'accion'    => $this->_accion,
			           'editable'  => $this->_editable,
			           'editableBtnClie' => $this->_editableBtnClie,
			           'empleados' => $empleados,
			       	   'data'      => $data);

		return $this->view->render($response, 'repartos/movimdispenser/movimDispenser.twig', $datos);
	}

	/**
	 * POST Movimiento dispenser - Crea o actualiza movimiento dispenser
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View Redirige /repartos/movimientodispenser 
	 */
	public function postMovimDispenser($request, $response)
	{
		$datos = $request->getParams();

		if (MovimientoDispenser::where('Id', $request->getParam('Id'))->first()) {
			// Actualiza...
			MovimientoDispenser::where('Id', $request->getParam('Id'))
			                     ->lockForUpdate()
			                     ->update([ 'IdDispenser'   => $datos['IdDispenser'],
			                     			'Fecha'         => $datos['Fecha'],
			                     			'IdEmpleado'    => ($datos['IdEmpleado']  === '') ? null : $datos['IdEmpleado'],
			                     			'IdCliente'     => ($datos['IdCliente']   === '') ? null : $datos['IdCliente'],
			                     			'IdDomicilio'   => ($datos['IdDomicilio'] === '') ? null : $datos['IdDomicilio'],
			                     			'Observaciones' => $datos['Observaciones'],
			                     			'Estado'        => $datos['Estado'] ]);
			$flash = 'Movimiento de dispenser actualizado con éxito !';

		} else {

			// Crea nuevo 
			$dispenser = MovimientoDispenser::create($datos);
			$flash = 'Movimiento de dispenser creado con éxito !';
		}

		$this->_actualizaDispenser($request);
		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('repartos.movimientodispenser'));
	}

	/**
	 * Inicio de busqueda de movimiento dispenser (ordena por fecha - Mas actuales arriba)
	 * Name: repartos.movimientodispenser.buscar
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View
	 */
	public function buscar($request, $response)
	{
		$listado = MovimientoDispenser::orderBy('Fecha', 'desc')
									  ->orderBy('Id', 'desc')
									  ->get();
		$txtOrdenadoPor = 'Fecha (desc)';
		$radioButtonCheck = "FechaAlta";

		$datos = ['titulo'   => 'Cesarini - Buscar mov. dispenser',
				  'accion'   => 'Buscar',
				  'txtOrdenadoPor' => $txtOrdenadoPor,
				  'radioButtonCheck' => $radioButtonCheck,
				  'listado'  => $listado ];

		return $this->view->render($response, 'repartos/movimdispenser/buscar.twig', $datos);
	}

	/**
	 * Buscar cliente y su domicilo de tabla ClientesDomicilio
	 * 
	 * Url: /repartos/movimientodispenser/buscarcliente 
	 * Name: repartos.movimientodispenser.buscarcliente
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function buscarCliente($request, $response)
	{
		// Para compartir pantalla con Visita a planta
		$vieneDe = $request->getParam('vienede');

		$arrClieDoms = [];
		$indice = 0;
		// Buscar clientes
		$clientes = Cliente::where('Estado', 'Activo')
							->orderBy('ApellidoNombre', 'asc')
							->get();

		// Crear array con domicilios
		foreach ($clientes as $clie) {
			$domicilios = $clie->Domicilios->toArray();
			// loop por domicilios
			foreach ($domicilios as $dom) {
				$arrClieDoms[$indice]['IdCliente']      = $clie->Id;
				$arrClieDoms[$indice]['ApellidoNombre'] = $clie->ApellidoNombre;
				$arrClieDoms[$indice]['NombreFantasia'] = $clie->NombreFantasia;
				$arrClieDoms[$indice]['IdDom']          = $dom['Id'];
				$arrClieDoms[$indice]['Direccion']      = $dom['Direccion'];
				$arrClieDoms[$indice]['Localidad']      = $dom['Localidad'];

				$indice++;
			}
		}

		$datos = [ 'titulo'   => 'Cesarini - Buscar Cliente',
				   'vienede'  => $vieneDe,
			       'clientes' => $arrClieDoms ];

		return $this->view->render($response, 'repartos/movimdispenser/buscarCliente.twig', $datos);
	}

	/**
	 * Elimina movimiento dispenser
	 * 
	 * Url: repartos/movimientodispenser/elimina 
	 * (Name: repartos/movimientodispenser/elimina)
	 * 
	 * @return redirecciona a pagina nueva de dispenser
	 */
	public function elimina($request, $response)
	{
		MovimientoDispenser::where('Id', $request->getParam('idmov'))->delete();
		// Buscar ultimo movim que queda, para pasar estado al Dispenser...
		$idUltMov = MovimientoDispenser::where('IdDispenser', $request->getParam('iddisp'))->max('Id');
		$ultMov   = MovimientoDispenser::select('Estado')->find($idUltMov);
		$estado   = $ultMov->Estado;
		// Actualiza datos en tabla Dispenser...
		Dispenser::where('Id', $request->getParam('iddisp'))
		          ->lockForUpdate()
		          ->update(['Estado' => $estado]);
			
		$this->flash->addMessage('info', "Movimiento de dispenser eliminado ! (Estado: $estado)");

		return $response->withRedirect($this->router->pathFor('repartos.movimientodispenser'));
	}

	/**
	 * Actualizar datos en SESSION['dataMovimDisp']
	 *
	 * @param $parametro string dato a cambiar
	 * @param $valor     string valor del parametro
	 * @param $data      object recordset de tabla MovimientoDispenser
	 *
	 * @return
	 */
	private function _actualizaDataSession($request)
	{
		$claves = array_keys($request->getParams());
		$parametro = $claves[0];

//print_r($parametro);
//echo "<br>";
//die('Ver parametro...');

		switch ($parametro) {
			case 'id':
				$this->_accion = 'Modifica';
				$_SESSION['accion'] = 'Modifica';
				$data = MovimientoDispenser::where('Id', $request->getParam('id'))
											->first();
				// Determino si es editable el movimiento...
				$this->_editable = $this->_verSiEsEditable($request->getParam('id'), $data->IdDispenser);
				// El boton Cliente está activo si el estado es Cliente...
				$this->_editableBtnClie = ($data->Dispenser->Estado === 'Cliente');

				$arrData = $data->toArray();

				$arrData['Dispenser'] = [ 'Id'         => $data->Dispenser->Id,
		                                  'NroSerie'   => $data->Dispenser->NroSerie,
		                                  'NroInterno' => $data->Dispenser->NroInterno,
		                                  'Modelo'     => $data->Dispenser->Modelo,
		                                  'Estado'     => $data->Dispenser->Estado ];
		        if ($data->IdEmpleado > 0) {
		        	$arrData['Empleado'] = ['Id'             => $data->IdEmpleado,
			                                'ApellidoNombre' => $data->Empleado->ApellidoNombre ];
		        }
		        if ($data->IdCliente > 0) {
				    $arrData['Cliente'] = ['Id'            => $data->IdCliente,
                                          'ApellidoNombre' => $data->Cliente->ApellidoNombre,
                                          'IdDom'          => $data->IdDomicilio,
                                          'Direccion'      => $data->ClienteDomicilio->Direccion,
                                          'Localidad'      => $data->ClienteDomicilio->Localidad,
                                          'Telefono'       => $data->ClienteDomicilio->Telefono,
                                          'Celular'        => $data->ClienteDomicilio->Celular ];
		        }

		        $_SESSION['dataMovimDisp'] = [];  # Vacio el array
		        $_SESSION['dataMovimDisp'] = $arrData;

				return $arrData;

			case 'idDisp':
				$_SESSION['dataMovimDisp']['IdDispenser'] = $request->getParam('idDisp');
				$datosDisp = Dispenser::where('Id', $request->getParam('idDisp'))
										->first();
				$fechaUltMov = MovimientoDispenser::select('Fecha')
													->where('IdDispenser', $request->getParam('idDisp'))
													->orderBy('Fecha', 'desc')
													->first();
				$_SESSION['dataMovimDisp']['Dispenser'] = ['Id' => $datosDisp->Id,
		                                                   'NroSerie' => $datosDisp->NroSerie,
		                                                   'NroInterno' => $datosDisp->NroInterno,
		                                                   'Modelo' => $datosDisp->Modelo,
		                                                   'Estado' => $datosDisp->Estado,
		                                                   'FechaUltMov' => $fechaUltMov->Fecha ];
			    break;

			case 'idEmpl':
			    $_SESSION['dataMovimDisp']['IdEmpleado'] = $request->getParam('idEmpl');
			    $datosEmpl = Empleado::where('Id', $request->getParam('idEmpl'))->select('Id', 'ApellidoNombre')->first();
			    $_SESSION['dataMovimDisp']['Empleado'] = ['Id' => $datosEmpl->Id,
			                                              'ApellidoNombre' => $datosEmpl->ApellidoNombre ];
			    break;

			case 'idClie':
				if ($request->getParam('idClie') == 0) {
					# Si es 0, es que viene de buscar cliente, sin seleccionar uno. Por lo tanto la data sigue igual...
					if ( !isset($_SESSION['dataMovimDisp']['Dispenser']['Estado']) ) {
						$_SESSION['dataMovimDisp']['Dispenser']['Estado'] = '';
					}
					break;
				}

			    $_SESSION['dataMovimDisp']['IdCliente'] = $request->getParam('idClie');
			    $datosClie = Cliente::where('Id', $request->getParam('idClie'))->select('Id', 'ApellidoNombre')->first();
			    $datosDom = ClienteDomicilio::where('Id', $request->getParam('idDom'))->first();
			    $_SESSION['dataMovimDisp']['Cliente'] = ['Id' => $request->getParam('idClie'),
			                                              'ApellidoNombre' => $datosClie->ApellidoNombre,
			                                              'IdDom' => $request->getParam('idDom'),
			                                              'Direccion' => $datosDom->Direccion,
			                                              'Localidad' => $datosDom->Localidad,
			                                              'Telefono' => $datosDom->Telefono,
			                                              'Celular' => $datosDom->Celular ];
			    $_SESSION['dataMovimDisp']['Estado'] = 'Cliente';

				if ( !isset($_SESSION['dataMovimDisp']['Dispenser']['Estado']) ) {
					$_SESSION['dataMovimDisp']['Dispenser']['Estado'] = '';
				}
			    break;
		}

		$data = $_SESSION['dataMovimDisp'];

		return $data;
	}

	/**
	 * Actualiza tabla Dispenser (campo 'Estado')
	 * 
	 * @param  Request $req 
	 * @return void
	 */
	private function _actualizaDispenser($req)
	{
		// Actualiza datos en tabla Dispenser...
		Dispenser::where('Id', $req->getParam('IdDispenser'))
		          ->lockForUpdate()
		           ->update(['Estado' => $req->getParam('Estado')]);

		if ($req->getParam('Estado') == 'Service') {

			Dispenser::where('Id', $req->getParam('IdDispenser'))
			          ->lockForUpdate()
			           ->update(['FechaUltService' => $req->getParam('Fecha')]);

		} elseif ($req->getParam('Estado') == 'Baja') {

			Dispenser::where('Id', $req->getParam('IdDispenser'))
			          ->lockForUpdate()
			           ->update(['FechaBaja' => $req->getParam('Fecha')]);
		}
	}

	/**
	 * Verifica si es editable el Movimiento de dispenser
	 * 
	 * @param  int $idMov
	 * @param  int $idDisp
	 * @return bool
	 */
	private function _verSiEsEditable($idMov, $idDisp)
	{
		// Busco el máximo Id del dispenser...
		$maxIdMov = MovimientoDispenser::where('IdDispenser', $idDisp)->max('Id');

		// retorna boolean TRUE si es el ULTIMO movimiento del dispenser
		return ($maxIdMov == $idMov);
	}

}
