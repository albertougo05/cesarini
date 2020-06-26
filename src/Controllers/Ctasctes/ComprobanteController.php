<?php

namespace App\Controllers\Ctasctes;

use App\Models\Comprobante;
use App\Models\ClienteDomicilio;
use App\Models\Empleado;

use App\Controllers\Controller;


/**
 *
 * Clase ComprobanteController
 * 
 */
class ComprobanteController extends Controller
{
	/**
	 * Comprobantes (Pantalla principal)
	 * Name: 'ctasctes.comprobante'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function comprobante($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");
	    $fecha_actual = date("Y-m-d");

	    $numComp = $this->_getNroComprobante('FA', 'B', 1);

		$datos = array('titulo'     => 'Cesarini - Comprobante', 
					   'fecha'      => $fecha_actual,
					   'tipoComp'   => 'B',
					   'sucursal'   => '1',
					   'numComp'    => $numComp );

		return $this->view->render($response, 'ctasctes/comprobantes/comprobante.twig', $datos);
	}

	/**
	 * Devuelve json con el número de comprobante
	 * Name: 'ctasctes.nrocomprobante'  (GET)
	 * 
	 * @param  Request  $request
	 * @param  Response $response 
	 * @return json
	 */
	public function numeroComprobante($request, $response)
	{
		$nrocomp = $this->_getNroComprobante( $request->getParam('comp'), 
			                                  $request->getParam('tipo'), 
			                                  $request->getParam('sucur') );

		return json_encode(["nrocomp" => $nrocomp]);
	}

	/**
	 * Genera comprobante con datos de pantalla
	 * Name: 'ctasctes.generacomprobante'  (POST)
	 * 
	 * @param  [type] $request  [description]
	 * @param  [type] $response [description]
	 * @return [type]           [description]
	 */
	public function generaComprobante($request, $response)
	{
		$status = 'Error';
		$data = json_decode($request->getParam('data'), true);
		// Convierto a int el string del número ' - 00000001'
		$nroComp = substr($data['nrocomp'], 3);
		$nroComp = intval($nroComp);

		// OJO ! Aquí debería buscar nuevamente el número de comprobante !!
		// (Si es multiusuario)

		$datos = [ 'Fecha'          => $data['fecha'],
				   'TipoForm'       => $data['tipform'], 
				   'Tipo'           => $data['tipo'], 
				   'Sucursal'       => (integer) $data['sucur'],
				   'NroComprobante' => $nroComp,
				   'IdCliente'      => $data['idclie'], 
				   'Firma'          => $data['cliente'], 
				   'Concepto'       => $data['concept'], 
				   'FechaDesde'     => $data['fecha'],
				   'FechaHasta'     => $data['fecha'],
				   'IdUsuario'      => $_SESSION['user'],
				   'Total'          => $this->utils->convStrToFloat( $data['importe'] ) ];

		$comp = Comprobante::insertGetId($datos);

		if ($comp > 0) {
			$status = 'OK';
		} 

		return json_encode(["status" => $status]);
	}

	/**
	 * Obtiene el número de comprobante según tipo y sucursal
	 * 
	 * @param  string $tipoForm (FA, ND, NC, RE)
	 * @param  string $tipo (A,B,C,X)
	 * @param  integer $suc
	 * @return string
	 */
	private function _getNroComprobante($tipoForm, $tipo, $suc)
	{
		$strNumero = "";
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

		$strNumero = strval($numComp);
		$strNumero = str_pad($strNumero, 8, "0", STR_PAD_LEFT);

		return $strNumero;
	}


}