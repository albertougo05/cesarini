<?php

namespace App\Controllers\Repartos;

use App\Models\Actividad;
use App\Models\Empleado;
use App\Models\Cliente;
use App\Models\GuiaReparto;
use App\Models\ClienteDomicilio;
use App\Models\ClienteReparto;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\ProductoClienteReparto;

use App\Controllers\Controller;


/**
 * 
 * Clase RepartosController
 * 
 */
class RepartosController extends Controller
{
	/**
	 * Muestra datos Guia de Reparto
	 * Name: repartos.guiareparto
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Guia de Reparto
	 */
	public function getGuiaReparto($request, $response)
	{
		$idGuia  = $request->getParam('idguia');    // Recibe parametros opcionales por GET ...
		$accion  = '';			 # Nombre de la acción del menú
		$showMsg = '';

		// Si no hay parametro de guia, ni la session tiene id - INICIO
		if ($idGuia == null) {

			if ($request->getParam('msg') == 'guardado') {
				$showMsg = 'Guía de Reparto guardada con éxito !!';
			}else if ($request->getParam('msg') == 'guiaBorrada') {
				$showMsg = 'Guía de Reparto eliminada !!';
			}

			$idGuia = $this->_buscarIdNuevaGuia();    // Buscar nuevo id de guia. El último de la tabla mas 1
			$accion = 'Nueva';

			$_SESSION['dataGuia']       = []; // Reset array con datos de Guia de Reparto
			$_SESSION['listaClie']      = [];  // Idem lista clientes
			$_SESSION['cliProductos']   = [];  // Idem lista de productos de clientes
			$_SESSION['guiaModificada'] = false;

			$visitasCliente = [];    # Lista vacia de visitas
			$listaTotalReparto = [];  # Lista vacia de productos reparto

			// Data vacio para nueva Guia de reparto
			$dataGuiaReparto = [
				'Id'          => $idGuia, 'DiaSemana' => '0', 'Turno' => '0', 
				'IdEmpleado'  => '0', 'IdActividad' => '0', 'HoraSalida' => '',
				'HoraRetorno' => '', 'Estado' => 'Vigente'
			];

		} else { 

			$accion = 'Modificar';

			if ($request->getParam('msg') == 'clieBorrado') {
				$showMsg = 'Cliente eliminado con éxito !!';
			}

			// Buscar datos de guia de reparto
			$dataGuiaReparto = $this->_buscarDataGuiaReparto($idGuia);
			// Buscar lista de clientes
			$visitasCliente = $this->_buscarClieTablaVisitas( $idGuia, 
															  $request->getParam('idclie'), 
															  $request->getParam('orden'),
															  $request->getParam('iddomi') );
			// Buscar productos de cada cliente
			$listaTotalReparto = $this->_armarListaTotalReparto($idGuia); // Armo lista del total de productos del reparto
		} 

		// La session lleva el id de guia de reparto
		$_SESSION['idguiarep'] = $idGuia;
		$repartidores = $this->_dataEmpleadosAlfa();

		$datos = [ 'titulo'         => 'Cesarini - Guia reparto',
				   'idguia'         => $idGuia,
				   'accion'         => $accion,
				   'hayMensaje'     => $showMsg,
				   'data'           => $dataGuiaReparto,
				   'repartidores'   => $repartidores,
				   'visitasCliente' => $visitasCliente,
				   'totalReparto'   => $listaTotalReparto ];

		return $this->view->render($response, 'repartos/guiadereparto/guiareparto.twig', $datos);
	}

	/**
	 * Recargar página reordenando lista de clientes a visitar
	 * Name: repartos.guiareparto.reordenarvisitas
	 *
	 * @param  Request $request
	 * @param  Response $response
	 * @return View Guia de Reparto
	 */
	public function reordenarVisitas($request, $response)
	{
		$accion = 'Modificar';
		$idGuia  = $request->getParam('idguia');
		$dataGuiaReparto   = $this->_buscarDataGuiaReparto($idGuia);
		$repartidores      = $this->_dataEmpleadosAlfa();
		$listaTotalReparto = $this->_armarListaTotalReparto($idGuia);
		// Reordenar la lista de visitas
		$visitasCliente = $this->_reordenarClieTablaVisitas($request->getParam('idclie'), $request->getParam('orden'));

		$datos = [ 'titulo'        => 'Cesarini - Guia reparto',
					'idguia'       => $idGuia,
					'accion'       => $accion,
					'hayMensaje'   => '',
					'data'         => $dataGuiaReparto,
					'repartidores' => $repartidores,
					'totalReparto' => $listaTotalReparto,
					'visitasCliente' => $visitasCliente ];

		return $response->withRedirect($this->router->pathFor('repartos.guiareparto')."?idguia=".$idGuia."&reorden=1");
	}

	/**
	 * Guardar Guia de Reparto (POST)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return Redireciono...
	 */
	public function postGuiaReparto($request, $response)
	{
		$idGuia = $request->getParam('idGuia');

		//Cargo los datos en la session 'dataGuia'
		$this->_cargoData_a_Session($request);
		// Guardo los campos en el registro de la BD
		$guiaRep = GuiaReparto::updateOrInsert(['Id' => $idGuia], $_SESSION['dataGuia']);
		// Ver si listaClie está vacio o tiene clientes anotados
		if ( !empty($_SESSION['listaClie'])) {
			// Guardar lista de clientes a visitar
			$this->_guardarVisitasClientes($idGuia);
		} 

		if (!empty($_SESSION['cliProductos'])) {
			// Guardar lista de productos de clientes
			$this->_guardarProdsClientes($idGuia);
		}

		// (NO) Redirecciono a misma Guia de Reparto para poner cartel creado con exito
		//return $response->withRedirect($this->router->pathFor('repartos.guiareparto')."?idguia=".$idGuia."&msg=guardado");
		// Vuelvo a GR vacia con mensaje
		return $response->withRedirect($this->router->pathFor('repartos.guiareparto')."?msg=guardado");
	}

	/**
	 * Borrar cliente de la lista de visitas
	 * Name: repartos.guiareparto.borrarcliente
	 * 
	 * @param  [type] $request  [description]
	 * @param  [type] $response [description]
	 * @return redirecciona 
	 */
	public function borrarCliente($request, $response)
	{
		$idClie = $request->getParam('idclie');
		$idDom  = $request->getParam('iddom');
		$idGuia = $_SESSION['idguiarep'];
		$idx = 0;		// Indice para borrrar cliente en $_SESSION['listaClie']

		// Loop para buscar el indice dentro de 'listaClie'
		foreach ($_SESSION['listaClie'] as $value) {

			if ( $value['idCliente'] == $idClie && $value['idDomicilio'] == $idDom ) {
				// Al encontrar el indice salgo del loop
				$indice = $value['id'];
				break;
			}
			$idx++;
		}
		// Poner true el campo 'borrado'
		$_SESSION['listaClie'][$idx]['borrado'] = true;
		// Pongo en 0 los productos, si es que tiene...
		if (isset($_SESSION['cliProductos'][$idx])) {

			foreach ($_SESSION['cliProductos'][$idx] as $key => $value) {
				$_SESSION['cliProductos'][$idx][$key] = 0;
			}
		}

		return $response->withRedirect($this->router->pathFor('repartos.guiareparto')."?idguia=".$idGuia."&msg=clieBorrado");
	}

	/**
	 * Eliminar (borrar) por COMPLETO la actual guia de  reparto
	 *
	 * 
	 * >>>  OPCION SACADA DEL MENU (NO PODEMOS ELIMINAR LOS DATOS DE GUIA DE REPARTO) !!
	 *
	 * 
	 * @param  Request  $request  [description]
	 * @param  Response $response [description]
	 * @return redirecciona a una nueva guia de rep
	 */
	public function eliminarGuiaReparto($request, $response)
	{
		$idGuia = $_SESSION['idguiarep'];

		// Borra guia
		GuiaReparto::where(['Id' => $idGuia])->delete();

		// Borra clientes
		ClienteReparto::where(['IdReparto' => $idGuia])->delete();

		// Borrar productos de clientes en la guia
		ProductoClienteReparto::where(['IdReparto' => $idGuia])->delete();

		// Elimina id de la session
		$_SESSION['idguiarep'] = null;
		$_SESSION['listaClie'] = null;
		$_SESSION['cliProductos'] = null;

		return $response->withRedirect($this->router->pathFor('repartos.guiareparto')."?msg=guiaBorrada");
	}

	/**
	 * Imprimir la actual guia de reparto
	 * 
	 * @param  Request  $request 
	 * @param  Response $response 
	 * @return redirecciona a una nueva guia de rep
	 */
	public function imprimirGuiaReparto($request, $response)
	{
		$idGuia = $_SESSION['idguiarep'];
		$idCliente = null;
		$accion = 'Imprimir Guia de Reparto';
		$showMsg = '';

		// Buscar datos de guia de reparto
		$dataGuiaReparto = $this->_buscarDataGuiaReparto($idGuia);
		$empleado = Empleado::find($dataGuiaReparto->IdEmpleado);
		$nombreEmpleado = $empleado->ApellidoNombre;
		$actividad = Actividad::find($dataGuiaReparto->IdActividad);
		$descActividad = $actividad->Descripcion;
		// Buscar lista de clientes
		$visitasCliente = $this->_buscarClieTablaVisitas($idGuia);
		// Buscar productos de cada cliente
		$listaTotalReparto = $this->_armarListaTotalReparto($idGuia); // Armo lista del total de productos del reparto

		// La session lleva el id de guia de reparto
		$idGuia = $_SESSION['idguiarep'];
		//$repartidores = $this->_dataEmpleadosAlfa();

		$datos = [
			'titulo'       => 'Cesarini - Imprimir Guia',
			'idguia'       => $idGuia,
			'data'         => $dataGuiaReparto,
			'nomEmpleado'  => $nombreEmpleado,
			'actividad'    => $descActividad,
			'visitasCliente' => $visitasCliente,
			'totalReparto' => $listaTotalReparto, 
		];

		return $this->view->render($response, 'repartos/guiadereparto/imprimirguia.twig', $datos);
	}



	/**
	 * Devuelve lista de empleados ordenados alfab
	 * @return array Lista de empleados
	 */
	private function _dataEmpleadosAlfa()
	{
		$empleados = $this->EmpleadosController->listaEmpleadosActivos();
		$arrEmp = [];

		foreach ($empleados as $value) {
			$arrTemp = ['id'        => $value->Id,
						'nombre'    => $value->ApellidoNombre,
					    'domicilio' => $value->Domicilio,
					    'localidad' => $value->Localidad,
		               ];
		    $arrEmp[] = $arrTemp;
		}

		return $arrEmp;
	}

	/**
	 * Busca datos de guia de reparto segun id
	 * 
	 * @param  integer $idGuia 
	 * @return array   Datos de tabla GuiaRepartos
	 */
	private function _buscarDataGuiaReparto($idGuia)
	{

		if (empty($_SESSION['dataGuia'])) {

			$data = GuiaReparto::find($idGuia);

		} else {

			$data = $_SESSION['dataGuia'];
		}

		return $data;
	}


	/**
	 * Buscar nuevo id para guia
	 * 
	 * @return integer
	 */
	private function _buscarIdNuevaGuia()
	{
		// Busco el máximo de Id y ese es el último
		$lastId = GuiaReparto::max('Id');

		return $lastId + 1;
	}

	private function _cargoData_a_Session($request)
	{
		$_SESSION['dataGuia'] = [
			'Id'          => $request->getParam('idGuia'),
			'DiaSemana'   => $request->getParam('diaSemana'),
			'Turno'       => $request->getParam('turno'),
			'IdEmpleado'  => $request->getParam('idEmpleado'),
			'IdActividad' => $request->getParam('idActividad'),
			'HoraSalida'  => $request->getParam('horaSalida'),
			'HoraRetorno' => $request->getParam('horaRetorno'),
			'Estado'      => $request->getParam('estado'),
		];		
	}

	/**
	 * Confecciona la lista de clientes a visitar
	 * 
	 * @param  [int] $idCliente
	 * @param  [int] $orden
	 * @return [type]            [description]
	 */
	private function _agregarClieTablaVisitas($idClie, $orden, $iddom)
	{
		$dataCliente = Cliente::find($idClie);
		//$dataDomicilio = ClienteDomicilio::where('IdCliente', $idClie)->first();
		$dataDomicilio = ClienteDomicilio::find($iddom);

		// Cuenta - Apellido Nombre - domicilio - localidad - celular
		$arrClie = array( 'id' => 0,
						  'idCliente'   => $dataCliente->Id,
                          'apellidoNom' => $dataCliente->ApellidoNombre,
                          'domicilio'   => $dataDomicilio->Direccion,
                          'localidad'   => $dataDomicilio->Localidad,
                          'celular'     => $dataDomicilio->Celular,
                          'idDomicilio' => $dataDomicilio->Id,
                          'ordenVisita' => (float) $orden,
                          'borrado'     => false );

		if (isset($_SESSION['listaClie'])) {
			// Si ya existe el array en session, el id será el último a agregar
			$arrClie['id'] = count( $_SESSION['listaClie'] );
			array_push( $_SESSION['listaClie'], $arrClie );
			$arrListaClientes = $_SESSION['listaClie'];

		} else {

			$_SESSION['listaClie'] = [];
			array_push($_SESSION['listaClie'], $arrClie);
			$arrListaClientes = $_SESSION['listaClie'];
		}

		// Ordena la lista por OrdenVisita
		usort($arrListaClientes, array($this, '_comparar'));

		// Reenumerar Orden de visita
		$arrListaClientes = $this->_reenumeraOrdenVisita($arrListaClientes);

		return $arrListaClientes;
	}

	/**
	 * Para ordenar array multidimension de clientes
	 * 
	 * @param  string $a
	 * @param  string $b 
	 * @return string
	 */
	private function _comparar($a, $b)
	{
	    if ($a["ordenVisita"] == $b["ordenVisita"]) {
	        return 0;
	    }

	    return ($a["ordenVisita"] < $b["ordenVisita"]) ? -1 : 1;

		//return strcmp($a["ordenVisita"], $b["ordenVisita"]);
		//return $a["ordenVisita"] - $b["ordenVisita"];
	}


	/**
	 * Reenumera en orden de visita de lista de clientes
	 * 
	 * @param  array $listaClies
	 * @return array
	 */
	private function _reenumeraOrdenVisita($listaClies)
	{
		$i = 1;
		foreach ($listaClies as $key => $value) {
			$listaClies[$key]['ordenVisita'] = $i;
			$i++;
		}

		return $listaClies;
	}

	/**
	 * Guardar lista de clientes a visitar en la guia (Distinto de v28)
	 * 
	 * @param  integer $idGuia Id de la Guia de Repartos
	 * @return void
	 */
	private function _guardarVisitasClientes($idGuia)
	{
		// Borro todos los registros de la Guia de Reparto
		$cant = ClienteReparto::where('IdReparto', $idGuia)->delete();

		foreach ($_SESSION['listaClie'] as $value) {

			if ( !$value['borrado'] ) {		// Si el cliente no ha sido borrado

				$dataCli = array( 'IdReparto'         => $idGuia, 
					              'IdCliente'          => $value['idCliente'],
					              'IdClienteDomicilio' => $value['idDomicilio'],
					              'OrdenVisita'        => $value['ordenVisita'] );
				// guardo registro en la tabla
				$data = ClienteReparto::insert( $dataCli );
			}
		}
	}

	/**
	 * Guardar lista de productos de cada cliente (Distinto de v28)
	 * 
	 * @param  integer $idGuia
	 * @return void
	 */
	private function _guardarProdsClientes($idGuia)
	{
		// Borro lo del disco y cargar lo actual !!
		ProductoClienteReparto::where( 'IdReparto', $idGuia )->delete();

		foreach ($_SESSION['cliProductos'] as $key => $prods) {
			// Busco el $key en los clientes. Para saber el cliente...
			$idArrCli = $this->_idDelcliente($key);

			foreach ($prods as $key => $cant) {

				if ($cant > 0) {		# Si la cantidad es cero, el cliente ha sido borrado

					$idProducto = intval(mb_substr($key, 2, 2));		# tipo de codigo en $key = id01tp1

					$dataProd = array('IdReparto'    => (integer) $idGuia, 
						              'IdCliente'    => (integer) $_SESSION['listaClie'][$idArrCli]['idCliente'],
						              'IdDomicilio'  => (integer) $_SESSION['listaClie'][$idArrCli]['idDomicilio'],
						              'IdProducto'   => (integer) $idProducto,
					    	          'CantSugerida' => (integer) $cant);
					// guardo registro en la tabla
					$data = ProductoClienteReparto::insert( $dataProd );
				}
			}
		}		
	}

	private function _idDelcliente($clave)
	{
		foreach ($_SESSION['listaClie'] as $key => $value) {
			if ( $value['id'] == $clave ) {
			    $id = $key;
			    break;
			}
		}

		return $id;
	}

	/**
	 * Buscar los clientes de la tabla visitas
	 * 
	 * @param  integer $idGuia
	 * @return array Lista de clientes a visitar
	 */
	private function _buscarClieTablaVisitas($idGuia, $idCliente = null, $orden = null, $idDom = null)
	{
		// Si está vacia la lista busco lo guardado en disco
		if (empty($_SESSION['listaClie']) && $idCliente == null) {

			$arrListaClientes = [];
			// Obtengo los registros de la tabla
			$arrClieVisitas = ClienteReparto::where('IdReparto', $idGuia)
											->orderBy('OrdenVisita')
											->get();
			$idx = 0;

			// Armo la lista para presentar en pantalla
			foreach ($arrClieVisitas as $value) {

				$dataCliente = Cliente::find((integer) $value->IdCliente);
				//$dataCliente = Cliente::where('Id', $value->IdCliente);
				$dataDomicil = ClienteDomicilio::find($value->IdClienteDomicilio);

				// ('id') Identifica al cliente en la lista
				$arrClie = array( 'id'          => $idx,
								  'idCliente'   => $dataCliente->Id,
	                              'apellidoNom' => $dataCliente->ApellidoNombre,
	                              'domicilio'   => $dataDomicil->Direccion,
	                              'localidad'   => $dataDomicil->Localidad,
	                              'celular'     => $dataDomicil->Celular,
	                              'idDomicilio' => $dataDomicil->Id, 
	                         	  'ordenVisita' => $value->OrdenVisita,
	                         	  'borrado'     => false );
				$arrListaClientes[] = $arrClie;
				$idx++;
			}

			$_SESSION['listaClie'] = $arrListaClientes;

		} else {

			if ($idCliente != null ) {
				// Cargo visitas a clientes de la guia de reparto si hay nuevo cliente...
				$arrListaClientes  = $this->_agregarClieTablaVisitas($idCliente, $orden, $idDom);
				$_SESSION['listaClie'] = $arrListaClientes;

			} else {
				// Si no hay nuevo cliente y hay lista de visitas a cliente devuelvo la que está ya cargada en session
				$arrListaClientes = $_SESSION['listaClie'];
			}
		}

		return $arrListaClientes;
	}


	/**
	 * Arma la lista de totales de productos de la guia con los datos de $_SESSION['cliProductos']
	 *
	 * Arma array con [codcli] => array(3) { ["codprod"]=> int(1) ["descrip"]=> string(34) "Agua Desmineralizada - Bidón x 10" ["cantid"]=> string(1) "1" } 
	 * 
	 * Es para presentar los datos en pantalla
	 * 
	 * @param int id guia de reparto
	 * @param int id cliente
	 * @return array [description]
	 */
	private function _armarListaTotalReparto($idGuia)
	{
		$arrLista = [];
		$arrListaCliProductos = [];

		// La lista está vacia, busco si hay productos en el disco...
		if (empty($_SESSION['cliProductos'])) {

			if (empty($_SESSION['listaClie'])) {

				// si no hay lista de clientes, retorno falso
				return false;

			} else {
				// print_r($_SESSION['listaClie']);
				// Array ( [0] => Array ( [id] => 0 [idCliente] => 3 [apellidoNom] => AGUERO Maximiliano [domicilio] => Neuquen 555 - Barrio La Florida Norte [localidad] => Jesús María [celular] => [idDomicilio] => 3 [ordenVisita] => 1 ) [1] => Array ( [id] => 1 [idCliente] => 4 [apellidoNom] => CANALE Sandra Isabel [domicilio] => La Toma 456 [localidad] => Jesús María [celular] => [idDomicilio] => 4 [ordenVisita] => 2 ) ) 

				// Si hay clientes. Loop por cada cliente...
				foreach ($_SESSION['listaClie'] as $value) {

					$id = $value['id'];
					// Busco los productos del cliente
					$dataProCli = ProductoClienteReparto::where( [ 'IdReparto'   => $idGuia, 
																   'IdCliente'   => $value['idCliente'],
																   'IdDomicilio' => $value['idDomicilio'] ]
																)->get();
					if ( $dataProCli->count() === 0 ) {
						# Si nada devuelve, sigue el loop..
						continue;

					} else {
						# Si hay productos...
						$cantDeProducto = [];    // Reset array para productos

						foreach ($dataProCli as $prod) {

							# Armo la lista para $_SESSION['cliProductos']
							$idProd = $prod->IdProducto;
							# Busco el tipo de producto...
							$idTipoProd = Producto::find($prod->IdProducto)->IdTipoProducto;
							# Armo el codigo del producto para $_SESSION['cliProductos']
							$codigoProd = "id".sprintf("%'.02d", $idProd)."tp".$idTipoProd;

							$cantDeProducto[$codigoProd] = $prod->CantSugerida;
						}

						$arrListaCliProductos[$id] = $cantDeProducto;
					}
				}

				$_SESSION['cliProductos'] = $arrListaCliProductos;

			}

		} //else { 

			// loop por cada cliente
			foreach ($_SESSION['cliProductos'] as $value) {
				// indice para cada producto
				$idx = 0;
				// loop por cada producto de cliente
				foreach ($value as $key => $cant) {
					// Obtengo el entero del codigo de producto
					$codProd = intval(mb_substr($key, 2, 2));
					$descProd = Producto::find($codProd)->Descripcion;

					// id01tp1 - Obtengo el entero del Id Tipo Producto
					$idTipoProd = intval(mb_substr($key, 6, 1));
					$descTipoProd = TipoProducto::find($idTipoProd)->Descripcion;

					// Si codProd ya está en el array...
					if (array_key_exists($codProd, $arrLista)) {
						// Sumo a la cant que ya está, la cantidad nueva
						$sumaCant = $cant + $arrLista[$codProd]['cantid'];
						$arrLista[$codProd] = array('codprod' => $codProd,
											    'descrip' => $descTipoProd.' - '.$descProd, 
											    'cantid' => $sumaCant);
					} else {
						// Si no está en el aray, lo agrego...
						$arrLista[$codProd] = array('codprod' => $codProd,
											'descrip' => $descTipoProd.' - '.$descProd, 
											'cantid' => $cant);
					}
				}

				$idx++;
			}
		//}

//var_dump($arrLista);
//die();

		return $arrLista;
	}

	/**
	 * Reordena la lista de visitas cuando cambia el orden de un cliente
	 * 
	 * @param  int $idclie
	 * @param  int $orden
	 * @return view redirecciona 
	 */
	private function _reordenarClieTablaVisitas($idclie, $orden)
	{
		$arrListaClientes = $_SESSION['listaClie'];

		// Busco el indice del cliente
		foreach ($arrListaClientes as $key => $value) {
			if ($value['idCliente'] == $idclie) {
				$indexCli = $key;
				break;
			}
		}
		// Asigno el nuevo orden al id de cliente pasado
		//$arrListaClientes[$indexCli]['ordenVisita'] = (integer) $orden;
		$arrListaClientes[$indexCli]['ordenVisita'] = (float) $orden;

		// reordeno array
		usort($arrListaClientes, array($this, '_comparar'));

		// Reenumerar orden de visita
		$arrListaClientes = $this->_reenumeraOrdenVisita($arrListaClientes);

		// Lo paso a la session
		$_SESSION['listaClie'] = $arrListaClientes;

		return $arrListaClientes;
	}



}
