<?php

namespace App\Controllers\Ctasctes;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\Empleado;
use App\Models\Visita;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 *
 * Clase InfoComprobantesController
 * 
 */
class InfoComprobantesController extends Controller
{
	/**
	 * Informe de Cobranzas (Pantalla principal)
	 * Name: 'ctasctes.infocomprobantes'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function infoComprobantes($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");
	    $fecha_actual = date("Y-m-d");
	    //$empleados = Empleado::select('Id', 'ApellidoNombre')->orderBy('ApellidoNombre', 'asc')->get();
		//resto 1 mes
		$datos = array('titulo'     => 'Cesarini - Info Comprobantes', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d') );

		return $this->view->render($response, 'ctasctes/infocomprobantes/infocomprobantes.twig', $datos);
	}

	/**
	 * Arma listado del informe
	 * Name: 'ctasctes.infocomprobantes.armainfocomprobantes'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function armaInfoComprobantes($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idcli=8
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$listado = $this->_getListaComp( $request->getParam('idcli'),
									     $request->getParam('desde'), 
									     $request->getParam('hasta') );

		$periodo = $this->utils->getPeriodo($request->getParam('desde'), $request->getParam('hasta'), $listado);

// echo "<br><pre>"; print_r($listado); echo "</pre><br>";
//echo "Periodo: ".$periodo; echo "<br><br>";
//die('Ver...');

		$datos = [ 'titulo'  => 'Cesarini - Info Cobranzas',
				   'fecha'   => $fecha,
				   'periodo' => $periodo,
				   'listado' => $listado ];

		return $this->view->render($response, 'ctasctes/infocomprobantes/imprimecomprobantes.twig', $datos);
	}

	/**
	 * Busca el listado de comprobantes
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _getListaComp($idcli, $desde, $hasta)
	{
		$sql = "SELECT Fecha, TipoForm, ";
		$sql = $sql . "CONCAT(LPAD(Sucursal, 4, '0'), '-', "; 
		$sql = $sql . "LPAD(NroComprobante, 8, '0')) AS Comprobante, ";
		$sql = $sql . "IdCliente, Firma, Concepto, ";
		$sql = $sql . "IF(TipoForm = 'NC' OR TipoForm = 'RE', Total * -1, Total) Importe ";
		$sql = $sql . "FROM Comprobantes ";

		if ($idcli > 0) {

			$sql = $sql . "WHERE IdCliente = " . $idcli . " ";
		} 

		if ($desde != '' || $hasta != '') {

			if ($idcli == 0) { 
				$sql = $sql . "WHERE "; 
			} else {
				$sql = $sql . "AND "; 
			}
			$sql = $sql . $this->_getWhereFechas($desde, $hasta); 
		}

		$sql = $sql . "ORDER BY Fecha ASC, Comprobante ASC";

		# Data del informe
		$list = $this->pdo->pdoQuery($sql);

		return $list;
	}

	/**
	 * Devuelve string con where de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	private function _getWhereFechas($desde, $hasta)
	{
		if ($desde == '' && $hasta == '') {
			$where = '';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = "Fecha >= '".$desde."' ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "Fecha <= '".$hasta."' ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "Fecha >= '".$desde."' AND Fecha <= '".$hasta."' ";
		}

		return $where;
	}

	private function _sumaTotal($list)
	{
		$total = 0;
		foreach ($list as  $value) {
			$total = $total + (float) $value['Total'];
		}

		return $total;
	}

}
