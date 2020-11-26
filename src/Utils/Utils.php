<?php 

namespace App\Utils;

use App\Models\Producto;
use App\Controllers\Controller;


/**
 * 
 * Clase con utilidades para todo el sistema
 * 
 */
class Utils extends Controller
{
	/**
	 * Convierte string de numero a float 
	 * (quita puntos de miles y cambia coma por punto decimal)
	 * 
	 * [ Ver en ProductosController: 
	 *   - 44 // Saco puntos de miles y cambio coma por punto decimal. Convierto a float.
     *   - 45 $datos['Precio'] = (float) preg_replace(['/\./', '/,/'],['', '.'], $datos['Precio']);
	 * ]
	 * 
	 * @param  string $cadena
	 * @return float 
	 */
	public function convStrToFloat($cadena)
	{
		$sinPuntos  = str_replace('.', '', $cadena);
  		$cambioComa = str_replace(',', '.', $sinPuntos);
  		$flotante   = floatval($cambioComa);

		return $flotante;
	}

	/**
	 * Calculo importe saldo. Une datos DEBE con HABER
	 * 
	 * @param  integer $idcli
	 * @param  date    $hasta ('Y-m-d')
	 * @return float
	 */
	public function getSaldo($idcli, $hasta='')
	{
		$haber = $this->_getHaberSaldo( $idcli, $hasta );
		$debe  = $this->_getDebeSaldo( $idcli, $hasta );

		$saldo = $sumDebe = $sumHaber = 0;
		// Sumo los Debe
		foreach ($debe as $value) {
			$sumDebe += $value['Importe'];
		}
		// Sumo los haber
		foreach ($haber as $value) {
			$sumHaber += $value['Entrega'];
		}		

		return $saldo = $sumDebe - $sumHaber;
	}

	/**
	 * Busca el stock de envases de un producto de un cliente
	 *
	 * @param integer $idprod
	 * @param integer $idcli
	 * @return integer
	 */
	public function stockEnvases( $idprod, $idcli )
	{
		$prod   = Producto::find($idprod);
		$conEnv = $prod->ConStock;
		$stock  = 0;

		if ($conEnv === 1) {
			$sql = "SELECT SUM(CantDejada) AS Ventas, ";
			$sql = $sql . "SUM(CantRetira) AS Envases, ";
			$sql = $sql . "SUM(CantDejada)-SUM(CantRetira) AS Stock ";
			$sql = $sql . "FROM VisitaDetalleClientes WHERE IdProducto = ";
			$sql = $sql . $idprod . " AND IdCliente = " . $idcli;
			$sql = $sql . " GROUP BY IdProducto";

			$calcStk = $this->pdo->pdoQuery($sql);
			if ( $calcStk ) {
				$stock = $calcStk[0]['Stock'];
			}
		}

		return $stock;
	}

	/**
	 * Devuelve string con dispensers del cliente
	 * 
	 * @param  integer $id 
	 * @return string "[T0001] [F002] ..."
	 */
	public function stringDispensersClie( $id )
	{
		$lista = $this->DispenserController->dispensersDeCliente( $id );
		$dispens = "";
		foreach ($lista as $value) {
			$dispens = $dispens . "[" . $value['NroInterno'] . "] ";
		}

		return $dispens;
	}

	/**
	 * Devuelve la fecha según formato pasado
	 * 
	 * @param  string $formato
	 * @return string
	 */
	public function getFechaActual($formato)
	{
		date_default_timezone_set("America/Buenos_Aires");
		$fecha = date($formato);

		return $fecha;
	}


	/**
	 * Obtiene los datos del DEBE (Comprobantes)
	 * 
	 * @param date $hasta
	 * @return Devuelve un array con datos del debe (facturación)
	 */
	private function _getDebeSaldo( $idcli, $hasta='' )
	{
		$sql = "SELECT IF( TipoForm = 'NC' OR  TipoForm = 'RE', Total * -1, Total ) Importe ";
		$sql = $sql . "FROM Comprobantes WHERE IdCliente = " . $idcli . " ";
		$sql = $sql . "AND Fecha <= '" . $hasta . "' ";
		$sql = $sql . "ORDER BY Fecha ASC";

		$debeComp = $this->pdo->pdoQuery($sql);

		// Debitos en visitas
		$debitVisit = $this->_debitVisitSaldo( $idcli, $hasta );

		// Junto comprobantes con debitos
		if (count($debitVisit) > 0) {
			$debe = array_merge($debeComp, $debitVisit);
		} else {
			$debe = $debeComp;
		}

		return $debe;
	}

	/**
	 * Lista de debitos en Visitas
	 * 
	 * @param  int $idcli
	 * @param  string $hasta
	 * @return array
	 */
	private function _debitVisitSaldo($idcli, $hasta='')
	{
		$sql = "SELECT vis.Fecha, vdc.Debito AS Importe ";
		$sql = $sql . "FROM VisitaDetalleClientes vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "WHERE vdc.Debito > 0 AND vdc.IdCliente = " . $idcli . " ";
		$sql = $sql . "AND Fecha <= '" . $hasta . "' ";
		$sql = $sql . "ORDER BY vis.Fecha ASC";

		$debitos = $this->pdo->pdoQuery($sql);

		return $debitos;	
	}

	/**
	 * Busca el listado de cobranzas (HABER)
	 *
	 * @param integer $idcli
	 * @param date $hasta
	 * @return array
	 */
	private function _getHaberSaldo( $idcli, $hasta='' )
	{
		$sql = "SELECT vdc.Entrega FROM VisitaDetalleClientes AS vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "WHERE vdc.Entrega > 0 ";
		$sql = $sql . "AND vdc.IdCliente = " . $idcli . " ";
		$sql = $sql . "AND vis.Fecha <= '" . $hasta . "' ";
		$sql = $sql . "ORDER BY vis.Fecha ASC";

		# Data del informe cobranzas...
		$haber = $this->pdo->pdoQuery($sql);

		return $haber;
	}

	/**
	 * Devuelve string con where de fechas
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	public function getWhereFechas($desde, $hasta, $asTable = 'vis')
	{
		if ($desde == '' && $hasta == '') {
			$where = '';
		} elseif ($hasta == '') {
			$desde = date('Y-m-d', strtotime($desde));
			$where = "AND " . $asTable . ".Fecha >= '".$desde."' ";
		} elseif ($desde == '') {
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "AND " . $asTable . ".Fecha <= '".$hasta."' ";
		} else {
			$desde = date('Y-m-d', strtotime($desde));
			$hasta = date('Y-m-d', strtotime($hasta));
			$where = "AND " . $asTable . ".Fecha >= '".$desde."' AND " . $asTable . ".Fecha <= '".$hasta."' ";
		}

		return $where;
	}

	/**
	 * Armo texto del periodo para informes
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	public function getPeriodo($desde, $hasta, $lista)
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



}
