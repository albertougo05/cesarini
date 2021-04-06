<?php

namespace App\Controllers\Repartos;

use App\Models\Cliente;
use App\Models\ClienteDomicilio;
use App\Models\VisitaSalidaProducto;
use App\Models\VisitaDetalleCliente;

use App\Controllers\Controller;


/**
 *
 * Clase VisitasSegunGuiaController
 * 
 */
class VisitasSegunGuiaController extends Controller
{

	/**
	 * Visitas según Guía de Reparto
	 * Name: 'repartos.visitassegunguia'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function visitasSegunGuia($request, $response)
	{
	    date_default_timezone_set("America/Buenos_Aires");
	    $listaGuiaRep = $this->RepartosBuscarGuiaController->listaParaBuscarGuiaRep('visitas');

		$datos = array( 'titulo'     => 'Cesarini - Listado Visitas',
						'fechaDesde' => date("Y-m-d",strtotime(date('Y-m-d')."- 1 month")),
			            'fechaHasta' => date('Y-m-d'),
			            'guiaReparto' => $listaGuiaRep );

		return $this->view->render($response, 'repartos/visitas/visitasSegunGuia.twig', $datos);
	}

	/**
	 * Arma el listado para impresion
	 * Name: 'repartos.visitassegunguia.imprime'
	 * 
	 * @param  Request  $request
	 * @param  Response $response
	 * @return view
	 */
	public function imprimeVSGR($request, $response)
	{
		// ?desde=2019-09-01&hasta=2019-09-30&idguia=8
		date_default_timezone_set("America/Buenos_Aires");
	    $fecha = date('d/m/Y');

		$listado = $this->_getListado( $request->getParam('desde'), 
									   $request->getParam('hasta'),
									   $request->getParam('idguia'),
									   $request->getParam('orden') );

		$periodo = $this->_getPeriodo($request->getParam('desde'), $request->getParam('hasta'));

		$datos = [ 'titulo'  => 'Cesarini - Visitas s/GR',
				   'fecha'   => $fecha,
				   'periodo' => $periodo,
				   'listado' => $listado ];

		return $this->view->render($response, 'repartos/visitas/imprimeVisitasSegunGuia.twig', $datos);
	}

	/**
	 * Busca el listado de visitas
	 * 
	 * @param  Request $req
	 * @return array
	 */
	private function _getListado($desde, $hasta, $idguia, $orden)
	{
		$sql = "SELECT vis.Fecha, vis.Id AS IdVisita, ";
		$sql = $sql . "vdc.IdCliente, cli.ApellidoNombre AS Cliente, ";
		$sql = $sql . "vdc.IdProducto, pro.Descripcion, ";
		$sql = $sql . "vdc.CantDejada AS Venta, vdc.CantRetira AS Envases, ";
		$sql = $sql . "vdc.Saldo, vdc.Entrega ";
		$sql = $sql . "FROM VisitaDetalleClientes AS vdc ";
		$sql = $sql . "LEFT JOIN Visitas AS vis ON vdc.IdVisita = vis.Id ";
		$sql = $sql . "LEFT JOIN Productos AS pro ON vdc.IdProducto = pro.Id ";
		$sql = $sql . "LEFT JOIN Clientes AS cli ON vdc.IdCliente = cli.Id ";
		$sql = $sql . "WHERE vis.IdGuiaReparto = " . $idguia;
		$sql = $sql . " AND vis.Fecha >= '".$desde."' AND vis.Fecha <= '".$hasta;

		if ($orden === 'fechaVis') {
			$sql = $sql . "' ORDER BY vis.Id, Cliente";
		} else {
			$sql = $sql . "' ORDER BY Cliente, vis.Fecha";
		}

		# Data del listado
		$list = $this->pdo->pdoQuery($sql);

		return $list;
	}

	/**
	 * Armo texto del periodo para informe
	 * 
	 * @param  string $desde
	 * @param  string $hasta
	 * @return string
	 */
	private function _getPeriodo($desde, $hasta)
	{
		# Si no hay hasta, es la fecha de hoy
		if ($hasta === '') {
			$hasta = date("d/m/Y");
		}
		$desde = date('d/m/Y', strtotime($desde));
		$hasta = date('d/m/Y', strtotime($hasta));
		$periodo = "desde $desde, hasta $hasta";

		return $periodo;
	}

}   # End clase
