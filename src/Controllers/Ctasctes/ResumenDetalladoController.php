<?php

namespace App\Controllers\Ctasctes;


use App\Models\Cliente;

use App\Controllers\Controller;


/**
 *
 * Clase ResumenDetalladoController
 * 
 */
class ResumenDetalladoController extends Controller
{
	/**
	 * Resumen Detallado (Pantalla principal)
	 * Name: 'ctasctes.resumendetallado'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function resumenDetallado($request, $response)
	{
	    $fecha_actual = $this->utils->getFechaActual("Y-m-d");

		$datos = array('titulo'     => 'Cesarini - Resumen Detallado', 
					   //resto 1 mes para fecha desde
					   'fechaDesde' => date("Y-m-d",strtotime($fecha_actual."- 1 month")),
					   'fechaHasta' => date('Y-m-d') );

		return $this->view->render($response, 'ctasctes/resumendetallado/resumendetallado.twig', $datos);
	}

	/**
	 * Muestra el resumen detallado
	 * Name: 'ctasctes.verresumendetallado'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function verResumenDetallado($request, $response)
	{
	    $fecha = $this->utils->getFechaActual("d/m/Y");

		$cliente = Cliente::find($request->getParam('idcli'));
		// Busco dispensers del cliente
		$dispensers = $this->utils->stringDispensersClie($request->getParam('idcli'));

	    // Datos Resumen cuenta
	      // Calcular Transporte...
			// Datos del haber
			$haberTrans = $this->ResumenController->getHaber(true, $request->getParam('idcli'), $request->getParam('desde'));
			// Datos del debe
			$debeTrans  = $this->ResumenController->getDebe(true, $request->getParam('idcli'), $request->getParam('desde') );
			// Armo los movimietos del transp
			$movsTrans = $this->ResumenController->juntoMovimientos( $debeTrans, $haberTrans );
			// Calcular los saldos del transp
			$movsTrans = $this->ResumenController->calculoSaldos($movsTrans, 0);
			// Calculo fecha transporte
			$fechaTransp = $this->ResumenController->calcFechaTransp($request->getParam('desde'), $movsTrans);
			// Importe transporte
			$transporte = (count($movsTrans) === 0 ) ? 0 : $movsTrans[count($movsTrans)-1]['Saldo'];
		// Buscar datos del resumen
			# Movimientos DEBE y HABER
			$haber = $this->ResumenController->getHaber( false, $request->getParam('idcli'), 
				                       $request->getParam('desde'), 
				                       $request->getParam('hasta') );
			$debe  = $this->ResumenController->getDebe( false, $request->getParam('idcli'), 
				                      $request->getParam('desde'), 
				                      $request->getParam('hasta') );
			# Junto los movimientos DEBE Y HABER
			$resumen = $this->ResumenController->juntoMovimientos( $debe, $haber );
			# Calculo los saldos desde el transporte
			$resumen = $this->ResumenController->calculoSaldos($resumen, $transporte);

			// Si NO tiene detalle de resumen (por el rango), el saldo es igual al transporte
			if (count($resumen) === 0) {
				$saldo = $transporte;
			} else {
				$saldo = $resumen[count($resumen)-1]['Saldo'];
			}

			// Cargo datos del debe al formato de las visitas
			$dataDebe = $this->_creaDataDebe( $request->getParam('idcli'), 
				                      		  $request->getParam('desde'), 
				                    		  $request->getParam('hasta') );
	    // Datos Visitas
			$dataVisitas = $this->_datosVisitas( $request->getParam('idcli'), 
				                    			 $request->getParam('desde'), 
				                    			 $request->getParam('hasta') );
	    // Unir todo
	    $dataResumen = $this->_unirDebeConVisitas($dataDebe, $dataVisitas, $transporte);
		// Período del resumen
		$periodo = $this->ResumenController->getPeriodoRes($request->getParam('desde'), $request->getParam('hasta'), $resumen);
		// Movimientos de dispenser de cliente en el periodo
		$movsDispensers = $this->_movsDispensers( $request->getParam('idcli'), 
				                    			  $request->getParam('desde'), 
				                    			  $request->getParam('hasta') );

		$datos = [ 'titulo'     => 'Cesarini - Cta.Cte.Detallado',
				   'fecha'      => $fecha,
				   'periodo'    => $periodo,
				   'cliente'    => $cliente,
				   'fechatrans' => $fechaTransp,
				   'transporte' => $transporte,
				   'saldo'      => $saldo,
				   'dispensers' => $dispensers,
				   'movsdisp'   => $movsDispensers,
				   'listado'    => $dataResumen ];

	    return $this->view->render($response, 'ctasctes/resumendetallado/imprimeResumDetal.twig', $datos);
	}

	private function _unirDebeConVisitas($debe, $visitas, $transp)
	{
		$data = array_merge($debe, $visitas);
		// Ordena por fecha
		usort($data, array($this, '_ordena'));
		// Calculo los saldos 
		$arrLen = count($data);

		for ($i=0; $i < $arrLen ; $i++) { 
			# Si es el primer registro
			if ($i === 0) {
				$data[$i]['Saldo'] = ($transp + $data[$i]['Debito']) - $data[$i]['Entrega'];
			} else {
				$data[$i]['Saldo'] = ($data[$i-1]['Saldo'] + ($data[$i]['Debito'] === '' ? 0 : $data[$i]['Debito'])) - ($data[$i]['Entrega'] === '' ? 0 : $data[$i]['Entrega']);
			}
		}

		return $data;
	}

	/**
	 * Para ordenar array por fecha
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _ordena($a, $b)
	{
		return strcmp($a["Fecha"], $b["Fecha"]);
	}

	/**
	 * Devuelve datos de todas las visitas al cliente
	 * 
	 * @param  integer $id
	 * @param  string $desde
	 * @param  string $hasta
	 * @return array
	 */
	private function _datosVisitas($id, $desde, $hasta)
	{
		$data = [];
		$sql2 = "SELECT visi.Fecha, ";
		$sql2 = $sql2."CONCAT('Reparto/Visita - ', visi.Id) AS Comprobante, ";
		$sql2 = $sql2."CONCAT(guia.DiaSemana, '-', guia.Turno) AS DiaTurno, ";
		$sql2 = $sql2."empl.ApellidoNombre AS Empleado, domi.Direccion AS Domicilio, ";
		$sql2 = $sql2."prod.Descripcion AS Producto, cant.CantStock AS Stock, ";
		$sql2 = $sql2."cant.CantDejada AS Deja, cant.CantRetira AS Retira, ";
		$sql2 = $sql2."cant.Saldo, cant.Entrega, cant.Debito ";
		$sql2 = $sql2."FROM VisitaDetalleClientes AS cant ";
		$sql2 = $sql2."LEFT JOIN Visitas AS visi ON cant.IdVisita = visi.Id ";
		$sql2 = $sql2."LEFT JOIN GuiaRepartos AS guia ON visi.IdGuiaReparto = guia.Id ";
		$sql2 = $sql2."LEFT JOIN Empleados AS empl ON visi.IdEmpleado = empl.Id ";
    	$sql2 = $sql2."LEFT JOIN Productos AS prod ON cant.IdProducto = prod.Id ";
    	$sql2 = $sql2."LEFT JOIN Clientes AS clie ON cant.IdCliente = clie.Id ";
    	$sql2 = $sql2."LEFT JOIN ClientesDomicilio AS domi ON cant.IdDomicilio = domi.Id ";
    	$sql2 = $sql2."WHERE cant.IdCliente = ";
    	// Agrego Id cliente
    	$sql2 = $sql2 . $id;
    	// Armo where con fechas
		$sql2 = $sql2 . " AND " . $this->VisitasListadoController->getWhereFechas( $desde, $hasta );
		$sql2 = $sql2 . " ORDER BY visi.Fecha ASC";

		# Data del cliente...
		$data = $this->pdo->pdoQuery($sql2);

		return $data;
	}

	/**
	 * Modifica array del Debe y lo adapta a array de visitas
	 * 
	 * @param  integer $idcli
	 * @param  string $desde
	 * @param  string $hasta
	 * @return array
	 */
	private function _creaDataDebe($idcli, $desde, $hasta)
	{
		$debe = $this->_debeComprobantes($idcli, $desde, $hasta);
		$data = [];

		foreach ($debe as $value) {
			$temp = [ 'Fecha'       => $value['Fecha'],
					  'Comprobante' => $value['Comprobante'],
					  'DiaTurno'    => '', 
					  'Empleado'    => '', 
					  'Domicilio'   => '',
					  'Producto'    => $value['Concepto'], 
					  'Stock'       => '', 
					  'Deja'        => '', 
					  'Retira'      => '', 
					  'Saldo'       => '', 
					  'Entrega'     => 0, 
					  'Debito'      => $value['Importe'] ];

			$data[] = $temp;
			unset($temp);
		}
		return $data;
	}

	/**
	 * Obtiene datos de comprobantes, sin los débitos de visitas
	 * 
	 * @param  integer $idcli
	 * @param  string $desde
	 * @param  string $hasta
	 * @return array
	 */
	private function _debeComprobantes($idcli, $desde, $hasta)
	{
		$sql = "SELECT Fecha, TipoForm, CONCAT(TipoForm, ' ', Tipo, ' ', ";
		$sql = $sql . "LPAD(Sucursal, 4, '0'), '-', LPAD(NroComprobante, 8, '0')) AS ";
		$sql = $sql . "Comprobante, IdCliente, Firma, Concepto, ";
		$sql = $sql . "IF(TipoForm = 'NC' OR TipoForm = 'RE', Total * -1, Total) Importe ";
		$sql = $sql . "FROM Comprobantes AS vis WHERE IdCliente = " . $idcli . " ";
		$sql = $sql . $this->ResumenController->getWhereFechas($desde, $hasta); 
		$sql = $sql . "ORDER BY vis.Fecha ASC";
		// Comprobantes
		$debe = $this->pdo->pdoQuery($sql);

		return $debe;
	}

	/**
	 * Devuelve movimientos de dispensers del cliente en el período
	 * 
	 * @param  integer $idcli
	 * @param  date $desde
	 * @param  date $hasta
	 * @return array
	 */
	private function _movsDispensers($idcli, $desde, $hasta)
	{
		$sql = "SELECT md.IdCliente, md.Fecha, md.IdDispenser, dis.NroInterno, ";
		$sql .= "dis.Modelo, md.Observaciones, md.Estado ";
		$sql .= "FROM MovimientosDispenser md ";
		$sql .= "LEFT JOIN Dispenser AS dis ON md.IdDispenser = dis.Id ";
		$sql .= "WHERE md.IdCliente = " . $idcli . " ";     // " AND md.Estado = 'Cliente' ";
		$sql .= $this->utils->getWhereFechas($desde, $hasta, $asTable = 'md');
		$sql .= " ORDER BY md.Fecha ASC";

		$movs = $this->pdo->pdoQuery($sql);

		return $movs;
	}

}
