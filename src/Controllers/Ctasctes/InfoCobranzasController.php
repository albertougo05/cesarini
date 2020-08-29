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
 * Clase InfoCobranzasController
 * 
 */
class InfoCobranzasController extends Controller
{
	/**
	 * Informe de Cobranzas (Pantalla principal)
	 * Name: 'ctasctes.infocobranzas'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function infocobranzas($request, $response)
	{
		date_default_timezone_set("America/Buenos_Aires");
		$fecha_actual = date("Y-m-d");
		$empleados = Empleado::select('Id', 'ApellidoNombre')
							 ->where('Estado', 'Alta')
							 ->orderBy('ApellidoNombre', 'asc')
							 ->get();
		//resto 1 mes
		$datos = array('titulo'     => 'Cesarini - Info Cobranzas', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d'),
					   'empleados'  => $empleados );

		return $this->view->render($response, 'ctasctes/infocobranzas/infocobranzas.twig', $datos);
	}

	/**
	 * Arma listado del informe
	 * Name: 'ctasctes.infocobranzas.armainfocobranzas'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function armaInfoCobranzas($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idcli=8
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$listado = $this->_getListaCob( $request->getParam('idcli'),
									    $request->getParam('idrep'),
									    $request->getParam('desde'), 
									    $request->getParam('hasta') );

		$periodo = $this->utils->getPeriodo($request->getParam('desde'), $request->getParam('hasta'), $listado);
		$total   = $this->_sumaTotal($listado);
		// $fechaTransp = Calcular fecha trasnporte...
		// $transporte = $this->_getTransporte($request->getParam('desde'));

// echo "<br><pre>";
//print_r($listado);
//echo "</pre><br>";
//echo "Periodo: ".$periodo;
//echo "<br><br>";

//die('Ver...');

		$datos = [ 'titulo'  => 'Cesarini - Info Cobranzas',
				   'fecha'   => $fecha,
				   'periodo' => $periodo,
				   //'fechatrans' => '00/00/00',   // $fechaTransp,
				   //'transporte' => 0.0,   //$transporte,
				   'total'   => $total,
				   'listado' => $listado ];

		return $this->view->render($response, 'ctasctes/infocobranzas/imprimecobranzas.twig', $datos);
	}

	/**
	 * Busca el listado de cobranzas
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _getListaCob($idcli, $idrep, $desde, $hasta)
	{
		$sql = $this->_sqlVisitaDetCli($idcli, $idrep);

		$sql = $sql . $this->utils->getWhereFechas($desde, $hasta);
		$sql = $sql . " ORDER BY vis.Fecha ASC, vdc.IdVisita ASC";

		# Data de cobranzas en visitas...
		$visitas = $this->pdo->pdoQuery($sql);

		# Si no filtra por repartidor...
		if ($idrep == 0) {
			# Data de Comprobantes 'RE'
			$recCobro = $this->_compRecCobro($idcli, $desde, $hasta);
		} else $recCobro = [];

		// Junto comprobantes con visitas
		if (count($recCobro) > 0) {
			$list = array_merge($visitas, $recCobro);
		} else {
			$list = $visitas;
		}

		// Ordena por fecha
		usort($list, array($this, '_orden'));

		return $list;
	}

	/**
	 * Devuelve string con sql para listado cobors en Visitas
	 * 
	 * @return string
	 */
	private function _sqlVisitaDetCli($idcli, $idrep)
	{
		$sql = "SELECT vis.Fecha, vdc.IdVisita, ";
		$sql = $sql ."IF(vis.IdGuiaReparto = 0,  'En planta', 'Reparto') Comprobante, ";
		$sql = $sql ."vis.IdEmpleado, emp.ApellidoNombre Empleado, ";
		$sql = $sql . "vdc.IdCliente, cli.ApellidoNombre, vdc.Entrega ";
		$sql = $sql . "FROM VisitaDetalleClientes vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "LEFT JOIN Empleados AS emp ON vis.IdEmpleado = emp.Id ";
		$sql = $sql . "LEFT JOIN Clientes as cli on vdc.IdCliente = cli.Id ";
		$sql = $sql . "WHERE vdc.Entrega > 0 ";

		if ($idcli > 0) {
			$sql = $sql . "AND vdc.IdCliente = " . $idcli . " ";
		}

		if ($idrep > 0) {
			$sql = $sql . "AND vis.IdEmpleado = " . $idrep . " ";
		}

		return $sql;
	}


	private function _compRecCobro($idcli, $desde, $hasta)
	{
		$sql = "SELECT Fecha, CONCAT(TipoForm, ' ', Tipo, ' ', ";
		$sql = $sql . "LPAD(Sucursal, 4, '0'), '-', LPAD(NroComprobante, 8, '0')) AS ";
		$sql = $sql . "Comprobante, IdCliente, Firma AS ApellidoNombre, ";
		$sql = $sql . "Concepto AS Empleado, Total AS Entrega ";
		$sql = $sql . "FROM Comprobantes AS vis WHERE TipoForm = 'RE' AND ";
		$sql = $sql . "IdCliente = " . $idcli . " ";

		$sql = $sql . $this->utils->getWhereFechas($desde, $hasta); 

		$sql = $sql . "ORDER BY vis.Fecha ASC";

		// Comprobantes
		$recibosCobro = $this->pdo->pdoQuery($sql);

		return $recibosCobro;
	}


	private function _sumaTotal($list)
	{
		$total = 0;
		foreach ($list as  $value) {
			$total = $total + (float) $value['Entrega'];
		}

		return $total;
	}

	/**
	 * Para ordenar array por fecha
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _orden($a, $b)
	{
		return strcmp($a["Fecha"], $b["Fecha"]);
	}


}
