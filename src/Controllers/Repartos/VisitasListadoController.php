<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 * Clase VisitasListadoController
 * 
 * Url base: '/repartos/visitaslistado'
 * 
 */
class VisitasListadoController extends Controller
{

	/**
	 * Visitas a clientes
	 * Name: 'repartos.visitaslistado'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function listado($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");

		$datos = array( 'titulo'     => 'Cesarini - Listado Visitas',
						'fechaDesde' => date("Y-m-d", strtotime(date('Y-m-d')."- 1 month")),
			            'fechaHasta' => date('Y-m-d') );

		return $this->view->render($response, 'repartos/visitas/visitasListado.twig', $datos);
	}

	/**
	 * Arma lista de visitas (por cliente o por producto)
	 * Name: repartos.visitaslistado.armarlista (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response 
	 * @return [type]           [description]
	 */
	public function armarLista($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idcli=8&iddom=11&todosdom=true&porProd=false
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$whereFecha = $this->getWhereFechas($request->getParam('desde'), $request->getParam('hasta'));

		$datos = [ 'titulo' => 'Cesarini - Listado Visitas',
				   'fecha'  => $fecha,
				   'entrefechas' => $this->_textEntreFechas($request->getParam('desde'), $request->getParam('hasta')) ];

		# Lista de cliente/s
		$clientes = $this->_clientesParaTitulo($request, $whereFecha);

		if ($request->getParam('porProd') === 'true') {
			# Select ordenado por producto
			$datosClie = $this->_porProducto($request, $whereFecha, $clientes);
			$datos['dataCli']  = $datosClie;
			$datos['client']   = $clientes;

			return $this->view->render($response, 'repartos/visitas/imprimePorProducto.twig', $datos);

		} else {

			# Datos de cliente/s
			$datosClie = $this->_todosLosClientes($request, $whereFecha, $clientes);

			if ( empty( $request->getParam('idcli') ) ) {   
				# Si no hay cliente seleccionado...
				$datos['unotodos'] = 'clientes';

			} else {
				# Datos con de un solo cliente...
				$datos['unotodos'] ='cliente';
			}

			if ($request->getParam('dejacero') === 'true') {
				$datos['dejacero'] = true;
			} else $datos['dejacero'] = false;

			$datos['dataCli'] = $datosClie;
			$datos['client']  = $clientes;

			return $this->view->render($response, 'repartos/visitas/imprimeTodosLosClie.twig', $datos);
		}
	}

	/**
	 * Devuelve por ajax datos del cliente buscado por codigo
	 * Name: repartos.visitaslistado.clieporcod (get)
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return json
	 */
	public function clientePorCodigo($request, $response)
	{
		if ( $this->auth->check() ) {

			// Busco cliente...
			$clie = Cliente::find($request->getParam('codcli'));

			if ($clie) {

				$domi = ClienteDomicilio::where('IdCliente', $request->getParam('codcli'))->first();
				$cliente = [ 'id'      => $clie->Id, 
				             'cliente' => $clie->ApellidoNombre, 
				             'iddom'   => $domi->Id, 
				             'direcc'  => $domi->Direccion,
				             'status'  => 'ok' ];

			} else $cliente = [ 'status' => 'noclie', 'codcli' => $request->getParam('codcli') ];

	    } else $cliente = [ 'status' => 'No user check !' ];

		return json_encode($cliente);
	}

	/**
	 * Devuelve string con where de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	public function getWhereFechas($desde, $hasta)
	{
		if ($desde == '' && $hasta == '') {
			$where = 'cant.IdVisita IN (SELECT Id FROM Visitas) ';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = "cant.IdVisita IN (SELECT Id FROM Visitas WHERE Fecha >= '".$desde."') ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "cant.IdVisita IN (SELECT Id FROM Visitas WHERE Fecha <= '".$hasta."') ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "cant.IdVisita IN (SELECT Id FROM Visitas WHERE Fecha >= '".$desde."' AND Fecha <= '".$hasta."') ";
		}

		return $where;
	}

	private function _clientesParaTitulo($req, $wheFecha)
	{
		# Array de clientes en el rango de fechas de visitas...
		$sql = $this->_sqlClientes($req, $wheFecha);
		// Es la lista de clientes...
		$clientes = $this->pdo->pdoQuery($sql);

		return $clientes;
	}

	/**
	 * Devuelve productos de cliente/s
	 * 
	 * @param  Request $req
	 * @param  string  $wheFecha
	 * @param  array   $clientes
	 * 
	 * @return array   $data
	 */
    private function _todosLosClientes($req, $wheFecha, $clientes)
	{
		$data = [];
		// Sql2 para buscar datos de cada cliente...
		$sql2 = $this->_sql2();

		# Por cada cliente buscar los productos
		foreach ($clientes as $value) {

			$sql3 = $sql2 . $value['IdCliente'];

			if ($req->getParam('todosdom') == 'true') {
				$sql3 = $sql3  . " AND cant.IdDomicilio = " . $value['IdDomicilio'];
			}

			$sql3 = $sql3 . " AND " . $wheFecha;
			$sql3 = $sql3 . "AND cant.IdProducto > 0 ";

			// Si no pide cantidades en cero, busco las columnas con algún dato de saldo o entrega
			if ( $req->getParam('dejacero') == 'false' ) {
				$sql3 = $sql3 . "AND (cant.CantDejada > 0 OR cant.CantRetira > 0 ";
				$sql3 = $sql3 . "OR cant.Saldo > 0 OR cant.Entrega > 0) ";
			}

			if ($req->getParam('visiplan') == 'true') {
				$sql3 = $sql3 . "AND visi.IdGuiaReparto = 0 ";
			}

			$sql3 = $sql3 . "ORDER BY visi.Fecha DESC";

//echo $sql3;
//echo "<br><br>";

			# Data del cliente...
			$data[] = $this->pdo->pdoQuery($sql3);
			$sql3   = '';
		}

//die('Ver sql...');

		return $data;
	}

	/**
	 * Arma string sql para lista de clientes
	 * 
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	private function _sqlClientes($req, $where)
	{
		if ($req->getParam('todosdom') == 'true') {

			# Si pide TODOS los domicilios
			$sql1 = "SELECT DISTINCT cant.IdCliente, cant.IdDomicilio, clie.ApellidoNombre, ";
			$sql1 = $sql1."domi.Direccion FROM VisitaDetalleClientes AS cant ";
			$sql1 = $sql1."LEFT JOIN Clientes AS clie ON cant.IdCliente = clie.Id ";
			$sql1 = $sql1."LEFT JOIN ClientesDomicilio AS domi ON cant.IdDomicilio = domi.Id ";

		} else {

			# Sin todos los domicilio
			$sql1 = "SELECT DISTINCT cant.IdCliente, clie.ApellidoNombre, clie.Direccion ";
			$sql1 = $sql1."FROM VisitaDetalleClientes AS cant ";
			$sql1 = $sql1."LEFT JOIN Clientes AS clie ON cant.IdCliente = clie.Id ";
		}

		$sql1 = $sql1."WHERE cant.IdProducto > 0 ";

		// Si no pide cant en cero, muestro lineas con al menos un dato
		if ( $req->getParam('dejacero') == 'false' ) {
			$sql1 = $sql1."AND (cant.CantDejada > 0 OR cant.CantRetira > 0 ";
			$sql1 = $sql1."OR cant.Saldo > 0 OR cant.Entrega > 0) ";
		}

		if ($req->getParam('idcli') != '') {
			# Si hay codigo de cliente...
			$sql1 = $sql1 . "AND cant.IdCliente = ". $req->getParam('idcli')." ";

			if ($req->getParam('todosdom') == 'false') {
				$sql1 = $sql1 . "AND cant.IdDomicilio = ". $req->getParam('iddom')." ";
			}
		}

		if ($req->getParam('visiplan') == 'true') {
			$sql1 = $sql1 . "AND cant.OrdenVisita = 0 ";
		}

		$sql1 = $sql1 . "AND ". $where . " ORDER BY clie.ApellidoNombre";

		return $sql1;
	}

	/**
	 * Arma string para sql2
	 * 
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	private function _sql2()
	{
		$sql2 = "SELECT visi.Fecha, visi.Id, visi.IdGuiaReparto, "; 
		$sql2 = $sql2."CONCAT(guia.DiaSemana, '-', guia.Turno) AS DiaTurno, ";
		$sql2 = $sql2."guia.IdEmpleado, empl.ApellidoNombre AS Empleado, ";
		$sql2 = $sql2."cant.IdCliente, cant.IdDomicilio, clie.ApellidoNombre, ";
		$sql2 = $sql2."domi.Direccion, cant.IdProducto, prod.Descripcion, ";
		$sql2 = $sql2."cant.CantDejada, cant.CantRetira, cant.Saldo, cant.Entrega, cant.Debito ";
		$sql2 = $sql2."FROM VisitaDetalleClientes AS cant ";
		$sql2 = $sql2."LEFT JOIN Visitas AS visi ON cant.IdVisita = visi.Id ";
		$sql2 = $sql2."LEFT JOIN GuiaRepartos AS guia ON visi.IdGuiaReparto = guia.Id ";
		$sql2 = $sql2."LEFT JOIN Empleados AS empl ON visi.IdEmpleado = empl.Id ";
    	$sql2 = $sql2."LEFT JOIN Productos AS prod ON cant.IdProducto = prod.Id ";
    	$sql2 = $sql2."LEFT JOIN Clientes AS clie ON cant.IdCliente = clie.Id ";
    	$sql2 = $sql2."LEFT JOIN ClientesDomicilio AS domi ON cant.IdDomicilio = domi.Id ";
    	$sql2 = $sql2."WHERE cant.IdCliente = ";

		return $sql2;
	}

	/**
	 * Devuelve productos de cliente/s sumados y ordenados
	 * 
	 * @param  Request $req
	 * @param  string  $wheFecha
	 * @param  array   $clientes
	 * 
	 * @return array   $data
	 */
	private function _porProducto($req, $wheFecha, $clientes)
	{
		$data = [];
		// Sql2 para buscar datos de cada cliente...
		$sql2 = $this->_sqlPorProducto($wheFecha);

		# Por cada cliente buscar los productos
		foreach ($clientes as $value) {

			# Armo linea sql
			$sql3 = $sql2.$value['IdCliente']." ";

			if ($req->getParam('todosdom') == 'true') {
				$sql3 = $sql3."AND cant.IdDomicilio = ".$value['IdDomicilio']." ";
			}

			if ($req->getParam('visiplan') == 'true') {
				$sql3 = $sql3 . "AND cant.OrdenVisita = 0 ";
			}

			$sql3 = $sql3 . "GROUP BY cant.IdProducto ";
			$sql3 = $sql3 . "ORDER BY cant.IdProducto";

			# Data del cliente...
			$dataCli = $this->pdo->pdoQuery($sql3);
			$data[]  = $dataCli;
			$sql3 = '';
		}

		return $data;
	}

	/**
	 * Arma string sql para sumar y ordenar por producto
	 * 
	 * @param  string $where
	 * @return string
	 */
	private function _sqlPorProducto($where)
	{
		$sql = "SELECT cant.IdProducto, cant.IdCliente, SUM(cant.CantDejada) AS Dejado, ";
		$sql = $sql . "SUM(cant.CantRetira) AS Retirado, prod.Descripcion ";
		$sql = $sql . "FROM VisitaDetalleClientes AS cant ";
		$sql = $sql . "LEFT JOIN Productos AS prod ON cant.IdProducto = prod.Id ";
		$sql = $sql . "WHERE cant.IdProducto > 0 ";
		$sql = $sql . "AND (cant.CantDejada > 0 OR cant.CantRetira > 0) AND ";
		$sql = $sql . $where . " AND cant.IdCliente = ";

		return $sql;
	}

	/**
	 * Aram texto para mostrar parametros de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	private function _textEntreFechas($desde, $hasta)
	{
		$fechaDesde = ($desde == '') ? '' : date('d/m/Y', strtotime($desde));
		$fechaHasta = ($hasta == '') ? '' : date('d/m/Y', strtotime($hasta));

		if ($desde == '' && $hasta == '') {
			$texto = '(No hay parámetros de fechas - Todos los registros)';
		} elseif ( !empty($hasta) && empty($desde) ) {
			$texto = "(Datos hasta: $fechaHasta)";
		} elseif ( !empty($desde) && empty($hasta) ) {
			$texto = "(Datos desde: $fechaDesde)";
		} else {
			$texto = "(Datos desde: $fechaDesde, hasta: $fechaHasta)";
		}

		return $texto;
	}


}
