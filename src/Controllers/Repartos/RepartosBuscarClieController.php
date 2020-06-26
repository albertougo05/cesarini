<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;

use App\Controllers\Controller;


/**
 * Url: '/repartos/buscarcliente'
 * 
 * Name: 'repartos.buscarcliente'
 * 
 */
class RepartosBuscarClieController extends Controller
{
	/**
	 * Buscar cliente para Guia de Reparto
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function buscarCliente($request, $response)
	{
		$idGuia = $request->getParam('idguia'); # Parametro pasado por GET
		$clientes = $this->_dataClientesAlfa();  # Trae todos los clientes ordenado alfabeticamente
		$clientesEnGuia = $_SESSION['listaClie'];

		$datos = [ 'titulo'   => 'Cesarini - Buscar Cliente',
				   'idGuia'   => $idGuia,
				   'clientes' => $clientes,
				   'cliesEnGuia' => $clientesEnGuia ];

		return $this->view->render($response, 'repartos/guiadereparto/buscarClienteReparto.twig', $datos);
	}

	/**
	 * Devuelve todos los clientes ordenados alfabet
	 *
	 * @return array con lista de clientes
	 */
	private function _dataClientesAlfa()
	{
		$clientes = Cliente::where('Estado', 'Activo')
							->orderBy('ApellidoNombre', 'asc')
							->get();
		$arrEmp = [];
		foreach ($clientes as $valueCli) {
			// Busco cuantos id domicilios tiene...
			$domicilios = ClienteDomicilio::where('IdCliente', $valueCli->Id)->get();

			foreach ($domicilios as $valueDom) {

				$arrTemp = ['id'        => $valueCli->Id,
							'nombre'    => $valueCli->ApellidoNombre,
							'fantasia'  => $valueCli->NombreFantasia,
							'iddomicil' => $valueDom->Id,
						    'direccion' => $valueDom->Direccion,
						    'localidad' => $valueDom->Localidad ];
			    $arrEmp[] = $arrTemp;
			}
		}

		return $arrEmp;
	}

}
