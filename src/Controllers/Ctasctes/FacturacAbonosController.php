<?php

namespace App\Controllers\Ctasctes;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\Comprobante;
use App\Models\GuiaReparto;
use App\Models\ClienteReparto;
use DateTime;

use App\Controllers\Controller;


/**
 *
 * Clase
 * 
 */
class FacturacAbonosController extends Controller
{
	/**
	 * Facturacion de abonos por períodos
	 * Name: 'ctasctes.factabonos'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function factAbonos($request, $response)
	{
		$_SESSION['IdFactAbonos'] = "factAbonos@".date('Y-m-d');    // Id para validar la carga del abono
		$fechasPeriodo = $this->_fechasPeriodo();	// Establecer fechas de periodo abono (array)
		//$clientes = $this->_clientesConAbonoFact( $fechasPeriodo['periodo'] );	// Clientes con abono
		$guiasRep = $this->_listaGuiasReparto(); 

		$datos = array('titulo'     => 'Cesarini - Fact. Abonos', 
					   'periodo'    => $fechasPeriodo['periodo'],
					   'fechaActual' => $fechasPeriodo['actual'],
					   'fechaDesde' => $fechasPeriodo['desde'],
					   'fechaHasta' => $fechasPeriodo['hasta'],
					   //'clientes'   => $clientes,
					   'guiasRep'   => $guiasRep );

		return $this->view->render($response, 'ctasctes/factabonos/factabonos.twig', $datos);
	}

	/**
	 * Devuelve HTML para tabla de productos entregados en un periodo 
	 * Name: ctasctes.factabonos.productos (GET)
	 * 
	 * @param  [type] $request  [description]
	 * @param  [type] $response [description]
	 * @return string HTML
	 */
	public function productos($request, $response)
	{
		$idcli = $request->getParam('id');
		$desde = $request->getParam('desde');
		$hasta = $request->getParam('hasta');
		$productos = [];
		$sql = "SELECT vdc.IdProducto, pro.Descripcion AS Producto, sum(vdc.CantDejada) AS Dejado ";
		$sql = $sql . "FROM VisitaDetalleClientes as vdc ";
		$sql = $sql . "LEFT JOIN Productos AS pro ON vdc.IdProducto = pro.Id ";
		$sql = $sql . "WHERE vdc.IdVisita IN (SELECT Id FROM Visitas ";
		$sql = $sql . "WHERE Fecha >= '" . $desde . "' ";
		$sql = $sql . "AND Fecha <= '" . $hasta . "') ";
		$sql = $sql . "AND vdc.IdProducto > 0 AND vdc.CantDejada > 0 ";
		$sql = $sql . "AND vdc.IdCliente = " . $idcli . " GROUP BY vdc.IdProducto";

		$productos = $this->pdo->pdoQuery($sql);
		//$productos = array('IdProducto' => 7, 'Producto' => 'Bidon x 20 lts.', 'Dejado' => 5);

		if (count($productos) === 0) {
			# Si no hay productos
			echo "<tr><td><strong>Sin Productos en el período</strong></td><td></td></tr>";
		} else {
			foreach ($productos as $value) {
				//echo "<li>" . $value['Producto'] . " - <strong>" . $value['Dejado'] . "</strong> unid.</li>";
				echo "<tr><td>" . $value['Producto'] . "</td><td class='cellRight'><strong>" . $value['Dejado'] . "</strong></td></tr>";
			}
		}
	}

	/**
	 * Genera comprobante confirmado por usuario
	 * Name: ctasctes.factabonos.confirmcomprob (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function confirmComprob($request, $response)
	{
		$status = 'Error';

		if ( $_SESSION['IdFactAbonos'] === "factAbonos@".date('Y-m-d') ) {
			# Validar por session id...
	    	date_default_timezone_set("America/Buenos_Aires");
	    	$fecha = date('Y-m-d');
			$nroComp = $this->_getNroComprob('FA', 'B');

			$datos = [ 'Fecha'          => $fecha,
					   'TipoForm'       => 'FA', 
					   'Tipo'           => 'B', 
					   'Sucursal'       => 1,
					   'NroComprobante' => $nroComp,
					   'IdCliente'      => $request->getParam('idcli'),
					   'Firma'          => $request->getParam('firma'),
					   'Concepto'       => "Abono ".$request->getParam('periodo'),
					   'PeriodoFact'    => $request->getParam('periodo'),
					   'FechaDesde'     => $request->getParam('desde'),
					   'FechaHasta'     => $request->getParam('hasta'),
					   'IdUsuario'      => $_SESSION['user'],
					   'Total'          => $this->utils->convStrToFloat( $request->getParam('importe') ) ];

			$id = Comprobante::insertGetId($datos);

			if ($id > 0) {
				$status = 'OK';
			} 
		}

		return json_encode( ["status" => $status] );
	}

	/**
	 * Devuelve todos los clientes con abono del periodo
	 * Name: ctasctes.factabonos.clientesabono (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function clientesAbono($request, $response)
	{
	    $periodo = [ 'periodo' => $request->getParam('periodo'),
	    			 'desde'   => $request->getParam('desde'), 
	    			 'hasta'   => $request->getParam('hasta') ];

		$clientes = $this->_clientesConAbonoFact( $periodo );

		return json_encode($clientes);
	}

	/**
	 * Devuelve clientes con abono de una guia de reparto
	 * Name: ctasctes.factabonos.clientesguiarep (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function clientesGuiaRep($request, $response)
	{
		$clientes = [];
		// Obtengo los registros de la tabla  //  select('IdCliente')
		$clieGuiaRep = ClienteReparto::distinct()
									 ->where('IdReparto', $request->getParam('idguia'))
									 ->orderBy('OrdenVisita')
									 ->get();

		#echo "<pre>"; print_r($clieGuiaRep->toArray()); echo "</pre><br>";

	    foreach ($clieGuiaRep as $value) {		// Verificar si tiene el periodo facturado
	    	// Busco el cliente que tenga abono...
	    	$cliente = Cliente::where([ ['Id', '=', $value->IdCliente], 
										['CostoAbono', '>', 0], 
	                                   	['Estado', '=', 'Activo'] ])
								->first();

			if ($cliente) {			// Si el cliente existe...

			#echo "Cliente con abono: $cliente->Id - $cliente->ApellidoNombre <br>";

				//$dataDomicil = ClienteDomicilio::find($value->IdClienteDomicilio);

				// Compruebo si tiene facturado ese periodo...
	    		$comp = Comprobante::where([ ['IdCliente', '=', $value->IdCliente], 
	                                     	 ['PeriodoFact', '=', $request->getParam('periodo')] ])
	    	                   	   ->first();
		    	if ($comp) {
		    		$periodoFact = 'si';
		    		$importe = $comp->Total;
		    	} else {
		    		$periodoFact = 'no';
		    		$importe = '';
		    	}

		    	$periodo    = ['desde' => $request->getParam('desde'), 'hasta' => $request->getParam('hasta')];
		    	$cantBx20   = $this->_cantBidones(7, $cliente->Id, $periodo);		// cant bidones x 20 lts. (id 7)
		    	$cantBx12   = $this->_cantBidones(8, $cliente->Id, $periodo);		// cant bidones x 12 lts. (id 8)
		    	$dispensers = $this->utils->stringDispensersClie($value->IdCliente); // String con dispensers

		    	# Armo el registro del cliente
		 		$clientes[] = array( 'Id' => $cliente->Id, 
		 							 'ApellidoNombre' => $cliente->ApellidoNombre, 
		 							 'Direccion'      => $cliente->Direccion,		//$dataDomicil
		 							 'Localidad'      => $cliente->Localidad, 	//$dataDomicil
		 							 'Observaciones'  => $cliente->Observaciones === null ? '' :  $cliente->Observaciones,
		 							 'Dispensers'     => $dispensers,
	 								 'cantBx20'       => $cantBx20,
	 								 'cantBx12'       => $cantBx12,
		 							 'CostoAbono'     => $cliente->CostoAbono,
		 							 'PeriodoFact'    => $periodoFact,
		 							 'ImporteFact'    => $importe );
		 	}
	 	}

#echo "<pre>"; print_r($clientes); echo "</pre><br>"; die('Ver ...');

	 	// Ordeno por ApellidoNombre
		//usort($clientes, array($this,'_ordenoArray'));

		return json_encode($clientes);
	}

	/**
	 * Devuelve fechas periodo segun fecha pasada
	 * Name: ctasctes.factabonos.fechaperiodo?fecha=2020-07-01 (GET)
	 * (Para modificación de fecha en pantalla)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function fechaPeriodo($request, $response)
	{
		$fechasPeriodo = $this->_fechasPeriodo( $request->getParam('fecha') );

		return json_encode($fechasPeriodo);
	}

	/**
	 * Funcion para ordenar array multidimensional
	 *
	 * @return integer (0, 1 or -1)
	 */
	private function _ordenoArray($a, $b)
	{
		if ($a == $b)
            return 0;

        return ($a['ApellidoNombre'] < $b['ApellidoNombre']) ? -1 : 1;
	}

	/**
	 * Devuelve fechas desde hasta del período abono y nombre
	 *
	 * @return array $periodo ['periodo' => 'ENE-2020', 'desde' => '2020-01-01', 'hasta' => '2020-01-31']
	 */
	private function _fechasPeriodo($fechaAct='')
	{
		date_default_timezone_set("America/Buenos_Aires");
		$meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SET', 'OCT', 'NOV', 'DIC'];

		if ($fechaAct === '') {
			
			$numMesActual = date('m');		// Num mes actual
			$numAnioActual = date('Y');		// Año actual
			$fechaAct = date('Y-m-d');

		} else {
			$fecha = new DateTime($fechaAct);
			$numMesActual = $fecha->format('m');
			$numAnioActual = $fecha->format('Y');
		}

		// Num mes anterior
		$numMesAnterior = (integer) $numMesActual - 1;
		$numMesAnterior = ($numMesAnterior === 0) ? 12 : $numMesAnterior;
		// Año del mes anterior
		if ($numMesAnterior === 12) {
		    $anioMesAnterior = (integer) $numAnioActual -1;
		} else {
		    $anioMesAnterior = $numAnioActual;
		}
		$primerDiaMesAnterior = date_format(date_create( $anioMesAnterior . "-" . $numMesAnterior . "-01" ), "Y-m-d" );
		$cantDiasMesAnterior  = cal_days_in_month(CAL_GREGORIAN, $numMesAnterior, $anioMesAnterior);
		$ultimoDiaMesAnterior = date_format(date_create( $anioMesAnterior . "-" . $numMesAnterior . "-" . $cantDiasMesAnterior ), "Y-m-d" );
		// String del periodo
		//$strPeriodo = $meses[(integer)$numMesAnterior -1]."-".$anioMesAnterior;
		$strPeriodo = $meses[(integer)$numMesActual -1]."-".$numAnioActual;  // Cambiado por pedido del 03/03/2020 (periodo es mes anterior al actual)

		$periodo = [ 'actual'  => $fechaAct,
					 'periodo' => $strPeriodo, 
		             'desde'   => $primerDiaMesAnterior, 
		             'hasta'   => $ultimoDiaMesAnterior ];

		return $periodo;
	}

	/**
	 * Lista de clientes con abono, verificando si tiene facturado el período
	 * 
	 * @return array 
	 */
    private function _clientesConAbonoFact($periodo)
    {
    	$clientes = [];
		$clientesDB = Cliente::where([ ['CostoAbono', '>', 0], 
	                                   ['Estado', '=', 'Activo'] ])
							 ->orderBy('ApellidoNombre', 'asc')
			            	 ->get();

	    foreach ($clientesDB as $value) {		// Verificar si tiene el periodo facturado
	    	$comp = Comprobante::where([ ['IdCliente', '=', $value->Id], 
	                                     ['PeriodoFact', '=', $periodo['periodo']] ])
	    	                   ->first();
	    	if ($comp) {
	    		$periodoFact = 'si';
	    		$importe = $comp->Total;
	    	} else {
	    		$periodoFact = 'no';
	    		$importe = '';
	    	}
	    	$cantBx20   = $this->_cantBidones(7, $value->Id, $periodo);		// cant bidones x 20 lts. (id 7)
	    	$cantBx12   = $this->_cantBidones(8, $value->Id, $periodo);		// cant bidones x 12 lts. (id 8)
	    	$dispensers = $this->utils->stringDispensersClie($value->Id);	// String con dispensers
	    	# Armo el registro del cliente
	 		$clientes[] = array( 'Id' => $value->Id, 
	 							 'ApellidoNombre' => $value->ApellidoNombre, 
	 							 'Direccion'      => $value->Direccion,
	 							 'Localidad'      => $value->Localidad, 
	 							 'Observaciones'  => $value->Observaciones === null ? '' :  $value->Observaciones,
	 							 'Dispensers'     => $dispensers,
	 							 'cantBx20'       => $cantBx20,
	 							 'cantBx12'       => $cantBx12,
	 							 'CostoAbono'     => $value->CostoAbono,
	 							 'PeriodoFact'    => $periodoFact,
	 							 'ImporteFact'    => $importe );
	 	}

		return $clientes;
    }

    /**
     * Devuelve número de comprobante disponible
     * 
     * @param  string $tipoForm
     * @param  string $tipo
     * @param  integer $suc
     * @return integer
     */
    private function _getNroComprob($tipoForm, $tipo, $suc=1)
    {
		$comprobante = Comprobante::select('NroComprobante')
								  ->where( [ ['TipoForm', '=', $tipoForm],
								             ['Tipo', '=', $tipo],
								      	     ['Sucursal', '=', $suc ] ] )
								  ->get();

		if ($comprobante->isNotEmpty()) {
			$numComp = (integer) $comprobante->last()->NroComprobante;
			$numComp++;

		} else {
			$numComp = 1;
		}

		return $numComp;
    }

    /**
     * Devuelve lista de Guias de reparto ordenadas por DiaSemana
     * 
     * @return array
     */
    private function _listaGuiasReparto()
    {
    	$diasSem = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
    	$guias = GuiaReparto::where('Estado', 'Vigente')->get();

    	foreach ($guias as $value) {
    		$numDia = array_search($value->DiaSemana, $diasSem, true);
    		$numDia = $numDia + 2; // Para que quede bien el día de la semana
    		$linea  = str_pad((string) $value->Id, 2, " ", STR_PAD_LEFT)." - ";
    		$linea  = $linea . str_pad($value->DiaSemana, 9)." - ";  // Miercoles
    		$linea  = $linea . str_pad($value->Turno, 6)." - ";  // Mañana
    		$linea  = $linea . str_pad($value->Empleado->ApellidoNombre, 30);

    		$guiasRep[] = [ 'id'       => $value->Id,
    	                    'numDia'   => $numDia,
    	                    'linea'    => $linea ];
//    	                    'diaSem'   => $value->DiaSemana,
//    	                    'turno'    => $value->Turno,
//    	                    'empleado' => $value->Empleado->ApellidoNombre ];
    	}
    	// Ordeno la lista por el número de día
    	usort($guiasRep, array($this, '_ordenar'));

#echo "<pre>"; print_r($guiasRep); echo "</pre><br>"; die('Ver...');

    	return $guiasRep;
    }

	/**
	 * Para ordenar array por número de día
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _ordenar($a, $b)
	{
		return strcmp($a["numDia"], $b["numDia"]);
	}

	/**
	 * Devuelve cantidad de bidones del cliente, según id de bidon
	 * 
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	private function _cantBidones($idProd, $idCli, $periodo)
	{
		$sql = "SELECT vdc.IdProducto, pro.Descripcion AS producto, SUM(vdc.CantDejada) AS dejado ";
		$sql = $sql . "FROM VisitaDetalleClientes AS vdc ";
		$sql = $sql . "LEFT join Productos AS pro ON vdc.IdProducto = pro.Id ";
		$sql = $sql . "WHERE vdc.IdVisita IN (SELECT Id FROM Visitas ";
		$sql = $sql . "WHERE Fecha >= '" . $periodo['desde'] . "' AND Fecha <= '" . $periodo['hasta'] . "') ";
		$sql = $sql . "AND vdc.IdCliente = " . $idCli;
		$sql = $sql . " AND vdc.IdProducto = " . $idProd;
		$sql = $sql . " AND vdc.CantDejada > 0 GROUP BY vdc.IdProducto;";

		$reg = $this->pdo->pdoQuery($sql);

		if (!$reg) {
			$cant = '';
		} else {
			$cant = $reg[0]['dejado'];
		}

		return $cant;
	}


}
