<?php 

namespace App\Controllers\Productos;

use App\Models\Dispenser;
use App\Models\TipoDispenser;
use App\Models\MovimientoDispenser;
use App\Models\Cliente;
use App\Controllers\Controller;


/**
 * Url: /productos/informedispenser
 * Name: productos.informedispenser
 * 
 */
class InfoDispenserController extends Controller
{
	// Estado de filtro fecha (bool)
	private $_filtroFecha;
	// String para imprime informe
	private $_txtImprimeInforme;
	// Bool para saber si el informe va con detalle de clientes
	private $_conCliente;
	// Bool para saber si informe va con detalle movimiento
	private $_conMovim;


	public function informeDispenser($request, $response)
	{
		$estados   = ['Stock', 'Cliente', 'Service', 'Baja'];
		$tiposDisp = TipoDispenser::where('Id', '>', 0)->orderBy('Descripcion')->get();
		$clientes  = $this->_buscarClientesConMovDisp();

		$accion = 'Informe de dispenser';
		$datos = [ 'titulo'    => 'Cesarini - Informe dispenser',
				   'accion'    => $accion,
				   'estados'   => $estados,
				   'clientes'  => $clientes,
				   'tiposdisp' => $tiposDisp,
				 ];

		return $this->view->render($response, 'productos/informedispenser.twig', $datos);
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

	/**
	 * Imprime/Muestra Informe de Dispenser
	 * Name: productos.infodispimprime
	 * 
	 * @param  Request  $request 
	 * @param  Response $response
	 * @return view
	 */
	public function infoDispImprime($request, $response)
	{
		// ?ordenar=NroSerie&orden=asc&agrupar=Modelo-Tipo&filtEstado=Stock&filtTipo=1&fecha=FechaAlta&ordfecha=asc&desde=2019-04-23&conmovim=true&conclie=true

		$query = "SELECT dp.*, td.Descripcion FROM Dispenser AS dp ";
		$query = $query."LEFT JOIN TipoDispenser AS td ON dp.IdTipo = td.Id";
		// Filtrar...
		$where = $this->_filtrar($request);
		// Fechas...
		$whereFecha = $this->_fechas($request);
		// Agrupar (ordenar)...
		$agrupar = $this->_agrupar($request);

		// Ordenar...
		if ($agrupar === ' ORDER BY ') { 
			$orden = ''; 
		} else $orden = ', '; 

		$orden = $orden.$request->getParam('ordenar')." ".mb_strtoupper($request->getParam('orden'));
		// Texto que informa como se ordena la lista (previo Agrupar, por funcion _agrupar())
		$this->_txtImprimeInforme = $this->_txtImprimeInforme.'Ordenado por: '.$request->getParam('ordenar');

		$query = $query.$where.$whereFecha.$agrupar.$orden;

		// Usando clase \Pdo\PdoClass ('pdo' en $container)
		$data = $this->pdo->pdoQuery($query);
		// Agrego detalle de movimientos dispenser, si los pide...
		$data = $this->_detalleMovim($request, $data);
		// Agrego datos del cliente si los pide y tiene el dispenser
		$data = $this->_detalleCliente($request, $data);
		// Filtro por cliente si lo pidió...
		$data = $this->_filtroCliente($request, $data);

		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$datos = [ 'titulo'   => 'Cesarini - Imprimir',
				   'fecha'   => $fecha,
				   'accion'   => $this->_txtImprimeInforme,
				   'conmovim' => $this->_conMovim,
				   'conclien' => $this->_conCliente,
				   'listado'  => $data ];

		return $this->view->render($response, 'productos/infodisp_imprime.twig', $datos);
	}

	/**
	 * Valores de agrupar (= order by)
	 */
	private function _agrupar($req)
	{
		$strAgrupar = ' ORDER BY ';
		if ($req->getParam('agrupar') != '') {
			// Si tiene guiones, tomar los parametros...
			$params = explode("-", $req->getParam('agrupar'));

			$this->_txtImprimeInforme = "Agrupado por: ";

			for ($i = 0; $i < count($params); $i++) {
				$strAgrupar = $strAgrupar.$params[$i];
				$this->_txtImprimeInforme = $this->_txtImprimeInforme.$params[$i];
				if ($i < count($params) - 1) {
					$strAgrupar = $strAgrupar.', ';
					$this->_txtImprimeInforme = $this->_txtImprimeInforme.", ";
				}
			}
			$this->_txtImprimeInforme = $this->_txtImprimeInforme." - ";

		} else $this->_txtImprimeInforme = "";

		return $strAgrupar;
	}

	/**
	 * Armar clausula de filtro..
	 * (Since 'Estado' can never simultaneously equal two different values, no results will be returned.)
	 *
	 * 
	 * @param  Request $req
	 * @return string
	 */
	private function _filtrar($req)
	{
		// Filtro por Estado
		if ($req->getParam('filtEstado') != '') {

			$arrEstado = explode("-", $req->getParam('filtEstado'));
			$strFiltro1 = " WHERE Estado = '";

			for ($i = 0; $i < count($arrEstado); $i++) {
				$strFiltro1 = $strFiltro1.$arrEstado[$i]."'";
				if ($i < count($arrEstado) - 1) {
					$strFiltro1 = $strFiltro1." OR Estado = '";
				}
			}
		} else $strFiltro1 = "";

		// Filtro por Tipo
		if ($req->getParam('filtTipo') != '') {
			$arrTipo = explode("-", $req->getParam('filtTipo'));

			if ($strFiltro1 == '') {
				$strFiltro2 = " WHERE IdTipo = ";
			} else {
				$strFiltro2 = " AND IdTipo = ";
			}

			for ($i = 0; $i < count($arrTipo); $i++) {
				$strFiltro2 = $strFiltro2.$arrTipo[$i];
				if ($i < count($arrTipo) - 1) {
					$strFiltro2 = $strFiltro2." OR IdTipo = ";
				}
			}
			$strFiltro1 = $strFiltro1.$strFiltro2;
		}

		// Si no hay filtro...
		if ($strFiltro1 === "") {
			$strFiltro1 = " WHERE Estado != 'Baja'";
		}

		return $strFiltro1;
	}

	/**
	 * Clausula where por las fechas.
	 *(&fecha=FechaAlta&ordfecha=asc&desde=2019-04-23 (||&&) &hasta=2019-04-23)
	 * 
	 * @param  [type] $req [description]
	 * @return [type]      [description]
	 */
	private function _fechas($req)
	{
		if ($req->getParam('fecha') != '') {

			$campoFecha = $req->getParam('fecha');

			if ($req->getParam('desde') != '') {
				$desde = " >= '".$req->getParam('desde')."'";
			} else $desde = '';

			if ($req->getParam('hasta') != '') {
				$hasta = " <= ".$req->getParam('hasta')."'";
			} else $hasta = '';

			$fechas = " AND ".$campoFecha.$desde.$hasta;

		} else $fechas = '';

		return $fechas;
	}

	/**
	 * Busca y agrega detalle de ultimo movimiento del dispenser
	 * 
	 * @param  Request $req
	 * @param  array $data
	 * @return array $data
	 */
	private function _detalleMovim($req, $datos)
	{
		if ($req->getParam('conmovim') === 'true') {

			for ($i=0; $i < count($datos); $i++) { 

				$sql = "SELECT md.*, em.ApellidoNombre FROM MovimientosDispenser AS md LEFT JOIN Empleados AS em ON md.IdEmpleado = em.Id WHERE IdDispenser = ".$datos[$i]['Id']." ORDER BY Fecha DESC LIMIT 1";
				// Busco datos movimiento...
				$movim = $this->pdo->pdoQuery($sql);
				// Agrego campos al array de datos
				$datos[$i]['UltMov']        = $movim[0]['Fecha'];
				$datos[$i]['Observaciones'] = $movim[0]['Observaciones'];
				$datos[$i]['Empleado']      = $movim[0]['ApellidoNombre'];
			}
			$this->_conMovim = true;

		} else $this->_conMovim = false;

		return $datos;
	}

	/**
	 * Busca y agrega datos de cliente, si el dispenser está en el cliente
	 * 
	 * @param  Request $req
	 * @param  array $datos
	 * @return array
	 */
	private function _detalleCliente($req, $datos)
	{
		if ($req->getParam('conclie') === 'true') {
			// Loop por todos los dispenser...
			for ($i=0; $i < count($datos); $i++) { 

				if ($datos[$i]['Estado'] === 'Cliente') {
					// Buscar en ultimo movimiento del dispenser...
					$maxIdMov = MovimientoDispenser::where('IdDispenser', $datos[$i]['Id'])->max('Id');
					$movim = MovimientoDispenser::find($maxIdMov);

					$datos[$i]['IdCliente'] = $movim->Cliente->Id;
					$datos[$i]['Cliente']   = $movim->Cliente->ApellidoNombre;
					$datos[$i]['Direccion'] = $movim->ClienteDomicilio->Direccion;
					$datos[$i]['Localidad'] = $movim->ClienteDomicilio->Localidad;
					$datos[$i]['Telefono']  = $movim->ClienteDomicilio->Telefono;
					$datos[$i]['Celular']   = $movim->ClienteDomicilio->Celular;
				}
			}
			$this->_conCliente = true;

		} else $this->_conCliente = false;

		return $datos;
	}

	/**
	 * Filtra los datos a solo los clientes seleccionados
	 * 
	 * @param  [type] $req   [description]
	 * @param  [type] $datos [description]
	 * @return [type]        [description]
	 */
	private function _filtroCliente($req, $datos)
	{
		//?agrupar=IdTipo&ordenar=NroInterno&orden=asc&filtEstado=Cliente&filtCliente=8&conmovim=true&conclie=true
		if ($req->getParam('filtCliente') != '' && $req->getParam('conclie') === 'true') {
			// Arrray de codigos de clientes seleccionados
			$arrCodsClies = explode("-", $req->getParam('filtCliente'));
			$newDatos = [];

//var_dump($datos);
//echo "<br>";

//echo "Filtro por cliente id: $arrCodsClies[0]<br><br>";

			for ($i=0; $i < count($datos); $i++) { 

//echo "Id de cliente de la lista: ".$datos[$i]['IdCliente']."<br> ";
//var_dump($datos[$i]['IdCliente']);
//echo "<br>";
				foreach ($arrCodsClies as $value) {

					if ($value == $datos[$i]['IdCliente'] ) {

//echo "Entró una vez...(coincide !!)<br>";

						$newDatos[] = $datos[$i];
					}
				}
			}

			$datos = $newDatos;
		}

//echo "<br>";
//var_dump($newDatos);
//echo "<br>";
//die('Ver que pasó...');

		return $datos;
	}


}
