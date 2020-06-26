<?php

namespace App\Controllers\Ctasctes;

use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 *
 * Clase Informe Saldos a Fecha
 * 
 */
class InfoSaldosFechaController extends Controller
{
	/**
	 * Informe de Saldos a fecha (Pantalla principal)
	 * Name: 'ctasctes.infosaldosfecha'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function infoSaldosFecha($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");

		$datos = array('titulo'     => 'Cesarini - Info saldos fecha', 
					   'fechaHasta' => date('Y-m-d'),
					    );

		return $this->view->render($response, 'ctasctes/infosaldos/infosaldos.twig', $datos);
	}

	/**
	 * Arma listado del informe
	 * Name: 'ctasctes.infosaldosfecha.imprime'
	 *
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function imprime($request, $response)
	{
		// ?hasta=2019-09-30
		date_default_timezone_set("America/Buenos_Aires");
	    $fechaAct = date('d/m/Y');

	    $listado = $this->_armaListado($request->getParam('hasta'));

		$datos = [ 'titulo'    => 'Cesarini - Info saldos fecha',
				   'fechaAct'  => $fechaAct,
				   'fechaInfo' => $request->getParam('hasta'),
				   'listado'   => $listado ];

		return $this->view->render($response, 'ctasctes/infosaldos/imprimeinfosaldos.twig', $datos);
	}

	/**
	 * Arma listado de informe saldos
	 * 
	 * @param  date $fechaHasta
	 * @return array
	 */
	private function _armaListado($fechaHasta)
	{
		$clientes = Cliente::select('Id', 'ApellidoNombre', 'Direccion', 'Localidad', 'Telefono', 'Celular')
							->orderBy('ApellidoNombre')
							->get();
		$listado = [];

		foreach ($clientes as $value) {

			# Pido saldo en Utils
			$saldo = $this->utils->getSaldo( $value->Id, $fechaHasta );

			# Armo linea de listado
			$lin = [ 'Id'        => $value->Id, 
		             'Cliente'   => $value->ApellidoNombre,
		             'Direccion' => $value->Direccion,
		             'Localidad' => $value->Localidad,
		             'Telefono'  => $value->Telefono,
		             'Celular'   => $value->Celular,
		             'Saldo'     => $saldo ];
		    $listado[] = $lin;
		}

		return $listado;
	}

}
