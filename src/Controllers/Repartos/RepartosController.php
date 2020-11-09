<?php

namespace App\Controllers\Repartos;

use App\Models\Actividad;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\GuiaReparto;
use App\Models\ClienteDomicilio;
use App\Models\ClienteReparto;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\ProductoClienteReparto;

use App\Controllers\Controller;


/**
 * 
 * Clase RepartosController
 * 
 */
class RepartosController extends Controller
{
	/**
	 * Inicio Guia de Reparto
	 * Name: repartos.guiareparto
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Guia de Reparto
	 */
	public function guiaReparto($request, $response)
	{
		$datos = $this->_dataParaViewGR(0, 'Nueva', $request);

		return $this->view->render($response, 'repartos/guiadereparto/guiareparto.twig', $datos);
	}

	/**
	 * Muestra GR segun id
	 * Uri:  repartos/getguiareparto/{id}
	 * Name: repartos.guiareparto
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @param  Array $args
	 * @return View
	 */
	public function getGuiaReparto($request, $response, $args)
	{
		$datos = $this->_dataParaViewGR($args['id'], 'Modifica', $request);

		return $this->view->render($response, 'repartos/guiadereparto/guiareparto.twig', $datos);
	}

	/**
	 * Guardar Guia de Reparto (POST)
	 * name: repartos.guiareparto
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return Redireciono...
	 */
	public function postGuiaReparto($request, $response)
	{
		// Datos a tabla GuiaReparto
		$statusGR = $this->_guardarGR($request);
		// Datos a tabla ClienteReparto
		$statusCR = $this->_guardarClientesGR($request);
		// Datos a tabla ProductoClienteReparto
		$statusPC = $this->_guardarProductosGR($request);

		return json_encode(['status' => ['gr' => $statusGR, 'cli' => $statusCR, 'prod' => $statusPC ]]);
	}

	/**
	 * Imprimir la actual guia de reparto
	 * 
	 * @param  Request  $request 
	 * @param  Response $response 
	 * @return redirecciona a una nueva guia de rep
	 */
	public function imprimirGuiaReparto($request, $response, $args)
	{
		$idGuia = $args['id'];
		$dataGuiaReparto = GuiaReparto::find($idGuia);
		$visitasCliente  = $this->_buscarClieTablaVisitas($idGuia);
		$listaTotalReparto  = $this->_armarListaTotalReparto($idGuia);

		$empleado = Empleado::find($dataGuiaReparto->IdEmpleado);
		$nombreEmpleado = $empleado->ApellidoNombre;
		$actividad = Actividad::find($dataGuiaReparto->IdActividad);
		$descActividad = $actividad->Descripcion;

//echo "Id guia: ".$idGuia;
//echo "<pre>";
//print_r($dataGuiaReparto);
//echo "<pre><br>";

//die('Ver...');

		$datos = [
			'titulo'       => 'Cesarini - Imprimir Guia',
			'idguia'       => $idGuia,
			'data'         => $dataGuiaReparto,
			'nomEmpleado'  => $nombreEmpleado,
			'actividad'    => $descActividad,
			'visitasCliente' => $visitasCliente,
			'totalReparto' => $listaTotalReparto, 
		];

		return $this->view->render($response, 'repartos/guiadereparto/imprimirguia.twig', $datos);
	}


	/**
	 * Guarda datos en tabla GuiaReparto
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _guardarGR($req)
	{
		$status = 'error';
		$idGuia = $req->getParam('idGuia');
		$data   = [ 'DiaSemana'   => $req->getParam('diaSemana'),
					'IdEmpleado'  => $req->getParam('idEmpleado'),
					'Turno'       => $req->getParam('turno'),
					'IdActividad' => $req->getParam('idActividad'),
					'HoraSalida'  => $req->getParam('horaSalida'),
					'HoraRetorno' => $req->getParam('horaRetorno'),
					'Estado'      => $req->getParam('estado') ];
		$guiaRep = GuiaReparto::updateOrInsert(['Id' => $idGuia], $data);

		if ($guiaRep) {
			$status = 'ok';
		}

		return ['status' => $status];
	}


	private function _guardarClientesGR($req)
	{
		$status = 'error';
		$idGuia = $req->getParam('idGuia');
		$data = json_decode($req->getParam('dataClientes'));
		$ordenVisita = 1;


//echo "Id guia: ".$idGuia;
//echo "<pre>";
//print_r($data);
//echo "<pre><br>";

//die('Ver...');



		$cant = ClienteReparto::where('IdReparto', $idGuia)->delete();

		foreach ($data as $value) {
			$cliente = [ 'IdReparto' => $idGuia,
						 'IdCliente' => $value->idCli,
						 'IdClienteDomicilio' => $value->idDomicilio,
						 'OrdenVisita' => $ordenVisita ];

			// guardo registro en la tabla
			$clie = ClienteReparto::create($cliente);
			$ordenVisita++;
		}

		if ($clie) {
			$status = 'ok';
		}

		return ['status' => $status];
	}

	private function _guardarProductosGR($req) {
		$status = 'error';
		$idGuia = $req->getParam('idGuia');
		$data = json_decode($req->getParam('dataProductos'));

		$cant = ProductoClienteReparto::where('IdReparto', $idGuia)->delete();

		foreach ($data as $value) {
			$product = [ 'IdReparto'   => $idGuia,
						 'IdCliente'   => $value->idCliente,
						 'IdDomicilio' => $value->idDomicilio,
						 'IdProducto'  => $value->idProducto,
						 'CantSugerida' => $value->cantidad ];
			// guardo registro en la tabla
			$prod = ProductoClienteReparto::create($product);
		}

		if ($prod) {
			$status = 'ok';
		}

		return ['status' => $status];
	}

	/**
	 * Datos para el view de la GR
	 * 
	 * @param  string $accion
	 * @param  Request $req
	 * @return array
	 */
	private function _dataParaViewGR($id, $accion, $req)
	{
		$data = [];
		$data['titulo'] = 'Cesarini - Guia reparto';
		$data['accion'] = $accion;
		$data['hayMensaje'] = $req->getParam('msg') || '';
		$data['repartidores'] = $this->_dataEmpleadosAlfa();

		switch ($accion) {
			case 'Nueva':
				$data['idGuia'] = $this->_buscarIdNuevaGuia();
				$data['data'] = ['Id'          => $data['idGuia'], 
								 'DiaSemana'   => '0', 
								 'Turno'       => '0', 
								 'IdEmpleado'  => '0', 
								 'IdActividad' => '0', 
								 'HoraSalida'  => '',
								 'HoraRetorno' => '', 
								 'Estado'      => 'Vigente'];
				$data['clientesGR']    = [];
				$data['productosClie'] = [];
				$data['productos']     = [];
				$data['totalReparto']  = [];
				break;

			case 'Modifica':
				$data['idGuia']        = $id;
				$data['data']          = GuiaReparto::find($id);
				$data['clientesGR']    = $this->_buscarClieTablaVisitas($id);
				$data['productosClie'] = $this->_buscaProductosCliente($id, $data['clientesGR'] );
				$data['productos']     = $this->_listaDeProductos();
				$data['totalReparto']  = $this->_armarListaTotalReparto($id);
				break;
		}

		return $data;
	}

	/**
	 * Buscar nuevo id para guia
	 * 
	 * @return integer
	 */
	private function _buscarIdNuevaGuia()
	{
		// Busco el máximo de Id y ese es el último
		$lastId = GuiaReparto::max('Id');

		return $lastId + 1;
	}

	/**
	 * Devuelve lista de empleados ordenados alfab
	 * 
	 * @return array Lista de empleados
	 */
	private function _dataEmpleadosAlfa()
	{
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
		$arrEmp = [];

		foreach ($empleados as $value) {
			$arrTemp = ['id'        => $value->Id,
						'nombre'    => $value->ApellidoNombre,
					    'domicilio' => $value->Domicilio,
					    'localidad' => $value->Localidad,
		               ];
		    $arrEmp[] = $arrTemp;
		}

		return $arrEmp;
	}

	/**
	 * Buscar los clientes de la tabla visitas
	 * 
	 * @param  integer $idGuia
	 * @return array Lista de clientes a visitar
	 */
	private function _buscarClieTablaVisitas($idGuia)
	{
		$arrListaClientes = [];
		$arrClieVisitas = ClienteReparto::where('IdReparto', $idGuia)
										->orderBy('OrdenVisita')
										->get();
		$idx = 0;
		// Armo la lista para presentar en pantalla
		foreach ($arrClieVisitas as $value) {

			$dataCliente = Cliente::find((integer) $value->IdCliente);
			//$dataCliente = Cliente::where('Id', $value->IdCliente);
			$dataDomicil = ClienteDomicilio::find($value->IdClienteDomicilio);

			// ('id') Identifica al cliente en la lista
			$arrClie = [ 'id'          => $idx,
						 'idCliente'   => $dataCliente->Id,
						 'apellidoNom' => $dataCliente->ApellidoNombre,
						 'domicilio'   => $dataDomicil->Direccion,
						 'localidad'   => $dataDomicil->Localidad,
						 'celular'     => $dataDomicil->Celular,
						 'idDomicilio' => $dataDomicil->Id, 
						 'ordenVisita' => $value->OrdenVisita,
						 'borrado'     => 0 ];
			$arrListaClientes[] = $arrClie;
			$idx++;
		}

		return $arrListaClientes;
	}

	/**
	 * Arma la lista de totales de productos
	 * 
	 * @param int id guia de reparto
	 * @return array
	 */
	private function _armarListaTotalReparto($idGuia)
	{
		$sql = "SELECT cant.IdReparto, cant.IdProducto, prod.IdTipoProducto, ";
		$sql = $sql . "CONCAT(tp.Descripcion, ' - ', prod.Descripcion ) as producto, ";
		$sql = $sql . "SUM(cant.CantSugerida) AS sumaCant FROM ProductoClienteReparto ";
		$sql = $sql . "AS cant LEFT JOIN Productos AS prod ON cant.IdProducto = prod.Id ";
		$sql = $sql . "LEFT JOIN TipoProducto AS tp ON prod.IdTipoProducto = tp.Id ";
		$sql = $sql . "WHERE cant.IdReparto = " . $idGuia . " ";
		$sql = $sql . "GROUP BY cant.IdProducto ORDER BY cant.IdProducto";

		$arLista = $this->pdo->pdoQuery($sql);

		return $arLista;
	}

	/**
	 * Busca productos de cada cliente en GR
	 * 
	 * @param  integer $id Id de la GR
	 * @return array
	 */
	private function _buscaProductosCliente($id, $clientes)
	{
		$lista = [];
		$sql = "SELECT prod.Id, prod.IdTipoProducto, ";
		$sql = $sql . "CONCAT(tp.Descripcion, ' - ', prod.Descripcion) AS Producto ";
		$sql = $sql . "FROM Productos AS prod LEFT JOIN TipoProducto AS tp ";
		$sql = $sql . "ON prod.IdTipoProducto = tp.Id WHERE prod.Id = ";

		foreach ($clientes as $clie) {
			$producto = ProductoClienteReparto::where([
												[ 'IdReparto', '=',	$id ],
												[ 'IdCliente', '=',	$clie['idCliente'] ],
												[ 'IdDomicilio', '=', $clie['idDomicilio'] ] ] )
											  ->get()
											  ->toArray();

			foreach ($producto as $value) {
				$sql2 = $sql . $value['IdProducto'];
				$prod = $this->pdo->pdoQuery($sql2);
				$DescripProducto = $prod[0]['Producto'];

				$lista[] = [ 'id'           => $clie['id'], 
							 'idCliente'    => $clie['idCliente'],
							 'idDomicilio'  => $clie['idDomicilio'],
							 'idProducto'   => $value['IdProducto'],
							 'producto'     => $DescripProducto,
							 'cantSugerida' => $value['CantSugerida'] ];
			}
		}

		return $lista;
	}

	/**
	 * Devuelve lista de productos
	 * 
	 * @return array
	 */
	private function _listaDeProductos()
	{
		$sql = "SELECT prod.Id, prod.IdTipoProducto, ";
		$sql = $sql . "CONCAT(tp.Descripcion, ' - ', prod.Descripcion) AS Producto ";
		$sql = $sql . "FROM Productos AS prod LEFT JOIN TipoProducto AS tp ";
		$sql = $sql . "ON prod.IdTipoProducto = tp.Id ORDER BY Producto";

		$lista = $this->pdo->pdoQuery($sql);

		return $lista;
	}



}
