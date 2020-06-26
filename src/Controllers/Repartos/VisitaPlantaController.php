<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ClienteReparto;
use App\Models\ClienteDomicilio;
use App\Models\Empleado;
use App\Models\Visita;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


class VisitaPlantaController extends Controller
{
	/**
	 * Visita a planta Home
	 * Name: repartos.visitaplanta
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function visitaPlanta($request, $response)
	{
		unset( $_SESSION['idCliente'] );
		$cliente = [];
		unset( $_SESSION['idEmpleado'] );
		$idEmpl = $idClien = 0;
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
		$productos = $this->_listaProductosconTipo();

		$datos = [ 'titulo'     => 'Cesarini - Visita a planta',
				   'accion'     => 'Nueva',
				   'empleados'  => $empleados,
				   'productos'  => $productos,
				   'cliente'    => $cliente,
				   'idempleado' => $idEmpl,
				   'idcliente'  => $idClien ];

		return $this->view->render($response, 'repartos/visitas/visitaPlanta.twig', $datos);
	}

	/**
	 * Muestra pantallla ya con datos (por los valores en session)
	 * Name: repartos.visitaplanta.condata
	 * 
	 * @param  Request $request 
	 * @param  Response $response
	 * @return View
	 */
	public function visitaConData($request, $response)
	{
		if ($request->getParam('idClie')) {
			$idClien = $_SESSION['idCliente'] = $request->getParam('idClie');
			$cliente = $this->_getDataCliente($request);
		} else {
			$cliente = [];
			$idClien = 0;
		}

		if ( isset($_SESSION['idEmpleado']) ) {
			$idEmpl = $_SESSION['idEmpleado'];
		} else {
			$idEmpl = 0;
		}

		$empleados = Empleado::select('Id', 'ApellidoNombre')->orderBy('ApellidoNombre', 'asc')->get();
		$productos = $this->_listaProductosconTipo();

		$datos = [ 'titulo'     => 'Cesarini - Visita a planta',
				   'accion'     => 'Nueva',
				   'empleados'  => $empleados,
				   'productos'  => $productos,
				   'cliente'    => $cliente,
				   'idempleado' => $idEmpl,
				   'idcliente'  => $idClien ];

		return $this->view->render($response, 'repartos/visitas/visitaPlanta.twig', $datos);
	}

	/**
	 * Guarda en session el dato del empleado seleccionado
	 * Name: repartos.visitaplanta.guardaidempleado  (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response con json 
	 */
	public function guardaIdEmpleado($request, $response)
	{
		$idEmpl = $request->getParam('idemp');

		$_SESSION['idEmpleado'] = $idEmpl;

		return json_encode(['id' => $idEmpl]);
	}

	/**
	 * Guardar datos de la visita a planta (GET)
	 * Name: repartos.visitaplanta.guardavisita
	 * 
	 * @param  Request $request
	 * @param  Respose $response
	 * @return json
	 */
	public function guardaVisita($request, $response)
	{
		$resultVisita = $resultProds = $resultClies = '';

		$idEmp = $request->getParam('idemp');
		$idCli = $request->getParam('idcli');
		$idDom = $request->getParam('iddom');
		$prods = $request->getParam('prods');

		// Guardo tabla Visita
		$guardarVisita = $this->_guardoVisita($idEmp);
		$id = $guardarVisita['id'];
		$resultVisita = $guardarVisita['result'];

		// Guardo en tabla VisitaSalidaProductos
		$resultProds = $this->_guardoProductosVisita($prods, $id);
		// Guardo en tabla VisitaDetalleCliente
		$resultClies = $this->_guardoDetalleCliente($prods, $id, $idCli, $idDom);

		return json_encode([ 'id' => $id, 
							 'resultVisita' => $resultVisita, 
							 'resultProds'  => $resultProds, 
							 'resultClies'  => $resultClies ]);
	}

	/**
	 * Imprimir visita a planta
	 * Name: repartos.visitaplanta.imprimir
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View
	 */
	public function imprimir($request, $response)
	{
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha    = date('d/m/Y');
		$visita   = Visita::find($request->getParam('idvisita'));

		// Buscar entrega de la visita
		$record = VisitaDetalleCliente::select('Entrega')
										->where('IdVisita', $request->getParam('idvisita'))
										->first();
		$entrega = $record->Entrega;

		$dataVisita = [ 'id'      => $request->getParam('idvisita'),
						'fecha'   => $visita->Fecha,
						'hora'    => $visita->HoraSalida,
						'idemp'   => $visita->IdEmpleado,
						'nombemp' => $visita->Empleado->ApellidoNombre,
						'entrega' => $entrega ];

		$productos = $this->_productosVisita($request->getParam('idvisita'));
		$cliente   = Cliente::find($_SESSION['idCliente']);

		$datos = [ 'titulo'     => 'Cesarini - Visita a planta',
				   'fecha'      => $fecha,
				   'dataVisita' => $dataVisita,
				   'productos'  => $productos,
				   'cliente'    => $cliente ];

		return $this->view->render($response, 'repartos/visitas/visitaPlantaImprime.twig', $datos);
	}

	/**
	 * Guardo datos en Visitas
	 * 
	 * @param  Request $req 
	 * @return array
	 */
	private function _guardoVisita($idEmpl)
	{
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('Y-m-d');
	    $hora  = date("H:i");

		$datos = array( 'IdGuiaReparto' => 0, 
					    'IdEmpleado'    => (integer) $idEmpl,
					    'Fecha'         => $fecha,
					    'HoraSalida'    => $hora,
					    'HoraRetorno'   => $hora,
					    'Pendiente'     => false, 
						'Observaciones' => 'Visita en planta.' );

		try {
			// En visita a planta, NO hay GR, ni hora salida y retorno. Pongo la misma en ambos
			$id = Visita::insertGetId($datos, 'Id');
			$result = 'Ok';

		} catch (Exception $e) {
			$result = 'Error salvando datos productos visita. '.$e;
			$id     = 0;
		}

		return array( 'id' => $id, 'result' => $result );
	}

	/**
	 * Guardo productos de la visita
	 * 
	 * @param  Request $req 
	 * @param  string $id
	 * @return string
	 */
	private function _guardoProductosVisita($prods, $id)
	{
		if ($id == 0) {
			# Si el id es 0 
			$result = 'Error no hay Id de visita. ';

		} else {

			try {

				foreach ($prods as $value) {

					$cants = array( 'IdVisita'         => (integer) $id,
									'IdProducto'       => (integer) $value['id'],
									'CantRetirado'     => (integer) $value['ret'],
									'CantDevuelto'     => 0,    // Es 0 porque no es devuelto por el empleado
									'EnvasesDevueltos' => (integer) $value['dev'] );

					$visita = VisitaSalidaProducto::create($cants);
				}
				$result = 'Ok';

			} catch (Exception $e) {
				$result = 'Error salvando datos productos visita. '.$e;
			}
		}

		return $result;
	}

	/**
	 * Guardo datos cliente visita
	 * 
	 * @param  array $prods
	 * @param  integer $id    id visita
	 * @param  integer $idcli id cliente
	 * @return string 
	 */
	private function _guardoDetalleCliente($prods, $id, $idcli, $iddom)
	{
		if ($id == 0) {
			# Si el id es 0 
			$result = 'Error no hay Id de visita (Clientes). ';

		} else {

			try {

				foreach ($prods as $value) {

					$cants = array( 'IdVisita'    => (integer) $id,
									'IdCliente'   => (integer) $idcli,
									'IdDomicilio' => (integer) $iddom,
									'OrdenVisita' => 0,
									'IdProducto'  => (integer) $value['id'],
									// Busco estock de envases
									'CantStock'   => $this->utils->stockEnvases($value['id'], $idcli),
									'CantDejada'  => (integer) $value['ret'],
									'CantRetira'  => (integer) $value['dev'],
									'Saldo'       => 0,
									'Entrega'     => (integer) $value['ent'] );

					$visita = VisitaDetalleCliente::create($cants);
				}
				$result = 'Ok';

			} catch (Exception $e) {
				$result = 'Error salvando datos cliente visita. '.$e;
			}
		}

		return $result;
	}

	/**
	 * Arma lista de productos incluye Tipo en la descripcion
	 * 
	 * @return Array
	 */
	private function _listaProductosconTipo()
	{
		$prods = Producto::orderBy('Descripcion', 'asc')->get();
		$products = [];

		foreach ($prods as $value) {
			$products[] = [ 'id' => $value->Id,
							'descripcion' => $value->DescripTipoProducto->Descripcion." - ".$value->Descripcion ];
		}

		usort($products, array($this, '_comparar'));

		return $products;
	}

	/**
	 * Para ordenar array multidimension de clientes GR
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _comparar($a, $b)
	{
		return strcmp($a["descripcion"], $b["descripcion"]);
	}

	/**
	 * Devuelve datos del cliente seleccionado
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _getDataCliente($req)
	{
		$recCli = Cliente::find($req->getParam('idClie'));
		$domCli = ClienteDomicilio::find($req->getParam('idDom'));
		$arrCli = [ 'Id'             => $recCli->Id,
					'IdDomicilio'    => $req->getParam('idDom'),
					'ApellidoNombre' => $recCli->ApellidoNombre,
					'Direccion'      => $domCli->Direccion,
					'Localidad'      => $domCli->Localidad,
					'Telefono'       => $domCli->Telefono,
					'Celular'        => $domCli->Celular ];

		return $arrCli;
	}

	/**
	 * Busco productos de la visita
	 * 
	 * @param  integer $idVis
	 * @return array
	 */
	private function _productosVisita($id)
	{
		$products = VisitaSalidaProducto::where('IdVisita', $id)->get();

		$listaProd = [];
		foreach ($products as $value) {
			// Busco el producto para obtener la descripcion del tipo
			$tipoProd = Producto::find($value->IdProducto);
		
			$listaProd[] = ['idprod'   => $value->IdProducto,
		                    'producto' => $tipoProd->DescripTipoProducto->Descripcion." - ".$value->Producto->Descripcion,
		                    'prodret'  => $value->CantRetirado,
		                    'proddev'  => $value->EnvasesDevueltos ];
		}

		return $listaProd;
	}


}
