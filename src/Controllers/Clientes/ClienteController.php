<?php 

namespace App\Controllers\Clientes;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\Actividad;
use App\Models\TipoFacturacion;
use App\Models\Dispenser;
use App\Models\MovimientoDispenser;
use App\Controllers\Controller;


/**
 * Url: /clientes
 */
class ClienteController extends Controller
{
	/**
	 * Datos del cliente
	 * Name: clientes.cliente
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return View 
	 */
	public function getCliente($request, $response)
	{
		$condFiscal  = array('Consumidor final', 'Exento', 'Monotributo', 'Resp. Inscripto');
		$actividades = Actividad::all();
		$tiposFact   = TipoFacturacion::all();

		$idClie = $request->getParam('Id');
		$otrosDom = false;
		$cantDom = 0;

		if (is_null($idClie) || $idClie === 0) {
			$accion = 'Nuevo';
			$cliente = [];

		} else {
			//$_array = Cliente::find($idClie)->toArray();
			$cliente = Cliente::find($idClie);  //->toArray();
			$accion = 'Modifica';
			// Cantidad domicilios
			$cantDom = ClienteDomicilio::where('IdCliente', $idClie)->count();
			if ($cantDom > 1) {
				// Si tiene otros domicilios, identifico el primero (idem datos Cliente)...
				$clienteDom = ClienteDomicilio::where('IdCliente', $idClie)->first();
				$idFirst = $clienteDom->Id;
				// Luego, recojo los siguientes, sin el primero...
				$otrosDom = ClienteDomicilio::where('Id', '>', $idFirst) 
											  ->where('IdCliente', $idClie)
										      ->get();
			}
		}

		$datos = array('titulo'      => 'Cesarini - Cliente',
			           'condFiscal'  => $condFiscal,
			           'actividades' => $actividades,
			           'tiposfact'   => $tiposFact,
			           'cliente'     => $cliente,
			           'otrosDom'    => $otrosDom,
			           'cantDom'     => $cantDom,
			           'accion'      => $accion);

		return $this->view->render($response, 'clientes/cliente.twig', $datos);
	}

	/**
	 * POST de Cliente - Crea un nuevo Cliente
	 * 
	 * @param  $request
	 * @param  $response
	 * @return Redirige /clientes/cliente (muestra cartel de cliente creado con éxito)
	 */
	public function postCliente($request, $response)
	{
		// Verificar costo del abono
		if ($request->getParam('CostoAbono') != '') {
			// Saco puntos de miles y cambio coma por punto decimal. Convierto a float.
	    	$costoAbono = (float) preg_replace(['/\./', '/,/'],['', '.'], $request->getParam('CostoAbono'));
		} else {
			$costoAbono = NULL;
		}

		$datos = array('ApellidoNombre'    => $request->getParam('ApellidoNombre'),
					   'NombreFantasia'    => $request->getParam('NombreFantasia'),
					   'Direccion'         => $request->getParam('Direccion'),
					   'Localidad'         => $request->getParam('Localidad'),
					   'Provincia'         => $request->getParam('Provincia'),
					   'CodPostal'         => $request->getParam('CodPostal'),
					   'Telefono'          => $request->getParam('Telefono'),
					   'Celular'           => $request->getParam('Celular'),
					   'CUIT'              => $request->getParam('CUIT'),
					   'CondicionFiscal'   => $request->getParam('CondicionFiscal'),
					   'Email'             => $request->getParam('Email'),
					   'Estado'            => $request->getParam('Estado'),
					   'NroContrato'       => $request->getParam('NroContrato'),
					   'FechaVencContrato' => ($request->getParam('FechaVencContrato') == '') ? null : $request->getParam('FechaVencContrato'),
					   'FechaAltaServicio' => ($request->getParam('FechaAltaServicio') == '') ? null : $request->getParam('FechaAltaServicio'),
					   'FechaFacturacion'  => ($request->getParam('FechaFacturacion') == '') ? null : $request->getParam('FechaFacturacion'),
					   'IdActividad'       => $request->getParam('IdActividad'),
					   'IdTipoFact'        => $request->getParam('IdTipoFact'),
					   'CostoAbono'        => $costoAbono );

		$datosDom = ['IdCliente' => (int) $request->getParam('Id'),
					 'Direccion' => $request->getParam('Direccion'),
					 'Localidad' => $request->getParam('Localidad'),
					 'Provincia' => $request->getParam('Provincia'),
					 'CodPostal' => $request->getParam('CodPostal'),
					 'Telefono'  => $request->getParam('Telefono'),
					 'Celular'   => $request->getParam('Celular'),
					 'Contacto'  => '' ];

		if (Cliente::where('Id', $request->getParam('Id'))->first()) {
			// Actualiza
			Cliente::where('Id', $request->getParam('Id'))
			       ->lockForUpdate()
			       ->update($datos);
			// Actualiza registro tabla ClientesDomiclilio
			// Busco id del primer registro...
			$clienteDom = ClienteDomicilio::where('IdCliente', $request->getParam('Id'))->first();
			$idFirst = $clienteDom->Id;
			// Actualizo ese registro
			ClienteDomicilio::where('Id', $idFirst)
			                ->lockForUpdate()
			                ->update($datosDom);

			$flash = 'Cliente actualizado con éxito !';

		} else {
			// Crea nuevo 
			$idCliente = Cliente::insertGetId($datos, 'Id');
			// Inserto el id en datos para ClienteDom...
			$datosDom['IdCliente'] = $idCliente;
			// Crea registro en tabla ClientesDomiclilio
			$clieDom = ClienteDomicilio::create($datosDom);

			$flash = 'Cliente creado con éxito !';
		}

		$this->flash->addMessage('info', $flash);

		return $response->withRedirect($this->router->pathFor('clientes.cliente'));
	}


	/**
	 * Post para OtrosDomicilios..
	 * Name: clientes.cliente.otrodomicilio
	 * 
	 * @param  $request 
	 * @param  $response
	 * @return View -> Redirect a clientes.cliente
	 */
	public function postOtroDomicilio($request, $response)
	{
		// Comprobamos si nos llega los datos por POST, 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        	$datosDom = ['IdCliente' => (int) $request->getParam('Id'),
					  'Direccion' => $request->getParam('Direccion2'),
					  'Localidad' => $request->getParam('Localidad2'),
					  'Provincia' => $request->getParam('Provincia2'),
					  'CodPostal' => $request->getParam('CodPostal2'),
					  'Telefono'  => $request->getParam('Telefono2'),
					  'Celular'   => $request->getParam('Celular2'),
					  'Contacto'  => $request->getParam('Contacto')
        	];

        	if ($request->getParam('IdDom') === '' ) {  // Si no se pasa el Id de dom editado, es nuevo dom

        		$clieDom = ClienteDomicilio::create($datosDom);
        		$flash = 'Otro domicilio creado con éxito !';
        	} else {
        		// Actualizo el registro
				ClienteDomicilio::where('Id',$request->getParam('IdDom'))
	                             ->lockForUpdate()
	                              ->update($datosDom);
	            $flash = 'Otro domicilio actualizado con éxito !';
        	}

			$this->flash->addMessage('info', $flash);

			return $response->withRedirect($this->router->pathFor('clientes.cliente')."?Id=".$request->getParam('Id'));

		} die('Error al enviar datos (not post');
	}

	/**
	 * Buscar cliente  
	 * 
	 * Url: /clientes/buscarcliente (clientes.buscarcliente)
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function buscarCliente($request, $response)
	{
		$clientes = Cliente::where('Estado', 'Activo')
							->orderBy('ApellidoNombre', 'asc')
							->get();  # Trae todos los clientes ordenado alfabeticamente
		$datos = [ 'titulo'   => 'Cesarini - Buscar Cliente',
			       'clientes' => $clientes ];

		return $this->view->render($response, 'clientes/buscarCliente.twig', $datos);
	}

	/**
	 * Marca el cliente como dado de Baja
	 * 
	 * @return redirecciona a pagina nueva de cliente
	 */
	public function eliminarCliente($request, $response)
	{
		$cliente = Cliente::find( $request->getParam('Id') );
		$cliente->Estado = 'Baja';
		$cliente->save();
		//ClienteDomicilio::where('IdCliente', $request->getParam('Id'))->delete();

		$this->flash->addMessage('info', "Cliente eliminado !");

		return $response->withRedirect($this->router->pathFor('clientes.cliente'));
	}

	/**
	 * Elimina otro domicilio
	 * 
	 * @return redirecciona a pagina de cliente ya seleccionado
	 */
	public function eliminarDomicilio($request, $response)
	{
		ClienteDomicilio::where('Id', $request->getParam('IdDom'))->delete();

		$this->flash->addMessage('info', "Domicilio eliminado !");

		return $response->withRedirect($this->router->pathFor('clientes.cliente')."?Id=".$request->getParam('Id'));
	}

	/**
	 * Informe de clientes  (Sirve para imprimir)
	 * Url: clientes/informe (Name: clientes.informe)
	 * 
	 * @param  Request $request
	 * @param  Response $response
	 * @return View       Informe
	 */
	public function informe($request, $response)
	{
		$tipoList = $request->getParam('list');
		$imprime  = $request->getParam('print');

		if (is_null($tipoList) || $tipoList == 1) {
			$accion = 'Alfabético';
			$urlParam = '?list=1';
			$listado = Cliente::where('Estado', 'Activo')
								->orderBy('ApellidoNombre', 'asc')
			                    ->get();  # Alfabético
		} elseif ($tipoList == '2') {
			$accion = 'Por Localidad';
			$urlParam = '?list=2';
			$listado = Cliente::where('Estado', 'Activo')
								->orderBy('Localidad', 'asc')
			                    ->orderBy('ApellidoNombre', 'asc')
			                    ->get();  # Por Localidad
		} elseif ($tipoList == '3') {
			$accion = 'Con dispenser';
			$urlParam = '?list=3';
			$listado = $this->_clientesConDisp();   # con Dispenser

		} elseif ($tipoList == '4') {
			$accion = 'Con abono';
			$urlParam = '?list=4';
			$listado = $this->_clientesConAbono();   # con Abono
		} 

		$datos = [ 'titulo'   => 'Cesarini - Informe clientes',
				   'accion'   => $accion,
				   'urlParam' => $urlParam,
				   'tipoList' => $tipoList,
				   'listado'  => $listado ];

		if ($imprime)  {
			# Si 'print' es true.
			return $this->view->render($response, 'clientes/imprime.twig', $datos);
		}

		return $this->view->render($response, 'clientes/informe.twig', $datos);		
	}


	/**
	 * Devuelva lista de clientes con dispenser
	 * 
	 * @return array
	 */
	private function _clientesConDisp()
	{
		// Dispenser en clientes
		$listDispEnClie = Dispenser::select('Id', 'NroInterno', 'Modelo')->where('Estado', 'Cliente')->get();
		foreach ($listDispEnClie as $value) {
			$arrDispEnClie[] = $value->Id;
			$arrModeloDisp[] = $value->NroInterno.'-'.$value->Modelo;
		}

		// Index arrModeloDisp
		$idx = 0;

		// Id Cliente en movim. dispenser
		foreach ($arrDispEnClie as $value) {
			// Buscar id dispenser, estado igual a cliente, ordenando por id de mov desc, para tener el último movimiento primero
			// (SELECT * FROM `MovimientosDispenser` WHERE `IdDispenser` = 11 AND `Estado` = 'Cliente' ORDER BY `Id` DESC )
			$idClieMovDisp = MovimientoDispenser::select('IdDispenser', 'Fecha', 'IdCliente', 'IdDomicilio')
												->where('IdDispenser', $value)
												->where('Estado', 'Cliente')
												->orderBy('Id', 'desc')
												->first();
			$arrTemp = [ 'IdDispenser' => $idClieMovDisp->IdDispenser,
						 'Modelo'      => $arrModeloDisp[$idx],
						 'Fecha'       => $idClieMovDisp->Fecha,
						 'IdCliente'   => $idClieMovDisp->IdCliente,
						 'IdDomicilio' => $idClieMovDisp->IdDomicilio ];
			$arrIdsClieConDisp[] = $arrTemp;
			$idx++;
		}

		foreach ($arrIdsClieConDisp as $value) {
			// Datos cliente
			$cliente = Cliente::find($value['IdCliente']);
			$domicilio = ClienteDomicilio::find($value['IdDomicilio']);
			// Armo array
			$arrClie = [ 'IdCliente'      => $cliente->Id,
						 'ApellidoNombre' => $cliente->ApellidoNombre,
						 'Fantasia'       => $cliente->NombreFantasia,
						 'Direccion'      => $domicilio->Direccion,
						 'Localidad'      => $domicilio->Localidad,
						 'CodPostal'      => $domicilio->CodPostal,
						 'IdDispenser'    => $value['IdDispenser'],
						 'Modelo'         => $value['Modelo'],
						 'Fecha'          => $value['Fecha'] ];
			$clientesConDisp[] = $arrClie;
		}

		// Ordeno por ApellidoNombre
		usort($clientesConDisp, array($this,'_ordenArray'));

		return $clientesConDisp;
	}

	/**
	 * Comprobar si existe cliente con ese cuit/dni
	 * Name: cliente.comprobarcuitdni
	 * 
	 * @param  $request  [description]
	 * @param  $response [description]
	 * @return json
	 */
	public function comprobarCuitDni($request, $response)
	{
		$cuitDni = $request->getParam('CUIT');

		return json_encode( ['existe' => Cliente::where('CUIT', $cuitDni)->count() === 1] );
	}

	/**
	 * Funcion para ordenar array multidimensional
	 *
	 * @return integer (0, 1 or -1)
	 */
	private function _ordenArray($a, $b)
	{
		if ($a == $b)
            return 0;

        return ($a['ApellidoNombre'] < $b['ApellidoNombre']) ? -1 : 1;
	}

	/**
	 * Informe de clientes con abono y dispenser
	 * 
	 * @return [type] [description]
	 */
    private function _clientesConAbono()
    {
		$clientes = Cliente::where([ ['CostoAbono', '>', 0], ['Estado', '=', 'Activo'] ])
						   ->orderBy('ApellidoNombre', 'asc')
			               ->get();
	    $idx = 0;

		// Agrego los dispenser de cada cliente
		foreach ($clientes as $value) {
			// String de dispensers
			$dispens = $this->utils->stringDispensersClie( $value->Id );

			$clientes[$idx]['Dispensers'] = $dispens;
			$idx++;
		}

		return $clientes;
    }


}
