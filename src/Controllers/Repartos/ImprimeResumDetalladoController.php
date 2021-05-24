<?php

namespace App\Controllers\Repartos;


use App\Models\Cliente;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 *
 * Clase ImprimeResumDetalladoController
 * 
 */
class ImprimeResumDetalladoController extends Controller
{
	/**
	 * Muestra el resumen detallado
	 * Name: 'repartos.visitas.impresumdetallado'
	 * 
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function imprimir($request, $response)
	{
		$dataResumen = [];
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = $this->utils->getFechaActual("d/m/Y");
	    $dataResumen = [];	// Donde van a ir los arrays de cada resumen
	    $fechaDesde = $request->getParam('desde');
	    $fechaHasta = $request->getParam('hasta');

	    if ( $request->getParam('id') !== null ) {
		    // Con Id de la visita busco el array de ids de clientes de la visita
		    $idsClientesVisita = $this->_buscarIdsClientesVisita($request->getParam('id'));	    	
	    } else {
	    	// Los ids vienen en la variable ids
	    	$idsClientesVisita = explode("-", $request->getParam('ids'));
	    }

		foreach($idsClientesVisita as $idcli) {
			$resumenClie = $this->_armarResumenCliente($idcli, $fechaDesde, $fechaHasta);
			$dataResumen[] = $resumenClie;
		}

	    // Array con los indices para salto de página
    	$idxsSaltoPag = [4,8,12,16,20,24,28,32,36,40,44,48,52,56,60,64,68,72,76,80,84,88,92,96,100,104,108,112,116,120];

		$datos = [ 'titulo'     => 'Cesarini - Visitas Resúmenes Detallado',
				   'fecha'      => $fecha,
				   'listado'    => $dataResumen,
				   'fechaHasta' => $fechaHasta,
				   'idxssalto'  => $idxsSaltoPag ];

	    return $this->view->render($response, 'repartos/visitas/imprimeResumDetalVisitas.twig', $datos);
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
		$sql2 = "SELECT visi.Fecha, cant.IdDomicilio, ";
		$sql2 = $sql2."CONCAT(cd.Direccion, ' - ', visi.Id) AS Comprobante, ";
		$sql2 = $sql2."CONCAT(guia.DiaSemana, '-', guia.Turno) AS DiaTurno, ";
		$sql2 = $sql2."prod.Descripcion AS Producto, cant.CantStock AS Stock, ";
		$sql2 = $sql2."cant.CantDejada AS Deja, cant.CantRetira AS Retira, ";
		$sql2 = $sql2."cant.Saldo, cant.Entrega, cant.Debito ";
		$sql2 = $sql2."FROM VisitaDetalleClientes AS cant ";
		$sql2 = $sql2."LEFT JOIN Visitas AS visi ON cant.IdVisita = visi.Id ";
		$sql2 = $sql2."LEFT JOIN GuiaRepartos AS guia ON visi.IdGuiaReparto = guia.Id ";
    	$sql2 = $sql2."LEFT JOIN Productos AS prod ON cant.IdProducto = prod.Id ";
    	$sql2 = $sql2."LEFT JOIN Clientes AS clie ON cant.IdCliente = clie.Id ";
    	$sql2 = $sql2."LEFT JOIN ClientesDomicilio AS cd ON cant.IdDomicilio = cd.Id ";
    	$sql2 = $sql2."WHERE cant.IdCliente = ";
    	// Agrego Id cliente
    	$sql2 = $sql2 . $id;
    	// Armo where con fechas
		$sql2 = $sql2 . " AND " . $this->VisitasListadoController->getWhereFechas( $desde, $hasta );
		$sql2 = $sql2 . " ORDER BY visi.Fecha ASC";
		// Obtengo data del cliente...
		$data = $this->pdo->pdoQuery($sql2);

		return $data;
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
	 * Retorna array con resumen de cuenta del cliente
	 * 
	 * @param  integer $id 
	 * @return object
	 */
	private function _armarResumenCliente($id, $desde, $hasta)
	{
		$resumenCta = [
			"cliente"    => Cliente::find($id),
			"fechatrans" => '',
			"transporte" => 0,
			"saldo"      => 0,
			"resumen"    => [],
			"dispensers" => [],
			//"saldoperiodo" => 0
			"consumoperiodo" => 0
		];

		$dataResumen2 = [];

	    // Calcular Transporte...
			// Datos del haber
			$haberTrans = $this->ResumenController->getHaber(true, $id, $desde);
			// Datos del debe
			$debeTrans  = $this->ResumenController->getDebe(true, $id, $desde);
			// Armo los movimietos del transp
			$movsTrans = $this->ResumenController->juntoMovimientos( $debeTrans, $haberTrans );
			// Calcular los saldos del transp
			$movsTrans = $this->ResumenController->calculoSaldos($movsTrans, 0);
			// Calculo fecha transporte
			$fechaTransp = $this->ResumenController->calcFechaTransp($desde, $movsTrans);
			// Importe transporte
			$transporte = (count($movsTrans) === 0 ) ? 0 : $movsTrans[count($movsTrans)-1]['Saldo'];
		// Buscar datos del resumen
			# Movimientos DEBE y HABER
			$haber = $this->ResumenController->getHaber(false, $id, $desde, $hasta);
			$debe  = $this->ResumenController->getDebe(false,  $id, $desde, $hasta);

			# Calcula saldo del periodo
			//$saldoperiodo = $this->_calculoSaldoPeriodo($debe, $haber);

			# Calcula consumos del período
			$consumoPeriodo = $this->_calculoConsumoPeriodo($debe);
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
		$dataDebe = $this->_creaDataDebe($id, $desde, $hasta);
	    // Datos Visitas
		$dataVisitas = $this->_datosVisitas($id, $desde, $hasta);
	    // Unir todo
	    $dataResumen = $this->_unirDebeConVisitas($dataDebe, $dataVisitas, $transporte);
		// Busco dispensers del cliente
		$dispensers = $this->utils->stringDispensersClie($id);

	    $resumenCta['fechatrans'] = $fechaTransp;
	    $resumenCta['transporte']  = $transporte;
	    $resumenCta['saldo']       = $saldo;
	    $resumenCta['resumen']     = $dataResumen;
		$resumenCta['dispensers']  = $dispensers;
		//$resumenCta['saldoperiodo'] = $saldoperiodo;
		$resumenCta['consumoperiodo'] = $consumoPeriodo;

		return $resumenCta;
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

	private function _buscarIdsClientesVisita($id)
	{
		$listado = VisitaDetalleCliente::select('IdCliente', 'OrdenVisita')
										->where('IdVisita', $id)
										->get();

		foreach ($listado as $value) {
			$ids[] = $value->IdCliente;
		}
		$collect = collect($ids);
		$unique = $collect->unique();

		return $unique;
	}

	private function _calculoSaldoPeriodo($debe, $haber) 
	{
		$sumaDebe = $sumaHaber = 0;

		foreach ($debe as $value) {
			$sumaDebe += $value['Importe'];
		}
		foreach ($haber as $value) {
			$sumaHaber += $value['Entrega'];
		}

		return $sumaDebe - $sumaHaber;
	}

	private function _calculoConsumoPeriodo($debe) 
	{
		$sumaDebe = 0;

		foreach ($debe as $value) {
			$sumaDebe += $value['Importe'];
		}

		return $sumaDebe;
	}

}
