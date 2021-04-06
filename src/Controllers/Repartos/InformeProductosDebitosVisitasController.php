<?php

namespace App\Controllers\Repartos;


use App\Models\Empleado;
use App\Models\Visita;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 * 
 * Clase
 * 
 */
class InformeProductosDebitosVisitasController extends Controller
{
	/**
	 * Informe de productos y debitos en visitas según 
	 * Name: repartos.infoprodsdebs
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Listado por fecha
	 */
	public function infoProdsDebs($request, $response)
	{
		date_default_timezone_set("America/Buenos_Aires");
		$fecha_actual = date("Y-m-d");
		$empleados = Empleado::select('Id', 'ApellidoNombre')
							 ->where('Estado', 'Alta')
							 ->orderBy('ApellidoNombre', 'asc')
							 ->get();
		//resto 1 mes
		$datos = array('titulo'     => 'Cesarini - Info Productos Débitos', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d'),
					   'empleados'  => $empleados );

		return $this->view->render($response, 'repartos/visitas/infoprodsdebs.twig', $datos);
	}

	/**
	 * Arma listado del informe - GET
	 * Name: 'repartos.visitas.imprimeinfoprodsdebs'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function imprimeInfoProdsDebs($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$listado = $this->_getListProdsDebs($request);
		$periodo = $this->utils->getPeriodo($request->getParam('desde'), $request->getParam('hasta'), $listado);

		if ((integer) $request->getParam('idemp') > 0) {
			$empleado = Empleado::find( $request->getParam('idemp') );
		} else $empleado = [];

		$totalAbonos = $this->_getTotalAbonos($request->getParam('desde'), $request->getParam('hasta'));

		$datos = [ 'titulo'   => 'Cesarini - Info Productos Débitos',
				   'fecha'    => $fecha,
				   'periodo'  => $periodo,
				   'empleado' => $empleado,
				   'listado'  => $listado,
				   'totalAbonos' => $totalAbonos ];

		return $this->view->render($response, 'repartos/visitas/imprimeinfoprodsdebs.twig', $datos);
	}

	private function _getListProdsDebs($req)
	{
		$sql = $this->_getSql($req);

		return $this->pdo->pdoQuery($sql);
	}

	private function _getSql($req)
	{
		$sql = "SELECT vdc.IdProducto, ";
		$sql = $sql ."CONCAT(tp.Descripcion, ' - ', prod.Descripcion) AS Producto, ";
		$sql = $sql . "SUM(vdc.CantDejada) AS SumaDejados, ";
		$sql = $sql . "SUM(vdc.Debito) AS SumaDebitos ";
		$sql = $sql . "FROM VisitaDetalleClientes AS vdc ";
		$sql = $sql . "LEFT JOIN Productos AS prod ON vdc.IdProducto = prod.Id ";
		$sql = $sql . "LEFT JOIN TipoProducto AS tp ON prod.IdTipoProducto = tp.Id ";
		$sql = $sql . "WHERE vdc.IdVisita IN (SELECT Id FROM Visitas AS vis WHERE ";
		$sql = $sql . $this->_whereFechas($req->getParam('desde'), $req->getParam('hasta'), "vis");

		if ((integer) $req->getParam('idemp') > 0) {
			$sql = $sql . " AND vis.IdEmpleado = " . $req->getParam('idemp') . ") ";
		} else {
			$sql = $sql . ") ";
		}
		//$sql = $sql . "AND vdc.IdProducto <> 7 AND vdc.IdProducto <> 8 ";
		$sql = $sql . "AND vdc.IdProducto <> 0 GROUP BY vdc.IdProducto ";
		$sql = $sql . "ORDER BY Producto";   //"ORDER BY vdc.IdProducto";

		return $sql;
	}

	/**
	 * Devuelve string con where de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	private function _whereFechas($desde, $hasta, $alias)
	{
		if ($desde == '' && $hasta == '') {
			$where = '';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = $alias . ".Fecha >= '".$desde."' ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = $alias . ".Fecha <= '".$hasta."' ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = $alias . ".Fecha >= '" . $desde . "' AND " . $alias . ".Fecha <= '" . $hasta."' ";
		}

		return $where;
	}

	/**
	 * Devuelve el total de abonos facturados en el periodo
	 * 
	 * @param  [date] $desde
	 * @param  [date] $hasta
	 * @return [float]
	 */
	private function _getTotalAbonos($desde, $hasta)
	{
		$sql = "SELECT SUM(c.Total) AS total FROM Comprobantes c ";
		$sql = $sql ."WHERE c.Concepto LIKE 'Abono%'";

		if ($desde != '' || $hasta != '') {
			$sql = $sql . " AND " . $this->_whereFechas($desde, $hasta, "c");
		}

		$total = $this->pdo->pdoQuery($sql);

		return $total[0];
	}

}
