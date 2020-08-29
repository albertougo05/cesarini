<?php

namespace App\Controllers\Ctasctes;


use App\Models\Cliente;

use App\Controllers\Controller;


/**
 *
 * Clase ResumenController
 * 
 */
class ResumenController extends Controller
{
	/**
	 * Resumen de cuentas (Pantalla principal)
	 * Name: 'ctasctes.resumen'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function resumen($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");
	    $fecha_actual = date("Y-m-d");
		//resto 1 mes
		$datos = array('titulo'     => 'Cesarini - Resumen Cta.', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d') );

		return $this->view->render($response, 'ctasctes/resumen/resumen.twig', $datos);
	}

	/**
	 * Arma el resumen de cuenta
	 * Name: 'ctasctes.armaresumen'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function armaResumen($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idcli=8
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

	    // Calcular Transporte...
			// Datos del haber
			$haberTrans = $this->getHaber(true, $request->getParam('idcli'), $request->getParam('desde'));
			// Datos del debe
			$debeTrans  = $this->getDebe(true, $request->getParam('idcli'), $request->getParam('desde') );
			// Armo los movimietos del transp
			$movsTrans = $this->juntoMovimientos( $debeTrans, $haberTrans );
			// Calcular los saldos del transp
			$movsTrans = $this->calculoSaldos($movsTrans, 0);
			// Calculo fecha transporte
			$fechaTransp = $this->calcFechaTransp($request->getParam('desde'), $movsTrans);
			// Importe transporte
			$transporte = (count($movsTrans) === 0 ) ? 0 : $movsTrans[count($movsTrans)-1]['Saldo'];

//echo "Array debe transp: <br>";
//echo "<pre>";
//print_r($debeTrans);
//echo "</pre><br>";
//die('Ver arrays...');


		// Buscar datos del resumen
			# Movimientos DEBE y HABER
			$haber = $this->getHaber( false, $request->getParam('idcli'), 
				                       $request->getParam('desde'), 
				                       $request->getParam('hasta') );
			$debe  = $this->getDebe( false, $request->getParam('idcli'), 
				                      $request->getParam('desde'), 
				                      $request->getParam('hasta') );

			# Junto los movimientos DEBE Y HABER
			$resumen = $this->juntoMovimientos( $debe, $haber );
			# Calculo los saldos desde el transporte
			$resumen = $this->calculoSaldos($resumen, $transporte);

			// Si NO tiene detalle de resumen (por el rango), el saldo es igual al transporte
			if (count($resumen) === 0) {
				$saldo = $transporte;
			} else {
				$saldo = $resumen[count($resumen)-1]['Saldo'];
			}

//echo "Array debe: <br>";
//echo "<pre>";
//print_r($debe);
//echo "</pre><br>";
//die('Ver Resumen...');

//echo "<br><pre>"; print_r($resumen); echo "</pre><br>"; echo "Transporte: ".$transporte;
//echo "<br>Saldo: $saldo"; echo "<br>"; die('Ver...');

		// Busco dispensers del cliente
		$dispens = $this->DispenserController->dispensersDeCliente($request->getParam('idcli'));
		$periodo = $this->getPeriodoRes($request->getParam('desde'), $request->getParam('hasta'), $resumen);
		$cliente = Cliente::find($request->getParam('idcli'));

		$datos = [ 'titulo'     => 'Cesarini - Resumen Cta.',
				   'fecha'      => $fecha,
				   'periodo'    => $periodo,
				   'cliente'    => $cliente,
				   'fechatrans' => $fechaTransp,
				   'transporte' => $transporte,
				   'saldo'      => $saldo,
				   'dispens'    => $dispens,
				   'listado'    => $resumen ];

		return $this->view->render($response, 'ctasctes/resumen/imprimeresumen.twig', $datos);
	}

	/**
	 * Devuelve json con aldo actual 
	 * Name: ctasctes.saldoactual
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function saldoActual($request, $response)
	{
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('Y-m-d');

		$saldo = $this->utils->getSaldo( $request->getParam('id'), $fecha );
		$cliente = Cliente::find($request->getParam('id'));
		$abono = ($cliente->CostoAbono > 0) ? 1 : 0;

		return json_encode([ "saldo" => $saldo, "abono" => $abono ]);
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
			$where = '';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = "AND vis.Fecha >= '".$desde."' ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "AND vis.Fecha <= '".$hasta."' ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "AND vis.Fecha >= '".$desde."' AND vis.Fecha <= '".$hasta."' ";
		}

		return $where;
	}

	/**
	 * Armo texto del periodo para informe
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	public function getPeriodoRes($desde, $hasta, $lista)
	{
		# Si no hay fecha desde, busca primera fecha del listado
		if ($desde === '') {
			$desde = $lista[0]['Fecha'];
		}
		# Si no hay hasta, es la fecha de hoy
		if ($hasta === '') {
			$hasta = date("d/m/Y");
		}
		$desde = date('d/m/Y', strtotime($desde));
		$hasta = date('d/m/Y', strtotime($hasta));
		$periodo = "desde $desde, hasta $hasta";

		return $periodo;
	}

	/**
	 * Busca el listado de cobranzas (HABER)
	 * 
	 * @param bool $transp (si es para transporte)
	 * @param int $idcli
	 * @param date $desde
	 * @param date $hasta (optional)
	 * @return array
	 */
	public function getHaber($transp, $idcli, $desde, $hasta='')
	{
		$sql = "SELECT vis.Fecha, vdc.IdVisita, ";
		$sql = $sql ."CONCAT(IF(vis.IdGuiaReparto = 0,  'En planta - ', 'Reparto/Visita - '), vdc.IdVisita) AS Comprobante, ";
		$sql = $sql ."CONCAT('Entrega', ' en visita ', vdc.IdVisita) AS Concepto, ";
		$sql = $sql ."vis.IdEmpleado, emp.ApellidoNombre AS Empleado, ";
		$sql = $sql . "vdc.IdCliente, cli.ApellidoNombre, vdc.Entrega ";
		$sql = $sql . "FROM VisitaDetalleClientes vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "LEFT JOIN Empleados AS emp ON vis.IdEmpleado = emp.Id ";
		$sql = $sql . "LEFT JOIN Clientes as cli on vdc.IdCliente = cli.Id ";
		$sql = $sql . "WHERE vdc.Entrega > 0 ";
		$sql = $sql . "AND vdc.IdCliente = " . $idcli . " ";

		if ($transp) {

			$sql = $sql . "AND vis.Fecha < '" . $desde . "' ";

		} else { 

			$sql = $sql . $this->getWhereFechas($desde, $hasta); 
		}

		$sql = $sql . "ORDER BY vis.Fecha ASC, vdc.IdVisita ASC";

		# Data del informe cobranzas...
		$list = $this->pdo->pdoQuery($sql);

		return $list;
	}

	/**
	 * Obtiene los datos del DEBE (Comprobantes)
	 * 
	 * @param bool $transp (si es para transporte)
	 * @param int $idcli
	 * @param date $desde
	 * @param date $hasta (optional)
	 * @return Devuelve un array con datos del debe (facturación)
	 */
	public function getDebe($transp, $idcli, $desde, $hasta='')
	{
		$sql = "SELECT Fecha, TipoForm, CONCAT(TipoForm, ' ', Tipo, ' ', ";
		$sql = $sql . "LPAD(Sucursal, 4, '0'), '-', LPAD(NroComprobante, 8, '0')) AS ";
		$sql = $sql . "Comprobante, IdCliente, Firma, Concepto, ";
		$sql = $sql . "IF(TipoForm = 'NC' OR TipoForm = 'RE', Total * -1, Total) Importe ";
		$sql = $sql . "FROM Comprobantes AS vis WHERE IdCliente = " . $idcli . " ";

		if ($transp) {

			$sql = $sql . "AND vis.Fecha < '" . $desde . "' ";

		} else { 

			$sql = $sql . $this->getWhereFechas($desde, $hasta); 
		}

		$sql = $sql . "ORDER BY vis.Fecha ASC";

		// Comprobantes
		$debeComp = $this->pdo->pdoQuery($sql);

		// Debitos en visitas
		$debitosVisitas = $this->_debitosVisitas( $transp, $idcli, $desde, $hasta );

		// Junto comprobantes con debitos
		if (count($debitosVisitas) > 0) {
			$debe = array_merge($debeComp, $debitosVisitas);
		} else {
			$debe = $debeComp;
		}

		return $debe;
	}

	/**
	 * Listado de debitos en Visitas
	 * 
	 * @param  bool $transp 
	 * @param  int $idcli
	 * @param  string $desde
	 * @param  string $hasta
	 * @return array
	 */
	private function _debitosVisitas($transp, $idcli, $desde, $hasta='')
	{
		$sql = "SELECT vis.Fecha, CONCAT('Débito en Visita ') AS Concepto, ";
		$sql = $sql . "CONCAT('Débito - Reparto/Visita - ', vdc.IdVisita) AS Comprobante, ";
		$sql = $sql . "vdc.IdCliente, cli.ApellidoNombre, vdc.Debito AS Importe ";
		$sql = $sql . "FROM VisitaDetalleClientes vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "LEFT JOIN Clientes as cli on vdc.IdCliente = cli.Id ";
		$sql = $sql . "WHERE vdc.Debito > 0 AND vdc.IdCliente = " . $idcli . " ";

		if ($transp) {

			$sql = $sql . "AND vis.Fecha < '" . $desde . "' ";

		} else { 

			$sql = $sql . $this->getWhereFechas($desde, $hasta); 
		}

		$sql = $sql . "ORDER BY vis.Fecha ASC";

		$debitos = $this->pdo->pdoQuery($sql);

		return $debitos;	
	}

	/**
	 * Arma listado movimientos. Une datos DEBE con HABER
	 * 
	 * @param  array $deb 
	 * @param  array $hab
	 * @return array
	 */
	public function juntoMovimientos($deb, $hab)
	{
		$resumen = [];

		// Ingresar array debe...
		foreach ($deb as $value) {
			$temp = [ 'Fecha'       => $value['Fecha'],
					  'Comprobante' => $value['Comprobante'],
					  'Repartidor'  => '',   // $value['Empleado'],
					  'Concepto'    => $value['Concepto'],
					  'Debe'        => $value['Importe'],
					  'Haber'       => 0,
					  'Saldo'       => 0 ];
			$resumen[] = $temp;
		}

		// Ingresar array haber...
		foreach ($hab as $value) {
			$temp = [ 'Fecha'       => $value['Fecha'],
					  'Comprobante' => $value['Comprobante'],
					  'Repartidor'  => $value['Empleado'],
					  'Concepto'    => $value['Concepto'],
					  'Debe'        => 0,
					  'Haber'       => $value['Entrega'],
					  'Saldo'       => 0 ];
			$resumen[] = $temp;
		}		

		// Ordena por fecha
		usort($resumen, array($this, '_ordenar'));

		return $resumen;
	}

	/**
	 * Para ordenar array de resumen por fecha
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _ordenar($a, $b)
	{
		return strcmp($a["Fecha"], $b["Fecha"]);
	}

	/**
	 * Calculo los saldos del resumen
	 * y corto por la fecha desde 
	 * 
	 * @param  array $list
	 * @param  date $desde
	 * @return array
	 */
	public function calculoSaldos($list, $transp)
	{
		$arrLen = count($list);

		for ($i=0; $i < $arrLen ; $i++) { 
			# Si es el primer registro
			if ($i === 0) {
				$list[$i]['Saldo'] = ($transp + $list[$i]['Debe']) - $list[$i]['Haber'];
			} else {
				$list[$i]['Saldo'] = ($list[$i-1]['Saldo'] + $list[$i]['Debe']) - $list[$i]['Haber'];
			}
		}

		return $list;
	}

	/**
	 * Calculo fecha del transporte
	 * 
	 * @param  date $desde
	 * @param  array $list
	 * @return date
	 */
	public function calcFechaTransp($desde, $list)
	{
		if (empty($desde)) {
			$fecha = $list[0]['Fecha'];
		} else {
			$fecha = date("d/m/Y", strtotime($desde)); 
		}

		return $fecha;
	}


}
