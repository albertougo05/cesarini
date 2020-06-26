<?php 

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\MovimientoDispenser;
use App\Controllers\Controller;


/**
 * Url: /repartos/informemovdispenser
 * 
 */
class InformeMovDispenserController extends Controller
{
	/**
	 * Listado inicial por Fecha desc
	 * Name: repartos.informemovdispenser
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Listado por fecha
	 */
	public function informeMovDispenser($request, $response)
	{
		if (empty($request->getParams())) {
			// Si no hay parametros es inicio
			$urlParam = "?column=Fecha&orden=desc";
		} else $urlParam = "?".$_SERVER['QUERY_STRING'];

		$_SESSION['urlParam'] = $urlParam;

		$txtOrdenadoPor = 'Fecha (desc)';
		// Parametros iniciales
		$urlParams = ['column' => 'Fecha', 'orden' => 'desc', 'ordFecha' => '',
				      'desde'  => '', 'hasta' => '', 'idDisp' => '', 'idClie' => '',
                  	  'estado' => '' ];

		$estados = ['Stock', 'Service', 'Cliente', 'Baja'];
		$listado  = $this->_getMovimientos($urlParams);
		// Lista de clientes para modal filtro de clientes
		$clientes = $this->_buscarClientesConMovDisp();
		$accion   = 'Informe movim. dispenser';

		$datos = [ 'titulo'         => 'Cesarini - Informe mov. disp.',
				   'accion'         => $accion,
				   'txtOrdenadoPor' => $txtOrdenadoPor,
				   'paramImpr'      => $urlParam,
				   'urlParams'      => $urlParams,
				   'clientes'       => $clientes,
				   'estados'        => $estados,
				   'listado'        => $listado ];

		return $this->view->render($response, 'repartos/movimdispenser/informe.twig', $datos);
	}

	/**
	 * Imprime Informe de dispensers
	 * 
	 * Url: /repartos/imprimeinforme
	 * (Name: repartos.imprimeinforme)
	 * 
	 */
	public function imprimeInforme($request, $response)
	{
		// Parametros a un array
		$urlParams = $this->_parametrosUrl($request);

		// Obtengo listado ordenado
		$listado = $this->_getMovimientos($urlParams);

		// Texto para mostrar orden y filtro fecha
		$txtOrdenadoPor = $this->_textoOrden($urlParams);

		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$datos = [ 'titulo'  => 'Cesarini - Imprime Info Mov.Disp.',
				   'accion'  => $txtOrdenadoPor,
				   'fecha'   => $fecha,
				   'listado' => $listado ];

		return $this->view->render($response, 'repartos/movimdispenser/imprimeinfo.twig', $datos);
	}

	/**
	 * Ordenar informe movim. Dispenser
	 * Name: repartos.ordenainforme
	 * 
	 * @param  $request
	 * @param  $response
	 * @return View
	 */
	public function ordenaInforme($request, $response)
	{
		$_SESSION['urlParam'] = "?".$_SERVER['QUERY_STRING'];
		// Parametros a un array
		$urlParams = $this->_parametrosUrl($request);
		// Obtengo listado ordenado
		$listado = $this->_getMovimientos($urlParams);
		// Texto para mostrar orden y filtro fecha
		$txtOrdenadoPor = $this->_textoOrden($urlParams);
		// Lista de clientes para modal filtro de clientes
		$clientes = $this->_buscarClientesConMovDisp();
		$estados = ['Stock', 'Service', 'Cliente', 'Baja'];

		$datos = [ 'titulo'         => 'Cesarini - Informe mov. disp.',
				   'accion'         => 'Informe movim. dispenser',
				   'listado'        => $listado,
				   'urlParams'      => $urlParams,
				   'paramImpr'      => $_SESSION['urlParam'],
				   'clientes'       => $clientes,
				   'estados'        => $estados,
				   'txtOrdenadoPor' => $txtOrdenadoPor ];

		return $this->view->render($response, 'repartos/movimdispenser/informe.twig', $datos);
	}

	/**
	 * Pasa a un array los parametros de la url
	 * 
	 * @param  Request $req
	 * @return Array
	 */
	private function _parametrosUrl($req)
	{
		$column   = $req->getParam('column');
		$orden    = $req->getParam('orden');
		// Filtro por fecha
		$desde    = $req->getParam('desde');
		$hasta    = $req->getParam('hasta');
		$ordFecha = $req->getParam('ordFecha');
		// Filtro por dispenser
		$idDisp = $req->getParam('idDisp');
		// Filtro por cliente
		$idClie = $req->getParam('idClie');
		// Filtro por estado
		$estado = $req->getParam('estado');

		// Si la columna es Fecha y el orden desc (inicial), y hay filtro de fecha...
		if ($ordFecha != '') {
			if ($column === 'Fecha') {
				// Cambio el orden, al orden de la fecha
				$orden = $ordFecha;
			}
		}

  		$urlParams = [ 'column'   => $column,
					   'orden'    => $orden,
                  	   'ordFecha' => $ordFecha,
				       'desde'    => $desde, 
                   	   'hasta'    => $hasta,
                  	   'idDisp'   => $idDisp,
                 	   'idClie'   => $idClie,
                  	   'estado'   => $estado ];

        return $urlParams;
	}

	/**
	 * Devuelve json con datos de movimiento de disp
	 * Name: repartos.datamovimdisp
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return json
	 */
	public function dataMovimDisp($request, $response)
	{
		$movimDisp = MovimientoDispenser::find($request->getParam('idmov'));

		// Hay que pasar los datos a un array...
		$datos['Id']          = $movimDisp->Id;
		$datos['IdDispenser'] = $movimDisp->IdDispenser;
		$datos['NroSerie']    = $movimDisp->Dispenser->NroSerie;
		$datos['NroInterno']  = $movimDisp->Dispenser->NroInterno;
		$datos['Modelo']      = $movimDisp->Dispenser->Modelo;
		$datos['EstadoDisp']  = $movimDisp->Dispenser->Estado;
		$datos['Empleado']    = ($movimDisp->IdEmpleado == null) ? '' : $movimDisp->Empleado->ApellidoNombre;
		$datos['Cliente']     = ($movimDisp->IdCliente == null) ? '' : $movimDisp->Cliente->ApellidoNombre;
		$datos['Direccion']   = ($movimDisp->IdCliente == null) ? '' : $movimDisp->Cliente->Direccion;
		$datos['Localidad']   = ($movimDisp->IdCliente == null) ? '' : $movimDisp->Cliente->Localidad;
		$datos['Telefono']    = ($movimDisp->IdCliente == null) ? '' : $movimDisp->Cliente->Telefono;
		$datos['Celular']     = ($movimDisp->IdCliente == null) ? '' : $movimDisp->Cliente->Celular;
		$datos['Fecha']       = $movimDisp->Fecha;
		$datos['Estado']      = $movimDisp->Estado;
		$datos['Observaciones'] = $movimDisp->Observaciones;

		return json_encode($datos);
	}

	/**
	 * Buscar Ids de clientes con movimientos de dispenser
	 * 
	 * @return array con id y nombre de cliente
	 */
	private function _buscarClientesConMovDisp()
	{
		// Busco los ids de clie en los movimientos...
		$idsClie = MovimientoDispenser::select('IdCliente')
		                              ->where('Estado', '!=', 'Baja')
		                              ->Where('IdCliente', '!=', 0 )
                                      ->whereNotNull('IdCliente')
		                              ->distinct()
		                              ->get();
		// Array con los Ids...
		$arrIds = $idsClie->toArray();
		// Extraigo solo los ids...
		foreach ($arrIds as $key => $value) {
			foreach ($value as $k => $val) {
				$arr[] = $val;
			}
		}
		// Busco los nombres de cada id de cliente...
		$clientes = Cliente::select('Id', 'ApellidoNombre', 'Direccion', 'Localidad')
		                    ->whereIn('Id', $arr)
                            ->orderBy('ApellidoNombre')
		                    ->get();

		return $clientes->toArray();
	}


	private function _mensajesOrdena()
	{
		$mensajes = ['NroSerie'   => "Nro. de Serie",
					 'NroInterno' => "Nro. Interno",
					 'Modelo'     => "Modelo",
					 'Estado'     => "Estado",
					 'Empleado'   => "Empleado",
					 'Cliente'    => "Cliente",
					 'Fecha'      => "Fecha"];

		return $mensajes;
	}

	/**
	 * Armo texto para mostrar orden y filtro por pantalla
	 * 
	 * @param  array $params
	 * @return string
	 */
	private function _textoOrden($params)
	{
		// Mensajes de como está ordenado
		$mensajes = $this->_mensajesOrdena();
		$infoDesdeHasta = '';

		if ($params['desde'] != '' && $params['hasta'] == '') {
			$infoDesdeHasta = ", desde: ".$params['desde'];
		} elseif ($params['desde'] == '' && $params['hasta'] != '') {
			$infoDesdeHasta = ", hasta: ".$params['hasta'];
		} elseif ($params['desde'] != '' && $params['hasta'] != '') {
			$infoDesdeHasta = ", desde: ".$params['desde'].", hasta: ".$params['hasta'];
		} 
		// Filtro Fecha...
		if ($params['ordFecha'] == '') {
			$ordenFecha = '';
		} else $ordenFecha = " (".$params['ordFecha'].")";
		// Filtro id dispenser
		if (!empty($params['idDisp'])) {
			$filtroDisp = ". Id dispenser: ".$params['idDisp'];
		} else $filtroDisp = "";
		// Filtro id cliente
		if (!empty($params['idCli'])) {
			$filtroClie = ". Id cliente: ".$params['idClie'];
		} else $filtroClie = "";
		// Filtro estados
		if (!empty($params['estado'])) {
			$filtroEstado = ". Estado: ".$params['estado'];
		} else $filtroEstado = "";

		return $mensajes[$params['column']]." (".$params['orden'].")".$infoDesdeHasta.$ordenFecha.$filtroDisp.$filtroClie.$filtroEstado;
	}

	/**
	 * Obtengo los movimientos ordenados
	 * 
	 * @param array con parametros
	 * @return array con movimientos ordenados y filtrados por fecha
	 */

	private function _getMovimientos($params)
	{
		// Armar query...
		$select = "SELECT md.Id, md.IdDispenser, md.Fecha, md.IdEmpleado, md.IdCliente, md.IdDomicilio, md.Estado, md.Observaciones, dis.NroSerie AS NroSerie, dis.NroInterno, dis.Modelo, emp.ApellidoNombre AS Empleado, cli.ApellidoNombre AS Cliente FROM MovimientosDispenser md LEFT JOIN Dispenser dis ON md.IdDispenser = dis.Id LEFT JOIN Empleados emp ON md.IdEmpleado = emp.Id LEFT JOIN Clientes cli ON md.IdCliente = cli.Id";

		// Armo los where por Fecha, si vienen los parametros...
		if ($params['desde'] != '' && $params['hasta'] == '') {
			$whereFecha = " WHERE md.Fecha >= '".$params['desde']."'";
		} elseif ($params['desde'] == '' && $params['hasta'] != '') {
			$whereFecha = " WHERE md.Fecha <= '".$params['hasta']."'";
		} elseif ($params['desde'] != '' && $params['hasta'] != '') {
			$whereFecha = " WHERE md.Fecha BETWEEN '".$params['desde']."' AND '".$params['hasta']."'";
		} else {
			// Vacio where fecha si NO hay parametros desde, hasta
			$whereFecha = "";
		}

		// Filtro por id de dispenser
		if (!empty($params['idDisp'])) {
			if ($whereFecha === '') {
				# si no hay where por fecha...
				$whereDisp = " WHERE md.IdDispenser = ".$params['idDisp'];
			} else $whereDisp = " AND md.IdDispenser = ".$params['idDisp'];

		} else $whereDisp = "";

		// Filtro por id de cliente
		if (!empty($params['idClie'])) {
			if ($whereFecha === '' && $whereDisp === '') {
				# si no hay where por fecha, ni id dispenser...
				$whereClie = " WHERE md.IdCliente = ".$params['idClie'];
			} else $whereClie = " AND md.IdCliente = ".$params['idClie'];

		} else $whereClie = "";

		// Filtro por estado
		if (!empty($params['estado'])) {
			if ($whereFecha === '' && $whereDisp === '' && $whereClie === "") {
				# si no hay where por fecha, ni id dispenser, ni id cliente...
				$whereEstado = " WHERE md.Estado = '".$params['estado']."'";
			} else $whereEstado = " AND md.Estado = '".$params['estado']."'";

		} else $whereEstado = "";

		if (empty($params['column'])) {
			// Por defecto ordena por fecha desc
			$ordenBy = " ORDER BY md.Fecha DESC, md.Id DESC";
		} else {
			// Si no hay orden por fecha...
			if ($params['ordFecha'] == '') {
			    $ordenBy = " ORDER BY ".$params['column']." ".mb_strtoupper($params['orden']);
			    // Agrego orden por fecha final para que los movimientos tengan orden desc
			    $ordenBy = $ordenBy.", md.Fecha DESC";
		    } else {
		    	// Ordena tambien por fecha si viene el filtro fecha...
		    	$ordenBy = " ORDER BY ".$params['column']." ".mb_strtoupper($params['orden']).", md.Fecha ".mb_strtoupper($params['ordFecha']);
			}
		}

		$query = $select.$whereFecha.$whereDisp.$whereClie.$whereEstado.$ordenBy;
		$data = $this->pdo->pdoQuery($query);

		return $data;
	}


}
