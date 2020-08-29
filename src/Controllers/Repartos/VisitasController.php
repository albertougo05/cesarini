<?php

namespace App\Controllers\Repartos;

use App\Models\GuiaReparto;
use App\Models\ProductoClienteReparto;
use App\Models\Producto;
use App\Models\Dispenser;
use App\Models\MovimientoDispenser;
use App\Models\Cliente;
use App\Models\ClienteReparto;
use App\Models\ClienteDomicilio;
use App\Models\Empleado;
use App\Models\Visita;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;
use App\Models\VisitaMovimDispenser;

use App\Controllers\Controller;


/**
 * Clase VisitasController
 * 
 * Url: '/repartos/visitas'
 * 
 */
class VisitasController extends Controller
{
	private $_accion;
	private $_sumaEntregas;

	/**
	 * Visitas a clientes
	 * Name: 'repartos.visitas'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function visitas($request, $response)
	{
		$this->_accion = 'Nueva';
		$usuario = $this->auth->user();
		$nivelUser = $usuario->Nivel;
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
	    date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('Y-m-d');
	    $fechaDesde = date("Y-m-d", strtotime($fecha."- 60 days")); 

		$datos = array('titulo'     => 'Cesarini - Visitas',
					   'nivelUser'  => $nivelUser,
			           'accion'     => $this->_accion,
			           'editable'   => false,
			           'dataGuia'   => ['idvisita' => 0, 'fechavisita' => $fecha],
			           'fechaDesde' => $fechaDesde,
			           'fechaHasta' => $fecha,
			           'empleados'  => $empleados);

		return $this->view->render($response, 'repartos/visitas/visitas.twig', $datos);
	}

	/**
	 * Muestra datos de guia de reparto, cuando seleccionó una
	 * Name: 'repartos.visitas.conguia'
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return view
	 */
	public function conGuia($request, $response)
	{
		$this->_accion = 'Nueva';  // Ver esto !!
		$usuario       = $this->auth->user();
		$nivelUser     = $usuario->Nivel;
		$idGuia        = $request->getParam('idguia');
		$guiaRep       = $this->_dataGuiaReparto($idGuia);
		// Agrego datos de la visita
		$guiaRep['idvisita'] = 0;
	    date_default_timezone_set("America/Buenos_Aires");
		$guiaRep['fechavisita'] = date('Y-m-d');
	    $fecha                  = date('Y-m-d');
	    $fechaDesde             = date("Y-m-d", strtotime($fecha."- 60 days")); 
		$guiaRep['pendiente']   = 1;

		$clientes  = $this->_clientesGuiaRep($idGuia, $fecha);		// Clientes de la guia de reparto
		$ultimOrd  = ( count($clientes) === 0 ) ? 0 : $clientes[count($clientes) - 1]['orden'];		// Saco el ultimo número de orden...
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
		$productos = $this->_productosGuiaReparto($idGuia);
		$listaProd = $this->_listaDeProductos();
		$dataDisp  = [];    // Mov. dispenser asociados a la visita

		// Para debug por falla en Cesarini office...
		//$this->logger->debug('Con todos lo datos de la GR', array('Id GR: ' => $idGuia));

		$datos = array( 'titulo'    => 'Cesarini - Visitas',
						'nivelUser' => $nivelUser,
			            'accion'    => $this->_accion,
			            'editable'  => true,
			            'empleados' => $empleados,
			            'productos' => $productos,
			            'listaprod' => $listaProd,
			       	    'dataGuia'  => $guiaRep,
			       	    'dataClie'  => $clientes,
			       	    'dataDisp'  => $dataDisp,
			            'fechaDesde' => $fechaDesde,
			            'fechaHasta' => $fecha,
			       	    'ultimOrd'  => $ultimOrd );

		return $this->view->render($response, 'repartos/visitas/visitas.twig', $datos);
	}

	/**
	 * Muestra vista despues de Buscar visita
	 * Name: 'repartos.visitas.conidvisita' (GET)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return view
	 */
	public function conIdVisita($request, $response)
	{
		$this->_accion = 'Modifica';
		$usuario = $this->auth->user();
		$nivelUser = $usuario->Nivel;
	    date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('Y-m-d');
	    $fechaDesde = date("Y-m-d", strtotime($fecha."- 60 days")); 

		// Datos de la visita
		$visita = Visita::find($request->getParam('idvisita'));

		// Busco datos Guia de Reparto de la visita...
		$dataVisita = $this->_dataGuiaReparto($visita->IdGuiaReparto);
		$dataVisita['idvisita'] = $request->getParam('idvisita');
		$dataVisita['fechavisita'] = $visita->Fecha;

		// Si el IdGuiaReparto es 0, es una visita en planta...
		// Verifico Empleado, que puede ser cambiado al de la guía de reparto
		if ( $dataVisita['idempl'] != $visita->IdEmpleado) {
			$empleado = Empleado::find($visita->IdEmpleado);
			$dataVisita['idempl']     = $visita->IdEmpleado;
			$dataVisita['nombreempl'] = $empleado->ApellidoNombre;
		}

		$dataVisita['salida']    = $visita->HoraSalida;
		$dataVisita['retorno']   = $visita->HoraRetorno;
		$dataVisita['pendiente'] = $visita->Pendiente;
		$dataVisita['observac']  = $visita->Observaciones;

    	// Productos de la visita
		$productos = $this->_productosVisita($request->getParam('idvisita'));
		// Clientes de la visita
		$clientes  = $this->_clientesVisita($request->getParam('idvisita'));
		// Suma productos dejados
		$productos = $this->_sumaProdDejados($productos, $clientes);
		// Suma de entregas
		$dataVisita['entregas']  = $this->_sumaEntregas;
		// Listas para busquedas...
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
		// Lista de productos, para modal agregar cliente y prod
		$listaProd = $this->_listaDeProductos();
		// Mov. dispenser asociados a la visita
		$dataDisp  = $this->_movimDispAsoc($request->getParam('idvisita'));

		$datos = array( 'titulo'     => 'Cesarini - Visitas',
						'nivelUser'  => $nivelUser,
			            'accion'     => $this->_accion,
			           	'editable'   => ($visita->IdGuiaReparto == 0) ? false: true,   // Para botón Agregar Cliente
			          	'empleados'  => $empleados,
			            'productos'  => $productos,
			            'listaprod'  => $listaProd,
			            'fechaDesde' => $fechaDesde,
			            'fechaHasta' => $fecha,
			            'dataDisp'   => $dataDisp,
			       	    'dataGuia'   => $dataVisita,
			       	    'dataClie'   => $clientes,
			       	    'ultimOrd'   => ( count($clientes) > 0 ) ? $clientes[count($clientes) - 1]['orden'] : 0 );

		return $this->view->render($response, 'repartos/visitas/visitas.twig', $datos);
	}

	/**
	 * Guardar datos de la visita (POST)
	 * Name: 'repartos.visitas.guardar'
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return view
	 */
	public function guardar($request, $response)
	{
		$datos = array( 'IdGuiaReparto' => (integer) ($request->getParam('idguiarep') == '') ? 0 : $request->getParam('idguiarep'), 
					    'IdEmpleado'    => (integer) $request->getParam('idemplead'),
					    'Fecha'         => $request->getParam('fechavisita'),
					    'HoraSalida'    => $request->getParam('horaSalida'),
					    'HoraRetorno'   => $request->getParam('horaRetorno'),
					    'Pendiente'     => $request->getParam('pendiente'),
					    'Observaciones' => $request->getParam('observ') );

		if ($request->getParam('idvisita') == 0) {
			# Crea nuevo registro visita
			$id = (integer) Visita::insertGetId($datos, 'Id');

		} else {
			# Update visita
			Visita::where('Id', $request->getParam('idvisita'))
			       ->lockForUpdate()
			       ->update($datos);
			$id = (integer) $request->getParam('idvisita');
		}

		// Guardo productos (vienen en el mismo request)
		$resultProds = $this->_guardarProductos($request, $id);
		// Guardo clientes de la visita (mismo request)
		$resultClies = $this->_guardarClientes($request, $id);
		// Guardo datos de Movimientos de dispenser
		$resultDisp  = $this->_guardarDispensers($request, $id);
		// Guardar en log los resultados de salvar datos
		$this->logger->debug('Salvando visita...', array('Id Visita: ' => $id, 
														 'resultProds' => $resultProds, 
														 'resultClies' => $resultClies, 
														 'resultDisp'  => $resultDisp));

		return json_encode([ 'id'          => $id, 
							 'resultProds' => $resultProds, 
							 'resultClies' => $resultClies,
							 'resultDisp'  => $resultDisp ]);
	}

	/**
	 * Eliminar visita
	 * Name: 'repartos.visitas.eliminar'
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return redirect a view
	 */
	public function eliminar($request, $response)
	{
		if ( !empty( $request->getParam('idvisita') ) ) {
			Visita::where('Id', $request->getParam('idvisita'))->delete();

			// Eliminar todas la referencias a ESA visita
			VisitaSalidaProducto::where('IdVisita', $request->getParam('idvisita'))->delete();
			VisitaDetalleCliente::where('IdVisita', $request->getParam('idvisita'))->delete();
			VisitaMovimDispenser::where('IdVisita', $request->getParam('idvisita'))->delete();

			$this->flash->addMessage('info', "Visita eliminada !");
		}

		return $response->withRedirect($this->router->pathFor('repartos.visitas'));
	}

	/**
	 * Imprimir datos de la visita
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return view imprime
	 */
	public function imprimir($request, $response)
	{
		// Datos de la visita
		$visita = Visita::find($request->getParam('idvisita'));

		// Busco datos Guia de Reparto de la visita...
		$dataVisita = $this->_dataGuiaReparto($visita->IdGuiaReparto);

		$dataVisita['idvisita']    = $request->getParam('idvisita');
		$dataVisita['fechavisita'] = $visita->Fecha;
		$dataVisita['salida']      = $visita->HoraSalida;
		$dataVisita['retorno']     = $visita->HoraRetorno;
		$dataVisita['observac']    = $visita->Observaciones;
		// Verifico Empleado, que puede ser cambiado al de la guía de reparto
		if ( $visita->IdGuiaReparto == 0 || $dataVisita['idempl'] != $visita->IdEmpleado) {
			$empleado = Empleado::find($visita->IdEmpleado);
			$dataVisita['idempl']     = $visita->IdEmpleado;
			$dataVisita['nombreempl'] = $empleado->ApellidoNombre;
		}

    	// Productos de la visita
		$productos = $this->_productosVisita($request->getParam('idvisita'));
		// Clientes de la visita
		$clientes = $this->_clientesVisita($request->getParam('idvisita'), true);
		// Suma productos dejados
		$productos = $this->_sumaProdDejados($productos, $clientes);
		// Suma de entregas (se suma en $this->_clientesVisita(...))
		$dataVisita['entregas']  = $this->_sumaEntregas;
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');
		// Mov. dispenser asociados a la visita
		$dataDisp  = $this->_movimDispAsoc($request->getParam('idvisita'));

		$datos = array('titulo'    => 'Cesarini - Imprime Visita',
					   'fecha'     => $fecha,
			           'productos' => $productos,
			       	   'dataGuia'  => $dataVisita,
			       	   'dataDisp'  => $dataDisp,
			       	   'dataClie'  => $clientes );

		return $this->view->render($response, 'repartos/visitas/imprime.twig', $datos);
	}

	/**
	 * Devuelve json con datos de cliente para busqueda de input por ajax
	 * Name: repartos.visitas.cliente
	 * 
	 * @param  $request
	 * @param  $response
	 * @return json
	 */
	public function cliente($request, $response)
	{
		// Para Typeahead...
    	$search = $request->getParam('search');

	    // Quito el string de la direccion...
	    if (strpos($search, ' - ') ) {
	        $search = substr($search, 0, strpos($search, ' - '));
	    }

	    //$query = "SELECT * FROM Clientes WHERE ApellidoNombre like'%".$search."%'";
	    //$result = mysqli_query($con, $query);

		$arrClieDoms = [];
		$indice = 0;
		// Buscar clientes
		$clientes = Cliente::where([ ['ApellidoNombre', 'like', "%$search%"], 
									 ['Estado', '=', 'Activo'] ])
						   ->orWhere('NombreFantasia', 'like', "%$search%")
						   ->orderBy('ApellidoNombre', 'asc')
						   ->get();
		// Crear array con domicilios
		foreach ($clientes as $clie) {
			$domicilios = $clie->Domicilios->toArray();
			// loop por domicilios
			foreach ($domicilios as $dom) {

				$arrClieDoms[] = [ 'id'      => $clie->Id,
								   'cliente' => $clie->ApellidoNombre,
								   'iddom'   => $dom['Id'],
								   'direccion' => $dom['Direccion'] ];
			}
		}

	    return json_encode($arrClieDoms);
	}

	/**
	 * Devuelve json listado visitas para buscar en modal via ajax
	 * Name: reparto.visitas.listado
	 *
	 * @param  $request
	 * @param  $response
	 * @return json
	 */
	public function listadoBuscar($request, $response)
	{
		$desde = $request->getParam('desde');
		$hasta = $request->getParam('hasta');
		$arrListado = [];

		$visitas = Visita::where([ ['Fecha', '>=', $desde], 
						 	       ['Fecha', '<=', $hasta] ])
						 ->orderBy('Id', 'desc')
						 ->get();

		foreach ($visitas as $value) {

			$arr = [ 'id'       => $value->Id, 
				 	 'fecha'    => $value->Fecha, 
				     'empleado' => $value->Empleado->ApellidoNombre, 
				     'diasem'   => ($value->IdGuiaReparto === 0) ? '' : $value->GuiaReparto->DiaSemana,
				     'turno'    => ($value->IdGuiaReparto === 0) ? '' : $value->GuiaReparto->Turno,
					 'salida'   => $value->HoraSalida, 
					 'retorno'  => $value->HoraRetorno,
					 'pendiente' => $value->Pendiente ];
			$arrListado[] = $arr;
		}

		return json_encode($arrListado);
	}

	/**
	 * Guardar clientes de la visita
	 * 
	 * @param  Request $req
	 * @param  integer $id
	 * @return nada por ahora
	 */
	private function _guardarClientes($req, $id)
	{
		// Armo el array con los datos a pasar a DB (faltan las cantidades)
		$dataProdsClies = $this->_datosProductosClientes($req, $id);
		// Cargo cantidades de los inputs
		$dataProdsClies = $this->_cargoCantidadesInputs($req, $dataProdsClies);

		// Armo array con los Id de cada registro
		$idsRegistros = $this->_cargoIdsDeRegistros($req);
		// Indice para los Id de registros
		$idxIds = 0;

//echo "<pre>";
//print_r($dataProdsClies);
//echo "</pre><br><pre>";
//print_r($idsRegistros);
//echo "</pre><br>";
//die('Ver...');

		try {
			// 
			foreach ($dataProdsClies as $value) {

				$busca = array( 'Id' => $idsRegistros[$idxIds] );

				$vdc = VisitaDetalleCliente::updateOrInsert( $busca, $value );
				//$detaClie = VisitaDetalleCliente::create($value);
				// Incremento el indice...
				$idxIds++;
			}
			$result = 'Ok';

		} catch (Exception $e) {
			// Para debug..
			$this->logger->debug('Error salvando clientes visita...', array('Id Visita: ' => $id));
			$result = "Error salvando clientes visita. ".$e;
		}

		return $result;		
	}

	/**
	 * Cargo las cantidades ingresadas en los inputs
	 * 
	 * @param  Request $req
	 * @param  Array $data
	 * @return Array $data
	 */
	private function _cargoCantidadesInputs($req, $data)
	{
		$idx = 0;
		// Cargo valores de los inputs (cantidades)
		foreach ($req->getParams() as $key => $value) {
			$pos = strpos($key, '_');

			if ($pos) {
				$accion = mb_substr($key, 0, $pos);

				if ($accion == 'stock') {
					$data[$idx]['CantStock'] = (integer) $value;

				} else if ($accion == 'deja') {
					$data[$idx]['CantDejada'] = (integer) $value;

				} else if ($accion == 'ret')  {
					$data[$idx]['CantRetira'] = (integer) $value;

				} else if ($accion == 'saldo')  {
					// Convertir string a float
					$data[$idx]['Saldo'] = $this->utils->convStrToFloat($value);

				} else if ($accion == 'entr')  {
					// Eentregas
					$data[$idx]['Entrega'] = $this->utils->convStrToFloat($value);
				
				} else if ($accion == 'debi')  {
					// Esta es la ultima referencia para aumentar el idx !! 
					$data[$idx]['Debito'] = $this->utils->convStrToFloat($value);
					$idx++;	
				}				
			}

			if ($key == 'idvisita3') {
				break;
			}
		}

		return $data;
	}

	/**
	 * Arma array para salvar datos de productos de clientes
	 * (La identificación es por en guión bajo. Si lo tiene )
	 * deja_{{ datcli.idclie }}x{{ datcli.iddomic }}_{{ datcli.idprod }}o{{ datcli.orden }}
	 * 
	 * @param  Request $req 
	 * @return array
	 */
	private function _datosProductosClientes($req, $id)
	{
		$idsParams = array_keys($req->getParams());
		$temp = $datos = [];

		foreach ($idsParams as $value) {
			$pos = strpos($value, '_');

			if ($pos) {
				# Si tiene el guión bajo, es el cliente
				$segPos = strrpos($value, '_');
				$marDom = strpos($value, 'x');
				$masOrd = strpos($value, 'o');

				$accion = mb_substr($value, 0, $pos);
				$idClie = mb_substr($value, $pos+1, $marDom-($pos+1));
				$idDom  = mb_substr($value, $marDom+1, $segPos-($marDom+1));
				$idProd = mb_substr($value, $segPos+1, $masOrd-($segPos+1));
				$ordCli = mb_substr($value, $masOrd+1);

				if ($accion === 'deja') {

					$temp = [ 'IdVisita'    => (integer) $id,
							  'IdCliente'   => (integer) $idClie,
							  'IdDomicilio' => (integer) $idDom,
							  'OrdenVisita' => (integer) $ordCli,
							  'IdProducto'  => (integer) $idProd,
							  'CantStock'   => 0,
							  'CantDejada'  => 0,
							  'CantRetira'  => 0,
							  'Saldo'       => 0,
							  'Entrega'     => 0,
							  'Debito'      => 0 ];

				} else if ($accion === 'entr') {
					// Si está vacio temp, es porque no tiene producto  (entr_8x11o3)
					if (empty($temp)) {
						// Saco de nuevo el iddom
						//$idDom  = mb_substr($value, $marDom+1, $masOrd-($marDom+1));
						$temp = [ 'IdVisita'    => (integer) $id,
								  'IdCliente'   => (integer) $idClie,
								  'IdDomicilio' => (integer) $idDom,
								  'OrdenVisita' => (integer) $ordCli,
								  'IdProducto'  => 0,
								  'CantStock'   => 0,
								  'CantDejada'  => 0,
								  'CantRetira'  => 0,
								  'Saldo'       => 0,
								  'Entrega'     => 0,
								  'Debito'      => 0 ];
					}
					// Asigno el array a datos
					$datos[] = $temp;
					// Vacio array
					$temp = [];
				}

			} else if ($value == 'idvisita3') {
				# ultimo campo del formulario...
				break;
			}
		}

		return $datos;
	}

	/**
	 * Armo array con los ids de cada registro
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _cargoIdsDeRegistros($req)
	{
		$ids = [];

		foreach ($req->getParams() as $key => $value) {
			$pos = strpos($key, '_');

			if ($pos) {
				$accion = mb_substr($key, 0, $pos);

				if ($accion === 'idreg') {
					$ids[] = (integer) $value;
				}
			}
		}

		return $ids;
	}

	/**
	 * Arma listado de clientes de la visita
	 * 
	 * @param  integer $idVisita
	 * @return array
	 */
	private function _clientesVisita($idVisita, $esPrint = false)
	{
		$clisVisit = [];
		$sumaEntregas = 0;
		$clientesVisita = VisitaDetalleCliente::where('IdVisita', $idVisita)
												->orderBy('OrdenVisita')
												->get();
		// Para indice de Orden
		$idx = 1;

		foreach ($clientesVisita as $value) {

			// Buscar domicilio segun el idDomicilio...
			$domCli = ClienteDomicilio::select('Direccion')->find($value->IdDomicilio);

			// Cliente puede NO tener producto...
			if ($value->IdProducto == 0) {
				$nombreProducto = '';
				$precioProd = 0;
			} else {
				// Si es para imprimir, no pongo el Tipo de Prod en nombre de producto
				if ($esPrint) {
					$nombreProducto = $value->Producto->Descripcion;
				} else {
					$nombreProducto = $value->Producto->DescripTipoProducto->Descripcion." - ".$value->Producto->Descripcion;
				}
				// Busco precio producto
				$producto = Producto::find( $value->IdProducto );
				$precioProd = $producto->Precio;
			}

			// Cargo codigos de dispenser, despues de domicilio del cliente
			$domicilio = $this->_agregoListaDispensers( $value->IdCliente, $domCli->Direccion );
			// Si el cliente tiene abono
			$abono = ($value->Cliente->CostoAbono > 0) ? 1 : 0;

			$clisVisit[] = [ 'idreg'     => $value->Id,
							 'idclie'    => $value->IdCliente,
							 'cliente'   => $value->Cliente->ApellidoNombre,
							 'orden'     => ($value->OrdenVisita === 0) ? 0 : $idx,
							 'iddomic'   => $value->IdDomicilio,
							 'domicilio' => $domicilio,    // $domCli->Direccion,
							 'idprod'    => $value->IdProducto,
							 'producto'  => $nombreProducto,
							 'precio'    => $precioProd,
							 'stockenv'  => $value->CantStock,
							 'cantsuge'  => '',
							 'cantidad'  => ($value->CantDejada === 0) ? '' : $value->CantDejada,
							 'retira'    => ($value->CantRetira === 0) ? '' : $value->CantRetira,
							 'saldo'     => ($value->Saldo === 0) ? '' : $value->Saldo,
							 'entrega'   => ($value->Entrega === 0) ? '' : $value->Entrega,
							 'debito'    => ($value->Debito === 0) ? '' : $value->Debito,
							 'abono'     => $abono ];

			$sumaEntregas += $value->Entrega;
			$idx++;
			unset($producto);
		}

		$this->_sumaEntregas = $sumaEntregas;

		return $clisVisit;
	}

	/**
	 * Guardar datos de productos de la visita
	 * 
	 * @param  Request $req
	 * @param  integer $idvisita
	 * @return boolean
	 */
	private function _guardarProductos($req, $idvisita)
	{
		// Obtengo array con id producto y cantidades
		$productCant = $this->_cantidProductos($req, $idvisita);

		try {

			foreach ($productCant as $key => $value) {

				$buscar = array( 'IdVisita'   => $idvisita,
								 'IdProducto' => $key );
				$actualiza = array(	'CantRetirado'     => $value['retira'],
									'CantDevuelto'     => $value['devuelve'],
									'EnvasesDevueltos' => $value['recupera'] );

				$visita = VisitaSalidaProducto::updateOrInsert($buscar, $actualiza);
			}
			$result = 'Ok';

		} catch (Exception $e) {
			// Para debug..
			$this->logger->debug('Error salvando productos visita...', array('Id Visita: ' => $idvisita));
			$result = 'Error salvando datos productos visita. '.$e;
		}

		return $result;
	}

	/**
	 * Produce array con id producto y cantidades del form Movim. Productos Visita
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _cantidProductos($req)
	{
		$idsParams   = array_keys($req->getParams());
		$productCant = [];
		$temp        = [];
		$idProdAnt   = '';

		foreach ($idsParams as $value) {
			$pos = strpos($value, '-');

			if ($pos) {
				# Si tiene el guión es el producto
				$desc   = mb_substr($value, 0, $pos);
				$idProd = mb_substr($value, $pos + 1);
				$cantid = $req->getParam($value);

				if ($idProdAnt === '') {
					$idProdAnt = $idProd;

				} elseif ($idProdAnt != $idProd) {
					$productCant[$idProdAnt] = $temp;
					$idProdAnt = $idProd;
				}

				switch ($desc) {
					case 'prodRet':
						# Retirado
						//$temp['retira'] = $cantid;
						$temp['retira'] = ($cantid == '') ? 0 : (integer) $cantid;
						break;
					case 'prodDev':
						# Devuelve
						$temp['devuelve'] = ($cantid == '') ? 0 : (integer) $cantid;
						break;
					case 'prodRecu':
						# Recupera
						$temp['recupera'] = ($cantid == '') ? 0 : (integer) $cantid;
						break;
				} 

			} else if ($value == 'idvisita2') {
				# ultimo campo del formulario...
				$productCant[$idProd] = $temp;
			}
		}

		return $productCant;
	}

	/**
	 * Guardo datos de Movimientos de dispenser
	 *
	 * @param Request $req
	 * @param integer $id
	 * @return json
	 */
	private function _guardarDispensers($req, $id)
	{
		if ( empty( $req->getParam('dispenser') ) ) {
			# Si no hay movim de dispenser..
			$result = 'Sin movim. de dispenser';

		} else {
			// $test = utf8_encode($_POST['test']); // Don't forget the encoding
			// $data = json_decode($test);
			// echo $data->test;
			$dispens = json_decode($req->getParam('dispenser'), true);

			try {
				# Borro todos los movim de id visita
				VisitaMovimDispenser::where('IdVisita', $id)->delete();

				foreach ($dispens as $value) {

					if ($value['idmov'] === 0) {    # Si el movimiento es 0, es nuevo
						$data = array( 'IdDispenser' => $value['iddisp'],
						               'Fecha'       => $req->getParam('fechavisita'),
						               'IdEmpleado'  => $req->getParam('idemplead'),
						               'IdCliente'   => $value['idclie'],
						               'IdDomicilio' => $value['iddomi'],
						               'Observaciones' => $value['observ'],
						               'Estado'      => $value['estado'] );

						$idMovDisp = MovimientoDispenser::insertGetId($data);

						# Actualizo estado en Dispensers...
						Dispenser::where('Id', $value['iddisp'])
			                      ->lockForUpdate()
			                      ->update(['Estado' => $value['estado']]);
			        } else {

			        	$idMovDisp = $value['idmov'];
			        }

		            # Registro asociacion Visita con mov. de disp.
					$data = array( 'IdVisita' => $id, 'IdMovDisp' => $idMovDisp );
					$mov = VisitaMovimDispenser::create($data);
				}
				$result = 'Ok';

			} catch (Exception $e) {
				$result = "Error salvando movimiento dispenser. ".$e;
			}
		}

		return $result;
	}

	/**
	 * Arma array con datos de guia de reparto
	 * 
	 * @param  integer $id
	 * @return array 
	 */
	private function _dataGuiaReparto($id)
	{
		if ( $id == 0 ) {

			return ['idvisita' => 0, 'fechavisita' => '', 'idempl' => 0];
		}

		$guiaRep = GuiaReparto::find($id);
		$arr = [ 'idguia'     => $guiaRep->Id,
				 'idempl'     => $guiaRep->IdEmpleado,
				 'nombreempl' => $guiaRep->Empleado->ApellidoNombre,
				 'dia'        => $guiaRep->DiaSemana,
				 'turno'      => $guiaRep->Turno,
				 'idactiv'    => $guiaRep->IdActividad,
				 'actividad'  => $guiaRep->Actividad->Descripcion,
				 'salida'     => $guiaRep->HoraSalida,
				 'retorno'    => $guiaRep->HoraRetorno ];

		return $arr;
	}

	/**
	 * Lista con cantidad de productos de cada cliente de la guia de reparto
	 * 
	 * @param  string $id
	 * @return array
	 */
	private function _clientesGuiaRep($id='', $fechaHasta)
	{
		// Control para buscar saldo
		$yaBuscoSaldo = false;

		// Conseguir todos los clientes de la guia de reparto
		$clientesEnGuia = $this->_clientesDeGuiaRep($id);
		$listProducCli = [];

		foreach ($clientesEnGuia as $value) {
			$contar = $saldo = 0;
			$domicilo = '';
			# Busco productos del cliente...	# Verifico si existe el registro...
			$contar = ProductoClienteReparto::where([ 'IdReparto' => $id, 
													  'IdCliente' => $value['idcli'], 
													  'IdDomicilio' => $value['iddomic'] ])
											->count();
		    # Buscar saldo 
		    $saldo = $this->utils->getSaldo( $value['idcli'], $fechaHasta );
			// Cargo los dispenser despues de domicilio del cliente
			$domicilio = $this->_agregoListaDispensers( $value['idcli'], $value['domicilio'] );

			if ( $contar > 0 ) {

				$productosClie = ProductoClienteReparto::where([ 'IdReparto' => $id, 
																 'IdCliente' => $value['idcli'],
																 'IdDomicilio' => $value['iddomic'] ])
											           ->get();

				foreach ($productosClie as $key => $prodCli) {

					if ($key > 0) {
						$saldo = '';
					}

					// Busco el producto para obtener la descripcion del tipo y precio
					$producto = Producto::find($prodCli->IdProducto);
					// Busco estock de envases
					$stockEnv = $this->utils->stockEnvases($prodCli->IdProducto, $value['idcli']);

					$listProducCli[] = ['idreg'    => 0,
										'idclie'   => $value['idcli'],
										'cliente'  => $prodCli->Cliente->ApellidoNombre,
										'orden'    => $value['orden'],
										'iddomic'  => $value['iddomic'],
										'domicilio' => $domicilio,    //$value['domicilio'],
										'idprod'   => $prodCli->IdProducto,
										'producto' => $producto->DescripTipoProducto->Descripcion." - ".$prodCli->Producto->Descripcion,
										'precio'   => $producto->Precio,
										'cantsuge' => $prodCli->CantSugerida,
										'stockenv' => $stockEnv,
										'cantidad' => '',
										'retira'   => '',
										'saldo'    => $saldo,
										'entrega'  => '',
										'debito'   => '' ];
				}

			} else {
				# Exception si no encuentra el producto
				$listProducCli[] = ['idreg'    => 0,
									'idclie'   => $value['idcli'],
									'cliente'  => $value['nombre'],
									'orden'    => $value['orden'],
									'iddomic'  => $value['iddomic'],
									'domicilio' => $domicilio,    //$value['domicilio'],
									'idprod'   => 0,
									'producto' => '',
									'precio'   => 0,
									'cantsuge' => '',
									'stockenv' => '',
									'cantidad' => '',
									'retira'   => '', 									
									'saldo'    => $saldo,
									'entrega'  => '',
									'debito'   => '' ];
			}
			unset($productosClie);
			unset($producto);
		}

	 	return $listProducCli;
	}

	/**
	 * Obtiene los clientes de Guia de reparto y ordena alfabet
	 * 
	 * @param  int $id
	 * @return array 
	 */
	private function _clientesDeGuiaRep($id)
	{
		$arrCliesEnGuia = [];
		$clisEnGuia = ClienteReparto::where('IdReparto', $id)
									->orderBy('OrdenVisita')
									->get();

		foreach ($clisEnGuia as $value) {
			$clienteDom = ClienteDomicilio::find($value->IdClienteDomicilio);
			$domicilio  = $clienteDom->Direccion;

			$arrTemp = ['idcli'     => $value->IdCliente,
						'orden'     => $value->OrdenVisita,
			            'nombre'    => $value->Cliente->ApellidoNombre,
			            'iddomic'   => $value->IdClienteDomicilio,
			            'domicilio' => $domicilio ];

			$arrCliesEnGuia[] = $arrTemp;
		}

		return $arrCliesEnGuia;
	}

	/**
	 * Lista de productos de GR
	 * 
	 * @param  int $id
	 * @return array
	 */
	private function _productosGuiaReparto($id)
	{
		$products = ProductoClienteReparto::select('IdProducto')
										  ->where('IdReparto', $id)
										  ->distinct()->get();
		$listaProd = [];
		foreach ($products as $value) {
			// Busco el producto para obtener la descripcion del tipo
			$tipoProd = Producto::find($value->IdProducto);

			// Suma de cantidad de ese producto en ProductoCliente Reparto
			$suma = '';  //ProductoClienteReparto::where(['IdReparto' => $id, 'IdProducto' => $value->IdProducto])
			            //                  ->sum('CantSugerida');

			$listaProd[] = ['idprod'   => $value->IdProducto,
		                    'producto' => $tipoProd->DescripTipoProducto->Descripcion." - ".$value->Producto->Descripcion,
		                    'prodretira' => $suma,
		                    'proddevuelve' => '',
		                    'prodrecupera' => '' ];
		}

		return $listaProd;
	}

	/**
	 * Busco productos de la visita
	 * 
	 * @param  integer $idVis
	 * @return array
	 */
	private function _productosVisita($id)
	{
		$listaProd = [];
		$products  = VisitaSalidaProducto::where('IdVisita', $id)
										 ->orderBy('IdProducto')
										 ->get();
		foreach ($products as $value) {

			if ($value->IdProducto > 0 ) {  # Si no hay id de prod es visita en planta
				// Busco el producto para obtener la descripcion del tipo
				$tipoProd = Producto::find($value->IdProducto);
				$producto = $tipoProd->DescripTipoProducto->Descripcion." - ".$value->Producto->Descripcion;
			} else {
				$producto = '';
			}

			$listaProd[] = ['idprod'       => $value->IdProducto,
		                    'producto'     => $producto,
		                    'prodretira'   => $value->CantRetirado,
		                    'proddevuelve' => $value->CantDevuelto,
		                    'proddejado'   => 0,
		                    'prodrecupera' => $value->EnvasesDevueltos ];
		}

		return $listaProd;
	}

	/**
	 * Arma lista de productos para modal agregar cliente
	 * 
	 * @return array
	 */
	private function _listaDeProductos()
	{
		$arrListProd = [];
		$prods = Producto::orderBy('Descripcion')->get();

		foreach ($prods as $value) {
			$arrListProd[] = [ 'id'      => $value->Id,
							   'descrip' => $value->DescripTipoProducto->Descripcion." - ".$value->Descripcion,
							   'precio'  => $value->Precio ];
		}

		usort($arrListProd, array($this, '_comparar'));

		return $arrListProd;
	}

	/**
	 * Para ordenar array multidimension de productos por descripcion
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _comparar($a, $b)
	{
		return strcmp($a["descrip"], $b["descrip"]);
	}

	/**
	 * Suma los productos dejados a cada cliente
	 * 
	 * @param  array $prods
	 * @param  array $clies
	 * @return array
	 */
	private function _sumaProdDejados($prods, $clies)
	{
		foreach ($clies as $value) {

			if ($value['idprod'] > 0) {
				//echo "<br>Id producto: ".$value['idprod']."<br>";
				$key = array_search($value['idprod'], array_column($prods, 'idprod'));
				//echo "Idx producto en array prods: ".$key."<br>";

				if ($key >= 0) {
					$prods[$key]['proddejado'] = (integer) $prods[$key]['proddejado'] + (integer) $value['cantidad'];

					// ACÁ HACER LA RESTA DE LOS RETIRADOS Y DEVUELTOS POR SI HAY DIFERENCIA DATO EN TABLA
					$prods[$key]['proddevuelve'] = (integer) $prods[$key]['prodretira'] - (integer) $prods[$key]['proddejado'];

					//echo "Cant dejada: ". $value['cantidad']."<br>";
					//echo "Suma dejados: ".$prods[$key]['proddejado']."<br>";
					//echo "Producto a devolver: ".$prods[$key]['proddevuelve']."<br>";

				}
			}
		}

		return $prods;
	}

	/**
	 * Obtiene los mov de disp asociados a la visita
	 * 
	 * @param  integer $idVis [description]
	 * @return array
	 */
	private function _movimDispAsoc($idVis)
	{
		$movim = [];

		$movDisp = VisitaMovimDispenser::where('IdVisita', $idVis)->get();

		if ( $movDisp->isNotEmpty() ) {

			foreach ($movDisp as $value) {
				// idmov / iddisp / nroint / modelo / idclie / iddomi / client / direcc / observ / estado

				$temp = array('idmov'  => $value->IdMovDisp,
							  'iddisp' => $value->MovDispenser->IdDispenser,
							  'nroint' => '',
							  'modelo' => '',
							  'idclie' => $value->MovDispenser->IdCliente,
							  'iddomi' => $value->MovDispenser->IdDomicilio,
							  'client' => '',
							  'direcc' => '',
							  'estado' => '',
							  'observ' =>  $value->MovDispenser->Observaciones );

				// Buscar movim de dispenser
				$movDisp = MovimientoDispenser::find($value->IdMovDisp);
				$temp['estado'] = $movDisp->Estado;
				// Buscar dispenser..
				$disp = Dispenser::find($temp['iddisp']);
				$temp['nroint'] = $disp->NroInterno;
				$temp['modelo'] = $disp->Modelo;
				unset($disp);
				// Buscar cliente
				$clie = Cliente::select('ApellidoNombre')->find($temp['idclie']);
				$temp['client'] = $clie->ApellidoNombre;
				unset($clie);
				// Buscar domicilio
				$domi = ClienteDomicilio::select('Direccion')->find($temp['iddomi']);
				$temp['direcc'] = $domi->Direccion;
				unset($domi);

				$movim[] = $temp;
				unset($temp);
			}
		}

		return $movim;
	}

	/**
	 * Agrego a direccion del cliente, los dispensers que posee
	 * 
	 * @param  integer $id 
	 * @param  string $direccion
	 * @return string
	 */
	private function _agregoListaDispensers( $id, $direccion )
	{
		$direccion = "(" . $direccion . ")";
		$dispensers = $this->utils->stringDispensersClie( $id );
		if ($dispensers !== '') {
			$direccion = $direccion . " - " . $dispensers;
		}

		return $direccion;
	}

}
