<?php

namespace App\Controllers\Repartos;

use App\Models\Empleado;

use App\Controllers\Controller;


/**
 *
 * Clase VisitasInfoResumController
 * 
 */
class VisitasInfoResumController extends Controller
{
	/**
	 * Resumen Resumido de Visitas (Pantalla principal)
	 * Name: 'repartos.visitasinforesum'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function informeResum($request, $response)
	{
		date_default_timezone_set("America/Buenos_Aires");
		$fecha_actual = date("Y-m-d");
		$empleados = Empleado::select('Id', 'ApellidoNombre')
							 ->where('Estado', 'Alta')
							 ->orderBy('ApellidoNombre', 'asc')
							 ->get();
		//resto 1 mes
		$datos = array('titulo'     => 'Cesarini - Info Visitas Resumido', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d'),
					   'empleados'  => $empleados );

		return $this->view->render($response, 'repartos/visitas/infovisitasresu.twig', $datos);
	}

	/**
	 * Arma listado del informe - GET
	 * Name: 'repartos.visitasinforesu.armainfovisitas'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function armaInfoVisitas($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idcli=8
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$listado = $this->_getListaVisitas( $request->getParam('idrep'),
											$request->getParam('desde'), 
											$request->getParam('hasta') );

		$periodo = $this->utils->getPeriodo($request->getParam('desde'), $request->getParam('hasta'), $listado);
		$totales = $this->_sumaTotales($listado);

//echo "<br><pre>"; print_r($listado); echo "</pre><br>";
//echo "Periodo: ".$periodo; echo "<br><br>";

//die('Ver...');

		$datos = [ 'titulo'   => 'Cesarini - Info Visitas Resumido',
				   'fecha'    => $fecha,
				   'periodo'  => $periodo,
				   'totentr'  => $totales[0],
				   'totdebi'  => $totales[1],
				   'listado'  => $listado ];

		return $this->view->render($response, 'repartos/visitas/imprimeinfovisitas.twig', $datos);
	}

	/**
	 * Busca el listado de visitas
	 * 
	 * @param integer $idrep - Repartidor
	 * @param string  $desde - Fecha
	 * @param string  $hasta - Fecha
	 * @return array
	 */
	private function _getListaVisitas($idrep, $desde, $hasta)
	{
		$sql = $this->_sqlVisitas($idrep, $desde, $hasta);

		//echo "<br><pre>"; print_r($sql); echo "</pre><br>";
		//die('Ver...');

		$list = $this->pdo->pdoQuery($sql);

		return $list;
	}

	/**
	 * Devuelve string con sql para listado cobors en Visitas
	 * 
	 * @return string
	 */
	private function _sqlVisitas($idrep, $desde, $hasta)
	{
		$sql = "SELECT vis.Id, vis.Fecha, vis.IdGuiaReparto, ";
		$sql = $sql ."gr.DiaSemana, gr.Turno, ";
		$sql = $sql ."vis.IdEmpleado, emp.ApellidoNombre, ";
		$sql = $sql ."SUM(vdc.Entrega) AS Entregas, SUM(vdc.Debito) AS Debitos ";
		$sql = $sql . "FROM Visitas AS vis ";
		$sql = $sql . "LEFT JOIN Empleados AS emp ON vis.IdEmpleado = emp.Id ";
		$sql = $sql . "LEFT JOIN GuiaRepartos AS gr ON vis.IdGuiaReparto = gr.Id ";
		$sql = $sql . "LEFT JOIN VisitaDetalleClientes AS vdc ON vis.Id = vdc.IdVisita ";

		$sql = $sql . "WHERE " . $this->_whereFechas($desde, $hasta);

		if ($idrep > 0) {
			if ($desde == '' && $hasta == '') {
				$sql = $sql . "vis.IdEmpleado = " . $idrep . " ";
			} else {
				$sql = $sql . " AND vis.IdEmpleado = " . $idrep . " ";
			}
		}

		$sql = $sql . "GROUP BY vis.Id ";		
		$sql = $sql . "ORDER BY vis.Fecha, vis.Id";

		return $sql;
	}

	/**
	 * Devuelve string con where de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	private function _whereFechas($desde, $hasta)
	{
		if ($desde == '' && $hasta == '') {
			$where = '';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = "vis.Fecha >= '".$desde."' ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "vis.Fecha <= '".$hasta."' ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "vis.Fecha >= '".$desde."' AND vis.Fecha <= '".$hasta."' ";
		}

		return $where;
	}

	private function _sumaTotales($list)
	{
		$totalEntr = $totalDebit = 0;
		foreach ($list as  $value) {
			$totalEntr = $totalEntr + (float) $value['Entregas'];
			$totalDebit = $totalDebit + (float) $value['Debitos'];
		}

		return [$totalEntr, $totalDebit];
	}



}
